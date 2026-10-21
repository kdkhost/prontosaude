<?php

// Funções utilitárias globais do Pronto Saúde

if (!function_exists('formata_mes_ano')) {
    /**
     * Formata data para o formato "Mês de Ano" em português sem depender de strftime (compatível com PHP 8.4+)
     */
    function formata_mes_ano($data) {
        if (empty($data)) return '';
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $ts = strtotime($data);
        if (!$ts) return (string)$data;
        $m = (int)date('n', $ts);
        $y = date('Y', $ts);
        return ($meses[$m] ?? '') . ' de ' . $y;
    }
}

if (!function_exists('sanitize_output')) {
    function sanitize_output($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('gerar_slug')) {
    /**
     * Gera slug limpo e amigável em português para URLs (compatível com PHP 8.4+)
     */
    function gerar_slug($texto) {
        if (empty($texto)) return '';
        // Converte acentos
        if (function_exists('transliterator_transliterate')) {
            $trans = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', (string)$texto);
            if ($trans !== false) {
                $texto = $trans;
            }
        }
        $texto = preg_replace('~[^\pL\d]+~u', '-', (string)$texto);
        if (function_exists('iconv')) {
            $conv = @iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $texto);
            if ($conv !== false) {
                $texto = $conv;
            }
        }
        $texto = preg_replace('~[^-\w]+~', '', $texto);
        $texto = trim($texto, '-');
        $texto = preg_replace('~-+~', '-', $texto);
        $texto = strtolower($texto);
        return $texto ?: 'item-' . time();
    }
}