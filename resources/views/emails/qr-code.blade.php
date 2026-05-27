<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tu QR de Webnu</title>
</head>
<body style="margin:0;padding:0;background-color:#f6f8fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f6f8fb;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="560" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 14px rgba(15,23,42,0.06);">
                    <tr>
                        <td style="padding:28px 32px 8px;background:#ffffff;">
                            <h1 style="margin:0;font-size:22px;color:#0f172a;letter-spacing:-0.01em;">Tu QR está listo</h1>
                            <p style="margin:8px 0 0;font-size:14px;color:#475569;line-height:1.5;">
                                Aquí tienes el código QR de <strong>{{ $company->name }}</strong> en PDF, adjunto a este correo.
                                @if($copies > 1)
                                    Incluye <strong>{{ $copies }} copias por hoja</strong>, listas para imprimir.
                                @else
                                    Listo para imprimir en una hoja A4.
                                @endif
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                <tr>
                                    <td style="padding:14px 18px;font-size:13px;color:#475569;">
                                        <p style="margin:0 0 4px;font-weight:600;color:#0f172a;">Tu carta pública</p>
                                        <a href="{{ $publicUrl }}" style="color:#004ac6;text-decoration:none;word-break:break-all;">{{ $publicUrl }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 28px;">
                            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.5;">
                                Consejos rápidos:
                            </p>
                            <ul style="margin:6px 0 0;padding-left:18px;font-size:13px;color:#64748b;line-height:1.6;">
                                <li>Imprime el QR en papel mate y plastifícalo para tus mesas.</li>
                                <li>Comparte el enlace en tus redes sociales y en Google Maps.</li>
                                <li>Puedes generar más copias o cambiar el diseño desde el panel de Webnu.</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;">
                            Recibes este correo porque solicitaste el envío del QR desde tu panel de Webnu.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
