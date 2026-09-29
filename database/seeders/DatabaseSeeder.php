<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            ['id' => 1, 'name' => 'super_admin', 'description' => 'Super Administrator'],
            ['id' => 2, 'name' => 'admin', 'description' => 'System Administrator'],
            ['id' => 3, 'name' => 'premium_user', 'description' => 'Premium Tier User'],
            ['id' => 4, 'name' => 'user', 'description' => 'Standard Tier User'],
        ];
        DB::table('roles')->insert($roles);

        // 2. Seed Permissions
        $permissions = [
            ['id' => 1, 'name' => 'scan_prescriptions', 'description' => 'Scan doctor prescriptions'],
            ['id' => 2, 'name' => 'search_medicines', 'description' => 'Search medicine explorer'],
            ['id' => 3, 'name' => 'translate_results', 'description' => 'Translate prescription results'],
            ['id' => 4, 'name' => 'generate_voice', 'description' => 'Generate TTS voice files'],
            ['id' => 5, 'name' => 'use_ai_assistant', 'description' => 'Use AI chat health assistant'],
            ['id' => 6, 'name' => 'export_reports', 'description' => 'Export reports as PDF/CSV/Excel'],
            ['id' => 7, 'name' => 'manage_users', 'description' => 'Manage system users'],
            ['id' => 8, 'name' => 'manage_medicines', 'description' => 'Manage medicine database'],
            ['id' => 9, 'name' => 'view_analytics', 'description' => 'View admin dashboard analytics'],
        ];
        DB::table('permissions')->insert($permissions);

        // 3. Map Permissions to Roles
        // Admin Roles map (roles 1 and 2 get all)
        for ($p = 1; $p <= 9; $p++) {
            DB::table('permission_role')->insert(['role_id' => 1, 'permission_id' => $p]);
            DB::table('permission_role')->insert(['role_id' => 2, 'permission_id' => $p]);
        }
        // Premium User maps (1,2,3,4,5,6)
        for ($p = 1; $p <= 6; $p++) {
            DB::table('permission_role')->insert(['role_id' => 3, 'permission_id' => $p]);
        }
        // Standard User maps (1,2,3,4,5,6)
        for ($p = 1; $p <= 6; $p++) {
            DB::table('permission_role')->insert(['role_id' => 4, 'permission_id' => $p]);
        }

        // 4. Create Super Admin User
        $admin = User::create([
            'name' => 'MediScan Admin',
            'email' => 'admin@mediscan.com',
            'password' => Hash::make('Password123'),
            'credits' => 5000,
            'mobile_number' => '+1234567890',
            'email_verified_at' => now(),
        ]);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => 1]);

        // 5. Create Regular User
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'user@mediscan.com',
            'password' => Hash::make('Password123'),
            'credits' => 5000,
            'mobile_number' => '+1987654321',
            'email_verified_at' => now(),
        ]);
        DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => 4]);

        // 6. Seed Medicines (10 detailed records)
        $medicines = [
            [
                'id' => 1,
                'medicine_name' => 'Aspirin 75mg',
                'generic_name' => 'Acetylsalicylic Acid',
                'brand_name' => 'Ecotrin 75',
                'composition' => 'Aspirin 75mg',
                'uses' => 'Prevention of cardiovascular events (heart attacks, strokes), management of mild to moderate pain, inflammation, and fever reduction.',
                'dosage' => 'Cardiovascular prevention: 75mg to 150mg once daily. Pain relief: 325mg to 650mg every 4-6 hours as needed. Take with food or water.',
                'side_effects' => 'Heartburn, stomach upset, nausea, increased bleeding tendencies (bruising, nosebleeds), and potential risk of gastrointestinal ulcers.',
                'warnings' => 'Contraindicated in active bleeding disorders, severe liver or kidney disease, and in children/teens recovering from viral infections (due to Reye\'s syndrome).',
                'pregnancy_safety' => 'Category D. Avoid during the third trimester as it may cause premature closure of the fetal ductus arteriosus.',
                'storage_conditions' => 'Store at room temperature below 30°C in a dry place. Keep container tightly closed.',
                'manufacturer' => 'Bayer Healthcare',
                'price' => 2.50,
                'category' => 'Cardiovascular / NSAID',
                'food_interactions' => 'Avoid heavy alcohol intake as it increases the risk of stomach bleeding.',
                'alcohol_interactions' => 'High risk. Increases chance of stomach ulceration and bleeding.',
            ],
            [
                'id' => 2,
                'medicine_name' => 'Warfarin 5mg',
                'generic_name' => 'Warfarin Sodium',
                'brand_name' => 'Coumadin 5',
                'composition' => 'Warfarin Sodium 5mg',
                'uses' => 'Prophylaxis and treatment of venous thrombosis, pulmonary embolism, thromboembolic complications associated with atrial fibrillation or cardiac valve replacement.',
                'dosage' => '2mg to 5mg once daily, adjusted based on regular International Normalized Ratio (INR) blood test results. Must be taken at the same time each day.',
                'side_effects' => 'Severe bleeding, easy bruising, nosebleeds, blood in urine/stools, skin necrosis, hair loss, and abdominal pain.',
                'warnings' => 'Narrow therapeutic index. Requires strict INR monitoring. High risk of bleeding. Inform doctors about all concurrent medications.',
                'pregnancy_safety' => 'Category X. Absolutely contraindicated in pregnancy due to warfarin embryopathy.',
                'storage_conditions' => 'Store between 15°C and 30°C. Protect from light.',
                'manufacturer' => 'Bristol-Myers Squibb',
                'price' => 8.20,
                'category' => 'Anticoagulant',
                'food_interactions' => 'Keep vitamin K intake (green leafy vegetables) consistent. Fluctuations in vitamin K intake affect warfarin efficacy.',
                'alcohol_interactions' => 'Avoid acute alcohol binges as it increases warfarin levels and bleeding risk.',
            ],
            [
                'id' => 3,
                'medicine_name' => 'Azithral 500mg',
                'generic_name' => 'Azithromycin',
                'brand_name' => 'Zithromax 500',
                'composition' => 'Azithromycin Dihydrate 500mg',
                'uses' => 'Treatment of mild to moderate bacterial infections: bronchitis, pneumonia, skin infections, tonsillitis, sinusitis, and certain sexually transmitted infections.',
                'dosage' => 'Usually 500mg once daily for 3 days, or 500mg on day 1 followed by 250mg once daily on days 2-5. Take 1 hour before or 2 hours after meals.',
                'side_effects' => 'Diarrhea, nausea, vomiting, abdominal pain, flatulence, headache, and transient hearing impairment at high doses.',
                'warnings' => 'Use with caution in patients with hepatic dysfunction, renal impairment, or predisposition to QT interval prolongation (cardiac arrhythmia).',
                'pregnancy_safety' => 'Category B. Considered safe for use in pregnancy when clearly needed.',
                'storage_conditions' => 'Store below 25°C in a dry place. Keep out of reach of children.',
                'manufacturer' => 'Alembic Pharmaceuticals',
                'price' => 1.50,
                'category' => 'Antibacterial (Macrolide)',
                'food_interactions' => 'Food may decrease absorption. Take on an empty stomach for maximum absorption.',
                'alcohol_interactions' => 'No direct chemical interaction, but alcohol may worsen side effects like headache, dizziness, or nausea.',
            ],
            [
                'id' => 4,
                'medicine_name' => 'Crocin 650mg',
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Panadol 650',
                'composition' => 'Paracetamol 650mg',
                'uses' => 'Relief of mild to moderate pain (headache, muscle ache, backache, dental pain) and reduction of fever.',
                'dosage' => 'One tablet (650mg) every 4-6 hours as needed. Do not exceed 4000mg (6 tablets) in 24 hours. Keep at least a 4-hour gap between doses.',
                'side_effects' => 'Generally mild. Rare cases of skin rashes, allergic reactions. Severe liver damage is possible if maximum daily dose is exceeded.',
                'warnings' => 'Liver warning: Severe liver damage can occur if you take more than 4000mg in 24 hours, take with other drugs containing paracetamol, or consume 3+ alcoholic drinks daily.',
                'pregnancy_safety' => 'Category A. Generally safe throughout pregnancy. Use the lowest effective dose for the shortest duration.',
                'storage_conditions' => 'Store below 30°C in a dark and dry place.',
                'manufacturer' => 'GlaxoSmithKline Consumer Healthcare',
                'price' => 0.50,
                'category' => 'Analgesic / Antipyretic',
                'food_interactions' => 'Can be taken with or without food. High-carbohydrate meals may slightly delay absorption.',
                'alcohol_interactions' => 'Chronic heavy drinking increases the risk of paracetamol-induced hepatotoxicity (liver damage).',
            ],
            [
                'id' => 5,
                'medicine_name' => 'Metformin 500mg',
                'generic_name' => 'Metformin Hydrochloride',
                'brand_name' => 'Glucophage 500',
                'composition' => 'Metformin Hydrochloride 500mg',
                'uses' => 'Management of type 2 diabetes mellitus to improve glycemic control in adults and children (10 years and older) as an adjunct to diet and exercise.',
                'dosage' => 'Starting dose is 500mg once or twice daily with meals. May be titrated up to 2000mg/day. Take with meals to reduce gastrointestinal side effects.',
                'side_effects' => 'Nausea, vomiting, diarrhea, flatulence, abdominal discomfort, metallic taste, and rarely, lactic acidosis.',
                'warnings' => 'Lactic Acidosis warning: Rare but serious complication. Contraindicated in severe renal impairment (eGFR < 30 mL/min/1.73m²), acute metabolic acidosis, or heart failure.',
                'pregnancy_safety' => 'Category B. Considered safe, but insulin is often preferred for gestational diabetes.',
                'storage_conditions' => 'Store between 20°C and 25°C.',
                'manufacturer' => 'Merck KGaA',
                'price' => 1.20,
                'category' => 'Anti-Diabetic (Biguanide)',
                'food_interactions' => 'Absorption is slightly slower with food, but taking with food is highly recommended to prevent GI side effects.',
                'alcohol_interactions' => 'Avoid excessive alcohol as it increases Metformin\'s effect on lactate metabolism, raising the risk of lactic acidosis.',
            ],
            [
                'id' => 6,
                'medicine_name' => 'Atorva 10mg',
                'generic_name' => 'Atorvastatin',
                'brand_name' => 'Lipitor 10',
                'composition' => 'Atorvastatin Calcium 10mg',
                'uses' => 'Reduction of elevated total cholesterol, LDL-C, apolipoprotein B, and triglycerides. Prevention of cardiovascular disease in high-risk patients.',
                'dosage' => '10mg to 80mg once daily. Can be taken at any time of day, with or without food.',
                'side_effects' => 'Headache, muscle ache (myalgia), joint pain, diarrhea, nasopharyngitis, and minor increases in liver transaminase enzymes.',
                'warnings' => 'Risk of myopathy or rhabdomyolysis (muscle breakdown). Promptly report unexplained muscle pain, tenderness, or weakness, especially if accompanied by fever or dark urine.',
                'pregnancy_safety' => 'Category X. Strictly contraindicated. Statins are harmful to the fetus as cholesterol is essential for fetal development.',
                'storage_conditions' => 'Store below 25°C.',
                'manufacturer' => 'Pfizer',
                'price' => 4.00,
                'category' => 'Lipid-Lowering Agent (Statin)',
                'food_interactions' => 'Avoid large quantities of grapefruit juice (> 1 liter/day) as it increases Atorvastatin concentrations, raising side-effect risks.',
                'alcohol_interactions' => 'Caution in heavy drinkers; increases risk of liver damage.',
            ],
            [
                'id' => 7,
                'medicine_name' => 'Amoxil 500mg',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Mox 500',
                'composition' => 'Amoxicillin Trihydrate 500mg',
                'uses' => 'Treatment of infections of the ear, nose, throat, urinary tract, skin, and lower respiratory tract caused by susceptible strains of bacteria.',
                'dosage' => '250mg to 500mg every 8 hours, or 500mg to 875mg every 12 hours. Can be taken with or without food.',
                'side_effects' => 'Diarrhea, nausea, vomiting, skin rash (especially in patients with mononucleosis), and yeast infections.',
                'warnings' => 'Contraindicated in patients with a history of hypersensitivity/allergy to penicillin. Discontinue immediately if allergic reaction occurs.',
                'pregnancy_safety' => 'Category B. Generally safe for use in pregnancy.',
                'storage_conditions' => 'Store below 25°C in a dry place.',
                'manufacturer' => 'GlaxoSmithKline',
                'price' => 1.80,
                'category' => 'Antibacterial (Penicillin)',
                'food_interactions' => 'No major food interactions.',
                'alcohol_interactions' => 'No direct interaction, but alcohol can delay recovery from infection.',
            ],
            [
                'id' => 8,
                'medicine_name' => 'Combiflam',
                'generic_name' => 'Ibuprofen + Paracetamol',
                'brand_name' => 'Ibugesic Plus',
                'composition' => 'Ibuprofen 400mg + Paracetamol 325mg',
                'uses' => 'Relief of acute painful inflammatory conditions like headache, toothache, muscular pain, joint pain, dysmenorrhea, and fever reduction.',
                'dosage' => 'One tablet 2-3 times daily after meals to minimize stomach irritation. Maximum 3 tablets in 24 hours.',
                'side_effects' => 'Indigestion, abdominal pain, nausea, heartburn, dizziness, and risk of gastric ulcers on prolonged use.',
                'warnings' => 'Use with caution in patients with history of asthma, peptic ulcers, cardiovascular disorders, liver, or renal impairment.',
                'pregnancy_safety' => 'Category D. Avoid especially in the third trimester of pregnancy.',
                'storage_conditions' => 'Store in a cool, dry place away from direct sunlight.',
                'manufacturer' => 'Sanofi India',
                'price' => 0.80,
                'category' => 'Analgesic / Anti-inflammatory',
                'food_interactions' => 'Must be taken with food to reduce gastrointestinal irritation.',
                'alcohol_interactions' => 'Significantly increases the risk of stomach bleeding and liver damage.',
            ],
            [
                'id' => 9,
                'medicine_name' => 'Pan-D',
                'generic_name' => 'Pantoprazole + Domperidone',
                'brand_name' => 'Pantocid D',
                'composition' => 'Pantoprazole Sodium 40mg + Domperidone 30mg',
                'uses' => 'Management of gastroesophageal reflux disease (GERD), acid peptic disease, and dyspepsia associated with nausea and vomiting.',
                'dosage' => 'One capsule daily in the morning, 30-60 minutes before breakfast. Swallow whole.',
                'side_effects' => 'Dry mouth, headache, diarrhea, dizziness, flatulence, and rare risk of hyperprolactinemia due to domperidone.',
                'warnings' => 'Long term use (>1 year) may increase the risk of bone fractures, vitamin B12 deficiency, and hypomagnesemia.',
                'pregnancy_safety' => 'Category C. Use only if benefits outweigh risks to the fetus.',
                'storage_conditions' => 'Store below 25°C, protected from moisture.',
                'manufacturer' => 'Alkem Laboratories',
                'price' => 3.50,
                'category' => 'Gastrointestinal (Proton Pump Inhibitor + Prokinetic)',
                'food_interactions' => 'Take strictly on an empty stomach, 1 hour before a meal.',
                'alcohol_interactions' => 'Alcohol increases stomach acid production, worsening GERD symptoms and counteracting the medication.',
            ],
            [
                'id' => 10,
                'medicine_name' => 'Limcee 500mg',
                'generic_name' => 'Ascorbic Acid',
                'brand_name' => 'Celin 500',
                'composition' => 'Vitamin C (Ascorbic Acid) 500mg',
                'uses' => 'Treatment and prevention of Vitamin C deficiency (scurvy), support immune health, wound healing, and as an antioxidant supplement.',
                'dosage' => '1 chewable tablet daily, or as recommended by the physician. Chew thoroughly before swallowing.',
                'side_effects' => 'Well tolerated. Extremely high doses (>2000mg/day) may cause diarrhea, stomach cramps, and kidney stones.',
                'warnings' => 'Diabetic patients should note that high Vitamin C intake can interfere with blood glucose tests. Caution in patients with kidney stones.',
                'pregnancy_safety' => 'Category A. Safe when used within recommended dietary allowance.',
                'storage_conditions' => 'Store below 25°C in a dry place, protected from heat.',
                'manufacturer' => 'Abbott India',
                'price' => 0.30,
                'category' => 'Vitamins / Nutritional Supplement',
                'food_interactions' => 'Increases absorption of dietary iron.',
                'alcohol_interactions' => 'No direct interaction, but alcohol impairs absorption of vitamins.',
            ],
        ];
        DB::table('medicines')->insert($medicines);

        // 7. Seed Drug Interactions
        $interactions = [
            // Aspirin + Warfarin (Critical)
            [
                'medicine_id_1' => 1, // Aspirin
                'medicine_id_2' => 2, // Warfarin
                'risk_level' => 'critical',
                'severity' => 'Major Bleeding Risk',
                'description' => 'Aspirin inhibits platelet aggregation, while Warfarin slows down blood clotting factors. Concomitant use increases the risk of severe gastrointestinal and systemic hemorrhage significantly.',
                'recommended_action' => 'Avoid this combination unless specifically prescribed by a cardiologist (e.g. post-stent placement). Monitor PT/INR closely, and watch for signs of bleeding (bruising, dark stools, nosebleeds).',
            ],
            // Crocin + Combiflam (Medium)
            [
                'medicine_id_1' => 4, // Crocin (Paracetamol)
                'medicine_id_2' => 8, // Combiflam (Ibuprofen + Paracetamol)
                'risk_level' => 'medium',
                'severity' => 'Duplication / Hepatotoxicity Risk',
                'description' => 'Both Crocin and Combiflam contain Paracetamol (Acetaminophen). Taking them together increases the total paracetamol dose, which can lead to accidental overdose and severe liver toxicity.',
                'recommended_action' => 'Do not take these medications together. Ensure total paracetamol intake from all sources does not exceed 4000mg in 24 hours. Space doses at least 4-6 hours apart.',
            ],
            // Amoxil + Azithral (Low)
            [
                'medicine_id_1' => 7, // Amoxil
                'medicine_id_2' => 3, // Azithral
                'risk_level' => 'low',
                'severity' => 'Antibiotic Overlap',
                'description' => 'Amoxicillin and Azithromycin are both antibiotics. Using them concurrently is generally therapeutic duplication and unnecessary unless treating specific complex atypical dual infections.',
                'recommended_action' => 'Usually, one antibiotic is sufficient. Confirm double therapy with your physician. Complete the full course of whichever single antibiotic is recommended.',
            ],
        ];
        DB::table('medicine_interactions')->insert($interactions);
    }
}
