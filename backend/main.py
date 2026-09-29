import os
import urllib.parse
from fastapi import FastAPI, WebSocket, WebSocketDisconnect, HTTPException, Query
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import httpx
import json
import pymysql

# Import local pipeline modules
from pipeline import (
    enhance_image, run_groq_vision_ocr, search_medicines_vector, 
    translate_text, init_vector_db, GROQ_API_URL, GROQ_API_KEY
)

app = FastAPI(title="MediScan AI 2.0 Backend Core")

# CORS middleware for local frontend connectivity
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Database Connection Helper (Direct MySQL PyMySQL)
DB_HOST = "127.0.0.1"
DB_PORT = 3306
DB_USER = "root"
DB_PASSWORD = ""
DB_NAME = "mediscan_db"

def get_db_connection():
    return pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASSWORD,
        database=DB_NAME,
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True
    )

# Cache / Startup: Load all medicines in vector DB
@app.on_event("startup")
def startup_event():
    print("[STARTUP] Starting FastAPI backend core...")
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT medicine_name FROM medicines")
            rows = cursor.fetchall()
            med_names = [row['medicine_name'] for row in rows]
            
        conn.close()
        # Initialize FAISS with all medicine names
        init_vector_db(med_names)
    except Exception as e:
        print(f"[WARNING] Failed to load medicines into FAISS: {e}. FAISS will run on demand or use string matcher.")

# Pydantic schemas
class SearchQuery(BaseModel):
    query: str
    limit: int = 5

class TranslationRequest(BaseModel):
    text: str
    target_language: str

# REST API Endpoint: Health check
@app.get("/api/health")
def health():
    return {"status": "ok", "engine": "FastAPI AI Core"}

# REST API Endpoint: Translate Text
@app.post("/api/translate")
def translate(req: TranslationRequest):
    try:
        translated = translate_text(req.text, req.target_language)
        return {"success": True, "translated_text": translated}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# REST API Endpoint: Vector Search Validation
@app.post("/api/validate")
def validate_medicine(req: SearchQuery):
    try:
        results = search_medicines_vector(req.query, req.limit)
        return {"success": True, "matches": results}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


# ═══════════ WEBSOCKET: REAL-TIME SCAN PIPELINE ═══════════
@app.websocket("/ws/scan")
async def websocket_scan(websocket: WebSocket, prescription_id: int = Query(...), image_path: str = Query(...), language: str = Query("English")):
    await websocket.accept()
    
    conn = get_db_connection()
    try:
        # Decode path
        image_path_decoded = urllib.parse.unquote(image_path)
        
        # 1. Update DB to 'processing'
        with conn.cursor() as cursor:
            cursor.execute(
                "UPDATE prescriptions SET status = 'processing' WHERE id = %s", 
                (prescription_id,)
            )
            
        # 2. Stage: Enhancing (OpenCV CLAHE / Rotation Correction)
        await websocket.send_json({"status": "enhancing"})
        enhanced_path = image_path_decoded.replace(".", "_enhanced.")
        enhancement_result = enhance_image(image_path_decoded, enhanced_path)
        
        # If enhancement succeeded, use the enhanced path; else fallback to original
        path_to_ocr = enhanced_path if enhancement_result.get("success", False) else image_path_decoded

        # 3. Stage: Extracting (Groq Vision OCR & Structure parsing)
        await websocket.send_json({"status": "extracting"})
        ocr_response = run_groq_vision_ocr(path_to_ocr, language)
        
        if "error" in ocr_response:
            raise ValueError(ocr_response["error"])

        # 4. Stage: Validating (FAISS Similarity Search & Database Matching)
        await websocket.send_json({"status": "validating"})
        
        # Fuzzy match extracted medicine names using FAISS vector search
        validated_medicines = []
        raw_confidence_sum = 0.0
        med_count = 0
        
        for med in ocr_response.get("medicines", []):
            med_count += 1
            query_name = med.get("name", "")
            matches = search_medicines_vector(query_name, top_k=1)
            
            # If we find a close match in the database, update name and fetch composition details
            if matches and matches[0]["confidence"] >= 80.0:
                match_name = matches[0]["medicine_name"]
                raw_confidence_sum += matches[0]["confidence"]
                
                # Retrieve compositions / warnings from MySQL database
                with conn.cursor() as cursor:
                    cursor.execute(
                        "SELECT generic_name, uses, side_effects, warnings, pregnancy_safety, category FROM medicines WHERE medicine_name = %s", 
                        (match_name,)
                    )
                    med_data = cursor.fetchone()
                
                if med_data:
                    med["name"] = match_name
                    med["composition"] = med_data["generic_name"] or med["composition"]
                    med["purpose"] = med_data["uses"] or med["purpose"]
                    med["side_effects"] = med_data["side_effects"] or med["side_effects"]
                    med["warnings"] = med_data["warnings"] or med["warnings"]
                    med["pregnancy_safety"] = med_data["pregnancy_safety"] or med["pregnancy_safety"]
                    med["category"] = med_data["category"]
            else:
                # Low confidence or match not found in database
                raw_confidence_sum += 60.0 # Standard fallback confidence
                med["category"] = "General"
                
        # Calculate average confidence score
        avg_confidence = round(raw_confidence_sum / max(1, med_count), 1)
        ocr_response["confidence_score"] = min(100.0, max(avg_confidence, ocr_response.get("confidence_score", 90.0)))
        
        # 5. Save results to Database
        raw_text_extracted = ocr_response.get("raw_text", "")
        extracted_json_str = json.dumps(ocr_response)
        
        with conn.cursor() as cursor:
            # Check if result already exists to prevent duplicate rows
            cursor.execute("SELECT id FROM prescription_results WHERE prescription_id = %s", (prescription_id,))
            exists = cursor.fetchone()
            
            if exists:
                cursor.execute(
                    "UPDATE prescription_results SET raw_ocr_text = %s, extracted_json = %s, confidence_score = %s, language = %s WHERE prescription_id = %s",
                    (raw_text_extracted, extracted_json_str, ocr_response["confidence_score"], language, prescription_id)
                )
            else:
                cursor.execute(
                    "INSERT INTO prescription_results (prescription_id, raw_ocr_text, extracted_json, confidence_score, language, created_at, updated_at) VALUES (%s, %s, %s, %s, %s, NOW(), NOW())",
                    (prescription_id, raw_text_extracted, extracted_json_str, ocr_response["confidence_score"], language)
                )
                
            # Update Prescription to completed
            cursor.execute(
                "UPDATE prescriptions SET status = 'completed' WHERE id = %s",
                (prescription_id,)
            )

        # 6. Push complete status
        await websocket.send_json({"status": "completed"})

    except Exception as e:
        print(f"[ERROR] WS Scan error: {e}")
        try:
            with conn.cursor() as cursor:
                cursor.execute("UPDATE prescriptions SET status = 'failed' WHERE id = %s", (prescription_id,))
        except Exception:
            pass
        await websocket.send_json({"status": "failed", "error": str(e)})
    finally:
        conn.close()
        await websocket.close()


