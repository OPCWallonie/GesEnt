<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ouvrier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom', 'prenom', 'numero_national', 'categorie',
        'cout_horaire', 'date_entree', 'date_sortie', 'actif',
        'telephone', 'email', 'notes',
    ];

    protected $casts = [
        'date_entree'  => 'date',
        'date_sortie'  => 'date',
        'cout_horaire' => 'decimal:2',
        'actif'        => 'boolean',
    ];

    // Catégories CP124
    public const CATEGORIES = ['I', 'II', 'III', 'IV'];

    // ─── Relations ───────────────────────────────────────────────
    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
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
        // Vérifie absence en cours
        return ! $this->absences()
            ->where('date_debut', '<=', today())
            ->where('date_fin', '>=', today())
            ->exists();
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
}
