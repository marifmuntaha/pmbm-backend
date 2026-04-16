<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - {{ $registration_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #2c3e50;
            padding: 20px;
        }

        /* Header Section */
        .header {
            width: 100%;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .logo-cell {
            width: 100px;
            text-align: left;
        }

        .logo {
            max-width: 90px;
            height: auto;
        }

        .info-cell {
            text-align: right;
            padding-left: 15px;
        }

        .institution-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .institution-title {
            font-size: 13px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 3px;
        }

        .reg-number {
            font-size: 11px;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Content Section */
        .content {
            width: 100%;
            margin-bottom: 20px;
        }

        .content-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        .photo-cell {
            padding-right: 5px;
            width: 170px;
            vertical-align: top;
        }

        .student-photo {
            width: auto;
            height: 225px;
            border: 2px solid #2c3e50;
            object-fit: cover;
            background: #e8f4f8;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: auto;
            height: 225px;
            border: 2px solid #2c3e50;
            background: #e8f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #999;
            text-align: center;
        }

        .info-cell-data {
            border: 1px solid black;
            padding-left: 5px;
        }

        /* Footer Section */
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: top;
            padding: 0;
        }

        .qr-cell {
            width: 150px;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            border: 1px solid #2c3e50;
            padding: 1px;
            background: white;
        }

        .qr-placeholder {
            width: 120px;
            height: 120px;
            border: 1px solid #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-size: 8px;
            text-align: center;
            color: #666;
        }

        .verification-text {
            font-size: 9px;
            color: #666;
            line-height: 1.6;
            margin-top: 10px;
        }

        .verification-text p {
            margin-bottom: 5px;
        }

        .signature-cell {
            text-align: right;
            padding-left: 20px;
        }

        .signature-date {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .signature-title {
            font-size: 10px;
            font-weight: bold;
        }

        .signature-name {
            font-size: 10px;
            font-weight: bold;
            border-bottom: 1px solid #333;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 150px;
        }

        .digital-signature-badge {
            display: inline-block;
            background: #d4edda;
            border: 1px solid #28a745;
            border-radius: 3px;
            padding: 4px 8px;
            font-size: 8px;
            color: #155724;
            margin-top: 8px;
            font-weight: bold;
        }

        .digital-signature-badge img {
            width: 10px;
            height: 10px;
            vertical-align: middle;
            margin-right: 3px;
        }

        .contact-info {
            font-size: 9px;
            color: #666;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        @if($logo_base64)
                            <img src="{{ $logo_base64 }}" alt="Logo" class="logo">
                        @else
                            <div class="logo-placeholder">LOGO</div>
                        @endif
                    </td>
                    <td class="info-cell">
                        <div class="institution-name">YAYASAN DARUL HIKMAH MENGANTI</div>
                        <div class="institution-name">
                            {{ strtoupper($institution->name) ?? 'MADRASAH TSANAWIYAH DARUL HIKMAH' }}
                        </div>
                        <div class="institution-title">BUKTI PENDAFTARAN MURID BARU</div>
                        <div class="reg-number">No: {{ $registration_number }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content -->
        <div class="content">
            <table class="content-table">
                <tr>
                    <td rowspan="9" class="photo-cell">
                        @if($photo_base64)
                            <div class="student-photo">
                                <img src="{{ $photo_base64 }}" alt="Foto Siswa">
                            </div>
                        @else
                            <div class="photo-placeholder">Foto<br>3x4</div>
                        @endif
                    </td>
                    <td class="info-cell-data">Nama Lengkap</td>
                    <td class="info-cell-data">{{ $student['name'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">NISN</td>
                    <td class="info-cell-data">{{ $student['nisn'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">NIK</td>
                    <td class="info-cell-data">{{ $student['nik'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">Tempat, Tanggal Lahir</td>
                    <td class="info-cell-data">
                        {{ $student['birthPlace'] }},
                        @php
                            $bulanId = ['Januari'=>'January','Februari'=>'February','Maret'=>'March','April'=>'April','Mei'=>'May','Juni'=>'June','Juli'=>'July','Agustus'=>'August','September'=>'September','Oktober'=>'October','November'=>'November','Desember'=>'December'];
                            $rawDate = $student['birthDate'];
                            $parsedDate = str_replace(array_keys($bulanId), array_values($bulanId), $rawDate);
                        @endphp
                        {{ \Carbon\Carbon::parse($parsedDate)->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td class="info-cell-data">Jenis Kelamin</td>
                    <td class="info-cell-data">{{ $student['gender'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">Nama Wali</td>
                    <td class="info-cell-data">{{ $student['guardName'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">Nomor Hp</td>
                    <td class="info-cell-data">{{ $student['phone'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">Program Madrasah</td>
                    <td class="info-cell-data">{{ $program['name'] }}</td>
                </tr>
                <tr>
                    <td class="info-cell-data">Program Boarding</td>
                    <td class="info-cell-data">{{ $program['boarding'] }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="qr-cell">
                        @if($qr_code !== 'FALLBACK' && str_starts_with($qr_code, 'data:image'))
                            <img src="{{ $qr_code }}" alt="QR Code" class="qr-code">
                        @else
                            <div class="qr-placeholder">QR Code<br>Verifikasi</div>
                        @endif
                        <div class="verification-text">
                            <p><strong>Verifikasi Bukti Pendaftaran</strong></p>
                        </div>
                    </td>
                    <td class="signature-cell">
                        <div class="signature-date">
                            Jepara, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                        </div>
                        <div class="signature-title">Kepala Madrasah</div>
                        <div style="height: 60px;"></div>
                        <div class="signature-name">
                            {{ $institution->head ?? 'Kepala ' . ($institution->name ?? 'Madrasah') }}
                        </div>
                        <div style="margin-top: 5px;">
                            <div class="digital-signature-badge">
                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDdWNUMxMiAyLjc5IDEwLjIxIDEgOCAxQzUuNzkgMSA0IDIuNzkgNCA1VjdDMy40NSA3IDMgNy40NSAzIDhWMTNDMyAxMy41NSAzLjQ1IDE0IDQgMTRIMTJDMTIuNTUgMTQgMTMgMTMuNTUgMTMgMTNWOEMxMyA3LjQ1IDEyLjU1IDcgMTIgN1pNOCA5LjVDOC44MyA5LjUgOS41IDEwLjE3IDkuNSAxMUM5LjUgMTEuODMgOC44MyAxMi41IDggMTIuNUM3LjE3IDEyLjUgNi41IDExLjgzIDYuNSAxMUM2LjUgMTAuMTcgNy4xNyA5LjUgOCA5LjVaTTEwLjUgN0g1LjVWNUM1LjUgMy42MiA2LjYyIDIuNSA4IDIuNUM5LjM4IDIuNSAxMC41IDMuNjIgMTAuNSA1VjdaIiBmaWxsPSIjMjhhNzQ1Ii8+Cjwvc3ZnPgo="
                                    style="width: 10px; height: 10px; vertical-align: middle; margin-right: 3px;"
                                    alt="lock">
                                Dokumen Ditandatangani Digital
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="contact-info">
                Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i:s') }} WIB
            </div>
        </div>
    </div>
</body>

</html>