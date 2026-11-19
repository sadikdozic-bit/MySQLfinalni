<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/components.php';
require __DIR__ . '/includes/auth.php';

trebaPrijava();

$st = $pdo->prepare(SQL_KARTICE . "
    JOIN wishlist w ON w.igra_id = i.id
    WHERE w.korisnik_id = ?
    GROUP BY i.id, w.datum_dodavanja
    ORDER BY w.datum_dodavanja DESC");
$st->execute([$_SESSION['korisnik_id']]);
$igre = $st->fetchAll();

$naslov_stranice = 'Moj wishlist – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Moj wishlist</h1>
        <p><?= count($igre) ?> igara</p>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($igre): ?>
            <div class="grid">
                <?php foreach ($igre as $g): ?>
                    <div class="wl-item">
                        <?php prikazi_karticu($g); ?>
                        <form method="post" action="wishlist_toggle.php">
                            <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                            <input type="hidden" name="igra_id" value="<?= (int)$g['id'] ?>">
                            <button class="btn btn-sm btn-outline" type="submit">Ukloni</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="prazno">Wishlist je prazan. Otvori neku igru i klikni "Dodaj na wishlist".</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>