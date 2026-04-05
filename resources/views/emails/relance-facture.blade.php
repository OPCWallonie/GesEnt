@php
    $parametres = \App\Models\ParametresEntreprise::instance();
    $retard = $facture->date_echeance ? (int) $facture->date_echeance->diffInDays(now()) : 0;
@endphp

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; line-height: 1.6;">

    <p>Bonjour,</p>

    @if($niveauRelance === 1)
    <p>
        Nous nous permettons de vous rappeler que notre facture
        <strong>{{ $facture->numero }}</strong>
        d'un montant de <strong>{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</strong>
        est arrivée à échéance le <strong>{{ $facture->date_echeance?->format('d/m/Y') }}</strong>.
    </p>
    <p>Si votre paiement a été effectué entre-temps, nous vous prions de ne pas tenir compte de ce rappel.</p>

    @elseif($niveauRelance === 2)
    <p>
        Sauf erreur de notre part, notre facture <strong>{{ $facture->numero }}</strong>
        d'un montant de <strong>{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</strong>
        reste impayée à ce jour, soit <strong>{{ $retard }} jour{{ $retard > 1 ? 's' : '' }}</strong>
        après l'échéance du {{ $facture->date_echeance?->format('d/m/Y') }}.
    </p>
    <p>Nous vous remercions de bien vouloir régulariser cette situation dans les meilleurs délais.</p>

    @else
    <p>
        Malgré nos rappels précédents, notre facture <strong>{{ $facture->numero }}</strong>
        d'un montant de <strong>{{ number_format($facture->montant_net_a_payer, 2, ',', ' ') }} €</strong>
        reste impayée (<strong>{{ $retard }} jour{{ $retard > 1 ? 's' : '' }}</strong> de retard).
    </p>
    <p>
        Sans règlement de votre part sous 8 jours, nous nous verrons dans l'obligation
        d'engager une procédure de recouvrement.
    </p>
    @endif

    <p>Veuillez trouver ci-joint un exemplaire de la facture.</p>

    <p>
        Cordialement,<br>
        <strong>{{ $parametres->nom_entreprise ?? $parametres->nom ?? 'L\'entreprise' }}</strong>
    </p>
</div>
