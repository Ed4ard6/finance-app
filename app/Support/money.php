<?php
// app/Support/money.php

/**
 * parse_cop(): convierte cualquier string de monto en **entero de pesos** (sin centavos).
 * - Ignora separadores de miles (puntos/comas/espacios).
 * - Mantiene el signo si viene con "-".
 * - No escala (no divide ni multiplica).
 *   Ej: "250.000" -> 250000, "COP - 1,200,000" -> -1200000
 */
function parse_cop(string $raw): int {
    $raw = trim($raw);
    if ($raw === '') return 0;

    // ¿negativo?
    $neg = str_contains($raw, '-');

    // quita todo menos dígitos
    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === '' ) return 0;

    $n = (int)$digits;
    return $neg ? -$n : $n;
}

/**
 * normalize_by_type(): aplica signo según el tipo/kind de la transacción.
 * Para mantener coherencia en BD:
 *  - income, saving  => positivo
 *  - expense, debt   => negativo
 */
function normalize_by_type(int $amount, string $kind): int {
    $kind = strtolower($kind);
    return in_array($kind, ['expense','debt'], true) ? -abs($amount) : abs($amount);
}
