<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->control_number }} - {{ $document->document_type_label }}</title>
    <style>
        @page {
            margin: 0.4in;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #222;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }
        /* Certificate Border */
        .border-container {
            border: 4px double #1a365d;
            padding: 20px;
            height: 94%;
        }
        /* Header styling */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 10px;
        }
        .republic {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
        }
        .province {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0 0 0;
        }
        .barangay {
            font-size: 16px;
            font-weight: bold;
            color: #1a365d;
            margin: 2px 0 0 0;
        }
        .office {
            font-size: 11px;
            font-style: italic;
            letter-spacing: 1px;
            margin: 5px 0 0 0;
        }

        /* Two Column Layout */
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sidebar {
            width: 30%;
            border-right: 2px solid #1a365d;
            padding-right: 15px;
            vertical-align: top;
        }
        .content {
            width: 70%;
            padding-left: 25px;
            vertical-align: top;
        }

        /* Sidebar Officials List */
        .officials-title {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #1a365d;
        }
        .official-item {
            margin-bottom: 12px;
            text-align: center;
        }
        .official-name {
            font-weight: bold;
            font-size: 11px;
            margin: 0;
        }
        .official-role {
            font-size: 9px;
            color: #555;
            margin: 0;
            text-transform: uppercase;
        }

        /* Content Area */
        .doc-title-container {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #1a365d;
            letter-spacing: 1px;
            border-bottom: 1px solid #1a365d;
            display: inline-block;
            padding-bottom: 5px;
            margin: 0;
        }
        .salutation {
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .text-body {
            text-align: justify;
            text-indent: 30px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .purpose-box {
            background-color: #f7fafc;
            border-left: 3px solid #1a365d;
            padding: 8px 12px;
            margin: 15px 0;
            font-style: italic;
            font-size: 12px;
        }

        /* Signatures and Seals */
        .signatures-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .sig-col {
            width: 50%;
            vertical-align: top;
        }
        .sig-block {
            text-align: center;
            margin-top: 20px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .sig-name {
            font-weight: bold;
            font-size: 12px;
            margin: 0;
        }
        .sig-title {
            font-size: 10px;
            color: #555;
            margin: 0;
        }

        /* Receipt and Control Info Footer */
        .document-footer {
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 0;
        }
        .seal-circle {
            width: 80px;
            height: 80px;
            border: 2px dashed #1a365d;
            border-radius: 50%;
            text-align: center;
            line-height: 80px;
            font-size: 9px;
            font-weight: bold;
            color: #1a365d;
            margin: 10px auto;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<div class="border-container">
    <!-- Header -->
    <div class="header">
        <p class="republic">Republic of the Philippines</p>
        <p class="province">Province of Metro Manila</p>
        <p class="province">City of Manila</p>
        <p class="barangay">BARANGAY SAN JOSE</p>
        <p class="office">OFFICE OF THE BARANGAY CHAIRMAN</p>
    </div>

    <!-- Main Content Table -->
    <table class="main-table">
        <tr>
            <!-- Sidebar with Barangay Officials -->
            <td class="sidebar">
                <div class="officials-title">Barangay Council</div>
                
                <div class="official-item">
                    <p class="official-name">HON. JUAN DELA CRUZ</p>
                    <p class="official-role">Barangay Chairman</p>
                </div>
                
                <div class="official-item">
                    <p class="official-name">Hon. Maria S. Santos</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>
                
                <div class="official-item">
                    <p class="official-name">Hon. Robert L. Reyes</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>

                <div class="official-item">
                    <p class="official-name">Hon. Jose P. Roxas</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>

                <div class="official-item">
                    <p class="official-name">Hon. Elena D. Cruz</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>

                <div class="official-item">
                    <p class="official-name">Hon. Antonio G. Luna</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>

                <div class="official-item">
                    <p class="official-name">Hon. Grace F. Poe</p>
                    <p class="official-role">Barangay Kagawad</p>
                </div>

                <div class="official-item" style="margin-top: 20px;">
                    <p class="official-name">Sarah J. Geronimo</p>
                    <p class="official-role">Barangay Secretary</p>
                </div>

                <div class="official-item">
                    <p class="official-name">Jose Marie Viceral</p>
                    <p class="official-role">Barangay Treasurer</p>
                </div>

                <div class="seal-circle">OFFICIAL SEAL</div>
            </td>

            <!-- Content Area -->
            <td class="content">
                <div class="doc-title-container">
                    <h2 class="doc-title">{{ strtoupper($document->document_type_label) }}</h2>
                </div>

                <div class="salutation">TO WHOM IT MAY CONCERN:</div>

                @if($document->document_type === 'clearance')
                    <div class="text-body">
                        This is to certify that <strong>{{ strtoupper($resident->last_name . ', ' . $resident->first_name) }}</strong>, of legal age, {{ strtolower($resident->gender) }}, {{ strtolower($resident->civil_status) }}, and a resident of <strong>{{ $resident->purok->purok_name }}</strong>, Barangay San Jose, is known to the undersigned as a person of good moral character with no derogatory record or blotter files on record in this office as of this date.
                    </div>
                    <div class="text-body">
                        This clearance is issued upon the request of the above-named resident in connection with his/her application for:
                    </div>
                @elseif($document->document_type === 'residency')
                    <div class="text-body">
                        This is to certify that <strong>{{ strtoupper($resident->last_name . ', ' . $resident->first_name) }}</strong>, of legal age, {{ strtolower($resident->gender) }}, {{ strtolower($resident->civil_status) }}, is a bona fide resident of <strong>{{ $resident->purok->purok_name }}</strong>, Barangay San Jose, City of Manila, Philippines.
                    </div>
                    <div class="text-body">
                        This certification is issued upon the request of the above-named resident for the purpose of:
                    </div>
                @else
                    {{-- indigency --}}
                    <div class="text-body">
                        This is to certify that <strong>{{ strtoupper($resident->last_name . ', ' . $resident->first_name) }}</strong>, of legal age, {{ strtolower($resident->gender) }}, {{ strtolower($resident->civil_status) }}, and a resident of <strong>{{ $resident->purok->purok_name }}</strong>, Barangay San Jose, City of Manila, belongs to a low-income / indigent family in this community.
                    </div>
                    <div class="text-body">
                        This certification is issued upon the request of the above-named resident for the purpose of:
                    </div>
                @endif

                <div class="purpose-box">
                    <strong>Purpose:</strong> {{ $document->purpose }}
                </div>

                <div class="text-body">
                    Given this <strong>{{ \Carbon\Carbon::parse($issued_date)->format('jS \o\f F, Y') }}</strong> at Barangay San Jose, City of Manila, Philippines.
                </div>

                <!-- Signatures -->
                <table class="signatures-table">
                    <tr>
                        <td class="sig-col">
                            <div class="sig-block" style="text-align: left; margin-left: 20px;">
                                <div style="font-size: 10px; margin-bottom: 25px;">Prepared by:</div>
                                <div style="font-weight: bold; font-size: 11px;">SARAH J. GERONIMO</div>
                                <div class="sig-title">Barangay Secretary</div>
                            </div>
                        </td>
                        <td class="sig-col">
                            <div class="sig-block">
                                <div class="sig-line"></div>
                                <p class="sig-name">HON. JUAN DELA CRUZ</p>
                                <p class="sig-title">Barangay Chairman</p>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Footer with Control Info -->
                <div class="document-footer">
                    <table class="info-table">
                        <tr>
                            <td style="font-weight: bold;">Control Number:</td>
                            <td>{{ $document->control_number }}</td>
                            <td style="font-weight: bold; text-align: right;">Amount Paid:</td>
                            <td style="text-align: right;">Php {{ number_format($document->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">OR Number:</td>
                            <td>{{ $document->official_receipt_no ?? 'N/A' }}</td>
                            <td style="font-weight: bold; text-align: right;">Valid Until:</td>
                            <td style="text-align: right;">{{ \Carbon\Carbon::parse($valid_until)->format('F d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
