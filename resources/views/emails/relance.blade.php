<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; line-height: 1.6;">

    <div style="white-space: pre-line;">{{ $corpsEmail }}</div>

    @if($signature)
        <div style="background:#fafafa;border:1px solid #e5e7eb;border-radius:6px;padding:16px;margin:20px 0;white-space:pre-line;">{{ $signature }}</div>
    @else
        <p>
            Cordialement,<br>
            <strong>{{ $parametres->nom ?? 'L\'entreprise' }}</strong>
        </p>
    @endif

</div>
