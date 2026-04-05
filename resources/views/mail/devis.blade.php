<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; line-height: 1.6; margin: 0; padding: 0; background: #f9fafb; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .header { background: #1e3a5f; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; opacity: .7; font-size: 14px; }
        .body { padding: 32px; }
        .body p { margin: 0 0 16px; }
        .highlight { background: #eff6ff; border-left: 4px solid #2563eb; border-radius: 4px; padding: 16px 20px; margin: 24px 0; }
        .highlight .label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
        .highlight .value { font-size: 24px; font-weight: 700; color: #1e40af; margin-top: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
        .info-item .label { font-size: 12px; color: #9ca3af; }
        .info-item .value { font-weight: 600; color: #374151; }
        .footer { background: #f3f4f6; padding: 20px 32px; font-size: 12px; color: #9ca3af; text-align: center; }
        .message-box { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin: 20px 0; white-space: pre-line; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ $parametres->nom ?? config('app.name') }}</h1>
        <p>Devis — {{ $devis->numero }}</p>
    </div>
    <div class="body">
        <p>Bonjour,</p>
        <p>Veuillez trouver ci-joint votre devis en pièce jointe (PDF).</p>

        @if($messagePersonnalise)
            <div class="message-box">{{ $messagePersonnalise }}</div>
        @endif

        <div class="highlight">
            <div class="label">Montant total TTC</div>
            <div class="value">{{ number_format($devis->montant_ttc, 2, ',', ' ') }} €</div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="label">Numéro de devis</div>
                <div class="value">{{ $devis->numero }}</div>
            </div>
            <div class="info-item">
                <div class="label">Date d'émission</div>
                <div class="value">{{ $devis->date_document->format('d/m/Y') }}</div>
            </div>
            @if($devis->date_validite)
            <div class="info-item">
                <div class="label">Valide jusqu'au</div>
                <div class="value">{{ $devis->date_validite->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($devis->chantier)
            <div class="info-item">
                <div class="label">Chantier</div>
                <div class="value">{{ $devis->chantier->nom }}</div>
            </div>
            @endif
        </div>

        <p>Pour toute question, n'hésitez pas à nous contacter.</p>
        <p>Cordialement,<br><strong>{{ $parametres->nom ?? config('app.name') }}</strong></p>
    </div>
    <div class="footer">
        @if($parametres->telephone) Tél : {{ $parametres->telephone }} — @endif
        @if($parametres->email) {{ $parametres->email }} @endif
        @if($parametres->numero_tva) — TVA : {{ $parametres->numero_tva }} @endif
    </div>
</div>
</body>
</html>
