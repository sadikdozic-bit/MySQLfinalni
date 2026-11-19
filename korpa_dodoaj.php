<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaPrijava();
provjeriCsrf();

$igra = (int)($_POST['igra_id'] ?? 0);
$platforma = (int)($_POST['platforma_id'] ?? 0);
$nazad = 'igra.php?id=' . $igra;

$st = $pdo->prepare('SELECT na_zalihi FROM igre_platforme WHERE igra_id = ? AND platforma_id = ?');
$st->execute([$igra, $platforma]);
$red = $st->fetch();

if (!$red || (int)$red['na_zalihi'] < 1) {
    $_SESSION['poruka'] = 'Ova igra trenutno nije dostupna za odabranu konzolu.';
    header('Location: ' . $nazad);
    exit;
}

$kljuc = $igra . ':' . $platforma;
$trenutno = $_SESSION['korpa'][$kljuc] ?? 0;
$max = min((int)$red['na_zalihi'], 10);

if ($trenutno >= $max) {
    $_SESSION['poruka'] = 'Već imaš maksimalnu količinu ove igre u korpi.';
} else {
    $_SESSION['korpa'][$kljuc] = $trenutno + 1;
    $_SESSION['poruka'] = 'Igra je dodana u korpu.';
}

header('Location: ' . $nazad);
exit;