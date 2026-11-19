<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/components.php';

// ps1.php, ps2.php... postave $platforma_id; inače se uzima iz adrese (?id=)
$id = $platforma_id ?? (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT id, naziv, kratki_naziv FROM platforme WHERE id = ?');
$st->execute([$id]);
$platforma = $st->fetch();

if (!$platforma) {
    http_response_code(404);
    $naslov_stranice = 'Konzola nije pronađena – GamerZone';
    require __DIR__ . '/includes/header.php';
    echo '<main class="wrap sekcija"><h1>Konzola nije pronađena</h1><p><a href="showroom.php">Nazad na showroom</a></p></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Sortiranje: dozvoljene su samo ove vrijednosti (zaštita od SQL injectiona)
$sortovi = [
    'naziv'  => ['Naziv',      'i.naslov ASC'],
    'godina' => ['Najnovije',  'i.godina_izlaska DESC, i.naslov ASC'],
    'cijena' => ['Cijena',     'ip.cijena ASC, i.naslov ASC'],
];
$sort = $_GET['sort'] ?? 'naziv';
if (!isset($sortovi[$sort])) {
    $sort = 'naziv';
}

$st = $pdo->prepare("
    SELECT i.id, i.naslov, i.naslovna_slika, ip.cijena,
           CASE WHEN ip.stara_cijena > ip.cijena
                THEN ROUND((1 - ip.cijena / ip.stara_cijena) * 100) ELSE 0 END AS popust
    FROM igre i
    JOIN igre_platforme ip ON ip.igra_id = i.id
    WHERE ip.platforma_id = ?
    ORDER BY {$sortovi[$sort][1]}");
$st->execute([$platforma['id']]);
$igre = $st->fetchAll();

$naslov_stranice = $platforma['naziv'] . ' – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <p><a href="showroom.php">&larr; Showroom</a></p>
        <h1><?= e($platforma['naziv']) ?></h1>
        <p><?= count($igre) ?> igara</p>

        <div class="sort" aria-label="Sortiranje">
            <?php foreach ($sortovi as $kljuc => [$naziv]): ?>
                <a href="?id=<?= (int)$platforma['id'] ?>&amp;sort=<?= e($kljuc) ?>"
                   class="<?= $kljuc === $sort ? 'aktivno' : '' ?>"><?= e($naziv) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($igre): ?>
            <div class="grid">
                <?php foreach ($igre as $g) {
                    $g['platforme'] = $platforma['kratki_naziv'];
                    $g['tacna_cijena'] = true;
                    prikazi_karticu($g);
                } ?>
            </div>
        <?php else: ?>
            <p class="prazno">Za ovu konzolu još nema igara.</p>
        <?php endif; ?>
    </div>
</section>

<form method="post" action="korpa_dodaj.php">
    <button class="btn btn-sm" type="submit">Dodaj u korpu</button>

<?php require __DIR__ . '/includes/footer.php'; ?>