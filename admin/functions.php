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