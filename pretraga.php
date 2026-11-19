<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/components.php';

$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 100);
$konzola = (int)($_GET['konzola'] ?? 0);

$platforme = $pdo->query('SELECT id, naziv FROM platforme ORDER BY id')->fetchAll();

$trazeno = $q !== '' || $konzola > 0;
$igre = [];

if ($trazeno) {
    $uslovi = [];
    $params = [];

    if ($q !== '') {
        // Specijalni znakovi za LIKE se escape-uju, da se %, _ i \ traže kao obični znakovi
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q);
        $uslovi[] = 'i.naslov LIKE ?';
        $params[] = '%' . $escaped . '%';
    }
    if ($konzola > 0) {
        $uslovi[] = 'ip.platforma_id = ?';
        $params[] = $konzola;
    }

    $st = $pdo->prepare(SQL_KARTICE . ' WHERE ' . implode(' AND ', $uslovi) . '
                        GROUP BY i.id
                        ORDER BY i.naslov
                        LIMIT 60');
    $st->execute($params);
    $igre = $st->fetchAll();
}

$naslov_stranice = 'Pretraga – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Pretraga</h1>

        <form class="pretraga-forma" method="get" action="pretraga.php" role="search">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Naziv igre" aria-label="Naziv igre" maxlength="100">
            <select name="konzola" aria-label="Konzola">
                <option value="0">Sve konzole</option>
                <?php foreach ($platforme as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= $konzola === (int)$p['id'] ? 'selected' : '' ?>>
                        <?= e($p['naziv']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-sm" type="submit">Traži</button>
        </form>

        <?php if ($trazeno): ?>
            <p><?= count($igre) ?> <?= count($igre) === 1 ? 'rezultat' : 'rezultata' ?><?= count($igre) === 60 ? ' (prikazano prvih 60)' : '' ?></p>
        <?php endif; ?>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if (!$trazeno): ?>
            <p class="prazno">Upiši naziv igre ili odaberi konzolu.</p>
        <?php elseif ($igre): ?>
            <div class="grid">
                <?php foreach ($igre as $g) { prikazi_karticu($g); } ?>
            </div>
        <?php else: ?>
            <p class="prazno">Nema rezultata. Probaj kraći naziv ili drugu konzolu.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>