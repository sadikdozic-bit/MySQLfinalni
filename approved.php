<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

trebaPrijava();

$admin = ($_SESSION['uloga'] ?? '') === 'admin';
$tabovi = ['odobreno' => 'Odobrene', 'na_cekanju' => 'Na čekanju', 'odbijeno' => 'Odbijene'];
$status = $_GET['status'] ?? 'odobreno';
if (!isset($tabovi[$status])) {
    $status = 'odobreno';
}

$sql = "SELECT n.id, n.datum, n.ukupan_iznos, k.ime,
               GROUP_CONCAT(CONCAT(i.naslov, ' (', p.kratki_naziv, ')') SEPARATOR ', ') AS stavke
        FROM narudzbe n
        JOIN korisnici k ON k.id = n.korisnik_id
        JOIN stavke_narudzbe s ON s.narudzba_id = n.id
        JOIN igre i ON i.id = s.igra_id
        JOIN platforme p ON p.id = s.platforma_id
        WHERE n.status = ?";
$params = [$status];
if (!$admin) {
    $sql .= ' AND n.korisnik_id = ?';
    $params[] = $_SESSION['korisnik_id'];
}
$sql .= ' GROUP BY n.id, n.datum, n.ukupan_iznos, k.ime ORDER BY n.datum DESC';

$st = $pdo->prepare($sql);
$st->execute($params);
$narudzbe = $st->fetchAll();

$naslov_stranice = 'Narudžbe – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1><?= $admin ? 'Sve narudžbe' : 'Moje narudžbe' ?></h1>
        <div class="sort" aria-label="Status narudžbe">
            <?php foreach ($tabovi as $kljuc => $naziv): ?>
                <a href="?status=<?= e($kljuc) ?>" class="<?= $kljuc === $status ? 'aktivno' : '' ?>"><?= e($naziv) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($narudzbe): ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead>
                        <tr><th>#</th><th>Datum</th><?php if ($admin): ?><th>Kupac</th><?php endif; ?><th>Igre</th><th>Iznos</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($narudzbe as $n): ?>
                        <tr>
                            <td><?= (int)$n['id'] ?></td>
                            <td><?= e(date('d.m.Y. H:i', strtotime($n['datum']))) ?></td>
                            <?php if ($admin): ?><td><?= e($n['ime']) ?></td><?php endif; ?>
                            <td><?= e($n['stavke']) ?></td>
                            <td><?= e(cijena((float)$n['ukupan_iznos'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="prazno">Nema narudžbi u ovoj kategoriji.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>