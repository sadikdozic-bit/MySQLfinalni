<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';

trebaAdmina();

$narudzbe = $pdo->query("
    SELECT n.id, n.datum, n.ukupan_iznos, k.ime, k.email,
           GROUP_CONCAT(CONCAT(i.naslov, ' (', p.kratki_naziv, ')') SEPARATOR ', ') AS stavke
    FROM narudzbe n
    JOIN korisnici k ON k.id = n.korisnik_id
    JOIN stavke_narudzbe s ON s.narudzba_id = n.id
    JOIN igre i ON i.id = s.igra_id
    JOIN platforme p ON p.id = s.platforma_id
    WHERE n.status = 'na_cekanju'
    GROUP BY n.id, n.datum, n.ukupan_iznos, k.ime, k.email
    ORDER BY n.datum ASC")->fetchAll();

$naslov_stranice = 'Zahtjevi na čekanju – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div class="wrap">
        <h1>Zahtjevi na čekanju</h1>
        <p><?= count($narudzbe) ?> zahtjeva</p>
    </div>
</section>

<section class="sekcija">
    <div class="wrap">
        <?php if ($narudzbe): ?>
            <div class="tabela-wrap">
                <table class="tabela">
                    <thead>
                        <tr><th>#</th><th>Datum</th><th>Kupac</th><th>Igre</th><th>Iznos</th><th></th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($narudzbe as $n): ?>
                        <tr>
                            <td><?= (int)$n['id'] ?></td>
                            <td><?= e(date('d.m.Y. H:i', strtotime($n['datum']))) ?></td>
                            <td><?= e($n['ime']) ?><br><span class="muted"><?= e($n['email']) ?></span></td>
                            <td><?= e($n['stavke']) ?></td>
                            <td><?= e(cijena((float)$n['ukupan_iznos'])) ?></td>
                            <td class="akcije">
                                <form method="post" action="accept.php">
                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="narudzba_id" value="<?= (int)$n['id'] ?>">
                                    <button class="btn btn-sm" type="submit">Prihvati</button>
                                </form>
                                <form method="post" action="decline.php">
                                    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
                                    <input type="hidden" name="narudzba_id" value="<?= (int)$n['id'] ?>">
                                    <button class="btn btn-sm btn-outline" type="submit">Odbij</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="prazno">Nema zahtjeva na čekanju.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>