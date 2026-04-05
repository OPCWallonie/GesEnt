<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        h1 { font-size: 22px; color: #1e3a5f; margin: 0; }
        .doc-ref { font-size: 11px; color: #374151; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
        .badge-valide { background: #dbeafe; color: #1e40af; }
        .badge-brouillon { background: #f3f4f6; color: #374151; }
        .badge-en_attente { background: #fef3c7; color: #92400e; }
        .footer { position: fixed; bottom: 0; left: 20px; right: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }
        page { page-break-after: always; }
    </style>
</head>
<body>

{{-- En-tête document --}}
<table width="100%" style="margin-bottom: 25px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px;">
    <tr>
        <td>
            <h1>DEVIS</h1>
            <p class="doc-ref" style="margin-top: 4px;">
                <strong>{{ $devis->numero }}</strong>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Date : {{ $devis->date_document->format('d/m/Y') }}
                @if($devis->date_validite)
                    &nbsp;&nbsp;|&nbsp;&nbsp; Valide jusqu'au : <strong>{{ $devis->date_validite->format('d/m/Y') }}</strong>
                @endif
                @if($devis->chantier)
                    <br>Chantier : {{ $devis->chantier->nom }}
                @endif
            </p>
        </td>
    </tr>
</table>

@php $document = $devis; @endphp
@include('pdf.partials.entete')
@include('pdf.partials.lignes', ['lignes' => $devis->lignes])
@include('pdf.partials.totaux')

{{-- Mode règlement --}}
@if($devis->modePaiement || $devis->delai_reglement)
    <div style="margin-top: 20px; padding: 8px; background: #f9fafb; border-radius: 4px; font-size: 9px;">
        @if($devis->modePaiement)
            <strong>Mode de règlement :</strong> {{ $devis->modePaiement->nom }}&nbsp;&nbsp;
        @endif
        @if($devis->delai_reglement)
            <strong>Délai :</strong> {{ $devis->delai_reglement }} jours
        @endif
        @if($devis->modePaiement?->instructions)
            <br>{{ $devis->modePaiement->instructions }}
        @endif
    </div>
@endif

{{-- Coordonnées bancaires --}}
@if($parametres->iban)
    <div style="margin-top: 8px; font-size: 9px; color: #374151;">
        <strong>Coordonnées bancaires :</strong>
        IBAN : {{ $parametres->iban }}
        @if($parametres->bic) &nbsp;| BIC : {{ $parametres->bic }} @endif
        @if($parametres->banque) &nbsp;| {{ $parametres->banque }} @endif
    </div>
@endif

{{-- Conditions générales --}}
@if($parametres->conditions_generales)
    <div style="margin-top: 15px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px;">
        {{ $parametres->conditions_generales }}
    </div>
@endif

<div class="footer">
    {{ $parametres->nom }}
    @if($parametres->numero_tva) &nbsp;— TVA : {{ $parametres->numero_tva }} @endif
    @if($parametres->mentions_pied_page) &nbsp;— {{ $parametres->mentions_pied_page }} @endif
    &nbsp;— Page <span class="pagenum"></span>
</div>

</body>
</html>
