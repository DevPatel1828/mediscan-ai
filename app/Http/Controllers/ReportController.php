<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use App\Models\PrescriptionResult;
use App\Services\CreditManager;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Helper to check user authorization
     */
    private function getAuthorizedPrescription($id)
    {
        $prescription = Prescription::with('result')->find($id);
        
        if (!$prescription || $prescription->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this prescription report.');
        }

        return $prescription;
    }

    /**
     * Generate HTML printable PDF layout
     */
    public function pdf($id)
    {
        $prescription = $this->getAuthorizedPrescription($id);
        $result = $prescription->result;
        $ocrResult = json_decode($result->extracted_json, true);

        // Deduct credits for export (cost is 10)
        CreditManager::deduct(auth()->user(), 10, 'export', "Exported PDF report for prescription #{$id}");

        return view('reports.prescription-pdf', [
            'prescription' => $prescription,
            'result' => $result,
            'ocrResult' => $ocrResult
        ]);
    }

    /**
     * Export prescription details as CSV
     */
    public function csv($id)
    {
        $prescription = $this->getAuthorizedPrescription($id);
        $result = $prescription->result;
        $ocrResult = json_decode($result->extracted_json, true);

        // Deduct credits for export (cost is 10)
        CreditManager::deduct(auth()->user(), 10, 'export', "Exported CSV report for prescription #{$id}");

        $fileName = "mediscan-report-{$id}.csv";

        $response = new StreamedResponse(function () use ($prescription, $ocrResult) {
            $handle = fopen('php://output', 'w');
            
            // Document header
            fputcsv($handle, ['MediScan AI 2.0 Prescription Analysis Report']);
            fputcsv($handle, ['Prescription ID', $prescription->id]);
            fputcsv($handle, ['Scan Date', $prescription->created_at->format('Y-m-d H:i:s')]);
            fputcsv($handle, ['OCR Confidence Score', ($ocrResult['confidence_score'] ?? 'N/A') . '%']);
            fputcsv($handle, ['Target Language', $prescription->result->language]);
            fputcsv($handle, []);

            // Medicine Table header
            fputcsv($handle, ['Medicine Name', 'Composition / Generic', 'Dosage Strength', 'Frequency', 'Duration', 'Patient Instructions']);

            foreach ($ocrResult['medicines'] ?? [] as $med) {
                fputcsv($handle, [
                    $med['name'] ?? 'N/A',
                    $med['composition'] ?? 'N/A',
                    $med['dosage'] ?? 'N/A',
                    $med['frequency'] ?? 'N/A',
                    $med['duration'] ?? 'N/A',
                    $med['instructions'] ?? 'N/A'
                ]);
            }

            fputcsv($handle, []);
            if (!empty($ocrResult['doctor_instructions'])) {
                fputcsv($handle, ['Doctor Instructions', $ocrResult['doctor_instructions']]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);

        return $response;
    }

    /**
     * Export prescription details as Excel (XLS/Tab-separated values)
     */
    public function xlsx($id)
    {
        $prescription = $this->getAuthorizedPrescription($id);
        $result = $prescription->result;
        $ocrResult = json_decode($result->extracted_json, true);

        // Deduct credits for export (cost is 10)
        CreditManager::deduct(auth()->user(), 10, 'export', "Exported Excel report for prescription #{$id}");

        $fileName = "mediscan-report-{$id}.xls";

        $response = new StreamedResponse(function () use ($prescription, $ocrResult) {
            $handle = fopen('php://output', 'w');
            
            // TSV headers to let Excel parse tabs properly
            fwrite($handle, "MediScan AI 2.0 Prescription Analysis Report\n");
            fwrite($handle, "Prescription ID\t" . $prescription->id . "\n");
            fwrite($handle, "Scan Date\t" . $prescription->created_at->format('Y-m-d H:i:s') . "\n");
            fwrite($handle, "Confidence Score\t" . ($ocrResult['confidence_score'] ?? 'N/A') . "%\n");
            fwrite($handle, "Target Language\t" . $prescription->result->language . "\n\n");

            // Table headers
            fwrite($handle, "Medicine Name\tComposition / Generic\tDosage Strength\tFrequency\tDuration\tPatient Instructions\n");

            foreach ($ocrResult['medicines'] ?? [] as $med) {
                fwrite($handle, 
                    ($med['name'] ?? 'N/A') . "\t" . 
                    ($med['composition'] ?? 'N/A') . "\t" . 
                    ($med['dosage'] ?? 'N/A') . "\t" . 
                    ($med['frequency'] ?? 'N/A') . "\t" . 
                    ($med['duration'] ?? 'N/A') . "\t" . 
                    ($med['instructions'] ?? 'N/A') . "\n"
                );
            }

            if (!empty($ocrResult['doctor_instructions'])) {
                fwrite($handle, "\nDoctor Instructions\t" . $ocrResult['doctor_instructions'] . "\n");
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);

        return $response;
    }
}
