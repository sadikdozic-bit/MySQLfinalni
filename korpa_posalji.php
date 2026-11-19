<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaPrijava();
provjeriCsrf();

$korpa = $_SESSION['korpa'] ?? [];
if (!$korpa) {
    $_SESSION['poruka'] = 'Korpa je prazna.';
    header('Location: korpa.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $provjera = $pdo->prepare('SELECT cijena, na_zalihi FROM igre_platforme
                               WHERE igra_id = ? AND platforma_id = ? FOR UPDATE');
    $redovi = [];
    $ukupnoCent = 0;

    foreach ($korpa as $kljuc => $kolicina) {
        [$igra, $platforma] = array_map('intval', explode(':', (string)$kljuc) + [0, 0]);
        $kolicina = (int)$kolicina;

        $provjera->execute([$igra, $platforma]);
        $red = $provjera->fetch();

        if (!$red || $kolicina < 1 || (int)$red['na_zalihi'] < $kolicina) {
            $pdo->rollBack();
            $_SESSION['poruka'] = 'Neka igra više nije dostupna u traženoj količini. Provjeri korpu.';
            header('Location: korpa.php');
            exit;
        }

        $cent = (int)round($red['cijena'] * 100);
        $ukupnoCent += $cent * $kolicina;
        $redovi[] = [$igra, $platforma, $kolicina, number_format($cent / 100, 2, '.', '')];
    }

    $pdo->prepare('INSERT INTO narudzbe (korisnik_id, ukupan_iznos) VALUES (?, ?)')
        ->execute([$_SESSION['korisnik_id'], number_format($ukupnoCent / 100, 2, '.', '')]);
    $narudzba = (int)$pdo->lastInsertId();

    $unos = $pdo->prepare('INSERT INTO stavke_narudzbe (narudzba_id, igra_id, platforma_id, kolicina, cijena_u_trenutku)
                           VALUES (?, ?, ?, ?, ?)');
    foreach ($redovi as [$igra, $platforma, $kolicina, $cijenaStavke]) {
        $unos->execute([$narudzba, $igra, $platforma, $kolicina, $cijenaStavke]);
    }

    $pdo->commit();
    $_SESSION['korpa'] = [];
    $_SESSION['poruka'] = 'Zahtjev za kupovinu #' . $narudzba . ' je poslan i čeka odobrenje.';
    header('Location: approved.php?status=na_cekanju');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['poruka'] = 'Greška pri slanju zahtjeva. Pokušaj ponovo.';
    header('Location: korpa.php');
}
exit;