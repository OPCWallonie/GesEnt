<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $user->update([
                'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            ]);
        }

        $secret = decrypt($user->two_factor_secret);
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'Gesent'),
            $user->email,
            $secret
        );

        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $writer = new \BaconQrCode\Writer(
            new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                $renderer
            )
        );
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor-setup', compact('secret', 'qrCodeSvg'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->with('error', 'Code invalide. Réessayez.');
        }

        $user->update([
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Authentification à deux facteurs activée.');
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $request->user()->update([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_confirmed_at' => null,
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
        $request->validate(['code' => 'required|digits:6']);

        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->with('error', 'Code invalide.');
        }

        session(['2fa_verified' => true]);
        return redirect()->intended(route('dashboard'));
    }
}
