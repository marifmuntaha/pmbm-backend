<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - {{ $receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 175mm;
            /* 210mm - 35mm (margins/borders) */
            margin: 10mm auto;
            padding: 10mm;
            position: relative;
            background: white;
            border: 2px solid #2c3e50;
        }

        .content {
            position: relative;
            z-index: 1;
        }



        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .institution-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 1px;
        }

        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            color: #27ae60;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .receipt-number {
            font-size: 14px;
            color: #2c3e50;
            margin-top: 1px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-title {
            font-size: 13px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #ecf0f1;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .info-label {
            width: 180px;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .amount-box {
            background: #ecf0f1;
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }

        .amount-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .qr-section {
            text-align: center;
            width: 200px;
        }

        .qr-code {
            max-width: 150px;
            height: auto;
            border: 2px solid #2c3e50;
            padding: 2px;
            background: white;
        }

        .qr-label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .signature-section {
            text-align: center;
            padding-left: 20px;
        }

        .signature-location {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #333;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 200px;
        }

        .disclaimer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
            font-size: 9px;
            color: #999;
            text-align: center;
        }

        .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }

        .digital-sig-badge {
            display: inline-block;
            background: #d4edda;
            border: 1px solid #28a745;
            border-radius: 3px;
            padding: 4px 8px;
            font-size: 8px;
            color: #155724;
            margin-top: 3px;
            font-weight: bold;
        }

        .lock-icon {
            width: 12px;
            height: auto;
            vertical-align: middle;
            margin-right: 6px;
            margin-top: -1px;
            background: transparent !important;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 100px;
            color: rgba(39, 174, 96, 0.15);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
            border: 10px solid rgba(39, 174, 96, 0.15);
            padding: 10px 40px;
            border-radius: 20px;
            text-transform: uppercase;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="watermark">LUNAS</div>
        <div class="content">
            <!-- Header -->
            <!-- Header -->
            <table style="width: 100%; border-bottom: 3px solid #2c3e50; padding-bottom: 20px; margin-bottom: 25px;">
                <tr>
                    <td style="width: 25%; vertical-align: middle;">
                        @if(!empty($institution_logo_path) && file_exists($institution_logo_path))
                            <img src="{{ $institution_logo_path }}" alt="Logo" class="logo"
                                style="max-height: 100px; width: auto;">
                        @endif
                    </td>
                    <td style="width: 75%; text-align: right; vertical-align: middle;">
                        <div class="header-title">
                            PENERIMAAN MURID BARU<br>
                            YAYASAN DARUL HIKMAH MENGANTI
                        </div>
                        <div class="institution-name">{{ strtoupper($institution_name) }}</div>
                        <div class="receipt-title">Bukti Pembayaran</div>
                        <div class="receipt-number">No: {{ $receipt_number }}</div>
                    </td>
                </tr>
            </table>

            <!-- Student Information -->
            <div class="info-section">
                <div class="info-title">Informasi Pendaftar</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nama Lengkap</td>
                        <td class="info-value">: {{ $student_name }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Nomor Pendaftaran</td>
                        <td class="info-value">: {{ $registration_number }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Program Pilihan</td>
                        <td class="info-value">: {{ $program_name }}</td>
                    </tr>
                </table>
            </div>

            <!-- Payment Information -->
            <div class="info-section">
                <div class="info-title">Detail Pembayaran</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nomor Invoice</td>
                        <td class="info-value">: {{ $invoice_reference }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tanggal Pembayaran</td>
                        <td class="info-value">: {{ $payment_date }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Metode Pembayaran</td>
                        <td class="info-value">: {{ $payment_method }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">ID Transaksi</td>
                        <td class="info-value">: {{ $transaction_id }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Keterangan</td>
                        <td class="info-value">: Pembayaran PMB {{ $institution_name }}</td>
                    </tr>
                </table>
            </div>

            <!-- Amount -->
            <div class="amount-box">
                <div class="amount-label">TOTAL PEMBAYARAN</div>
                <div class="amount-value">{{ $amount }}</div>
            </div>

            <!-- Footer with QR and Signature -->
            <table class="footer-table">
                <tr>
                    <td class="qr-section" style="vertical-align: top;">
                        @if(!empty($qr_code_path) && file_exists($qr_code_path))
                            <img src="{{ $qr_code_path }}" alt="QR Code" class="qr-code">
                        @else
                            <div
                                style="width: 150px; height: 150px; border: 2px solid #2c3e50; padding: 10px; text-align: center; background: #f8f9fa; margin: 0 auto;">
                                <div style="font-size: 40px; margin-top: 30px;">📱</div>
                                <div style="font-size: 10px; color: #666; margin-top: 10px;">
                                    Scan QR Code<br>untuk verifikasi
                                </div>
                            </div>
                        @endif
                        <div class="qr-label">Scan untuk verifikasi</div>
                        <div class="qr-label" style="margin-top: 10px; font-size: 8px; word-break: break-all;">
                            {{ $verify_url }}
                        </div>
                    </td>
                    <td class="signature-section" style="vertical-align: top;">
                        <div class="signature-location">
                            {{ $signature_location }}, {{ $signature_date }}
                        </div>
                        <div>Bendahara</div>
                        <div style="margin-top: 50px; text-align: center;">
                            <div class="signature-name">{{ $signature_name }}</div>
                            <div style="margin-top: 8px;">
                                <div class="digital-sig-badge">
                                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDdWNUMxMiAyLjc5IDEwLjIxIDEgOCAxQzUuNzkgMSA0IDIuNzkgNCA1VjdDMy40NSA3IDMgNy40NSAzIDhWMTNDMyAxMy41NSAzLjQ1IDE0IDQgMTRIMTJDMTIuNTUgMTQgMTMgMTMuNTUgMTMgMTNWOEMxMyA3LjQ1IDEyLjU1IDcgMTIgN1pNOCA5LjVDOC44MyA5LjUgOS41IDEwLjE3IDkuNSAxMUM5LjUgMTEuODMgOC44MyAxMi41IDggMTIuNUM3LjE3IDEyLjUgNi41IDExLjgzIDYuNSAxMUM2LjUgMTAuMTcgNy4xNyA5LjUgOCA5LjVaTTEwLjUgN0g1LjVWNUM1LjUgMy42MiA2LjYyIDIuNSA4IDIuNUM5LjM4IDIuNSAxMC41IDMuNjIgMTAuNSA1VjdaIiBmaWxsPSIjMjhhNzQ1Ii8+Cjwvc3ZnPgo="
                                        style="width: 10px; height: 10px; vertical-align: middle; margin-right: 3px;"
                                        alt="lock">
                                    Dokumen Ditandatangani Digital
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Disclaimer -->
            <div class="disclaimer">
                Bukti pembayaran ini sah dan dihasilkan secara elektronik dengan tanda tangan digital.
                Untuk verifikasi keaslian, silakan scan QR code di atas atau kunjungi {{ $verify_url }}
                <br>
                Simpan bukti pembayaran ini sebagai arsip. Hubungi bendahara jika ada pertanyaan.
            </div>

            <div class="print-date">
                Dicetak pada: {{ $print_date }}
            </div>
        </div>
    </div>
</body>

</html>
