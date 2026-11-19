<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

trebaPrijava();

$stavke = [];
$ukupnoCent = 0;
$uklonjeno = false;

$st = $pdo->prepare('SELECT i.id, i.naslov, i.naslovna_slika, p.naziv AS konzola, ip.cijena, ip.na_zalihi
                     FROM igre_platforme ip
                     JOIN igre i ON i.id = ip.igra_id
                     JOIN platforme p ON p.id = ip.platforma_id
                     WHERE ip.igra_id = ? AND ip.platforma_id = ?');

foreach (($_SESSION['korpa'] ?? []) as $kljuc => $kolicina) {
    [$igra, $platforma] = array_map('intval', explode(':', (string)$kljuc) + [0, 0]);
    $st->execute([$igra, $platforma]);
    $red = $st->fetch();

    // Igra je nestala ili je rasprodana: izbaci je iz korpe
    if (!$red || (int)$red['na_zalihi'] < 1) {
        unset($_SESSION['korpa'][$kljuc]);
        $uklonjeno = true;
        continue;
    }

    // Količina ne može biti veća od zalihe
    $kolicina = min((int)$kolicina, (int)$red['na_zalihi'], 10);
    $_SESSION['korpa'][$kljuc] = $kolicina;

    $cent = (int)round($red['cijena'] * 100);
    $stavke[] = [
        'kljuc' => $kljuc,
        'igra_id' => (int)$red['id'],
        'naslov' => $red['naslov'],
        'slika' => $red['naslovna_slika'],
        'konzola' => $red['konzola'],
        'cijena' => (float)$red['cijena'],
        'kolicina' => $kolicina,
        'max' => min((int)$red['na_zalihi'], 10),
        'zbir' => ($cent * $kolicina) / 100,
    ];
    $ukupnoCent += $cent * $kolicina;
}

if ($uklonjeno) {
    $_SESSION['poruka'] = 'Neke igre više nisu dostupne i uklonjene su iz korpe.';
}

$naslov_stranice = 'Korpa – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Korpa</h1>
        <p><?= count($stavke) ?> <?= count($stavke) === 1 ? 'igra' : 'igara' ?></p>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($stavke): ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead>
                        <tr><th></th><th>Igra</th><th>Konzola</th><th>Cijena</th><th>Količina</th><th>Iznos</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($stavke as $s): ?>
                        <?php $slika = naslovna($s['slika']); ?>
                        <tr>
                            <td class="mala-slika">
                                <?php if ($slika): ?>
                                    <img src="<?= e($slika) ?>" alt="">
                                <?php endif; ?>
                            </td>
                            <td><a href="igra.php?id=<?= $s['igra_id'] ?>"><?= e($s['naslov']) ?></a></td>
                            <td><?= e($s['konzola']) ?></td>
                            <td><?= e(cijena($s['cijena'])) ?></td>
                            <td>
                                <form method="post" action="korpa_izmijeni.php" class="kolicina">
                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="akcija" value="kolicina">
                                    <input type="hidden" name="kljuc" value="<?= e($s['kljuc']) ?>">
                                    <input type="number" name="kolicina" value="<?= $s['kolicina'] ?>" min="1" max="<?= $s['max'] ?>" aria-label="Količina">
                                    <button class="btn btn-sm btn-outline" type="submit">OK</button>
                                </form>
                            </td>
                            <td><?= e(cijena($s['zbir'])) ?></td>
                            <td>
                                <form method="post" action="korpa_izmijeni.php">
                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="akcija" value="ukloni">
                                    <input type="hidden" name="kljuc" value="<?= e($s['kljuc']) ?>">
                                    <button class="btn btn-sm btn-outline" type="submit">Ukloni</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="ukupno">Ukupno: <strong><?= e(cijena($ukupnoCent / 100)) ?></strong></p>

            <div class="akcije">
                <form method="post" action="korpa_posalji.php">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                    <button class="btn" type="submit">Pošalji zahtjev za kupovinu</button>
                </form>
                <form method="post" action="korpa_izmijeni.php">
                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                    <input type="hidden" name="akcija" value="isprazni">
                    <button class="btn btn-outline" type="submit">Isprazni korpu</button>
                </form>
            </div>
        <?php else: ?>
            <p class="prazno">Korpa je prazna. <a href="showroom.php">Pogledaj showroom</a> i dodaj neku igru.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>