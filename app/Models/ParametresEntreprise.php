<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametresEntreprise extends Model
{
    protected $table = 'parametres_entreprise';

    protected $fillable = [
        'nom', 'statut_juridique', 'adresse', 'code_postal', 'ville', 'pays',
        'telephone', 'email', 'site_web', 'numero_tva', 'numero_entreprise',
        'iban', 'bic', 'banque',
        'logo_path', 'conditions_generales', 'mentions_pied_page',
        'delai_reglement_defaut', 'validite_devis_defaut',
        'ai_provider', 'ai_api_key', 'ai_model', 'ai_url',
        'peppol_mode', 'peppol_provider', 'peppol_api_key', 'peppol_entity_id',
        'peppol_id', 'peppol_environment', 'peppol_webhook_token',
        'odoo_actif', 'odoo_url', 'odoo_database', 'odoo_username',
        'odoo_api_key', 'odoo_mapping', 'peppol_gere_par',
        'deux_facteurs_obligatoires',
        'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password',
        'mail_from_address', 'mail_from_name', 'mail_signature',
        'mail_template_devis', 'mail_template_facture', 'mail_template_bdc', 'mail_template_relance',
    ];

    protected $casts = [
        'odoo_actif'                  => 'boolean',
        'odoo_mapping'                => 'array',
        'deux_facteurs_obligatoires'  => 'boolean',
        'mail_password'               => 'encrypted',
    ];

    protected $hidden = ['ai_api_key', 'peppol_api_key', 'odoo_api_key'];

    public static function instance(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'nom'                    => 'Mon Entreprise',
            'pays'                   => 'Belgique',
            'delai_reglement_defaut' => 30,
            'validite_devis_defaut'  => 30,
        ]);
    }

    // Clé API stockée chiffrée
    public function getAiApiKeyDecrypteAttribute(): ?string
    {
        if (!$this->ai_api_key) return null;
        try { return decrypt($this->ai_api_key); } catch (\Exception) { return null; }
    }

    public function setAiApiKeyAttribute(?string $value): void
    {
        $this->attributes['ai_api_key'] = $value ? encrypt($value) : null;
    }

    public function aiConfiguree(): bool
    {
        return !empty($this->ai_provider) && (!empty($this->ai_api_key) || $this->ai_provider === 'ollama');
    }

    // --- Peppol ---

    public function getPeppolApiKeyDecrypteAttribute(): ?string
    {
        if (!$this->peppol_api_key) return null;
        try { return decrypt($this->peppol_api_key); } catch (\Exception) { return null; }
    }

    public function peppolActif(): bool
    {
        return in_array($this->peppol_mode ?? 'desactive', ['envoi', 'complet'])
            && !empty($this->peppol_provider)
            && !empty($this->peppol_api_key);
    }

    // --- Odoo ---

    public function getOdooApiKeyDecrypteAttribute(): ?string
    {
        if (!$this->odoo_api_key) return null;
        try { return decrypt($this->odoo_api_key); } catch (\Exception) { return null; }
    }

    public function odooActif(): bool
    {
        return (bool) $this->odoo_actif
            && !empty($this->odoo_url)
            && !empty($this->odoo_database)
            && !empty($this->odoo_username)
            && !empty($this->odoo_api_key);
    }

    public function odooMapping(string $key, $default = null): mixed
    {
        return ($this->odoo_mapping ?? [])[$key] ?? $default;
    }

    public function peppolGereParOdoo(): bool
    {
        return $this->odooActif() && $this->peppol_gere_par === 'odoo';
    }
}
