<?php
require __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['korisnik_id'])) {
    header('Location: index.php');
    exit;
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

$greske = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $greske[] = 'Forma je istekla. Pokušaj ponovo.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $lozinka = $_POST['lozinka'] ?? '';

        $st = $pdo->prepare('SELECT id, ime, lozinka_hash, uloga FROM korisnici WHERE email = ?');
        $st->execute([$email]);
        $k = $st->fetch();

        if ($k && password_verify($lozinka, $k['lozinka_hash'])) {
            session_regenerate_id(true);
            $_SESSION['korisnik_id'] = (int)$k['id'];
            $_SESSION['ime'] = $k['ime'];
            $_SESSION['uloga'] = $k['uloga'];
            $_SESSION['poruka'] = 'Dobro došao nazad, ' . $k['ime'] . '!';
            header('Location: ' . ($k['uloga'] === 'admin' ? 'dashboard.php' : 'showroom.php'));
            exit;
        }
        $greske[] = 'Pogrešan email ili lozinka.';
    }
}

$naslov_stranice = 'Prijava – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<main class="auth">
    <div class="auth-box">
        <h1>Prijava</h1>

        <?php if ($greske): ?>
            <ul class="greske" role="alert">
                <?php foreach ($greske as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">

            <label for="lozinka">Lozinka</label>
            <input type="password" id="lozinka" name="lozinka" required autocomplete="current-password">

            <button class="btn" type="submit">Prijavi se</button>
        </form>

        <p class="auth-alt">Nemaš račun? <a href="signin.php">Registruj se</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>