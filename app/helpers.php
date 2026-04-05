<?php

if (!function_exists('like_escape')) {
    /**
     * Échappe les caractères spéciaux LIKE (%, _, \) pour les requêtes Eloquent/PDO.
     * Utilisation : '%' . like_escape($value) . '%'
     */
    function like_escape(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
