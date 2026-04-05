<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        h1 { font-size: 22px; color: #991b1b; margin: 0; }
        .doc-ref { font-size: 11px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #991b1b; color: #fff; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .total-row td { font-weight: bold; background: #fef2f2; border-top: 2px solid #991b1b; }
        .label { color: #6b7280; }
        .footer { position: fixed; bottom: 0; left: 20px; right: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }
        .badge { display: inline-block; background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body>

<table style="margin-bottom: 25px; border-bottom: 2px solid #991b1b; padding-bottom: 10px;">
    <tr>
        <td>
            <h1>NOTE DE CRÉDIT / AVOIR</h1>
            <p class="doc-ref" style="margin-top: 4px;">
                <strong>{{ $avoir->numero }}</strong>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Date : {{ $avoir->date_document->format('d/m/Y') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Réf. facture : <strong>{{ $avoir->facture->numero }}</strong>
            </p>
        </td>
        <td style="text-align: right; vertical-align: top;">
            @if($parametres->nom)
                <strong style="font-size: 12px;">{{ $parametres->nom }}</strong><br>
                @if($parametres->adresse) {{ $parametres->adresse }}<br> @endif
                @if($parametres->code_postal || $parametres->ville) {{ $parametres->code_postal }} {{ $parametres->ville }}<br> @endif
                @if($parametres->telephone) Tél : {{ $parametres->telephone }}<br> @endif
                @if($parametres->email) {{ $parametres->email }}<br> @endif
                @if($parametres->numero_tva) TVA : {{ $parametres->numero_tva }} @endif
            @endif
        </td>
    </tr>
</table>

{{-- Destinataire --}}
<table style="margin-bottom: 20px;">
    <tr>
        <td width="50%">
            <div style="background: #fef2f2; border-left: 3px solid #991b1b; padding: 10px 14px; border-radius: 4px;">
                <div style="font-size: 8px; text-transform: uppercase; color: #9ca3af; margin-bottom: 4px;">Crédit accordé à</div>
                <strong style="font-size: 11px;">{{ $avoir->client->nom }}</strong><br>
                @if($avoir->client->adresse) {{ $avoir->client->adresse }}<br> @endif
                @if($avoir->client->code_postal || $avoir->client->ville) {{ $avoir->client->code_postal }} {{ $avoir->client->ville }}<br> @endif
                @if($avoir->client->numero_tva) TVA : {{ $avoir->client->numero_tva }} @endif
            </div>
        </td>
        <td width="50%" style="padding-left: 20px; vertical-align: top;">
            @if($avoir->chantier)
                <div style="font-size: 9px; color: #6b7280; margin-bottom: 3px;">Chantier</div>
                <strong>{{ $avoir->chantier->nom }}</strong><br>
            @endif
        </td>
    </tr>
</table>

{{-- Motif --}}
<div style="margin-bottom: 20px; padding: 12px 14px; background: #fff7f7; border: 1px solid #fecaca; border-radius: 4px;">
    <div style="font-size: 9px; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px;">Motif de l'avoir</div>
    <p style="margin: 0; line-height: 1.5;">{{ $avoir->motif }}</p>
</div>

{{-- Tableau montants --}}
<table>
    <thead>
        <tr>
            <th>Description</th>
            <th style="text-align: right; width: 120px;">Montant HT</th>
            <th style="text-align: right; width: 80px;">TVA</th>
            <th style="text-align: right; width: 130px;">Montant TTC</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Avoir sur facture {{ $avoir->facture->numero }}<br>
                <span style="color: #6b7280; font-size: 9px;">{{ $avoir->motif }}</span>
            </td>
            <td style="text-align: right;">{{ number_format($avoir->montant_ht, 2, ',', ' ') }} €</td>
            <td style="text-align: right;">{{ number_format($avoir->taux_tva, 0) }}%</td>
            <td style="text-align: right;">{{ number_format($avoir->montant_ttc, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right; color: #6b7280; font-size: 9px; padding-top: 8px;">Montant HT :</td>
            <td colspan="2" style="text-align: right; padding-top: 8px;">{{ number_format($avoir->montant_ht, 2, ',', ' ') }} €</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: right; color: #6b7280; font-size: 9px;">TVA {{ number_format($avoir->taux_tva, 0) }}% :</td>
            <td colspan="2" style="text-align: right;">{{ number_format($avoir->montant_tva, 2, ',', ' ') }} €</td>
        </tr>
        <tr class="total-row">
            <td colspan="2" style="text-align: right;">TOTAL À DÉDUIRE TTC :</td>
            <td colspan="2" style="text-align: right; font-size: 14px; color: #991b1b;">
                {{ number_format($avoir->montant_ttc, 2, ',', ' ') }} €
            </td>
        </tr>
    </tbody>
</table>

@if($avoir->notes)
    <div style="margin-top: 20px; font-size: 9px; color: #6b7280;">
        <strong>Notes :</strong> {{ $avoir->notes }}
    </div>
@endif

@if($parametres->conditions_generales)
    <div style="margin-top: 15px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px;">
        {{ $parametres->conditions_generales }}
    </div>
@endif

<div class="footer">
    {{ $parametres->nom }}
    @if($parametres->numero_tva) &nbsp;— TVA : {{ $parametres->numero_tva }} @endif
    @if($parametres->mentions_pied_page) &nbsp;— {{ $parametres->mentions_pied_page }} @endif
</div>

</body>
</html>
