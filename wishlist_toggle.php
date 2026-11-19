<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaPrijava();
provjeriCsrf();

$igra = (int)($_POST['igra_id'] ?? 0);
$nazad = (($_POST['nazad'] ?? '') === 'igra') ? 'igra.php?id=' . $igra : 'wishlist.php';

$st = $pdo->prepare('SELECT id FROM igre WHERE id = ?');
$st->execute([$igra]);
if (!$st->fetch()) {
    header('Location: wishlist.php');
    exit;
}

$st = $pdo->prepare('DELETE FROM wishlist WHERE korisnik_id = ? AND igra_id = ?');
$st->execute([$_SESSION['korisnik_id'], $igra]);

if ($st->rowCount() === 0) {
    $pdo->prepare('INSERT INTO wishlist (korisnik_id, igra_id) VALUES (?, ?)')
        ->execute([$_SESSION['korisnik_id'], $igra]);
    $_SESSION['poruka'] = 'Igra je dodana na wishlist.';
} else {
    $_SESSION['poruka'] = 'Igra je uklonjena s wishliste.';
}

header('Location: ' . $nazad);
exit;