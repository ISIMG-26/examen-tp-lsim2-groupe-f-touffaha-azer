<?php

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money_dt($value, int $decimals = 2): string
{
    return number_format((float)$value, $decimals, ',', ' ') . ' DT';
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/**
 * Exécute une requête préparée MySQLi et retourne le statement.
 * $types suit la convention mysqli_bind_param (ex: 'siid').
 */
function db_stmt(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new RuntimeException('Erreur prepare: ' . mysqli_error($conn));
    }

    if ($types !== '' && $params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        throw new RuntimeException('Erreur execute: ' . $err);
    }

    return $stmt;
}

function db_fetch_one(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $stmt = db_stmt($conn, $sql, $types, $params);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function db_fetch_all(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $stmt = db_stmt($conn, $sql, $types, $params);
    $res = mysqli_stmt_get_result($stmt);
    $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($stmt);
    return $rows;
}

function db_scalar(mysqli $conn, string $sql, string $types = '', array $params = [])
{
    $row = db_fetch_one($conn, $sql, $types, $params);
    if (!$row) return null;
    $values = array_values($row);
    return $values[0] ?? null;
}

function db_exec(mysqli $conn, string $sql, string $types = '', array $params = []): int
{
    $stmt = db_stmt($conn, $sql, $types, $params);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    return $affected;
}

/**
 * Déduit la quantité du stock d'un produit
 */
function update_product_stock(mysqli $conn, int $product_id, int $quantity): int {
    return db_exec($conn, "UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?", 'ii', [$quantity, $product_id]);
}

