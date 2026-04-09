<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCode;
use App\Models\ParametresEntreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $user      = $request->user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->update([
                'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            ]);
        }

        $secret    = decrypt($user->two_factor_secret);
        $qrCodeUrl = $google2fa->getQRCodeUrl(config('app.name', 'Gesent'), $user->email, $secret);

        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            )
        );
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor-setup', compact('secret', 'qrCodeSvg'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user      = $request->user();
        $google2fa = new Google2FA();
        $secret    = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->with('error', 'Code invalide. Réessayez.');
        }

        // Générer 8 codes de récupération (format XXXXX-XXXXX)
        $plainCodes  = collect(range(1, 8))->map(fn() => Str::upper(Str::random(5)) . '-' . Str::upper(Str::random(5)));
        $hashedCodes = $plainCodes->map(fn($c) => Hash::make($c))->values()->all();

        $user->update([
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $hashedCodes,
        ]);

        // Stocker les codes en clair EN SESSION pour les afficher une seule fois
        session(['2fa_recovery_codes_plain' => $plainCodes->all()]);

        return redirect()->route('2fa.recovery-codes');
    }

    public function showRecoveryCodes(Request $request)
    {
        $codes = session()->pull('2fa_recovery_codes_plain', []);

        if (empty($codes) && !$request->user()->two_factor_enabled) {
            return redirect()->route('profile.edit');
        }

        return view('auth.two-factor-recovery-codes', compact('codes'));
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $request->user()->update([
            'two_factor_secret'            => null,
            'two_factor_enabled'           => false,
            'two_factor_confirmed_at'      => null,
            'two_factor_recovery_codes'    => null,
            'two_factor_email_code'        => null,
            'two_factor_email_code_expires_at' => null,
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Authentification à deux facteurs désactivée.');
    }

    public function verify()
    {
        return view('auth.two-factor-verify');
    }

    public function verifyCheck(Request $request)
    {
        $request->validate(['code' => 'required|string|min:6|max:13']);

        $user  = $request->user();
        $code  = trim($request->code);
        $valid = false;

        // Tentative 1 : code TOTP
        if (preg_match('/^\d{6}$/', $code)) {
            $google2fa = new Google2FA();
            $secret    = decrypt($user->two_factor_secret);
            $valid     = (bool) $google2fa->verifyKey($secret, $code);
        }

        // Tentative 2 : code email (6 chiffres mais via onglet email — on laisse le TOTP gérer les 6 chiffres ; le code email est aussi 6 chiffres mais stocké en session différemment)
        if (!$valid && $request->filled('via_email') && preg_match('/^\d{6}$/', $code)) {
            if ($user->two_factor_email_code
                && $user->two_factor_email_code_expires_at?->isFuture()
                && Hash::check($code, $user->two_factor_email_code)
            ) {
                $valid = true;
                $user->update(['two_factor_email_code' => null, 'two_factor_email_code_expires_at' => null]);
            }
        }

        // Tentative 3 : code de récupération (format XXXXX-XXXXX)
        if (!$valid && preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}$/i', $code)) {
            $codes   = $user->two_factor_recovery_codes ?? [];
            $idx     = null;
            $codeUp  = Str::upper($code);

            foreach ($codes as $i => $hashed) {
                if (Hash::check($codeUp, $hashed)) {
                    $idx = $i;
                    break;
                }
            }

            if ($idx !== null) {
                $valid = true;
                array_splice($codes, $idx, 1);
                $user->update(['two_factor_recovery_codes' => array_values($codes)]);
            }
        }

        if (!$valid) {
            return back()->with('error', 'Code invalide ou expiré.');
        }

        session(['2fa_verified' => true]);
        return redirect()->intended(route('dashboard'));
    }

    public function sendEmailCode(Request $request)
    {
        $user = $request->user();

        $plain  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update([
            'two_factor_email_code'            => Hash::make($plain),
            'two_factor_email_code_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new TwoFactorCode($user, $plain));

        return back()->with('email_sent', 'Code envoyé à ' . $user->email . '. Valable 10 minutes.');
    }
}
