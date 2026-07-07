{{--
    Layout email Cetar — table-based + inline CSS (wajib untuk kompatibilitas email client).
    Brand: header orange #F5872A, teks navy #22384D, angka monospace.
--}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="margin:0; padding:0; background-color:#F4F6F5; font-family:'Segoe UI', Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4F6F5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0"
                    style="max-width:560px; width:100%; background-color:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(16,24,40,.08);">

                    {{-- HEADER BRAND --}}
                    <tr>
                        <td style="background:linear-gradient(100deg,#F5872A 0%,#FBB823 100%); padding:24px 32px;">
                            <span style="color:#FFFFFF; font-size:22px; font-weight:800; letter-spacing:-0.5px;">
                                {{ config('app.name', 'Cetar') }}
                            </span>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="padding:32px;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="padding:20px 32px; background-color:#FBF9F6;">
                            <p style="margin:0; color:#9AA7AE; font-size:12px; line-height:1.6;">
                                Email ini dikirim otomatis oleh {{ config('app.name', 'Cetar') }} — mohon tidak membalas.
                                <br>© {{ date('Y') }} {{ config('app.name', 'Cetar') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
