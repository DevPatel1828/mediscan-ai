<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription Analysis Report #{{ $prescription->id }}</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
        }
        .brand h1 {
            font-size: 1.5rem;
            margin: 0;
            color: #0f172a;
        }
        .brand span {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .meta-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        .meta-item span {
            color: #64748b;
            display: block;
            margin-bottom: 0.25rem;
        }
        .meta-item strong {
            color: #0f172a;
            font-size: 1rem;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        .table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
            background-color: #dcfce7;
            color: #166534;
        }
        .badge.warning {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .notes-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        .notes-card h4 {
            margin: 0 0 0.5rem;
            color: #0f172a;
        }
        .disclaimer {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            font-size: 0.75rem;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto 1.5rem;
            padding: 1rem;
            background: #0f172a;
            border-radius: 12px;
            color: white;
        }
        .btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-size: 0.875rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Top floating menu bar, hidden when printing -->
    <div class="no-print-bar">
        <span style="font-size: 0.875rem; font-weight: 500;">MediScan AI Report Print Preview</span>
        <button class="btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="brand">
                <img src="/brand_logo.png" alt="MediScan Logo">
                <div>
                    <h1>MediScan AI 2.0</h1>
                    <span>Prescription Analysis Platform</span>
                </div>
            </div>
            <div>
                <span style="font-size: 0.875rem; color: #64748b; text-align: right; display: block;">Report ID: #{{ $prescription->id }}</span>
                <span style="font-size: 0.75rem; color: #94a3b8; text-align: right; display: block;">Generated: {{ now()->format('Y-m-d H:i') }}</span>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <div class="meta-item">
                <span>Patient Account</span>
                <strong>{{ auth()->user()->name }}</strong>
            </div>
            <div class="meta-item" style="text-align: right;">
                <span>AI Accuracy Confidence</span>
                <strong class="{{ ($ocrResult['confidence_score'] ?? 100) >= 80 ? '' : 'warning' }}" style="color: {{ ($ocrResult['confidence_score'] ?? 100) >= 80 ? '#166534' : '#854d0e' }}">
                    {{ $ocrResult['confidence_score'] ?? 'N/A' }}%
                </strong>
            </div>
            <div class="meta-item">
                <span>Target Language Translation</span>
                <strong>✓ {{ $prescription->result->language }}</strong>
            </div>
            <div class="meta-item" style="text-align: right;">
                <span>Scan Created At</span>
                <strong>{{ $prescription->created_at->format('M d, Y h:i A') }}</strong>
            </div>
        </div>

        <!-- Medical Disclaimer -->
        <div class="disclaimer">
            <strong>Medical Disclaimer:</strong> This analysis report is generated by an artificial intelligence pipeline for literacy purposes. Never alter prescribed medication schedules, start new therapies, or discontinue treatments without the direct authorization of a certified medical practitioner.
        </div>

        <!-- Medicine Table -->
        <table class="table">
            <thead>
                <tr>
                    <th>Medicine & Generic Composition</th>
                    <th>Dosage Strength</th>
                    <th>Duration & Frequency</th>
                    <th>Patient Instructions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ocrResult['medicines'] ?? [] as $med)
                    <tr>
                        <td>
                            <strong>{{ $med['name'] }}</strong>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">{{ $med['composition'] ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $med['dosage'] ?? 'As directed' }}</td>
                        <td>
                            <div>{{ $med['duration'] ?? 'As directed' }}</div>
                            <div style="font-size: 0.75rem; color: #2563eb; font-weight: 600; margin-top: 2px;">{{ $med['frequency'] ?? 'N/A' }}</div>
                        </td>
                        <td style="font-size: 0.8rem; line-height: 1.4;">{{ $med['instructions'] ?? 'Take as directed.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #64748b; padding: 2rem;">No medicines extracted.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Doctor Instructions -->
        @if(!empty($ocrResult['doctor_instructions']))
            <div class="notes-card">
                <h4>👨‍⚕️ General Doctor Notes</h4>
                <p style="margin: 0; line-height: 1.5; color: #475569;">{{ $ocrResult['doctor_instructions'] }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div style="border-top: 1px solid #e2e8f0; padding-top: 1.5rem; text-align: center; font-size: 0.75rem; color: #94a3b8; margin-top: 3rem;">
            MediScan AI 2.0 • Digital Healthcare Intelligence Platform • Powered by Laravel & FastAPI Python Neural Pipelines.
        </div>
    </div>

</body>
</html>
