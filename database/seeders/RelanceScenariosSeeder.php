<?php

namespace Database\Seeders;

use App\Models\RelanceEtape;
use App\Models\RelanceScenario;
use Illuminate\Database\Seeder;

class RelanceScenariosSeeder extends Seeder
{
    public function run(): void
    {
        if (RelanceScenario::exists()) {
            return;
        }

        $scenario = RelanceScenario::create([
            'nom'        => 'Scénario standard',
            'description' => '3 relances progressives : rappel cordial à J+7, relance ferme à J+21, mise en demeure à J+35.',
            'est_defaut' => true,
        ]);

        RelanceEtape::create([
            'relance_scenario_id' => $scenario->id,
            'numero_ordre'        => 1,
            'delai_jours'         => 7,
            'sujet'               => 'Rappel — Facture {numero}',
            'corps_email'         => "Bonjour {client},\n\nNous nous permettons de vous rappeler que notre facture {numero} d'un montant de {solde_du} est arrivée à échéance le {date_facture}.\n\nSi votre paiement est en cours, nous vous remercions de ne pas tenir compte de ce message.\n\nCordialement,\n{entreprise}",
            'canal'               => 'mail',
            'ton'                 => 'cordial',
            'actif'               => true,
        ]);

        RelanceEtape::create([
            'relance_scenario_id' => $scenario->id,
            'numero_ordre'        => 2,
            'delai_jours'         => 21,
            'sujet'               => '2ème rappel — Facture {numero} ({jours_retard} jours de retard)',
            'corps_email'         => "Bonjour {client},\n\nSauf erreur de notre part, notre facture {numero} d'un montant de {solde_du} reste impayée à ce jour, soit {jours_retard} jours après l'échéance du {date_facture}.\n\nNous vous remercions de bien vouloir régulariser cette situation dans les meilleurs délais.\n\nCordialement,\n{entreprise}",
            'canal'               => 'mail',
            'ton'                 => 'ferme',
            'actif'               => true,
        ]);

        RelanceEtape::create([
            'relance_scenario_id' => $scenario->id,
            'numero_ordre'        => 3,
            'delai_jours'         => 35,
            'sujet'               => 'URGENT — Facture {numero} impayée ({jours_retard} jours)',
            'corps_email'         => "Bonjour {client},\n\nMalgré nos rappels précédents, notre facture {numero} d'un montant de {solde_du} reste impayée ({jours_retard} jours de retard).\n\nSans règlement de votre part sous 8 jours, nous nous verrons dans l'obligation d'engager une procédure de recouvrement.\n\nVous trouverez ci-joint un courrier formel de mise en demeure.\n\nCordialement,\n{entreprise}",
            'canal'               => 'les_deux',
            'ton'                 => 'formel',
            'actif'               => true,
        ]);
    }
}
