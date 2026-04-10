<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #111827; margin: 0; padding: 20px; }
        h1 { font-size: 18px; color: #1e3a5f; margin: 0 0 4px 0; }
        .footer { position: fixed; bottom: 0; left: 20px; right: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 4px; }
        .corps { margin-top: 30px; font-size: 11px; line-height: 1.8; white-space: pre-line; }
        .ref-block { margin: 20px 0; padding: 10px; border-left: 3px solid #1e3a5f; background: #f8fafc; font-size: 10px; }
        .lieu-date { text-align: right; margin-bottom: 30px; font-size: 10px; color: #555; }
    </style>
</head>
<body>

@php $document = $facture; @endphp
@include('pdf.partials.entete')

<div class="lieu-date">
    {{ $parametres->ville ? $parametres->ville . ', ' : '' }}le {{ now()->isoFormat('D MMMM YYYY') }}
</div>

<h1>
    @if($etape->ton === 'formel')
        MISE EN DEMEURE
    @elseif($etape->ton === 'ferme')
        RELANCE — Facture impayée
    @else
        Rappel — Facture {{ $facture->numero }}
    @endif
</h1>

<div class="ref-block">
    <strong>Facture :</strong> {{ $facture->numero }}
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <strong>Montant :</strong> {{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <strong>Échéance :</strong> {{ $facture->date_echeance?->format('d/m/Y') ?? '—' }}
    @if($facture->chantier)
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Chantier :</strong> {{ $facture->chantier->nom }}
    @endif
</div>

@php
    $joursRetard = $facture->date_echeance
        ? max(0, (int) $facture->date_echeance->diffInDays(now()))
        : 0;
    $soldeDu = number_format((float) $facture->montant_net_a_payer, 2, ',', ' ') . ' €';

    $vars = [
        '{client}'         => $facture->client?->nom ?? '',
        '{numero}'         => $facture->numero ?? '',
        '{montant}'        => $soldeDu,
        '{solde_du}'       => $soldeDu,
        '{entreprise}'     => $parametres->nom ?? '',
        '{jours_retard}'   => (string) $joursRetard,
        '{date_facture}'   => $facture->date_echeance?->format('d/m/Y') ?? '',
        '{date_rappel}'    => now()->format('d/m/Y'),
        '{chantier}'       => $facture->chantier?->nom ?? '',
        '{nb_relance}'     => (string) ($facture->nb_relances + 1),
        '{delai_paiement}' => (string) ($facture->delai_reglement ?? 30),
    ];
    $corps = str_replace(array_keys($vars), array_values($vars), $etape->corps_email);
@endphp

<div class="corps">{{ $corps }}</div>

@if($etape->ton === 'formel')
<div style="margin-top: 40px; font-size: 10px; color: #555; border-top: 1px solid #e5e7eb; padding-top: 12px;">
    <p>
        En l'absence de règlement ou de contestation motivée dans un délai de <strong>8 jours</strong> à compter
        de la réception de ce courrier, nous nous réservons le droit d'engager toute procédure de recouvrement
        judiciaire, les frais étant à votre charge.
    </p>
    <p style="margin-top: 8px;">
        Ce courrier vaut mise en demeure au sens de l'article 1344 du Code civil.
    </p>
</div>
@endif

<div style="margin-top: 50px; font-size: 10px;">
    <p>{{ $parametres->nom }}</p>
    @if($parametres->mail_from_name && $parametres->mail_from_name !== $parametres->nom)
        <p>{{ $parametres->mail_from_name }}</p>
    @endif
    <p style="margin-top: 20px;">_______________________<br>Signature</p>
</div>

<div class="footer">
    {{ $parametres->nom }}
    @if($parametres->numero_tva) &nbsp;— TVA : {{ $parametres->numero_tva }} @endif
    @if($parametres->mentions_pied_page) &nbsp;— {{ $parametres->mentions_pied_page }} @endif
</div>

</body>
</html>
