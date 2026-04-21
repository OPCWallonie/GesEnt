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
        'opc', 'opc_numero_affiliation',
        'cle_repartition_frais',
        'volatilite_active',
        'volatilite_fenetre_mois',
        'volatilite_min_changements_pour_classer',
        'volatilite_seuil_stable_amplitude_pct',
        'volatilite_seuil_a_variation_pct',
        'volatilite_seuil_a_max_changements_anciens',
        'volatilite_seuil_b_pente_annuelle_pct',
        'volatilite_seuil_b_r2_min',
        'volatilite_seuil_c_nb_changements',
        'volatilite_seuil_c_amplitude_pct',
        'volatilite_garde_fou_absolu_pct',
        'volatilite_signal_relatif_ecart_pct',
        'volatilite_seuil_ligne_devis_eur',
        'volatilite_cross_seuil_prix_pct',
        'volatilite_cross_seuil_position',
        'volatilite_cross_seuil_tendance_pp',
    ];

    public const CLES_REPARTITION = [
        'prorata_heures' => 'Au prorata des heures pointées par chantier',
        'prorata_ca'     => 'Au prorata du CA HT par chantier',
        'uniforme'       => 'Répartition uniforme sur les chantiers actifs',
    ];

    // Organismes Paritaires de la Construction en Belgique
    public const OPC_LIST = [
        'constructiv'       => 'Constructiv (Fonds de Formation de la Construction)',
        'forem'             => 'FOREM',
        'vdab'              => 'VDAB',
        'bruxelles_formation' => 'Bruxelles Formation',
        'ifapme'            => 'IFAPME',
        'syntra'            => 'Syntra',
        'autre'             => 'Autre',
    ];

    protected $casts = [
        'odoo_actif'                  => 'boolean',
        'odoo_mapping'                => 'array',
        'deux_facteurs_obligatoires'  => 'boolean',
        'mail_password'               => 'encrypted',
        'volatilite_active'           => 'boolean',
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
