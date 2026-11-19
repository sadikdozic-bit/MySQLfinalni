<?php
// Zajednički upit za kartice igara. Dodaj WHERE / GROUP BY / ORDER BY / LIMIT iza njega.
const SQL_KARTICE = "
    SELECT i.id, i.naslov, i.naslovna_slika,
           MIN(ip.cijena) AS cijena,
           MAX(CASE WHEN ip.stara_cijena > ip.cijena
                    THEN ROUND((1 - ip.cijena / ip.stara_cijena) * 100) ELSE 0 END) AS popust,
           GROUP_CONCAT(DISTINCT p.kratki_naziv ORDER BY p.id SEPARATOR ',') AS platforme
    FROM igre i
    JOIN igre_platforme ip ON ip.igra_id = i.id
    JOIN platforme p ON p.id = ip.platforma_id";

function prikazi_karticu(array $g): void
{
    $slika = naslovna($g['naslovna_slika']);
    $platforme = array_filter(explode(',', (string)$g['platforme']));
    ?>
    <a class="card" href="igra.php?id=<?= (int)$g['id'] ?>">
        <div class="card-cover">
            <?php if ($slika): ?>
                <img src="<?= e($slika) ?>" alt="Naslovnica igre <?= e($g['naslov']) ?>" loading="lazy">
            <?php else: ?>
                <div class="cover-placeholder" aria-hidden="true"><?= e(mb_substr($g['naslov'], 0, 1)) ?></div>
            <?php endif; ?>
            <?php if ((int)$g['popust'] > 0): ?>
                <span class="price-tag"><?= empty($g['tacna_cijena']) ? 'od ' : '' ?><?= e(cijena((float)$g['cijena'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <h3><?= e($g['naslov']) ?></h3>
            <p class="plats">
                <?php foreach ($platforme as $p): ?><span><?= e($p) ?></span><?php endforeach; ?>
            </p>
            <span class="price-tag">od <?= e(cijena((float)$g['cijena'])) ?></span>
        </div>
    </a>
    <?php
}

function prikazi_sekciju(string $id, string $naslov, array $igre): void
{
    ?>
    <section class="sekcija" id="<?= e($id) ?>">
        <div class="wrap">
            <h2><?= e($naslov) ?></h2>
            <?php if ($igre): ?>
                <div class="grid">
                    <?php foreach ($igre as $g) { prikazi_karticu($g); } ?>
                </div>
            <?php else: ?>
                <p class="prazno">Ovdje još nema igara. Dodaj ih u admin panelu i pojavit će se odmah.</p>
            <?php endif; ?>
        </div>
    </section>
    <?php
}