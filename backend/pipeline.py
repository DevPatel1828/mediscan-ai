import os
import cv2
import numpy as np
from PIL import Image
import httpx
import json
import base64
import difflib

# Try importing ML libraries
try:
    import joblib
    from rapidfuzz import fuzz, process
    HAS_ML = True
except ImportError:
    HAS_ML = False

# API configuration
GROQ_API_URL = "https://api.groq.com/openai/v1/chat/completions"
GROQ_VISION_MODEL = "meta-llama/llama-4-scout-17b-16e-instruct"
GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")

MODEL_CACHE_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "model_cache", "medicine_search_model.joblib")

# Global ML model artifacts
trained_model = None
indexed_medicines = []

def init_vector_db(medicines_list: list = None):
    """Loads the pre-trained vector model or initializes a fallback index."""
    global trained_model, indexed_medicines
    
    # 1. Check if trained model artifact exists
    if os.path.exists(MODEL_CACHE_PATH):
        try:
            print(f"[STARTUP] Loading trained medicine model from {MODEL_CACHE_PATH}...")
            trained_model = joblib.load(MODEL_CACHE_PATH)
            indexed_medicines = trained_model.get("medicine_names", [])
            total = len(indexed_medicines)
            print(f"[SUCCESS] Trained neural medicine model loaded with {total:,} medicines.")
            return
        except Exception as e:
            print(f"[WARNING] Could not load serialized model: {e}. Reverting to runtime index.")
            
    # 2. Fallback runtime initialization
    if medicines_list:
        indexed_medicines = list(medicines_list)
        print(f"[INFO] Initialized runtime fallback index with {len(indexed_medicines)} medicines.")

def search_medicines_vector(query: str, top_k: int = 5) -> list:
    """Searches across 250k+ medicines using trained TF-IDF vectorizer + Nearest Neighbors + RapidFuzz re-ranking."""
    if not query:
        return []
        
    query_clean = str(query).strip()
    
    # Method 1: Using trained ML model if available
    if trained_model is not None:
        try:
            vectorizer = trained_model.get("vectorizer")
            nn_model = trained_model.get("nn_model")
            med_names = trained_model.get("medicine_names", [])
            records = trained_model.get("medicine_records", [])
            
            # Vector transform & nearest neighbors search
            query_vec = vectorizer.transform([query_clean.lower()])
            n_search = min(top_k * 4, 30)
            distances, indices = nn_model.kneighbors(query_vec, n_neighbors=n_search)
            
            candidates = []
            for dist, idx in zip(distances[0], indices[0]):
                if idx < len(med_names):
                    med_name = med_names[idx]
                    rec = records[idx] if idx < len(records) else {}
                    
                    # Compute hybrid score combining cosine similarity and token sort ratio
                    vector_conf = max(0.0, (1.0 - float(dist)) * 100.0)
                    fuzzy_ratio = fuzz.token_sort_ratio(query_clean.lower(), med_name.lower())
                    partial_ratio = fuzz.partial_ratio(query_clean.lower(), med_name.lower())
                    
                    # High confidence weighting
                    final_conf = round(0.45 * vector_conf + 0.35 * fuzzy_ratio + 0.20 * partial_ratio, 1)
                    
                    candidates.append({
                        "medicine_name": med_name,
                        "composition": rec.get("composition", ""),
                        "manufacturer": rec.get("manufacturer", ""),
                        "confidence": min(100.0, max(0.0, final_conf))
                    })
                    
            candidates.sort(key=lambda x: x["confidence"], reverse=True)
            return candidates[:top_k]
        except Exception as e:
            print(f"[WARNING] Trained model search error: {e}")
            
    # Method 2: RapidFuzz fallback if model is not loaded
    if HAS_ML and indexed_medicines:
        try:
            matches = process.extract(
                query_clean,
                indexed_medicines,
                scorer=fuzz.token_sort_ratio,
                limit=top_k
            )
            return [{"medicine_name": m[0], "confidence": round(float(m[1]), 1)} for m in matches]
        except Exception as e:
            print(f"[WARNING] RapidFuzz search error: {e}")
            
    # Method 3: difflib standard fallback
    matches = difflib.get_close_matches(query_clean, indexed_medicines, n=top_k, cutoff=0.25)
    results = []
    for match in matches:
        seq = difflib.SequenceMatcher(None, query_clean.lower(), match.lower())
        results.append({
            "medicine_name": match,
            "confidence": round(seq.ratio() * 100, 1)
        })
    return results

# OpenCV image enhancement
def detect_blur(image) -> tuple:
    """Calculates Laplace variance to check if the image is blurry."""
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    variance = cv2.Laplacian(gray, cv2.CV_64F).var()
    is_blurry = bool(variance < 80.0)
    return is_blurry, round(float(variance), 2)

