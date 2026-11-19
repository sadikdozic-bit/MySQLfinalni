<?php
declare(strict_types=1);

$host = 'localhost';
$dbname = 'gamerzone';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Greška pri povezivanju s bazom podataka.');
}

// Siguran ispis u HTML-u
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function cijena(float $c): string
{
    return number_format($c, 2, ',', '.') . ' KM';
}

// Vraća putanju slike ako fajl postoji, inače null
function naslovna(?string $fajl): ?string
{
    if (!$fajl) {
        return null;
    }
    return is_file(__DIR__ . '/../img/covers/' . $fajl) ? 'img/covers/' . rawurlencode($fajl) : null;
}