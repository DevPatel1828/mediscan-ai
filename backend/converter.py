import re

# Dictionaries for medical abbreviations
ABBREVIATIONS = {
    # Form
    r'\bTab\b': 'Tablet',
    r'\bTabs\b': 'Tablets',
    r'\bCap\b': 'Capsule',
    r'\bCaps\b': 'Capsules',
    r'\bSyr\b': 'Syrup',
    r'\bInj\b': 'Injection',
    r'\bOint\b': 'Ointment',
    r'\bSusp\b': 'Suspension',
    
    # Route
    r'\bPO\b': 'by mouth',
    
    # Frequency / Timing
    r'\bOD\b': 'once daily',
    r'\bQD\b': 'once daily',
    r'\bBD\b': 'twice daily',
    r'\bBID\b': 'twice daily',
    r'\bTDS\b': 'three times daily',
    r'\bTID\b': 'three times daily',
    r'\bQID\b': 'four times daily',
    r'\bQ4H\b': 'every 4 hours',
    r'\bQ6H\b': 'every 6 hours',
    r'\bHS\b': 'at bedtime (night)',
    r'\bPRN\b': 'as needed',
    r'\bAC\b': 'before meals',
    r'\bPC\b': 'after meals',
    r'\bSTAT\b': 'immediately',
    
    # Duration / Measurement
    r'\bmg\b': 'mg',
    r'\bmcg\b': 'mcg',
    r'\bml\b': 'ml',
}

def translate_abbreviations(text: str) -> str:
    """Replaces common medical abbreviations in text with full English terms."""
    processed = text
    for abbr, full in ABBREVIATIONS.items():
        processed = re.sub(abbr, full, processed, flags=re.IGNORECASE)
    
    # Convert duration shorthand like 'x 5 Days' or 'x5 Days'
    processed = re.sub(r'\bx\s*(\d+)\s*(days|day|weeks|week|months|month)\b', r'for \1 \2', processed, flags=re.IGNORECASE)
    return processed

def parse_prescription_line(line: str) -> dict:
    """
    Parses a single line of prescription text using rule-based regex.
    Example input: 'Rx Tab Azithral 500 OD x 5 Days'
    """
    line_clean = line.strip()
    if not line_clean:
        return None
        
    # Remove Rx prefix if present
    line_clean = re.sub(r'^Rx:?\s*', '', line_clean, flags=re.IGNORECASE)
    
    # Detect Form (Tab/Cap/Syr etc)
    form = "Medicine"
    for f_pattern, f_val in [
        (r'\b(Tab|Tablet)s?\b', 'Tablet'),
        (r'\b(Cap|Capsule)s?\b', 'Capsule'),
        (r'\bSyr(up)?\b', 'Syrup'),
        (r'\bInj(ection)?\b', 'Injection'),
        (r'\bSusp(ension)?\b', 'Suspension')
    ]:
        if re.search(f_pattern, line_clean, re.IGNORECASE):
            form = f_val
            line_clean = re.sub(f_pattern, '', line_clean, flags=re.IGNORECASE).strip()
            break
            
    # Detect Duration
    duration = ""
    dur_match = re.search(r'\b(?:x|for)?\s*(\d+)\s*(days|day|weeks|week|months|month)\b', line_clean, re.IGNORECASE)
    if dur_match:
        duration = f"{dur_match.group(1)} {dur_match.group(2)}"
        line_clean = re.sub(r'\b(?:x|for)?\s*' + dur_match.group(1) + r'\s*' + dur_match.group(2) + r'\b', '', line_clean, flags=re.IGNORECASE).strip()
        
    # Detect Frequency
    frequency = ""
    freq_desc = ""
    for pattern, desc in [
        (r'\b(OD|QD)\b', 'Take one daily'),
        (r'\b(BD|BID)\b', 'Take twice daily'),
        (r'\b(TDS|TID)\b', 'Take three times daily'),
        (r'\bQID\b', 'Take four times daily'),
        (r'\bHS\b', 'Take at bedtime'),
        (r'\bPRN\b', 'Take as needed')
    ]:
        if re.search(pattern, line_clean, re.IGNORECASE):
            frequency = pattern.replace(r'\b', '').replace('(', '').replace(')', '').split('|')[0]
            freq_desc = desc
            line_clean = re.sub(pattern, '', line_clean, flags=re.IGNORECASE).strip()
            break
            
    # Detect timing (AC/PC)
    timing = ""
    for pattern, desc in [
        (r'\bAC\b', 'before meals'),
        (r'\bPC\b', 'after meals')
    ]:
        if re.search(pattern, line_clean, re.IGNORECASE):
            timing = desc
            line_clean = re.sub(pattern, '', line_clean, flags=re.IGNORECASE).strip()
            break

    # Clean up trailing spaces or commas in remaining text which represents the name and dosage
    name_and_dosage = re.sub(r'\s+', ' ', line_clean).strip(', ')
    
    # Try to split name and strength/dosage (e.g. 'Azithral 500' -> 'Azithral', '500mg')
    strength = ""
    strength_match = re.search(r'\b(\d+\s*(?:mg|mcg|ml|g|tablets|capsules)?)\b', name_and_dosage, re.IGNORECASE)
    medicine_name = name_and_dosage
    if strength_match:
        strength = strength_match.group(1)
        # Ensure it has unit
        if not re.search(r'[a-zA-Z]', strength):
            strength += "mg"
        medicine_name = name_and_dosage.replace(strength_match.group(1), '').strip()
        
    if not medicine_name:
        return None
        
    instructions = []
    if freq_desc:
        instructions.append(freq_desc)
    if timing:
        instructions.append(timing)
        
    return {
        "medicine_name": medicine_name,
        "type": form,
        "dosage": strength or "As directed",
        "frequency": frequency or "N/A",
        "duration": duration or "As directed",
        "instructions": ", ".join(instructions) if instructions else "Take as directed by doctor"
    }

def convert_to_human_readable(ocr_text: str) -> list:
    """Parses multiline OCR text into structured human-readable prescription items."""
    lines = ocr_text.split('\n')
    results = []
    for line in lines:
        parsed = parse_prescription_line(line)
        if parsed:
            results.append(parsed)
    return results
