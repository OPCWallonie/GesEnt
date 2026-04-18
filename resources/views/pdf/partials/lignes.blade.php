{{-- Tableau des lignes commun à tous les PDF --}}
<table width="100%" style="border-collapse: collapse; font-size: 10px; margin-top: 10px;">
    <thead>
        <tr style="background-color: #1e3a5f; color: white;">
            <th align="left"  style="padding: 6px 8px; width: 40%;">Désignation</th>
            <th align="center" style="padding: 6px 8px; width: 8%;">Unité</th>
            <th align="right"  style="padding: 6px 8px; width: 7%;">Qté</th>
            <th align="right"  style="padding: 6px 8px; width: 12%;">Prix unit. HT</th>
            <th align="right"  style="padding: 6px 8px; width: 10%;">Remise</th>
            <th align="right"  style="padding: 6px 8px; width: 7%;">TVA %</th>
            <th align="right"  style="padding: 6px 8px; width: 12%;">Total HT</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lignes as $ligne)
            @if($ligne->est_section)
                <tr>
                    <td colspan="7" style="padding: 8px; background-color: #e8f0fe; font-weight: bold; font-size: 11px; color: #1a3a6b;">
                        {{ $ligne->designation }}
                    </td>
                </tr>
            @else
                <tr style="background-color: {{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                    <td style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">
                        <strong>{{ $ligne->designation }}</strong>
                        @if($ligne->detail)
                            <br><span style="color: #6b7280; font-size: 9px; white-space: pre-line;">{{ $ligne->detail }}</span>
                        @endif
                    </td>
                    <td align="center" style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">{{ $ligne->unite }}</td>
                    <td align="right"  style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">{{ number_format($ligne->quantite, 2, ',', ' ') }}</td>
                    <td align="right"  style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">{{ number_format($ligne->prix_unitaire, 4, ',', ' ') }} €</td>
                    <td align="right"  style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">
                        @if($ligne->remise_valeur > 0)
                            {{ number_format($ligne->remise_valeur, 2, ',', ' ') }}{{ $ligne->remise_type === 'pourcentage' ? ' %' : ' €' }}
                        @else
                            —
                        @endif
                    </td>
                    <td align="right"  style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb;">{{ number_format($ligne->taux_tva, 0) }} %</td>
                    <td align="right"  style="padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-weight: bold;">{{ number_format($ligne->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
