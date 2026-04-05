<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        h1 { font-size: 22px; color: #1e3a5f; margin: 0; }
        .doc-ref { font-size: 11px; color: #374151; }
        .footer { position: fixed; bottom: 0; left: 20px; right: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }
    </style>
</head>
<body>

<table width="100%" style="margin-bottom: 25px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px;">
    <tr>
        <td>
            <h1>FACTURE</h1>
            <p class="doc-ref" style="margin-top: 4px;">
                <strong>{{ $facture->numero }}</strong>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Date : {{ $facture->date_document->format('d/m/Y') }}
                @if($facture->date_echeance)
                    &nbsp;&nbsp;|&nbsp;&nbsp; Échéance : <strong>{{ $facture->date_echeance->format('d/m/Y') }}</strong>
                @endif
                @if($facture->bonCommande)
                    <br>Réf. BDC : {{ $facture->bonCommande->numero }}
                @endif
                @if($facture->chantier)
                    &nbsp;&nbsp;| Chantier : {{ $facture->chantier->nom }}
                @endif
            </p>
        </td>
    </tr>
</table>

@php $document = $facture; @endphp
@include('pdf.partials.entete')
@include('pdf.partials.lignes', ['lignes' => $facture->lignes])
@include('pdf.partials.totaux')

@if($facture->modePaiement || $facture->delai_reglement)
    <div style="margin-top: 20px; padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 9px;">
        <strong>Modalités de paiement :</strong>
        @if($facture->modePaiement) {{ $facture->modePaiement->nom }} &nbsp; @endif
        @if($facture->delai_reglement) — {{ $facture->delai_reglement }} jours @endif
        @if($facture->date_echeance)
            <strong style="color: #dc2626;">— À régler avant le {{ $facture->date_echeance->format('d/m/Y') }}</strong>
        @endif
    </div>
@endif

@if($parametres->iban)
    <div style="margin-top: 8px; padding: 8px; background: #eff6ff; font-size: 9px; color: #1e40af; border-radius: 4px;">
        <strong>Virement à effectuer sur :</strong>
        IBAN : {{ $parametres->iban }}
        @if($parametres->bic) &nbsp;| BIC : {{ $parametres->bic }} @endif
        @if($parametres->banque) &nbsp;| {{ $parametres->banque }} @endif
        <br>Communication : <strong>{{ $facture->numero }}</strong>
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
