{{-- Entête commun à tous les documents PDF --}}
<table width="100%" style="margin-bottom: 20px;">
    <tr>
        <td width="50%" valign="top">
            @if($parametres->logo_path && file_exists(storage_path('app/public/' . $parametres->logo_path)))
                <img src="{{ storage_path('app/public/' . $parametres->logo_path) }}"
                     style="max-height: 70px; max-width: 200px;">
            @endif
            <p style="margin-top: 8px; font-weight: bold; font-size: 13px;">{{ $parametres->nom }}</p>
            @if($parametres->statut_juridique)
                <p style="font-size: 10px; color: #666;">{{ $parametres->statut_juridique }}</p>
            @endif
            <p style="font-size: 10px;">{{ $parametres->adresse }}</p>
            <p style="font-size: 10px;">{{ $parametres->code_postal }} {{ $parametres->ville }}</p>
            @if($parametres->telephone)
                <p style="font-size: 10px;">Tél : {{ $parametres->telephone }}</p>
            @endif
            @if($parametres->email)
                <p style="font-size: 10px;">{{ $parametres->email }}</p>
            @endif
            @if($parametres->numero_tva)
                <p style="font-size: 10px;">TVA : {{ $parametres->numero_tva }}</p>
            @endif
        </td>
        <td width="50%" valign="top" align="right">
            <p style="font-size: 10px; color: #555;">Destinataire :</p>
            <p style="font-weight: bold; font-size: 12px;">{{ $document->client->nom }}</p>
            @if($document->client->statut_juridique)
                <p style="font-size: 10px; color: #666;">{{ $document->client->statut_juridique }}</p>
            @endif
            <p style="font-size: 10px;">{{ $document->client->adresse }}</p>
            <p style="font-size: 10px;">{{ $document->client->code_postal }} {{ $document->client->ville }}</p>
            @if($document->client->numero_tva)
                <p style="font-size: 10px;">TVA : {{ $document->client->numero_tva }}</p>
            @endif
        </td>
    </tr>
</table>
