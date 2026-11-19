<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaAdmina();
provjeriCsrf();

$id = (int)($_POST['narudzba_id'] ?? 0);

try {
    $pdo->beginTransaction();

    $st = $pdo->prepare("SELECT id FROM narudzbe WHERE id = ? AND status = 'na_cekanju' FOR UPDATE");
    $st->execute([$id]);
    if (!$st->fetch()) {
        $pdo->rollBack();
        $_SESSION['poruka'] = 'Narudžba ne postoji ili je već obrađena.';
        header('Location: admin_narudzbe.php');
        exit;
    }

    $st = $pdo->prepare('SELECT igra_id, platforma_id, kolicina FROM stavke_narudzbe WHERE narudzba_id = ?');
    $st->execute([$id]);
    $stavke = $st->fetchAll();

    $umanji = $pdo->prepare('UPDATE igre_platforme SET na_zalihi = na_zalihi - ?
                             WHERE igra_id = ? AND platforma_id = ? AND na_zalihi >= ?');
    foreach ($stavke as $s) {
        $umanji->execute([$s['kolicina'], $s['igra_id'], $s['platforma_id'], $s['kolicina']]);
        if ($umanji->rowCount() === 0) {
            $pdo->rollBack();
            $_SESSION['poruka'] = 'Nema dovoljno igara na zalihi. Narudžba nije odobrena.';
            header('Location: admin_narudzbe.php');
            exit;
        }
    }

    $pdo->prepare("UPDATE narudzbe SET status = 'odobreno', datum_odluke = NOW() WHERE id = ?")->execute([$id]);
    $pdo->commit();
    $_SESSION['poruka'] = 'Narudžba #' . $id . ' je odobrena.';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['poruka'] = 'Greška pri odobravanju narudžbe.';
}

header('Location: admin_narudzbe.php');
exit;