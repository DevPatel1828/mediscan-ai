import os
import sys
import json
import pymysql

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from pipeline import enhance_image, run_groq_vision_ocr, search_medicines_vector, init_vector_db

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

def test_integration():
    print("--- STARTING INTEGRATION TEST ---")
    
    # 1. Paths
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    image_path = os.path.join(base_dir, "storage", "app", "public", "prescriptions", "test_prescription.png")
    enhanced_path = os.path.join(base_dir, "storage", "app", "public", "prescriptions", "test_prescription_enhanced.png")
    
    if not os.path.exists(image_path):
        print(f"[FAIL] Error: Test prescription image not found at {image_path}")
        return
        
    print(f"[OK] Found test prescription image: {image_path}")

    # 2. Test OpenCV Enhancement
    print("\nStep 1: Enhancing image with OpenCV...")
    enhance_res = enhance_image(image_path, enhanced_path)
    print("Enhancement Result:", json.dumps(enhance_res, indent=2))
    
    if not enhance_res.get("success", False):
        print("[FAIL] OpenCV image enhancement failed!")
        return
    print("[OK] OpenCV image enhancement succeeded. Output saved.")

    # 3. Initialize FAISS using DB medicines
    print("\nStep 2: Initializing FAISS with database medicines...")
    try:
        conn = get_db_connection()
        with conn.cursor() as cursor:
            cursor.execute("SELECT medicine_name FROM medicines")
            rows = cursor.fetchall()
            med_names = [row['medicine_name'] for row in rows]
        conn.close()
        init_vector_db(med_names)
        print(f"[OK] FAISS initialized with {len(med_names)} medicines.")
    except Exception as e:
        print(f"[WARNING] FAISS initialization failed: {e}. Using fallback fuzzy matcher.")

    # 4. Test Groq Vision OCR
    print("\nStep 3: Running Groq Vision OCR (calling Groq API)...")
    ocr_res = run_groq_vision_ocr(enhanced_path, "English")
    print("OCR API Result:", json.dumps(ocr_res, indent=2))
    
    if "error" in ocr_res:
        print(f"[FAIL] Groq Vision OCR failed: {ocr_res['error']}")
        return
        
    print("[OK] Groq Vision OCR succeeded.")

    # 5. Test FAISS Vector Search / Database Matching
    print("\nStep 4: Validating extracted medicines...")
    for med in ocr_res.get("medicines", []):
        name = med.get("name", "")
        matches = search_medicines_vector(name, top_k=1)
        print(f"Query Name: '{name}' -> Matches: {matches}")
        
    print("\n--- INTEGRATION TEST COMPLETED SUCCESSFULLY ---")

if __name__ == "__main__":
    test_integration()
