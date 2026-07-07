<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $payment->external_id }}</title>
    <style>
        /* dompdf: hanya CSS sederhana + layout tabel; font default DejaVu Sans */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #2B2B2B; }
        .page { padding: 40px 44px; }

        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }

        .muted { color: #7B8794; }
        .faint { color: #9AA7AE; }
        .navy { color: #22384D; }
        .orange { color: #F5872A; }
        .label { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #9AA7AE; }
        .mono { font-family: 'DejaVu Sans Mono', monospace; }

        .stamp { display: inline-block; padding: 5px 14px; border-radius: 12px; font-size: 10px; font-weight: bold; letter-spacing: 1px; }
        .stamp-paid { background: #E3F4EA; color: #27A35A; }
        .stamp-pending { background: #FCF3CF; color: #C9A200; }
        .stamp-void { background: #FBEAEA; color: #E94B3C; }

        .items th { background: #22384D; color: #ffffff; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 9px 12px; text-align: left; }
        .items td { padding: 12px; border-bottom: 1px solid #EAECEF; }
        .items .right, .totals .right { text-align: right; }

        .totals td { padding: 7px 12px; }
        .total-row td { border-top: 2px solid #22384D; padding-top: 10px; font-size: 15px; font-weight: bold; }

        .footer-note { border-top: 1px solid #EAECEF; margin-top: 36px; padding-top: 16px; font-size: 10px; line-height: 1.7; color: #9AA7AE; }
    </style>
</head>
<body>
@php
    $isPaid = $payment->status->value === 'settled';
    $isPending = $payment->status->value === 'pending';
    [$stampClass, $stampLabel] = match (true) {
        $isPaid => ['stamp-paid', 'LUNAS'],
        $isPending => ['stamp-pending', 'BELUM DIBAYAR'],
        default => ['stamp-void', strtoupper($payment->status->value === 'expired' ? 'KEDALUWARSA' : 'GAGAL')],
    };
    $logo = public_path('assets/images/logo_cetar.png');
@endphp
<div class="page">

    {{-- HEADER: BRAND + STATUS --}}
    <table style="margin-bottom: 32px;">
        <tr>
            <td width="55%">
                @if (file_exists($logo))
                    <img src="{{ $logo }}" alt="Cetar" width="44" style="margin-bottom: 8px;">
                @endif
                <div class="navy" style="font-size: 20px; font-weight: bold;">Cetar</div>
                <div class="muted" style="font-size: 10px; margin-top: 3px;">
                    Platform try out CBT persiapan CPNS &amp; UTBK-SNBT
                </div>
            </td>
            <td width="45%" style="text-align: right;">
                <div class="navy" style="font-size: 26px; font-weight: bold; letter-spacing: 3px;">INVOICE</div>
                <div class="mono muted" style="font-size: 10px; margin: 6px 0 10px;">{{ $payment->external_id }}</div>
                <span class="stamp {{ $stampClass }}">{{ $stampLabel }}</span>
            </td>
        </tr>
    </table>

    {{-- META: TANGGAL + PIHAK --}}
    <table style="margin-bottom: 28px;">
        <tr>
            <td width="34%">
                <div class="label">Diterbitkan Oleh</div>
                <div class="navy" style="font-weight: bold; margin-top: 4px;">Cetar</div>
                <div class="muted" style="font-size: 10px; margin-top: 2px;">{{ config('app.url') }}</div>
            </td>
            <td width="33%">
                <div class="label">Ditagihkan Kepada</div>
                <div class="navy" style="font-weight: bold; margin-top: 4px;">{{ $payment->user->name }}</div>
                <div class="muted" style="font-size: 10px; margin-top: 2px;">{{ $payment->user->email }}</div>
            </td>
            <td width="33%" style="text-align: right;">
                <div class="label">Tanggal Terbit</div>
                <div class="navy" style="font-weight: bold; margin-top: 4px;">{{ $payment->created_at->translatedFormat('d F Y, H:i') }}</div>
                @if ($isPending)
                    <div class="muted" style="font-size: 10px; margin-top: 2px;">
                        Bayar sebelum {{ $payment->created_at->addMinutes(60)->translatedFormat('d F Y, H:i') }}
                    </div>
                @elseif ($isPaid)
                    <div class="muted" style="font-size: 10px; margin-top: 2px;">Dibayar via DOKU</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- RINCIAN ITEM --}}
    <table class="items" style="margin-bottom: 4px;">
        <thead>
            <tr>
                <th width="52%">Deskripsi</th>
                <th width="16%">Durasi Akses</th>
                <th width="8%" class="right">Qty</th>
                <th width="24%" class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="navy" style="font-weight: bold;">{{ $payment->packagePlan->package->name }} — {{ $payment->packagePlan->name }}</div>
                    <div class="muted" style="font-size: 10px; margin-top: 3px;">
                        Akses penuh semua try out &amp; materi dalam paket. Aktif otomatis setelah pembayaran terkonfirmasi.
                    </div>
                </td>
                <td>{{ $payment->packagePlan->duration_days }} hari</td>
                <td class="right">1</td>
                <td class="right mono">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- TOTAL --}}
    <table class="totals">
        <tr>
            <td width="60%"></td>
            <td width="20%" class="muted">Subtotal</td>
            <td width="20%" class="right mono">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="muted">Pajak</td>
            <td class="right mono">Rp0</td>
        </tr>
        <tr class="total-row">
            <td></td>
            <td class="navy">Total</td>
            <td class="right mono orange">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- CATATAN --}}
    <div class="footer-note">
        <div class="navy" style="font-size: 10px; font-weight: bold; margin-bottom: 4px;">Catatan</div>
        Pembayaran diproses aman melalui gateway DOKU.
        Setelah pembayaran terkonfirmasi, akses paket aktif otomatis tanpa konfirmasi manual dan tanda terima dikirim ke email kamu.
        Invoice ini dibuat otomatis oleh sistem dan sah tanpa tanda tangan.
        Butuh bantuan? Hubungi admin melalui halaman pengaturan akunmu.
    </div>
</div>
</body>
</html>
