<x-emails.layout>
    <h1 style="margin:0 0 8px; color:#22384D; font-size:20px; font-weight:800;">
        Pembayaran Diterima! 🎉
    </h1>
    <p style="margin:0 0 24px; color:#2B2B2B; font-size:15px; line-height:1.7;">
        Terima kasih, {{ $payment->user->name }}. Pembayaranmu sudah kami terima dan akses paketmu <strong>sudah aktif</strong>.
    </p>

    {{-- KWITANSI --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color:#E3F4EA; border-radius:12px; margin-bottom:24px;">
        <tr>
            <td style="padding:20px 24px;">
                <p style="margin:0 0 4px; color:#27A35A; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                    Lunas — {{ $payment->updated_at->translatedFormat('d M Y, H:i') }}
                </p>
                <p style="margin:0 0 16px; color:#22384D; font-size:14px; font-weight:700; font-family:'Courier New', monospace;">
                    {{ $payment->external_id }}
                </p>

                <p style="margin:0 0 4px; color:#7B8794; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                    Paket
                </p>
                <p style="margin:0 0 16px; color:#22384D; font-size:15px; font-weight:700;">
                    {{ $payment->packagePlan->package->name }} — {{ $payment->packagePlan->name }}
                </p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <p style="margin:0 0 4px; color:#7B8794; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                                Total Dibayar
                            </p>
                            <p style="margin:0; color:#22384D; font-size:20px; font-weight:800; font-family:'Courier New', monospace;">
                                Rp{{ number_format($payment->amount, 0, ',', '.') }}
                            </p>
                        </td>
                        <td align="right">
                            <p style="margin:0 0 4px; color:#7B8794; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                                Aktif Sampai
                            </p>
                            <p style="margin:0; color:#27A35A; font-size:16px; font-weight:800;">
                                {{ $subscription->expires_at->translatedFormat('d M Y') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- CTA KE DASHBOARD --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
        <tr>
            <td align="center">
                <a href="{{ route('user.dashboard') }}"
                    style="display:inline-block; background-color:#22384D; color:#FFFFFF; font-size:15px; font-weight:700; text-decoration:none; padding:14px 40px; border-radius:12px;">
                    Mulai Belajar Sekarang
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0; color:#7B8794; font-size:13px; line-height:1.6;">
        Semua try out dan materi dalam paket ini sudah terbuka di dashboard kamu. Semangat! 💪
    </p>
</x-emails.layout>
