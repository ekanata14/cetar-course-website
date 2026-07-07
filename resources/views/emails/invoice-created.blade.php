<x-emails.layout>
    <h1 style="margin:0 0 8px; color:#22384D; font-size:20px; font-weight:800;">
        Halo, {{ $payment->user->name }}! 👋
    </h1>
    <p style="margin:0 0 24px; color:#2B2B2B; font-size:15px; line-height:1.7;">
        Invoice kamu sudah dibuat. Selesaikan pembayaran untuk membuka akses paket belajarmu.
    </p>

    {{-- DETAIL INVOICE --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#FBF9F6; border-radius:12px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px 24px;">
                <p style="margin:0 0 4px; color:#9AA7AE; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                    Nomor Invoice
                </p>
                <p style="margin:0 0 16px; color:#22384D; font-size:14px; font-weight:700; font-family:'Courier New', monospace;">
                    {{ $payment->external_id }}
                </p>

                <p style="margin:0 0 4px; color:#9AA7AE; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                    Paket
                </p>
                <p style="margin:0 0 16px; color:#22384D; font-size:15px; font-weight:700;">
                    {{ $payment->packagePlan->package->name }} — {{ $payment->packagePlan->name }}
                </p>

                <p style="margin:0 0 4px; color:#9AA7AE; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                    Total Tagihan
                </p>
                <p style="margin:0; color:#F5872A; font-size:24px; font-weight:800; font-family:'Courier New', monospace;">
                    Rp{{ number_format($payment->amount, 0, ',', '.') }}
                </p>
            </td>
        </tr>
    </table>

    {{-- TOMBOL BAYAR --}}
    @if ($payment->payment_url)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
            <tr>
                <td align="center">
                    <a href="{{ $payment->payment_url }}"
                        style="display:inline-block; background:linear-gradient(100deg,#F5872A 0%,#FBB823 100%); color:#FFFFFF; font-size:15px; font-weight:700; text-decoration:none; padding:14px 40px; border-radius:12px;">
                        Bayar Sekarang
                    </a>
                </td>
            </tr>
        </table>
        <p style="margin:0; color:#7B8794; font-size:13px; line-height:1.6;">
            Link pembayaran berlaku 60 menit. Setelah pembayaran terkonfirmasi, akses paketmu aktif otomatis.
        </p>
    @else
        <p style="margin:0; color:#7B8794; font-size:13px; line-height:1.6;">
            Instruksi pembayaran akan menyusul. Simpan nomor invoice ini sebagai referensi.
        </p>
    @endif
</x-emails.layout>
