<?php

if (! function_exists('trans_safe')) {
    /**
     * Translate the given message safely (ensures string output).
     */
    function trans_safe(string $key, array $replace = [], ?string $locale = null): string
    {
        $translation = __($key, $replace, $locale);

        // If translation returns an array, convert to string
        if (is_array($translation)) {
            $currentLocale = $locale ?? app()->getLocale();

            return $translation[$currentLocale] ?? $translation['en'] ?? $key;
        }

        return (string) $translation;
    }
}
