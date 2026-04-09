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
            <h1>BON DE COMMANDE</h1>
            <p class="doc-ref" style="margin-top: 4px;">
                <strong>{{ $bonCommande->numero }}</strong>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                Date : {{ $bonCommande->date_document->format('d/m/Y') }}
                @if($bonCommande->date_debut_travaux)
                    &nbsp;&nbsp;|&nbsp;&nbsp; Début travaux : {{ $bonCommande->date_debut_travaux->format('d/m/Y') }}
                @endif
                @if($bonCommande->chantier)
                    <br>Chantier : {{ $bonCommande->chantier->nom }}
                @endif
            </p>
        </td>
    </tr>
</table>

@php $document = $bonCommande; @endphp
@include('pdf.partials.entete')

{{-- Lignes BDC --}}
@include('pdf.partials.lignes', ['lignes' => $bonCommande->lignes])

{{-- Avenants --}}
@foreach($bonCommande->avenants as $avenant)
    @if($avenant->lignes->count() > 0)
        <div style="margin-top: 12px; padding: 5px 0; border-top: 1px dashed #93c5fd;">
            <p style="font-size: 10px; font-weight: bold; color: #1e40af;">
                Avenant {{ $avenant->numero_ordre }} — {{ $avenant->numero }}
                @if($avenant->objet) : {{ $avenant->objet }} @endif
            </p>
        </div>
        @include('pdf.partials.lignes', ['lignes' => $avenant->lignes])
    @endif
@endforeach

{{-- Totaux cumulés --}}
<table width="100%" style="margin-top: 15px;">
    <tr>
        <td width="60%"></td>
        <td width="40%" valign="top">
            <table width="100%" style="font-size: 10px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 3px 6px;">Total HT</td>
                    <td align="right" style="padding: 3px 6px; font-weight: bold;">{{ number_format($totaux['ht'], 2, ',', ' ') }} €</td>
                </tr>
                @foreach($totauxTva as $taux => $montant)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">TVA {{ number_format((float)$taux, 0) }}%</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">{{ number_format($montant, 2, ',', ' ') }} €</td>
                    </tr>
                @endforeach
                <tr style="background-color: #1e3a5f; color: white;">
                    <td style="padding: 5px 6px; font-weight: bold;">Total TTC</td>
                    <td align="right" style="padding: 5px 6px; font-weight: bold;">{{ number_format($totaux['ttc'], 2, ',', ' ') }} €</td>
                </tr>
                @if($totaux['acompte'] > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">Acompte</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">-{{ number_format($totaux['acompte'], 2, ',', ' ') }} €</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

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