# ═══════════ WEBSOCKET: AI HEALTHCARE CHAT ASSISTANT ═══════════
@app.websocket("/ws/chat")
async def websocket_chat(websocket: WebSocket, user_id: int = Query(...)):
    await websocket.accept()
    
    conn = get_db_connection()
    try:
        while True:
            # Wait for client query
            data = await websocket.receive_text()
            payload = json.loads(data)
            message = payload.get("message", "").strip()
            
            if not message:
                continue
                
            # 1. Validate Credits (requires 15 credits)
            with conn.cursor() as cursor:
                cursor.execute("SELECT credits FROM users WHERE id = %s", (user_id,))
                user = cursor.fetchone()
                
            if not user or user["credits"] < 15:
                await websocket.send_json({"status": "error", "message": "Insufficient credits! You need 15 credits to chat."})
                continue
                
            # 2. Get past chat history to provide context (Last 5 exchanges)
            with conn.cursor() as cursor:
                cursor.execute(
                    "SELECT message, response FROM chat_histories WHERE user_id = %s ORDER BY id DESC LIMIT 5",
                    (user_id,)
                )
                history_rows = cursor.fetchall()
            
            # Format history for LLM
            messages = [
                {"role": "system", "content": "You are MediScan AI assistant - a professional, empathetic medical assistant. Answer patient queries about medicines, side effects, and warning parameters. NEVER provide diagnostic conclusions. Always include a short reminder disclaimer: 'This is an AI guide. Please verify details with your physician.'"}
            ]
            
            # Append history (reverse order to keep chronologically correct)
            for row in reversed(history_rows):
                messages.append({"role": "user", "content": row["message"]})
                if row["response"]:
                    messages.append({"role": "assistant", "content": row["response"]})
                    
            messages.append({"role": "user", "content": message})

            # 3. Request Streaming completions from Groq
            headers = {
                "Authorization": f"Bearer {GROQ_API_KEY}",
                "Content-Type": "application/json"
            }
            
            llm_payload = {
                "model": "llama3-8b-8192", # Lightweight and fast model for chat
                "messages": messages,
                "temperature": 0.5,
                "stream": True
            }
            
            full_response = ""
            
            async with httpx.AsyncClient(timeout=30.0) as client:
                async with client.stream("POST", GROQ_API_URL, json=llm_payload, headers=headers) as response:
                    if response.status_code != 200:
                        raise ValueError(f"Groq API returned error status {response.status_code}")
                        
                    async for line in response.aiter_lines():
                        if not line:
                            continue
                            
                        # Clean stream prefix
                        if line.startswith("data: "):
                            line = line[6:]
                        if line.strip() == "[DONE]":
                            break
                            
                        try:
                            chunk = json.loads(line)
                            delta = chunk["choices"][0]["delta"].get("content", "")
                            if delta:
                                full_response += delta
                                # Send chunk to browser
                                await websocket.send_json({"status": "chunk", "text": delta})
                        except Exception:
                            continue
                            
            # 4. Deduct 15 credits from user & Log Transaction
            with conn.cursor() as cursor:
                cursor.execute("UPDATE users SET credits = credits - 15 WHERE id = %s", (user_id,))
                
                # Log transaction
                cursor.execute(
                    "INSERT INTO credit_transactions (user_id, amount, action_type, description, created_at, updated_at) VALUES (%s, -15, 'chat', 'Consumed 15 credits for AI assistant chat query', NOW(), NOW())",
                    (user_id,)
                )
                
                # Save chat log in database
                cursor.execute(
                    "INSERT INTO chat_histories (user_id, message, response, tokens_used, created_at, updated_at) VALUES (%s, %s, %s, %s, NOW(), NOW())",
                    (user_id, message, full_response, len(full_response)//4) # Rough token estimate
                )
                
            # 5. Send Completed status
            await websocket.send_json({"status": "completed"})

    except WebSocketDisconnect:
        print(f"[DISCONNECT] WS Chat disconnected for user {user_id}")
    except Exception as e:
        print(f"[ERROR] WS Chat error: {e}")
        try:
            await websocket.send_json({"status": "error", "message": str(e)})
        except Exception:
            pass
    finally:
        conn.close()
