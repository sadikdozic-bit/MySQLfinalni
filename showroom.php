<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/components.php';

$naslov_stranice = 'Showroom – GamerZone';

$kategorije = $pdo->query("
    SELECT p.id, p.naziv, p.kratki_naziv, COUNT(ip.igra_id) AS broj_igara
    FROM platforme p
    LEFT JOIN igre_platforme ip ON ip.platforma_id = p.id
    GROUP BY p.id, p.naziv, p.kratki_naziv
    ORDER BY p.id")->fetchAll();

$istaknuto = $pdo->query(SQL_KARTICE . "
    JOIN showroom s ON s.igra_id = i.id
    WHERE s.aktivno = 1
    GROUP BY i.id, s.redoslijed
    ORDER BY s.redoslijed
    LIMIT 8")->fetchAll();

$akcija = $pdo->query(SQL_KARTICE . "
    GROUP BY i.id
    HAVING popust > 0
    ORDER BY popust DESC
    LIMIT 8")->fetchAll();

$najnovije = $pdo->query(SQL_KARTICE . "
    GROUP BY i.id
    ORDER BY i.godina_izlaska DESC, i.id DESC
    LIMIT 8")->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Showroom</h1>
        <p>Odaberi konzolu i pogledaj sve igre za nju.</p>
    </div>
</section>

<section class="sekcija" id="kategorije">
    <div class="wrap">
        <div class="kat-grid">
            <?php foreach ($kategorije as $k): ?>
                <a class="kat" href="konzola.php?id=<?= (int)$k['id'] ?>">
                    <span class="kat-kod"><?= e($k['kratki_naziv']) ?></span>
                    <span class="kat-naziv"><?= e($k['naziv']) ?></span>
                    <span class="kat-broj"><?= (int)$k['broj_igara'] ?> igara</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
prikazi_sekciju('istaknuto', 'Istaknuto', $istaknuto);
prikazi_sekciju('akcija', 'Na akciji', $akcija);
prikazi_sekciju('novo', 'Najnovija izdanja', $najnovije);
require __DIR__ . '/includes/footer.php';