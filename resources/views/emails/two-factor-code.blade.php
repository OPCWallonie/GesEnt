<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><title>Code de vérification</title></head>
<body style="font-family:sans-serif;background:#f9fafb;padding:40px 0;margin:0">
    <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
        <div style="background:#2563eb;padding:24px 32px">
            <h1 style="color:#fff;margin:0;font-size:20px">{{ config('app.name') }}</h1>
        </div>
        <div style="padding:32px">
            <p style="margin:0 0 12px;color:#374151;font-size:15px">
                Bonjour {{ $user->name }},
            </p>
            <p style="margin:0 0 24px;color:#374151;font-size:15px">
                Voici votre code de vérification à usage unique :
            </p>
            <div style="text-align:center;margin:0 0 24px">
                <span style="font-size:36px;font-weight:700;letter-spacing:12px;color:#1d4ed8;font-family:monospace">
                    {{ $code }}
                </span>
            </div>
            <p style="margin:0 0 8px;color:#6b7280;font-size:13px">
                Ce code est valable <strong>10 minutes</strong>.
            </p>
            <p style="margin:0;color:#6b7280;font-size:13px">
                Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.
            </p>
        </div>
    </div>
</body>
</html>