def enhance_image(image_path: str, save_path: str) -> dict:
    """Applies noise reduction, CLAHE, and deskewing to a prescription image."""
    try:
        img = cv2.imread(image_path)
        if img is None:
            raise ValueError("Could not read image file.")
            
        is_blurry, blur_var = detect_blur(img)
        
        # 1. Grayscale
        gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
        
        # 2. De-noising
        denoised = cv2.fastNlMeansDenoising(gray, h=10, templateWindowSize=7, searchWindowSize=21)
        
        # 3. CLAHE (Contrast Limited Adaptive Histogram Equalization)
        clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
        enhanced_gray = clahe.apply(denoised)
        
        # 4. Deskewing (rotation correction)
        coords = np.column_stack(np.where(enhanced_gray > 0))
        angle = 0.0
        if len(coords) > 0:
            angle = cv2.minAreaRect(coords)[-1]
            if angle < -45:
                angle = -(90 + angle)
            else:
                angle = -angle
                
        # Rotate image if skew is significant (> 1 degree)
        h, w = img.shape[:2]
        if abs(angle) > 1.0 and abs(angle) < 45.0:
            center = (w // 2, h // 2)
            M = cv2.getRotationMatrix2D(center, angle, 1.0)
            enhanced = cv2.warpAffine(enhanced_gray, M, (w, h), flags=cv2.INTER_CUBIC, borderMode=cv2.BORDER_REPLICATE)
        else:
            enhanced = enhanced_gray
            
        cv2.imwrite(save_path, enhanced)
        
        return {
            "success": True,
            "blur_variance": blur_var,
            "is_blurry": is_blurry,
            "skew_angle": round(float(angle), 2),
            "output_path": save_path
        }
    except Exception as e:
        return {"success": False, "error": str(e)}

# Local OCR using EasyOCR
def run_easyocr(image_path: str) -> str:
    """Runs local EasyOCR text recognition."""
    try:
        import easyocr
        reader = easyocr.Reader(['en'], gpu=True if (HAS_ML and cv2.cuda.getCudaEnabledDeviceCount() > 0) else False)
        result = reader.readtext(image_path, detail=0)
        return "\n".join(result)
    except Exception as e:
        print(f"[WARNING] EasyOCR run failed: {e}. Falling back to default parser.")
        return ""

# Groq Vision OCR
def run_groq_vision_ocr(image_path: str, language: str = "English") -> dict:
    """Calls Groq Vision API to perform high-fidelity OCR, handwriting recognition, and medicine extraction."""
    try:
        # Read image and convert to base64
        with open(image_path, "rb") as image_file:
            base64_image = base64.b64encode(image_file.read()).decode("utf-8")
            
        prompt = f"""You are MediScan AI — a professional medical prescription analysis assistant.
Task: Analyze this doctor's handwritten prescription or medical report.
Translate everything into {language}.

Extract the following in structured JSON output format:
{{
  "raw_text": "Insert the complete transcriped text here",
  "medicines": [
     {{
       "name": "Medicine name with strength",
       "composition": "Generic/chemical composition",
       "purpose": "What disease/condition this treats",
       "dosage": "dosage instructions (e.g. 500mg, 1 tablet, 5ml)",
       "frequency": "Frequency code (e.g. OD, BD, PRN)",
       "duration": "Duration (e.g. 5 Days)",
       "instructions": "Simple patient instructions in {language}"
     }}
  ],
  "doctor_instructions": "General doctor guidance or warning in {language}",
  "confidence_score": 92.5
}}
Return ONLY the JSON. No markdown backticks, no wrap text, just the raw JSON object."""

        payload = {
            "model": GROQ_VISION_MODEL,
            "messages": [
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "image_url",
                            "image_url": {
                                "url": f"data:image/png;base64,{base64_image}"
                            }
                        },
                        {
                            "type": "text",
                            "text": prompt
                        }
                    ]
                }
            ],
            "response_format": {"type": "json_object"},
            "max_tokens": 2048,
            "temperature": 0.2,
        }
        
        headers = {
            "Authorization": f"Bearer {GROQ_API_KEY}",
            "Content-Type": "application/json"
        }
        
        with httpx.Client(timeout=60.0) as client:
            response = client.post(GROQ_API_URL, json=payload, headers=headers)
            
        if response.status_code != 200:
            return {"error": f"Groq API returned error {response.status_code}: {response.text}"}
            
        result = response.json()
        content = result["choices"][0]["message"]["content"]
        
        parsed_result = json.loads(content)
        return parsed_result
        
    except Exception as e:
        return {"error": str(e)}

# Multi-language translation using deep-translator or LLM
def translate_text(text: str, target_lang: str) -> str:
    """Translates medical analysis text into targeted Indian or international language."""
    if not text or target_lang.lower() == "english":
        return text
        
    try:
        from deep_translator import GoogleTranslator
        # Map target languages to ISO codes
        lang_codes = {
            "hindi": "hi", "gujarati": "gu", "marathi": "mr", "tamil": "ta",
            "telugu": "te", "bengali": "bn", "kannada": "kn", "punjabi": "pa",
            "malayalam": "ml", "urdu": "ur", "spanish": "es", "french": "fr",
            "german": "de", "arabic": "ar", "chinese": "zh-CN", "japanese": "ja",
            "russian": "ru", "portuguese": "pt"
        }
        code = lang_codes.get(target_lang.lower(), "en")
        if code == "en":
            return text
            
        translated = GoogleTranslator(source='auto', target=code).translate(text)
        return translated
    except Exception as e:
        print(f"[WARNING] Local translation failed: {e}. Falling back to LLM translation.")
        # Fallback to Groq API
        try:
            payload = {
                "model": "llama3-8b-8192",
                "messages": [
                    {"role": "system", "content": f"You are a professional medical translator. Translate the given text exactly into {target_lang}. Preserve markdown headings and structure."},
                    {"role": "user", "content": text}
                ],
                "temperature": 0.3
            }
            headers = {"Authorization": f"Bearer {GROQ_API_KEY}", "Content-Type": "application/json"}
            with httpx.Client(timeout=30.0) as client:
                response = client.post(GROQ_API_URL, json=payload, headers=headers)
            if response.status_code == 200:
                return response.json()["choices"][0]["message"]["content"]
        except Exception as ex:
            print(f"[WARNING] LLM translation fallback failed: {ex}")
        return text
