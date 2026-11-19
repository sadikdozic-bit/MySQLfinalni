<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare('SELECT id, naslov, opis, godina_izlaska, naslovna_slika FROM igre WHERE id = ?');
$st->execute([$id]);
$igra = $st->fetch();

if (!$igra) {
    http_response_code(404);
    $naslov_stranice = 'Igra nije pronađena – GamerZone';
    require __DIR__ . '/includes/header.php';
    echo '<main class="wrap sekcija"><h1>Igra nije pronađena</h1><p><a href="showroom.php">Nazad na showroom</a></p></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$st = $pdo->prepare('SELECT p.id AS platforma_id, p.naziv, ip.cijena, ip.stara_cijena, ip.na_zalihi
                     FROM igre_platforme ip
                     JOIN platforme p ON p.id = ip.platforma_id
                     WHERE ip.igra_id = ?
                     ORDER BY p.id');
$st->execute([$id]);
$platforme = $st->fetchAll();

$ulogovan = isset($_SESSION['korisnik_id']);
$uListi = false;
if ($ulogovan) {
    $st = $pdo->prepare('SELECT 1 FROM wishlist WHERE korisnik_id = ? AND igra_id = ?');
    $st->execute([$_SESSION['korisnik_id'], $id]);
    $uListi = (bool)$st->fetchColumn();
}

$slika = naslovna($igra['naslovna_slika']);
$naslov_stranice = $igra['naslov'] . ' – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<main class="wrap detalj">
    <div class="detalj-slika">
        <?php if ($slika): ?>
            <img src="<?= e($slika) ?>" alt="Naslovnica igre <?= e($igra['naslov']) ?>">
        <?php else: ?>
            <div class="cover-placeholder" aria-hidden="true"><?= e(mb_substr($igra['naslov'], 0, 1)) ?></div>
        <?php endif; ?>
    </div>

    <div>
        <h1><?= e($igra['naslov']) ?></h1>
        <p class="muted">Godina izlaska: <?= e((string)$igra['godina_izlaska']) ?></p>
        <p><?= $igra['opis'] ? nl2br(e($igra['opis'])) : 'Opis uskoro.' ?></p>

        <div class="tabela-wrap">
            <table class="tabela">
                <thead>
                    <tr><th>Konzola</th><th>Cijena</th><th>Zaliha</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($platforme as $p): ?>
                    <tr>
                        <td><?= e($p['naziv']) ?></td>
                        <td>
                            <?php if ($p['stara_cijena'] && $p['stara_cijena'] > $p['cijena']): ?>
                                <s class="muted"><?= e(cijena((float)$p['stara_cijena'])) ?></s>
                            <?php endif; ?>
                            <?= e(cijena((float)$p['cijena'])) ?>
                        </td>
                        <td><?= (int)$p['na_zalihi'] > 0 ? (int)$p['na_zalihi'] . ' kom' : 'Rasprodano' ?></td>
                        <td>
                            <?php if (!$ulogovan): ?>
                                <a href="login.php">Prijavi se za kupovinu</a>
                            <?php elseif ((int)$p['na_zalihi'] > 0): ?>
                                <form method="post" action="kupi.php">
                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="igra_id" value="<?= (int)$igra['id'] ?>">
                                    <input type="hidden" name="platforma_id" value="<?= (int)$p['platforma_id'] ?>">
                                    <button class="btn btn-sm" type="submit">Zatraži kupovinu</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($ulogovan): ?>
            <form method="post" action="wishlist_toggle.php">
                <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                <input type="hidden" name="igra_id" value="<?= (int)$igra['id'] ?>">
                <input type="hidden" name="nazad" value="igra">
                <button class="btn btn-sm btn-outline" type="submit">
                    <?= $uListi ? 'Ukloni s wishliste' : 'Dodaj na wishlist' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>