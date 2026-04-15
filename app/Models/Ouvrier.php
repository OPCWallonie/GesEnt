<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ouvrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_personnel', 'nom', 'prenom', 'numero_national',
        'commission_paritaire', 'categorie',
        'cout_horaire', 'cout_mensuel',
        'date_entree', 'date_sortie', 'motif_sortie',
        'actif', 'telephone', 'email', 'notes', 'metier', 'qualifications',
    ];

    protected $casts = [
        'date_entree'      => 'date',
        'date_sortie'      => 'date',
        'cout_horaire'     => 'decimal:2',
        'cout_mensuel'     => 'decimal:2',
        'actif'            => 'boolean',
        'qualifications'   => 'array',
        'numero_national'  => 'encrypted',
    ];

    // ─── Constantes ──────────────────────────────────────────────

    // Rétrocompatibilité : catégories CP124 (utilisées par le code existant)
    public const CATEGORIES = ['I', 'II', 'III', 'IIIA', 'IIIB', 'IV'];

    public const TYPES_PERSONNEL = [
        'ouvrier'         => 'Ouvrier',
        'employe_terrain' => 'Employé de terrain',
        'employe_admin'   => 'Employé administratif',
        'direction'       => 'Direction',
    ];

    public const COMMISSIONS_PARITAIRES = [
        'CP124' => 'CP 124 — Construction',
        'CP149' => 'CP 149.01 — Électriciens',
        'CP111' => 'CP 111 — Constructions métallique/mécanique',
        'CP200' => 'CP 200 — Commission auxiliaire employés',
        'autre' => 'Autre',
    ];

    public const CATEGORIES_PAR_CP = [
        'CP124' => ['I', 'II', 'III', 'IIIA', 'IIIB', 'IV'],
        'CP149' => ['A', 'B', 'C', 'D', 'E', 'F'],
        'CP111' => ['1', '2', '3', '4', '5', '6', '7', '8'],
        'CP200' => [],
        'autre' => [],
    ];

    // Personnel apparaissant dans la grille de pointage
    public const TYPES_PLANIFIABLES = ['ouvrier', 'employe_terrain'];

    /**
     * Taux de majoration des heures supplémentaires par CP.
     * En Belgique le taux légal est 50% pour toutes les CP listées ici,
     * mais en le sortant en constante on peut l'ajuster par CP sans toucher la logique.
     */
    public const MAJORATION_HEURES_SUP = [
        'CP124' => 0.50,
        'CP149' => 0.50,
        'CP111' => 0.50,
        'CP200' => 0.50,
        'autre' => 0.50,
    ];

    public const MOTIFS_SORTIE = [
        'licenciement' => 'Licenciement (C4)',
        'demission'    => 'Démission',
        'fin_contrat'  => 'Fin de contrat',
        'pension'      => 'Pension / Prépension',
        'accord_mutuel'=> 'Accord mutuel',
        'deces'        => 'Décès',
        'autre'        => 'Autre',
    ];

    // ─── Scopes ──────────────────────────────────────────────────

    // Personnel planifiable sur chantier (ouvriers + employés terrain)
    public function scopePlanifiable($query)
    {
        return $query->whereIn('type_personnel', self::TYPES_PLANIFIABLES);
    }

    // Personnel en frais généraux (non affecté directement aux chantiers)
    public function scopeFraisGeneraux($query)
    {
        return $query->whereIn('type_personnel', ['employe_admin', 'direction']);
    }

    // ─── Relations ───────────────────────────────────────────────

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class)->orderBy('type');
    }

    // Absence en cours aujourd'hui (eager-loadable sans N+1)
    public function absenceActuelle()
    {
        return $this->hasOne(Absence::class)
            ->whereDate('date_debut', '<=', now())
            ->whereDate('date_fin', '>=', now());
    }

    // Certifications expirées ou expirant dans les 90 jours
    public function certificationsARenouveler()
    {
        return $this->certifications()
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<=', now()->addDays(90));
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getAncienneteAttribute(): int
    {
        return (int) $this->date_entree->diffInYears(now());
    }

    public function getEstDisponibleAttribute(): bool
    {
        if (! $this->actif) {
            return false;
        }
        return ! $this->absences()
            ->where('date_debut', '<=', today())
            ->where('date_fin', '>=', today())
            ->exists();
    }

    // Le membre est-il planifiable sur un chantier ?
    public function getEstPlanifiableAttribute(): bool
    {
        return in_array($this->type_personnel, self::TYPES_PLANIFIABLES);
    }

    // Coût horaire effectif : direct si renseigné, sinon converti depuis le mensuel
    // Base de conversion : 38h/semaine × 4,33 semaines = 164,54 h/mois
    public function getCoutHoraireEffectifAttribute(): float
    {
        if ($this->cout_horaire > 0) {
            return (float) $this->cout_horaire;
        }
        if ($this->cout_mensuel > 0) {
            return round((float) $this->cout_mensuel / 164.54, 2);
        }
        return 0;
    }

    // Taux de majoration applicable à ce membre du personnel
    public function getTauxMajorationAttribute(): float
    {
        return self::MAJORATION_HEURES_SUP[$this->commission_paritaire] ?? 0.50;
    }

    // Libellé CP + catégorie combinés (ex: "CP 124 — Construction – III")
    public function getLabelCpAttribute(): string
    {
        $cp = self::COMMISSIONS_PARITAIRES[$this->commission_paritaire] ?? $this->commission_paritaire;
        return $this->categorie ? "{$cp} – {$this->categorie}" : $cp;
    }

    // Catégories disponibles pour la CP de ce record
    public function getCategoriesDisponiblesAttribute(): array
    {
        return self::CATEGORIES_PAR_CP[$this->commission_paritaire] ?? [];
    }

    // ─── Méthodes ────────────────────────────────────────────────

    public function totalHeuresSemaine(\Carbon\Carbon $lundi): float
    {
        return (float) $this->pointages()
            ->whereBetween('date', [$lundi, $lundi->copy()->addDays(4)])
            ->sum(\Illuminate\Support\Facades\DB::raw('heures + heures_sup'));
    }

    public function coutTotal(?int $annee = null): float
    {
        $query = $this->pointages();
        if ($annee) {
            $query->whereYear('date', $annee);
        }
        return (float) $query->sum('cout_total');
    }

    public function reposCompensatoiresRestants(int $annee): float
    {
        // CP124 : 12 jours de repos compensatoire par an
        $utilises = $this->absences()
            ->where('type', 'repos_compensatoire')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);

        return max(0, 12 - $utilises);
    }

    /**
     * Bradford Factor : B = S² × D
     * S = nombre d'épisodes de maladie, D = total jours maladie
     */
    public function bradfordFactor(int $annee): int
    {
        $absencesMaladie = $this->absences()
            ->where('type', 'maladie')
            ->whereYear('date_debut', $annee)
            ->get();

        $s = $absencesMaladie->count();
        $d = (int) $absencesMaladie->sum(fn($a) => $a->nb_jours);

        return $s * $s * $d;
    }
}
