<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

samoPost();
trebaAdmina();
provjeriCsrf();

$id = (int)($_POST['narudzba_id'] ?? 0);

$st = $pdo->prepare("UPDATE narudzbe SET status = 'odbijeno', datum_odluke = NOW()
                     WHERE id = ? AND status = 'na_cekanju'");
$st->execute([$id]);

$_SESSION['poruka'] = $st->rowCount() > 0
    ? 'Narudžba #' . $id . ' je odbijena.'
    : 'Narudžba ne postoji ili je već obrađena.';

header('Location: admin_narudzbe.php');
exit;