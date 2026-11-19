<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

trebaPrijava();

$st = $pdo->prepare("
    SELECT i.id, i.naslov, i.naslovna_slika, p.kratki_naziv,
           SUM(s.kolicina) AS kolicina,
           MAX(COALESCE(n.datum_odluke, n.datum)) AS kupljeno
    FROM narudzbe n
    JOIN stavke_narudzbe s ON s.narudzba_id = n.id
    JOIN igre i ON i.id = s.igra_id
    JOIN platforme p ON p.id = s.platforma_id
    WHERE n.korisnik_id = ? AND n.status = 'odobreno'
    GROUP BY i.id, i.naslov, i.naslovna_slika, p.id, p.kratki_naziv
    ORDER BY kupljeno DESC, i.naslov");
$st->execute([$_SESSION['korisnik_id']]);
$igre = $st->fetchAll();

$naCekanju = $pdo->prepare("SELECT COUNT(*) FROM narudzbe WHERE korisnik_id = ? AND status = 'na_cekanju'");
$naCekanju->execute([$_SESSION['korisnik_id']]);
$naCekanju = (int)$naCekanju->fetchColumn();

$naslov_stranice = 'Moja biblioteka – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Moja biblioteka</h1>
        <p><?= count($igre) ?> <?= count($igre) === 1 ? 'igra' : 'igara' ?></p>
        <?php if ($naCekanju > 0): ?>
            <p class="muted">
                Imaš <?= $naCekanju ?> <?= $naCekanju === 1 ? 'zahtjev' : 'zahtjeva' ?> na čekanju.
                <a href="approved.php?status=na_cekanju">Pogledaj</a>
            </p>
        <?php endif; ?>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($igre): ?>
            <div class="grid">
                <?php foreach ($igre as $g): ?>
                    <?php $slika = naslovna($g['naslovna_slika']); ?>
                    <a class="card" href="igra.php?id=<?= (int)$g['id'] ?>">
                        <div class="card-cover">
                            <?php if ($slika): ?>
                                <img src="<?= e($slika) ?>" alt="Naslovnica igre <?= e($g['naslov']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="cover-placeholder" aria-hidden="true"><?= e(mb_substr($g['naslov'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <?php if ((int)$g['kolicina'] > 1): ?>
                                <span class="badge-sale">×<?= (int)$g['kolicina'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><?= e($g['naslov']) ?></h3>
                            <p class="plats"><span><?= e($g['kratki_naziv']) ?></span></p>
                            <span class="muted kupljeno">Kupljeno: <?= e(date('d.m.Y.', strtotime($g['kupljeno']))) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="prazno">
                Biblioteka je prazna. Igre se pojave ovdje kad admin odobri tvoj zahtjev za kupovinu.
                <a href="showroom.php">Pogledaj showroom</a>
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>