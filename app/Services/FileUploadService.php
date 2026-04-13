<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    // Types MIME autorisés par catégorie
    private const MIMES = [
        'document'   => ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'],
        'image'      => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'certificat' => ['application/pdf', 'image/jpeg', 'image/png'],
        'csv'        => ['text/csv', 'text/plain', 'application/vnd.ms-excel'],
    ];

    // Tailles max par catégorie (en Ko)
    private const MAX_SIZES = [
        'document'   => 20480,  // 20 Mo
        'image'      => 5120,   // 5 Mo
        'certificat' => 10240,  // 10 Mo
        'csv'        => 20480,  // 20 Mo
    ];

    /**
     * Valide et stocke un fichier uploadé.
     * Retourne le chemin relatif stocké, ou throw une exception.
     */
    public function stocker(UploadedFile $fichier, string $categorie, string $dossier): string
    {
        $mimesAutorises = self::MIMES[$categorie] ?? self::MIMES['document'];
        $tailleMax      = self::MAX_SIZES[$categorie] ?? 20480;

        // Vérification MIME réelle (pas juste l'extension)
        $mimeReel = $fichier->getMimeType();
        if (! in_array($mimeReel, $mimesAutorises)) {
            throw new \InvalidArgumentException(
                "Type de fichier non autorisé ({$mimeReel}). Types acceptés : " . implode(', ', $mimesAutorises)
            );
        }

        // Vérification taille
        if ($fichier->getSize() / 1024 > $tailleMax) {
            throw new \InvalidArgumentException(
                'Fichier trop volumineux (' . round($fichier->getSize() / 1024 / 1024, 1) . ' Mo). Max : ' . ($tailleMax / 1024) . ' Mo.'
            );
        }

        // Nom sécurisé — jamais le nom original (risque d'injection)
        $nomSecurise = time() . '_' . bin2hex(random_bytes(8)) . '.' . $fichier->guessExtension();

        // Stocker dans storage/app/ (PAS public/)
        return $fichier->storeAs($dossier, $nomSecurise);
    }

    /**
     * Servir un fichier protégé depuis storage/app/.
     */
    public static function servir(string $chemin): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! Storage::exists($chemin)) {
            abort(404);
        }

        return Storage::download($chemin);
    }
}
