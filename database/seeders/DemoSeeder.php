<?php

namespace Database\Seeders;

use App\Models\Avoir;
use App\Models\BonCommande;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\FactureAchat;
use App\Models\Fournisseur;
use App\Models\JournalChantier;
use App\Models\Kit;
use App\Models\ModePaiement;
use App\Models\Paiement;
use App\Models\ParametresEntreprise;
use App\Models\Produit;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\NumerotationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoSeeder extends Seeder
{
    private DocumentService $documentService;
    private NumerotationService $numerotation;

    public function run(): void
    {
        $this->documentService = app(DocumentService::class);
        $this->numerotation    = app(NumerotationService::class);

        // --- PARAMÈTRES ENTREPRISE ---
        $params = ParametresEntreprise::instance();
        $params->update([
            'nom'                    => 'Construct Demo SPRL',
            'statut_juridique'       => 'SPRL',
            'adresse'                => 'Rue de la Démonstration 42',
            'code_postal'            => '6000',
            'ville'                  => 'Charleroi',
            'pays'                   => 'Belgique',
            'telephone'              => '+32 71 12 34 56',
            'email'                  => 'info@construct-demo.be',
            'site_web'               => 'https://www.construct-demo.be',
            'numero_tva'             => 'BE0123.456.789',
            'numero_entreprise'      => '0123.456.789',
            'iban'                   => 'BE68 5390 0754 7034',
            'bic'                    => 'BMPBBEBB',
            'banque'                 => 'Belfius',
            'conditions_generales'   => 'Conditions générales de Construct Demo SPRL. Tous nos travaux sont garantis 10 ans conformément à la législation belge.',
            'mentions_pied_page'     => 'Construct Demo SPRL — TVA BE0123.456.789 — IBAN BE68 5390 0754 7034',
            'delai_reglement_defaut' => 30,
            'validite_devis_defaut'  => 30,
        ]);

        // --- UTILISATEURS ---
        $admin = User::firstOrCreate(
            ['email' => 'demo@gesent.be'],
            ['name' => 'Jean Dupont (Admin)', 'password' => Hash::make('Demo2026!')]
        );
        $admin->assignRole('admin');

        $comptable = User::firstOrCreate(
            ['email' => 'comptable@gesent.be'],
            ['name' => 'Marie Lecomte (Comptable)', 'password' => Hash::make('Demo2026!')]
        );
        $comptable->assignRole(Role::firstOrCreate(['name' => 'comptable']));

        $lecture = User::firstOrCreate(
            ['email' => 'lecture@gesent.be'],
            ['name' => 'Pierre Martin (Lecture)', 'password' => Hash::make('Demo2026!')]
        );
        $lecture->assignRole(Role::firstOrCreate(['name' => 'lecture']));

        // --- CLIENTS ---
        $clientsData = [
            ['nom' => 'Dubois Construction SA', 'statut_juridique' => 'SA', 'adresse' => 'Avenue Louise 150', 'code_postal' => '1050', 'ville' => 'Bruxelles', 'email' => 'info@dubois-construction.be', 'numero_tva' => 'BE0456.789.012', 'telephone' => '+32 2 345 67 89', 'coefficient_marge' => 15],
            ['nom' => 'Immobilière du Hainaut SPRL', 'statut_juridique' => 'SPRL', 'adresse' => 'Rue Neuve 28', 'code_postal' => '7000', 'ville' => 'Mons', 'email' => 'contact@immo-hainaut.be', 'numero_tva' => 'BE0567.890.123', 'telephone' => '+32 65 33 44 55', 'coefficient_marge' => 20],
            ['nom' => 'Martin Jean-Pierre', 'adresse' => 'Rue des Alliés 15', 'code_postal' => '6001', 'ville' => 'Marcinelle', 'email' => 'jp.martin@gmail.com', 'gsm' => '+32 475 12 34 56', 'coefficient_marge' => 25],
            ['nom' => 'Commune de Fleurus', 'statut_juridique' => 'Commune', 'adresse' => 'Place Albert 1er', 'code_postal' => '6220', 'ville' => 'Fleurus', 'email' => 'travaux@fleurus.be', 'numero_tva' => 'BE0207.362.961', 'telephone' => '+32 71 82 03 11'],
            ['nom' => 'Brasserie Dupont SCRL', 'statut_juridique' => 'SCRL', 'adresse' => 'Rue Basse 5', 'code_postal' => '7904', 'ville' => 'Tourpes', 'email' => 'admin@brasserie-dupont.com', 'numero_tva' => 'BE0401.264.584', 'telephone' => '+32 69 67 10 66', 'coefficient_marge' => 12],
            ['nom' => 'Van den Berg Luc', 'adresse' => 'Chemin des Hayettes 7', 'code_postal' => '6200', 'ville' => 'Châtelet', 'email' => 'luc.vdb@skynet.be', 'gsm' => '+32 496 78 90 12'],
        ];
        $clients = collect($clientsData)->map(fn($data) => Client::firstOrCreate(
            ['email' => $data['email']],
            array_merge($data, ['actif' => true])
        ));

        // --- FOURNISSEURS ---
        $fournisseursData = [
            ['nom' => 'Desco Materials', 'email' => 'commandes@desco.be', 'numero_tva' => 'BE0891.234.567', 'adresse' => 'Zoning Industriel 12', 'code_postal' => '7110', 'ville' => 'Houdeng-Goegnies', 'telephone' => '+32 64 21 54 00'],
            ['nom' => 'Van Marcke', 'email' => 'pro@vanmarcke.be', 'numero_tva' => 'BE0405.123.456', 'adresse' => 'Chaussée de Bruxelles 305', 'code_postal' => '7000', 'ville' => 'Mons', 'telephone' => '+32 65 39 50 00'],
            ['nom' => 'BigMat Cannone', 'email' => 'charleroi@bigmat.be', 'numero_tva' => 'BE0447.456.789', 'adresse' => 'Route de Philippeville 189', 'code_postal' => '6010', 'ville' => 'Couillet', 'telephone' => '+32 71 36 11 84'],
            ['nom' => 'Soudal NV', 'email' => 'orders@soudal.com', 'numero_tva' => 'BE0408.765.432', 'adresse' => 'Everdongenlaan 18-20', 'code_postal' => '2300', 'ville' => 'Turnhout'],
            ['nom' => 'Electricité Henrard', 'email' => 'devis@henrard-elec.be', 'numero_tva' => 'BE0678.901.234', 'adresse' => 'Rue de Gozée 46', 'code_postal' => '6110', 'ville' => 'Montigny-le-Tilleul', 'telephone' => '+32 71 51 90 00'],
        ];
        $fournisseurs = collect($fournisseursData)->map(fn($data) => Fournisseur::firstOrCreate(
            ['email' => $data['email']],
            array_merge($data, ['actif' => true])
        ));

        // --- CHANTIERS ---
        $chantiers = [
            $this->creerChantier($clients[0], 'Rénovation bureaux Avenue Louise', 'Avenue Louise 150, 1050 Bruxelles', 'actif', 60),
            $this->creerChantier($clients[1], 'Construction 3 appartements Mons', 'Rue de la Station 42, 7000 Mons', 'actif', 35),
            $this->creerChantier($clients[2], 'Salle de bain + cuisine Marcinelle', 'Rue des Alliés 15, 6001 Marcinelle', 'actif', 80),
            $this->creerChantier($clients[3], 'Réfection toiture école communale', 'Rue de Wanfercée 12, 6220 Fleurus', 'termine', 100),
            $this->creerChantier($clients[4], 'Extension salle de brassage', 'Rue Basse 5, 7904 Tourpes', 'actif', 15),
            $this->creerChantier($clients[5], 'Rénovation façade', 'Chemin des Hayettes 7, 6200 Châtelet', 'inactif', 0),
        ];

        // --- PRODUITS INTERNES ---
        $produits = collect([
            ['designation' => "Main d'œuvre maçonnerie", 'unite' => 'h', 'prix_unitaire' => 45, 'taux_tva' => 21, 'categorie' => "Main d'œuvre"],
            ['designation' => "Main d'œuvre plomberie", 'unite' => 'h', 'prix_unitaire' => 50, 'taux_tva' => 21, 'categorie' => "Main d'œuvre"],
            ['designation' => "Main d'œuvre électricité", 'unite' => 'h', 'prix_unitaire' => 48, 'taux_tva' => 21, 'categorie' => "Main d'œuvre"],
            ['designation' => "Main d'œuvre carrelage", 'unite' => 'h', 'prix_unitaire' => 42, 'taux_tva' => 21, 'categorie' => "Main d'œuvre"],
            ['designation' => "Main d'œuvre peinture", 'unite' => 'h', 'prix_unitaire' => 38, 'taux_tva' => 21, 'categorie' => "Main d'œuvre"],
            ['designation' => 'Brique Wienerberger Terca', 'unite' => 'pièce', 'prix_unitaire' => 0.85, 'taux_tva' => 21, 'categorie' => 'Maçonnerie'],
            ['designation' => 'Ciment Portland CEM I 52.5', 'unite' => 'kg', 'prix_unitaire' => 0.18, 'taux_tva' => 21, 'categorie' => 'Maçonnerie'],
            ['designation' => 'Bloc béton 39x19x14', 'unite' => 'pièce', 'prix_unitaire' => 1.45, 'taux_tva' => 21, 'categorie' => 'Maçonnerie'],
            ['designation' => 'Plaque de plâtre Gyproc 12.5mm', 'unite' => 'm²', 'prix_unitaire' => 6.50, 'taux_tva' => 21, 'categorie' => 'Parachèvement'],
            ['designation' => 'Isolation PUR 10cm', 'unite' => 'm²', 'prix_unitaire' => 28, 'taux_tva' => 6, 'categorie' => 'Isolation'],
            ['designation' => 'Tuile Koramic Actua 10', 'unite' => 'pièce', 'prix_unitaire' => 1.20, 'taux_tva' => 21, 'categorie' => 'Toiture'],
            ['designation' => 'Chaudière Vaillant ecoTEC plus', 'unite' => 'pièce', 'prix_unitaire' => 2850, 'taux_tva' => 6, 'categorie' => 'Chauffage'],
            ['designation' => 'Thermostat Netatmo', 'unite' => 'pièce', 'prix_unitaire' => 179, 'taux_tva' => 21, 'categorie' => 'Chauffage'],
            ['designation' => 'Tube cuivre 22mm (barre 5m)', 'unite' => 'pièce', 'prix_unitaire' => 45, 'taux_tva' => 21, 'categorie' => 'Plomberie'],
            ['designation' => 'Robinet Grohe Eurosmart', 'unite' => 'pièce', 'prix_unitaire' => 89, 'taux_tva' => 21, 'categorie' => 'Sanitaire'],
            ['designation' => 'WC suspendu Geberit', 'unite' => 'pièce', 'prix_unitaire' => 320, 'taux_tva' => 21, 'categorie' => 'Sanitaire'],
            ['designation' => 'Déplacement / installation chantier', 'unite' => 'forfait', 'prix_unitaire' => 150, 'taux_tva' => 21, 'categorie' => 'Frais'],
            ['designation' => 'Évacuation déchets (container)', 'unite' => 'pièce', 'prix_unitaire' => 450, 'taux_tva' => 21, 'categorie' => 'Frais'],
        ])->map(fn($data) => Produit::create(array_merge($data, [
            'reference' => 'INT-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
        ])));

        // --- DEVIS ---
        $devis1 = $this->creerDevis($clients[0], $chantiers[0], 'valide', now()->subMonths(3), [
            ['designation' => 'Démolition cloisons existantes', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 2500, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre maçonnerie", 'unite' => 'h', 'quantite' => 120, 'prix_unitaire' => 45, 'taux_tva' => 21],
            ['designation' => 'Bloc béton 39x19x14', 'unite' => 'pièce', 'quantite' => 800, 'prix_unitaire' => 1.45, 'taux_tva' => 21],
            ['designation' => 'Plaque de plâtre Gyproc 12.5mm', 'unite' => 'm²', 'quantite' => 200, 'prix_unitaire' => 6.50, 'taux_tva' => 21],
            ['designation' => 'Peinture (fourniture + pose)', 'unite' => 'm²', 'quantite' => 350, 'prix_unitaire' => 18, 'taux_tva' => 21],
            ['designation' => 'Évacuation déchets', 'unite' => 'pièce', 'quantite' => 2, 'prix_unitaire' => 450, 'taux_tva' => 21],
        ]);

        $devis2 = $this->creerDevis($clients[2], $chantiers[2], 'en_attente', now()->subWeeks(2), [
            ['designation' => 'Démolition salle de bain existante', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 1200, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre plomberie", 'unite' => 'h', 'quantite' => 40, 'prix_unitaire' => 50, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre carrelage", 'unite' => 'h', 'quantite' => 32, 'prix_unitaire' => 42, 'taux_tva' => 21],
            ['designation' => 'WC suspendu Geberit', 'unite' => 'pièce', 'quantite' => 1, 'prix_unitaire' => 320, 'taux_tva' => 21],
            ['designation' => 'Robinet Grohe Eurosmart', 'unite' => 'pièce', 'quantite' => 2, 'prix_unitaire' => 89, 'taux_tva' => 21],
            ['designation' => 'Carrelage sol 60x60', 'unite' => 'm²', 'quantite' => 12, 'prix_unitaire' => 35, 'taux_tva' => 21],
        ]);

        $devis3 = $this->creerDevis($clients[3], $chantiers[3], 'valide', now()->subMonths(6), [
            ['designation' => 'Dépose toiture existante', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 3800, 'taux_tva' => 21],
            ['designation' => 'Charpente — remplacement pannes', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 5200, 'taux_tva' => 21],
            ['designation' => 'Isolation PUR 10cm', 'unite' => 'm²', 'quantite' => 180, 'prix_unitaire' => 28, 'taux_tva' => 6],
            ['designation' => 'Tuile Koramic Actua 10', 'unite' => 'pièce', 'quantite' => 2500, 'prix_unitaire' => 1.20, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre couvreur", 'unite' => 'h', 'quantite' => 200, 'prix_unitaire' => 48, 'taux_tva' => 21],
            ['designation' => 'Échafaudage (location)', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 1800, 'taux_tva' => 21],
        ]);

        $devis4 = $this->creerDevis($clients[4], $chantiers[4], 'brouillon', now()->subDays(3), [
            ['designation' => 'Fondations béton armé', 'unite' => 'm³', 'quantite' => 15, 'prix_unitaire' => 180, 'taux_tva' => 21],
            ['designation' => 'Maçonnerie blocs béton', 'unite' => 'm²', 'quantite' => 120, 'prix_unitaire' => 65, 'taux_tva' => 21],
            ['designation' => 'Charpente métallique', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 12000, 'taux_tva' => 21],
        ]);

        $devis5 = $this->creerDevis($clients[5], $chantiers[5], 'refuse', now()->subMonths(1), [
            ['designation' => 'Nettoyage façade haute pression', 'unite' => 'm²', 'quantite' => 80, 'prix_unitaire' => 15, 'taux_tva' => 21],
            ['designation' => 'Rejointoiement façade', 'unite' => 'm²', 'quantite' => 80, 'prix_unitaire' => 45, 'taux_tva' => 21],
        ]);

        // --- BDC ---
        $bdc1 = $this->creerBdcDepuisDevis($devis1, 'en_cours');
        $bdc2 = $this->creerBdcDepuisDevis($devis3, 'termine');

        // --- FACTURES (situations) ---
        $facture1 = $this->creerFactureSituation($bdc1, 1, 30, 'envoyee', now()->subMonths(2));
        $facture2 = $this->creerFactureSituation($bdc1, 2, 30, 'en_attente', now()->subWeeks(2));
        $facture3 = $this->creerFactureSituation($bdc2, 1, 100, 'payee', now()->subMonths(4));

        // --- PAIEMENTS ---
        Paiement::create([
            'facture_id'    => $facture1->id,
            'date_paiement' => now()->subMonths(1)->toDateString(),
            'montant'       => round($facture1->montant_net_a_payer * 0.5, 2),
            'mode'          => 'virement',
            'reference'     => 'VIR-2026-0142',
        ]);
        $facture1->recalculerPaiements();

        Paiement::create([
            'facture_id'    => $facture3->id,
            'date_paiement' => now()->subMonths(3)->toDateString(),
            'montant'       => $facture3->montant_net_a_payer,
            'mode'          => 'virement',
            'reference'     => 'VIR-2026-0098',
        ]);
        $facture3->recalculerPaiements();

        // --- FACTURES ACHAT ---
        $this->creerFactureAchat($fournisseurs[0], $chantiers[0], 'Fourniture blocs béton', 2450, now()->subMonths(2));
        $this->creerFactureAchat($fournisseurs[1], $chantiers[2], 'Robinetterie et sanitaires', 1870, now()->subWeeks(3));
        $this->creerFactureAchat($fournisseurs[2], $chantiers[0], 'Plaques de plâtre + colles', 980, now()->subMonths(1));
        $this->creerFactureAchat($fournisseurs[2], $chantiers[3], 'Tuiles + liteaux', 4200, now()->subMonths(5), 'payee');
        $this->creerFactureAchat($fournisseurs[3], null, 'Colles et mastics (stock)', 320, now()->subWeeks(1));
        $this->creerFactureAchat($fournisseurs[4], $chantiers[0], 'Mise en conformité tableau électrique', 1650, now()->subWeeks(2));

        // --- AVOIR ---
        Avoir::create([
            'numero'        => $this->numerotation->suivant('avoir'),
            'facture_id'    => $facture1->id,
            'client_id'     => $facture1->client_id,
            'chantier_id'   => $facture1->chantier_id,
            'created_by'    => $admin->id,
            'date_document' => now()->subWeeks(3)->toDateString(),
            'motif'         => 'Erreur de facturation — ligne plâtre en double',
            'montant_ht'    => 650,
            'taux_tva'      => 21,
            'montant_tva'   => 136.50,
            'montant_ttc'   => 786.50,
        ]);

        // --- KITS ---
        $kitSdb = Kit::create([
            'nom'         => 'Salle de bain standard',
            'categorie'   => 'Sanitaire',
            'description' => 'Kit complet rénovation salle de bain : démolition, plomberie, carrelage, sanitaires',
            'created_by'  => $admin->id,
        ]);
        foreach ([
            ['designation' => 'Démolition salle de bain existante', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 1200, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Plomberie', 'est_section' => true, 'unite' => '—', 'quantite' => 0, 'prix_unitaire' => 0, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre plomberie", 'unite' => 'h', 'quantite' => 40, 'prix_unitaire' => 50, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Tube cuivre 22mm', 'unite' => 'pièce', 'quantite' => 4, 'prix_unitaire' => 45, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Sanitaires', 'est_section' => true, 'unite' => '—', 'quantite' => 0, 'prix_unitaire' => 0, 'taux_tva' => 21],
            ['designation' => 'WC suspendu Geberit', 'unite' => 'pièce', 'quantite' => 1, 'prix_unitaire' => 320, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Robinet Grohe Eurosmart', 'unite' => 'pièce', 'quantite' => 2, 'prix_unitaire' => 89, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Carrelage', 'est_section' => true, 'unite' => '—', 'quantite' => 0, 'prix_unitaire' => 0, 'taux_tva' => 21],
            ['designation' => "Main d'œuvre carrelage", 'unite' => 'h', 'quantite' => 32, 'prix_unitaire' => 42, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Carrelage sol 60x60', 'unite' => 'm²', 'quantite' => 12, 'prix_unitaire' => 35, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Carrelage mural 30x60', 'unite' => 'm²', 'quantite' => 18, 'prix_unitaire' => 28, 'taux_tva' => 21, 'est_section' => false],
        ] as $ordre => $ligne) {
            $kitSdb->lignes()->create(array_merge($ligne, ['ordre' => $ordre]));
        }

        $kitChauffage = Kit::create([
            'nom'         => 'Remplacement chaudière',
            'categorie'   => 'Chauffage',
            'description' => 'Chaudière condensation + thermostat connecté + raccordements',
            'created_by'  => $admin->id,
        ]);
        foreach ([
            ['designation' => 'Dépose ancienne chaudière', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 350, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Chaudière Vaillant ecoTEC plus', 'unite' => 'pièce', 'quantite' => 1, 'prix_unitaire' => 2850, 'taux_tva' => 6, 'est_section' => false],
            ['designation' => 'Thermostat Netatmo', 'unite' => 'pièce', 'quantite' => 1, 'prix_unitaire' => 179, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Kit raccordement hydraulique', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 280, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => "Main d'œuvre plomberie", 'unite' => 'h', 'quantite' => 16, 'prix_unitaire' => 50, 'taux_tva' => 21, 'est_section' => false],
            ['designation' => 'Mise en service + certificat', 'unite' => 'forfait', 'quantite' => 1, 'prix_unitaire' => 180, 'taux_tva' => 21, 'est_section' => false],
        ] as $ordre => $ligne) {
            $kitChauffage->lignes()->create(array_merge($ligne, ['ordre' => $ordre]));
        }

        // --- JOURNAL CHANTIER ---
        JournalChantier::create(['chantier_id' => $chantiers[0]->id, 'user_id' => $admin->id, 'type' => 'note', 'titre' => 'Début des travaux', 'contenu' => 'Début des travaux de démolition des cloisons du 2ème étage. RAS.', 'created_at' => now()->subMonths(2)]);
        JournalChantier::create(['chantier_id' => $chantiers[0]->id, 'user_id' => $admin->id, 'type' => 'note', 'titre' => 'Livraison matériaux', 'contenu' => 'Livraison blocs béton (800 pièces). Stockage dans le parking souterrain.', 'created_at' => now()->subMonths(2)->addDays(3)]);
        JournalChantier::create(['chantier_id' => $chantiers[0]->id, 'user_id' => $admin->id, 'type' => 'note', 'titre' => 'Avancement maçonnerie', 'contenu' => 'Montage des nouvelles cloisons terminé. Passage électricien prévu semaine prochaine.', 'created_at' => now()->subMonths(1)]);
        JournalChantier::create(['chantier_id' => $chantiers[2]->id, 'user_id' => $admin->id, 'type' => 'note', 'titre' => 'Découverte plomberie', 'contenu' => 'Démolition ancienne salle de bain OK. Découverte tuyauterie plomb à remplacer.', 'created_at' => now()->subWeeks(3)]);
        JournalChantier::create(['chantier_id' => $chantiers[3]->id, 'user_id' => $admin->id, 'type' => 'note', 'titre' => 'Réception provisoire', 'contenu' => 'Chantier terminé. Réception provisoire OK. PV signé par le bourgmestre.', 'created_at' => now()->subMonths(3)]);

        $this->command->info('Base de démonstration créée avec succès !');
        $this->command->table(
            ['Compte', 'Email', 'Mot de passe', 'Rôle'],
            [
                ['Admin', 'demo@gesent.be', 'Demo2026!', 'admin'],
                ['Comptable', 'comptable@gesent.be', 'Demo2026!', 'comptable'],
                ['Lecture', 'lecture@gesent.be', 'Demo2026!', 'lecture'],
            ]
        );
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function creerChantier(Client $client, string $nom, string $adresse, string $statut, int $avancement): Chantier
    {
        $parts = explode(', ', $adresse);
        $villePartie = $parts[1] ?? '';
        $villeTokens = explode(' ', $villePartie);
        $codePostal  = $villeTokens[0] ?? null;
        $ville       = implode(' ', array_slice($villeTokens, 1)) ?: null;

        return Chantier::create([
            'client_id'        => $client->id,
            'nom'              => $nom,
            'adresse_chantier' => $parts[0] ?? $adresse,
            'code_postal'      => $codePostal,
            'ville'            => $ville,
            'statut'           => $statut,
            'avancement'       => $avancement,
            'date_debut'       => now()->subMonths(rand(2, 8))->toDateString(),
            'date_fin_prevue'  => now()->addMonths(rand(1, 6))->toDateString(),
        ]);
    }

    private function creerDevis(Client $client, Chantier $chantier, string $statut, Carbon $date, array $lignes): Devis
    {
        $devis = Devis::create([
            'numero'           => $this->numerotation->suivant('devis'),
            'client_id'        => $client->id,
            'chantier_id'      => $chantier->id,
            'mode_paiement_id' => ModePaiement::first()?->id,
            'created_by'       => 1,
            'statut'           => $statut,
            'date_document'    => $date->toDateString(),
            'date_validite'    => $date->copy()->addDays(30)->toDateString(),
            'delai_reglement'  => 30,
        ]);

        $this->documentService->enregistrerLignes($devis, $lignes);
        $this->documentService->recalculerMontants($devis);

        return $devis;
    }

    private function creerBdcDepuisDevis(Devis $devis, string $statut): BonCommande
    {
        $devis->load('lignes');

        $bdc = BonCommande::create([
            'numero'           => $this->numerotation->suivant('bon_commande'),
            'devis_id'         => $devis->id,
            'client_id'        => $devis->client_id,
            'chantier_id'      => $devis->chantier_id,
            'mode_paiement_id' => $devis->mode_paiement_id,
            'created_by'       => 1,
            'statut'           => $statut,
            'date_document'    => $devis->date_document,
            'delai_reglement'  => 30,
        ]);

        $lignesData = $devis->lignes->map(fn($l) => [
            'designation'   => $l->designation,
            'detail'        => $l->detail,
            'unite'         => $l->unite,
            'quantite'      => $l->quantite,
            'prix_unitaire' => $l->prix_unitaire,
            'taux_tva'      => $l->taux_tva,
            'est_section'   => $l->est_section,
        ])->toArray();

        $this->documentService->enregistrerLignes($bdc, $lignesData);
        $this->documentService->recalculerMontants($bdc);

        return $bdc;
    }

    private function creerFactureSituation(BonCommande $bdc, int $numSituation, float $pctAvancement, string $statut, Carbon $date): Facture
    {
        $bdc->load('lignes');
        $pctFactor = $pctAvancement / 100;

        $facture = Facture::create([
            'numero'                 => $this->numerotation->suivant('facture'),
            'bon_commande_id'        => $bdc->id,
            'client_id'              => $bdc->client_id,
            'chantier_id'            => $bdc->chantier_id,
            'mode_paiement_id'       => $bdc->mode_paiement_id,
            'created_by'             => 1,
            'statut'                 => $statut,
            'date_document'          => $date->toDateString(),
            'date_echeance'          => $date->copy()->addDays(30)->toDateString(),
            'delai_reglement'        => 30,
            'retenue_garantie_pct'   => 5,
            'numero_situation'       => $numSituation,
            'pourcentage_avancement' => $pctAvancement,
            'pourcentage_cumule'     => $numSituation * $pctAvancement,
        ]);

        $lignesData = $bdc->lignes->where('est_section', false)->map(fn($l) => [
            'designation'   => $l->designation,
            'unite'         => $l->unite,
            'quantite'      => round((float)$l->quantite * $pctFactor, 2),
            'prix_unitaire' => $l->prix_unitaire,
            'taux_tva'      => $l->taux_tva,
        ])->values()->toArray();

        $this->documentService->enregistrerLignes($facture, $lignesData);
        $this->documentService->recalculerMontants($facture);

        $facture->update([
            'montant_anterieur' => $numSituation > 1
                ? Facture::where('bon_commande_id', $bdc->id)
                    ->where('numero_situation', '<', $numSituation)
                    ->sum('montant_ttc')
                : 0,
        ]);

        return $facture;
    }

    private function creerFactureAchat(Fournisseur $fournisseur, ?Chantier $chantier, string $description, float $montantHt, Carbon $date, string $statut = 'en_attente'): FactureAchat
    {
        $tva = round($montantHt * 0.21, 2);
        return FactureAchat::create([
            'numero'                => $this->numerotation->suivant('facture_achat'),
            'fournisseur_id'        => $fournisseur->id,
            'chantier_id'           => $chantier?->id,
            'reference_fournisseur' => 'F-' . strtoupper(substr(md5((string)rand()), 0, 8)),
            'categorie'             => 'materiel',
            'date_document'         => $date->toDateString(),
            'date_echeance'         => $date->copy()->addDays(30)->toDateString(),
            'montant_ht'            => $montantHt,
            'taux_tva'              => 21,
            'montant_tva'           => $tva,
            'montant_ttc'           => round($montantHt + $tva, 2),
            'statut'                => $statut,
            'date_paiement'         => $statut === 'payee' ? $date->copy()->addDays(25)->toDateString() : null,
            'notes'                 => $description,
            'peppol_source'         => 'manuel',
        ]);
    }
}
