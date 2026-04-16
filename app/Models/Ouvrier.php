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
        'cout_horaire', 'cout_mensuel', 'heures_semaine', 'mode_heures_sup_defaut',
        'jours_conges_supplementaires',
        'date_entree', 'date_sortie', 'motif_sortie',
        'actif', 'telephone', 'email', 'notes', 'metier', 'qualifications',
    ];

    protected $casts = [
        'date_entree'      => 'date',
        'date_sortie'      => 'date',
        'cout_horaire'     => 'decimal:2',
        'cout_mensuel'     => 'decimal:2',
        'heures_semaine'   => 'decimal:1',
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

    /**
     * Plafond sectoriel de jours RC pour certaines CPs.
     * NULL = pas de plafond (formule brute s'applique).
     */
    public const PLAFOND_RC_PAR_CP = [
        'CP124' => 12,
        'CP149' => null,
        'CP111' => null,
        'CP200' => null,
        'autre'  => null,
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

    // Heures par jour ouvré (régime normal 40h/5j = 8h, proportionnel sinon)
    public function getHeuresJourAttribute(): float
    {
        return round((float) ($this->heures_semaine ?? 38) / 5, 2);
    }

    /**
     * Quota annuel de jours RC, calculé dynamiquement à partir du régime horaire.
     * Formule : (heures_hebdo - 38) × 52 / heures_par_jour, arrondi inférieur.
     * CP124 : plafonné à 12 jours par convention sectorielle.
     */
    public function getQuotaRcAnnuelAttribute(): int
    {
        $heures = (float) ($this->heures_semaine ?? 38);

        if ($heures <= 38) {
            return 0;
        }

        $heuresJour = $this->heures_jour;
        if ($heuresJour <= 0) {
            return 0;
        }

        $joursCalcules = ($heures - 38) * 52 / $heuresJour;

        $plafond = self::PLAFOND_RC_PAR_CP[$this->commission_paritaire] ?? null;
        if ($plafond !== null) {
            return min($plafond, (int) floor($joursCalcules));
        }

        return (int) floor($joursCalcules);
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
        $quota = $this->quota_rc_annuel;
        if ($quota === 0) {
            return 0;
        }

        $utilises = $this->absences()
            ->where('type', 'repos_compensatoire')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);

        return max(0, $quota - $utilises);
    }

    public function reposCompensatoiresUtilises(int $annee): float
    {
        return (float) $this->absences()
            ->where('type', 'repos_compensatoire')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);
    }

    // ─── Compteur récupération heures sup ────────────────────────

    /**
     * Heures sup marquées "récupérées" accumulées sur l'année.
     */
    public function heuresRecuperablesAccumulees(int $annee): float
    {
        return (float) $this->pointages()
            ->whereYear('date', $annee)
            ->where('mode_heures_sup', 'recuperees')
            ->where('heures_sup', '>', 0)
            ->sum('heures_sup');
    }

    /**
     * Heures de récupération déjà consommées (absences type recup_heures_sup).
     */
    public function heuresRecupereesConsommees(int $annee): float
    {
        return (float) $this->absences()
            ->where('type', 'recup_heures_sup')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours * $this->heures_jour);
    }

    /**
     * Solde de récupération : accumulées − consommées.
     */
    public function soldeRecuperation(int $annee): float
    {
        return round(
            $this->heuresRecuperablesAccumulees($annee) - $this->heuresRecupereesConsommees($annee),
            2
        );
    }

    // ─── Congés payés ────────────────────────────────────────────

    /**
     * Quota total de jours de congés payés = 20 légaux + jours supplémentaires.
     */
    public function getQuotaCongesPayesAttribute(): int
    {
        return 20 + (int) ($this->jours_conges_supplementaires ?? 0);
    }

    /**
     * Jours de congés payés posés sur l'année (conge + conge_anciennete).
     */
    public function congesPayesUtilises(int $annee): float
    {
        return (float) $this->absences()
            ->whereIn('type', ['conge', 'conge_anciennete'])
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);
    }

    public function congesPayesRestants(int $annee): float
    {
        return max(0, $this->quota_conges_payes - $this->congesPayesUtilises($annee));
    }

    public function congesLegauxUtilises(int $annee): float
    {
        return (float) $this->absences()
            ->where('type', 'conge')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);
    }

    public function congesAncienneteUtilises(int $annee): float
    {
        return (float) $this->absences()
            ->where('type', 'conge_anciennete')
            ->whereYear('date_debut', $annee)
            ->get()
            ->sum(fn($a) => $a->nb_jours);
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
