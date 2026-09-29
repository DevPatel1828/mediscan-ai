import unittest
import sys
import os

# Add parent directory to path so we can import modules
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from converter import translate_abbreviations, parse_prescription_line, convert_to_human_readable
from pipeline import search_medicines_vector, init_vector_db

class TestBackendProcessing(unittest.TestCase):

    def test_translate_abbreviations(self):
        # Basic abbreviations
        self.assertEqual(translate_abbreviations("Tab Azithral"), "Tablet Azithral")
        self.assertEqual(translate_abbreviations("Cap Amoxil PO BD"), "Capsule Amoxil by mouth twice daily")
        self.assertEqual(translate_abbreviations("Syr Limcee TDS PC"), "Syrup Limcee three times daily after meals")
        
        # Duration abbreviations
        self.assertEqual(translate_abbreviations("x 5 Days"), "for 5 Days")
        self.assertEqual(translate_abbreviations("x5 Days"), "for 5 Days")
        self.assertEqual(translate_abbreviations("x 10 day"), "for 10 day")

    def test_parse_prescription_line(self):
        # Single line parsing
        res1 = parse_prescription_line("Rx Tab Azithral 500mg OD x 5 Days")
        self.assertIsNotNone(res1)
        self.assertEqual(res1["medicine_name"], "Azithral")
        self.assertEqual(res1["type"], "Tablet")
        self.assertEqual(res1["dosage"], "500mg")
        self.assertEqual(res1["frequency"], "OD")
        self.assertEqual(res1["duration"], "5 Days")
        self.assertEqual(res1["instructions"], "Take one daily")

        # Line without some fields
        res2 = parse_prescription_line("Cap Amoxil 250 BD")
        self.assertIsNotNone(res2)
        self.assertEqual(res2["medicine_name"], "Amoxil")
        self.assertEqual(res2["type"], "Capsule")
        self.assertEqual(res2["dosage"], "250mg")
        self.assertEqual(res2["frequency"], "BD")
        self.assertEqual(res2["instructions"], "Take twice daily")

        # Line with timing (AC)
        res3 = parse_prescription_line("Syr Limcee 10ml TDS AC x 3 Days")
        self.assertIsNotNone(res3)
        self.assertEqual(res3["medicine_name"], "Limcee")
        self.assertEqual(res3["type"], "Syrup")
        self.assertEqual(res3["dosage"], "10ml")
        self.assertEqual(res3["frequency"], "TDS")
        self.assertEqual(res3["duration"], "3 Days")
        self.assertEqual(res3["instructions"], "Take three times daily, before meals")

    def test_convert_to_human_readable(self):
        ocr_text = "Rx Tab Azithral 500mg OD x 5 Days\nCap Amoxil 250 BD\n"
        results = convert_to_human_readable(ocr_text)
        self.assertEqual(len(results), 2)
        self.assertEqual(results[0]["medicine_name"], "Azithral")
        self.assertEqual(results[1]["medicine_name"], "Amoxil")

    def test_fuzzy_search_medicines(self):
        # Initialize a mock medicine list
        meds = ["Aspirin 75mg", "Warfarin 5mg", "Azithral 500mg", "Crocin 650mg", "Metformin 500mg"]
        init_vector_db(meds)
        
        # Test exact match
        matches = search_medicines_vector("Aspirin 75mg", top_k=1)
        self.assertTrue(len(matches) > 0)
        self.assertEqual(matches[0]["medicine_name"], "Aspirin 75mg")
        self.assertTrue(matches[0]["confidence"] >= 90.0)

        # Test fuzzy match
        matches_fuzzy = search_medicines_vector("Azithral", top_k=1)
        self.assertTrue(len(matches_fuzzy) > 0)
        self.assertEqual(matches_fuzzy[0]["medicine_name"], "Azithral 500mg")
        self.assertTrue(matches_fuzzy[0]["confidence"] >= 70.0)

if __name__ == '__main__':
    unittest.main()
