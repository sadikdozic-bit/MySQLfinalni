<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaPrijava();
provjeriCsrf();

$akcija = $_POST['akcija'] ?? '';
$kljuc = (string)($_POST['kljuc'] ?? '');

if ($akcija === 'isprazni') {
    $_SESSION['korpa'] = [];
    $_SESSION['poruka'] = 'Korpa je ispražnjena.';
} elseif (preg_match('/^\d+:\d+$/', $kljuc) && isset($_SESSION['korpa'][$kljuc])) {
    if ($akcija === 'ukloni') {
        unset($_SESSION['korpa'][$kljuc]);
        $_SESSION['poruka'] = 'Igra je uklonjena iz korpe.';
    } elseif ($akcija === 'kolicina') {
        [$igra, $platforma] = array_map('intval', explode(':', $kljuc));
        $kolicina = (int)($_POST['kolicina'] ?? 0);

        $st = $pdo->prepare('SELECT na_zalihi FROM igre_platforme WHERE igra_id = ? AND platforma_id = ?');
        $st->execute([$igra, $platforma]);
        $zaliha = (int)$st->fetchColumn();

        if ($kolicina < 1) {
            unset($_SESSION['korpa'][$kljuc]);
        } else {
            $_SESSION['korpa'][$kljuc] = min($kolicina, $zaliha, 10);
            if ($kolicina > min($zaliha, 10)) {
                $_SESSION['poruka'] = 'Količina je ograničena na ' . min($zaliha, 10) . '.';
            }
        }
    }
}

header('Location: korpa.php');
exit;