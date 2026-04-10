<?php

namespace App\Services;

use App\Models\ParametresEntreprise;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfigService
{
    /**
     * Override the SMTP configuration at runtime using parametres_entreprise.
     * Falls back silently if no SMTP host is configured.
     */
    public static function configure(): void
    {
        $p = ParametresEntreprise::instance();

        if (empty($p->mail_host)) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $p->mail_host,
            'port'       => $p->mail_port ?? 587,
            'encryption' => $p->mail_encryption ?: null,
            'username'   => $p->mail_username,
            'password'   => $p->mail_password, // auto-decrypted via 'encrypted' cast
            'timeout'    => null,
        ]);
        Config::set('mail.from', [
            'address' => $p->mail_from_address ?: ($p->email ?? 'noreply@example.com'),
            'name'    => $p->mail_from_name ?: $p->nom,
        ]);

        // Purge cached mailer so the next Mail::send() picks up new config
        Mail::purge('smtp');
    }
}
