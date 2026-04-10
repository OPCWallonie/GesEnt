{{--
    Totaux PDF communs.
    Variables attendues :
    - $document : Devis|BonCommande|Facture
    - $totauxTva : array [taux => ['ht' => float, 'tva' => float]]
    - $labelAcompte (optionnel) : string
--}}
<table width="100%" style="margin-top: 15px;">
    <tr>
        <td width="60%">
            @if(isset($document->notes) && $document->notes)
                <p style="font-size: 9px; color: #6b7280; margin-top: 5px;"><em>{{ $document->notes }}</em></p>
            @endif
        </td>
        <td width="40%" valign="top">
            <table width="100%" style="font-size: 10px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 3px 6px;">Total HT</td>
                    <td align="right" style="padding: 3px 6px; font-weight: bold;">{{ number_format($document->montant_ht, 2, ',', ' ') }} €</td>
                </tr>
                @foreach($totauxTva as $taux => $montant)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">TVA {{ number_format((float)$taux, 0) }}%</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">{{ number_format($montant['tva'], 2, ',', ' ') }} €</td>
                    </tr>
                @endforeach
                @if($document->frais_port > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">Frais de port</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">{{ number_format($document->frais_port, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                @if(isset($document->ristourne_globale) && $document->ristourne_globale > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #dc2626;">Ristourne {{ number_format($document->ristourne_globale, 0) }}%</td>
                        <td align="right" style="padding: 3px 6px; color: #dc2626;">-</td>
                    </tr>
                @endif
                <tr style="background-color: #1e3a5f; color: white;">
                    <td style="padding: 5px 6px; font-weight: bold;">Total TTC</td>
                    <td align="right" style="padding: 5px 6px; font-weight: bold;">{{ number_format($document->montant_ttc, 2, ',', ' ') }} €</td>
                </tr>
                @if(isset($document->acompte) && $document->acompte > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">Acompte versé</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">-{{ number_format($document->acompte, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                @if(isset($document->acompte_deduit) && $document->acompte_deduit > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #6b7280;">Acompte déduit</td>
                        <td align="right" style="padding: 3px 6px; color: #6b7280;">-{{ number_format($document->acompte_deduit, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                @if(isset($document->retenue_garantie_pct) && $document->retenue_garantie_pct > 0)
                    <tr>
                        <td style="padding: 3px 6px; color: #92400e;">Retenue de garantie ({{ number_format($document->retenue_garantie_pct, 0) }}%)</td>
                        <td align="right" style="padding: 3px 6px; color: #92400e;">-{{ number_format($document->retenue_garantie_montant, 2, ',', ' ') }} €</td>
                    </tr>
                @endif
                @if(isset($document->montant_net_a_payer))
                    <tr style="background-color: #dcfce7;">
                        <td style="padding: 5px 6px; font-weight: bold; color: #166534;">NET À PAYER</td>
                        <td align="right" style="padding: 5px 6px; font-weight: bold; color: #166534;">{{ number_format($document->montant_net_a_payer, 2, ',', ' ') }} €</td>
                    </tr>
                    @if(isset($document->retenue_garantie_pct) && $document->retenue_garantie_pct > 0)
                        <tr>
                            <td colspan="2" style="padding: 4px 6px; font-size: 8px; color: #92400e; font-style: italic;">
                                Dont {{ number_format($document->retenue_garantie_montant, 2, ',', ' ') }} € de retenue de garantie ({{ number_format($document->retenue_garantie_pct, 0) }}%) libérable en fin de période de garantie.
                            </td>
                        </tr>
                    @endif
                @endif
            </table>
        </td>
    </tr>
</table>
