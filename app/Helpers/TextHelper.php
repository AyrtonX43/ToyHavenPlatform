<?php

if (!function_exists('normalizePhilippineText')) {
    /**
     * Normalize Philippine text by replacing special characters with ASCII equivalents.
     * Handles both UTF-8 encoded characters and double-encoded characters (Ã±).
     *
     * @param string|null $text
     * @return string|null
     */
    function normalizePhilippineText($text)
    {
        if (empty($text)) {
            return $text;
        }

        $replacements = [
            // Handle double-encoded characters (common database encoding issue)
            'Ã±' => 'n',  // ñ double-encoded
            'Ã' => 'N',  // Ñ double-encoded
            'Ã¡' => 'a',  // á double-encoded
            'Ã©' => 'e',  // é double-encoded
            'Ã­' => 'i',  // í double-encoded
            'Ã³' => 'o',  // ó double-encoded
            'Ãº' => 'u',  // ú double-encoded
            'Ã¼' => 'u',  // ü double-encoded
            
            // Handle byte-level encoding issues
            chr(195).chr(177) => 'n',  // ñ as bytes
            chr(195).chr(145) => 'N',  // Ñ as bytes
            chr(195).chr(161) => 'a',  // á as bytes
            chr(195).chr(169) => 'e',  // é as bytes
            chr(195).chr(173) => 'i',  // í as bytes
            chr(195).chr(179) => 'o',  // ó as bytes
            chr(195).chr(186) => 'u',  // ú as bytes
            
            // Handle proper UTF-8 characters
            'ñ' => 'n', 'Ñ' => 'N',
            'á' => 'a', 'Á' => 'A',
            'é' => 'e', 'É' => 'E',
            'í' => 'i', 'Í' => 'I',
            'ó' => 'o', 'Ó' => 'O',
            'ú' => 'u', 'Ú' => 'U',
            'ü' => 'u', 'Ü' => 'U',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
