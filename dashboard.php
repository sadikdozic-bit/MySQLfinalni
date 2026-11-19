<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

trebaAdmina();

$brojKorisnika = (int)$pdo->query('SELECT COUNT(*) FROM korisnici')->fetchColumn();
$brojIgara = (int)$pdo->query('SELECT COUNT(*) FROM igre')->fetchColumn();
$naCekanju = (int)$pdo->query("SELECT COUNT(*) FROM narudzbe WHERE status = 'na_cekanju'")->fetchColumn();
$prihod = (float)$pdo->query("SELECT COALESCE(SUM(ukupan_iznos), 0) FROM narudzbe WHERE status = 'odobreno'")->fetchColumn();

$poMjesecu = $pdo->query("
    SELECT DATE_FORMAT(datum_odluke, '%Y-%m') AS mjesec, SUM(ukupan_iznos) AS iznos, COUNT(*) AS broj
    FROM narudzbe
    WHERE status = 'odobreno'
    GROUP BY mjesec
    ORDER BY mjesec DESC
    LIMIT 12")->fetchAll();

$poKonzoli = $pdo->query("
    SELECT p.naziv, SUM(s.kolicina) AS komada, SUM(s.kolicina * s.cijena_u_trenutku) AS iznos
    FROM stavke_narudzbe s
    JOIN narudzbe n ON n.id = s.narudzba_id
    JOIN platforme p ON p.id = s.platforma_id
    WHERE n.status = 'odobreno'
    GROUP BY p.id, p.naziv
    ORDER BY iznos DESC")->fetchAll();

$topIgre = $pdo->query("
    SELECT i.naslov, SUM(s.kolicina) AS komada
    FROM stavke_narudzbe s
    JOIN narudzbe n ON n.id = s.narudzba_id
    JOIN igre i ON i.id = s.igra_id
    WHERE n.status = 'odobreno'
    GROUP BY i.id, i.naslov
    ORDER BY komada DESC, i.naslov
    LIMIT 5")->fetchAll();

$maloZaliha = $pdo->query("
    SELECT i.naslov, p.kratki_naziv, ip.na_zalihi
    FROM igre_platforme ip
    JOIN igre i ON i.id = ip.igra_id
    JOIN platforme p ON p.id = ip.platforma_id
    WHERE ip.na_zalihi <= 3
    ORDER BY ip.na_zalihi, i.naslov
    LIMIT 10")->fetchAll();

$maxMjesec = 0.0;
foreach ($poMjesecu as $m) {
    $maxMjesec = max($maxMjesec, (float)$m['iznos']);
}

$naslov_stranice = 'Dashboard – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Dashboard</h1>
        <div class="sort">
            <a href="admin_narudzbe.php">Zahtjevi na čekanju</a>
            <a href="approved.php">Sve narudžbe</a>
        </div>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <div class="stat-grid">
            <div class="stat"><span class="stat-broj"><?= e(cijena($prihod)) ?></span><span class="muted">Ukupan prihod</span></div>
            <div class="stat"><span class="stat-broj"><?= $naCekanju ?></span><span class="muted">Zahtjeva na čekanju</span></div>
            <div class="stat"><span class="stat-broj"><?= $brojKorisnika ?></span><span class="muted">Korisnika</span></div>
            <div class="stat"><span class="stat-broj"><?= $brojIgara ?></span><span class="muted">Igara u bazi</span></div>
        </div>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <h2>Prihod po mjesecu</h2>
        <?php if ($poMjesecu): ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead><tr><th>Mjesec</th><th>Narudžbi</th><th>Prihod</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($poMjesecu as $m): ?>
                        <tr>
                            <td><?= e($m['mjesec']) ?></td>
                            <td><?= (int)$m['broj'] ?></td>
                            <td><?= e(cijena((float)$m['iznos'])) ?></td>
                            <td class="traka-celija">
                                <span class="traka" style="width: <?= $maxMjesec > 0 ? round($m['iznos'] / $maxMjesec * 100) : 0 ?>%"></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="prazno">Još nema odobrenih narudžbi.</p>
        <?php endif; ?>
    </div>
</section>

<section class="sekcija">
    <div class="wrap dvije-kolone">
        <div>
            <h2>Prihod po konzoli</h2>
            <?php if ($poKonzoli): ?>
                <div class="tabela-wrap">
                    <table class="tabela">
                        <thead><tr><th>Konzola</th><th>Komada</th><th>Prihod</th></tr></thead>
                        <tbody>
                        <?php foreach ($poKonzoli as $k): ?>
                            <tr>
                                <td><?= e($k['naziv']) ?></td>
                                <td><?= (int)$k['komada'] ?></td>
                                <td><?= e(cijena((float)$k['iznos'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="prazno">Nema podataka.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2>Najprodavanije igre</h2>
            <?php if ($topIgre): ?>
                <div class="tabela-wrap">
                    <table class="tabela">
                        <thead><tr><th>Igra</th><th>Komada</th></tr></thead>
                        <tbody>
                        <?php foreach ($topIgre as $t): ?>
                            <tr><td><?= e($t['naslov']) ?></td><td><?= (int)$t['komada'] ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="prazno">Nema podataka.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <h2>Malo na zalihi (3 ili manje)</h2>
        <?php if ($maloZaliha): ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead><tr><th>Igra</th><th>Konzola</th><th>Zaliha</th></tr></thead>
                    <tbody>
                    <?php foreach ($maloZaliha as $z): ?>
                        <tr>
                            <td><?= e($z['naslov']) ?></td>
                            <td><?= e($z['kratki_naziv']) ?></td>
                            <td><?= (int)$z['na_zalihi'] === 0 ? 'Rasprodano' : (int)$z['na_zalihi'] . ' kom' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="prazno">Sve igre imaju dovoljno zaliha.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>