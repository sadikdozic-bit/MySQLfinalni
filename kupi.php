<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaPrijava();
provjeriCsrf();

$igra = (int)($_POST['igra_id'] ?? 0);
$platforma = (int)($_POST['platforma_id'] ?? 0);
$nazad = 'igra.php?id=' . $igra;

$st = $pdo->prepare('SELECT cijena, na_zalihi FROM igre_platforme WHERE igra_id = ? AND platforma_id = ?');
$st->execute([$igra, $platforma]);
$red = $st->fetch();

if (!$red || (int)$red['na_zalihi'] < 1) {
    $_SESSION['poruka'] = 'Ova igra trenutno nije dostupna za odabranu konzolu.';
    header('Location: ' . $nazad);
    exit;
}

try {
    $pdo->beginTransaction();

    $pdo->prepare('INSERT INTO narudzbe (korisnik_id, ukupan_iznos) VALUES (?, ?)')
        ->execute([$_SESSION['korisnik_id'], $red['cijena']]);
    $narudzba = (int)$pdo->lastInsertId();

    $pdo->prepare('INSERT INTO stavke_narudzbe (narudzba_id, igra_id, platforma_id, kolicina, cijena_u_trenutku)
                   VALUES (?, ?, ?, 1, ?)')
        ->execute([$narudzba, $igra, $platforma, $red['cijena']]);

    $pdo->commit();
    $_SESSION['poruka'] = 'Zahtjev za kupovinu je poslan i čeka odobrenje.';
    header('Location: approved.php?status=na_cekanju');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['poruka'] = 'Greška pri slanju zahtjeva. Pokušaj ponovo.';
    header('Location: ' . $nazad);
}
exit;