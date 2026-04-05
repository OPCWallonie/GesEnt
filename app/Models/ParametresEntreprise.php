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
    ];

    protected $hidden = ['ai_api_key'];

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
}
