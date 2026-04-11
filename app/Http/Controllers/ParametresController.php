<?php

namespace App\Http\Controllers;

use App\Models\ParametresEntreprise;
use App\Services\MailConfigService;
use App\Services\OdooService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ParametresController extends Controller
{
    public function edit()
    {
        $parametres = ParametresEntreprise::instance();
        return view('parametres.edit', compact('parametres'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nom'                     => 'required|string|max:100',
            'statut_juridique'        => 'nullable|string|max:20',
            'adresse'                 => 'nullable|string|max:150',
            'code_postal'             => 'nullable|string|max:10',
            'ville'                   => 'nullable|string|max:80',
            'pays'                    => 'nullable|string|max:60',
            'telephone'               => 'nullable|string|max:30',
            'email'                   => 'nullable|email|max:120',
            'site_web'                => 'nullable|url|max:100',
            'numero_tva'              => 'nullable|string|max:30',
            'numero_entreprise'       => 'nullable|string|max:30',
            'iban'                    => 'nullable|string|max:34',
            'bic'                     => 'nullable|string|max:11',
            'banque'                  => 'nullable|string|max:80',
            'conditions_generales'    => 'nullable|string',
            'mentions_pied_page'      => 'nullable|string',
            'delai_reglement_defaut'  => 'nullable|integer|min:0',
            'validite_devis_defaut'   => 'nullable|integer|min:0',
            'logo'                    => 'nullable|image|max:2048',
            'ai_provider'             => 'nullable|string|in:claude,openai,gemini,mistral,ollama',
            'ai_api_key'              => 'nullable|string|max:500',
            'ai_model'                => 'nullable|string|max:100',
            'ai_url'                  => 'nullable|url|max:200',
            'peppol_mode'             => 'required|in:desactive,envoi,complet',
            'peppol_provider'         => 'nullable|in:storecove,billit,einvoice_be',
            'peppol_api_key'          => 'nullable|string|max:500',
            'peppol_entity_id'        => 'nullable|string|max:100',
            'peppol_id'               => 'nullable|string|max:50',
            'peppol_environment'      => 'nullable|in:sandbox,production',
            // Odoo
            'odoo_actif'              => 'nullable|boolean',
            'odoo_url'                => 'nullable|url|max:255',
            'odoo_database'           => 'nullable|string|max:100',
            'odoo_username'           => 'nullable|string|max:100',
            'odoo_api_key'            => 'nullable|string|max:500',
            'odoo_mapping'            => 'nullable|array',
            'odoo_mapping.*'          => 'nullable|string|max:20',
            'peppol_gere_par'             => 'nullable|in:gesent,odoo',
            // Sécurité
            'deux_facteurs_obligatoires'  => 'nullable|boolean',
            // Email / SMTP
            'mail_host'              => 'nullable|string|max:255',
            'mail_port'              => 'nullable|integer|min:1|max:65535',
            'mail_encryption'        => 'nullable|in:ssl,tls,starttls,',
            'mail_username'          => 'nullable|string|max:255',
            'mail_password'          => 'nullable|string|max:500',
            'mail_from_address'      => 'nullable|email|max:255',
            'mail_from_name'         => 'nullable|string|max:100',
            'mail_signature'         => 'nullable|string',
            'mail_template_devis'    => 'nullable|string',
            'mail_template_facture'  => 'nullable|string',
            'mail_template_bdc'      => 'nullable|string',
            'mail_template_relance'  => 'nullable|string',
            'opc'                    => 'nullable|string|max:100',
            'opc_numero_affiliation' => 'nullable|string|max:50',
        ]);

        $parametres = ParametresEntreprise::instance();

        if ($request->hasFile('logo')) {
            if ($parametres->logo_path) {
                Storage::disk('public')->delete($parametres->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        unset($data['logo']);

        // Clé API : ne mettre à jour que si une nouvelle valeur est fournie
        if (empty($data['ai_api_key'])) {
            unset($data['ai_api_key']);
        }

        // Supprimer la config IA si le provider est vidé
        if (empty($data['ai_provider'])) {
            $data['ai_provider'] = null;
            $data['ai_api_key']  = null;
            $data['ai_model']    = null;
            $data['ai_url']      = null;
        }

        // Clé API Peppol : chiffrer si nouvelle valeur, conserver sinon
        if (!empty($data['peppol_api_key'])) {
            $data['peppol_api_key'] = encrypt($data['peppol_api_key']);
        } else {
            unset($data['peppol_api_key']);
        }

        // Générer un token webhook si le mode complet est activé et qu'il n'y en a pas encore
        if (($data['peppol_mode'] ?? '') === 'complet' && !$parametres->peppol_webhook_token) {
            $data['peppol_webhook_token'] = bin2hex(random_bytes(32));
        }

        // Odoo : chiffrer la clé API si nouvelle valeur, conserver sinon
        if (!empty($data['odoo_api_key'])) {
            $data['odoo_api_key'] = encrypt($data['odoo_api_key']);
        } else {
            unset($data['odoo_api_key']);
        }

        // Cases à cocher (absentes du POST si décochées)
        $data['odoo_actif']                   = $request->boolean('odoo_actif');
        $data['deux_facteurs_obligatoires']   = $request->boolean('deux_facteurs_obligatoires');

        // peppol_gere_par par défaut = gesent
        $data['peppol_gere_par'] = $data['peppol_gere_par'] ?? 'gesent';

        // Mail password: keep existing if field left empty
        if (empty($data['mail_password'])) {
            unset($data['mail_password']);
        }

        // mail_encryption: empty string → null
        if (isset($data['mail_encryption']) && $data['mail_encryption'] === '') {
            $data['mail_encryption'] = null;
        }

        $parametres->update($data);

        return redirect()->route('parametres.edit')->with('success', 'Paramètres sauvegardés.');
    }

    public function testerOdoo(OdooService $odoo)
    {
        $resultat = $odoo->testerConnexion();
        return response()->json($resultat);
    }

    public function testerEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        MailConfigService::configure();

        try {
            Mail::raw('Ceci est un email de test envoyé depuis GesEnt. La configuration SMTP fonctionne correctement.', function ($message) use ($data) {
                $message->to($data['email'])->subject('Test SMTP — GesEnt');
            });

            return response()->json(['success' => true, 'message' => 'Email de test envoyé à ' . $data['email']]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
