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
$ime = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $greske[] = 'Forma je istekla. Pokušaj ponovo.';
    }

    $ime = trim($_POST['ime'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';
    $potvrda = $_POST['potvrda'] ?? '';

    if (mb_strlen($ime) < 2 || mb_strlen($ime) > 60) {
        $greske[] = 'Ime mora imati između 2 i 60 znakova.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
        $greske[] = 'Unesi ispravnu email adresu.';
    }
    if (strlen($lozinka) < 8) {
        $greske[] = 'Lozinka mora imati najmanje 8 znakova.';
    }
    if ($lozinka !== $potvrda) {
        $greske[] = 'Lozinke se ne poklapaju.';
    }

    if (!$greske) {
        try {
            $st = $pdo->prepare('INSERT INTO korisnici (ime, email, lozinka_hash) VALUES (?, ?, ?)');
            $st->execute([$ime, $email, password_hash($lozinka, PASSWORD_DEFAULT)]);

            session_regenerate_id(true);
            $_SESSION['korisnik_id'] = (int)$pdo->lastInsertId();
            $_SESSION['ime'] = $ime;
            $_SESSION['uloga'] = 'kupac';
            $_SESSION['poruka'] = 'Dobro došao, ' . $ime . '!';
            header('Location: showroom.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $greske[] = 'Korisnik s ovom email adresom već postoji.';
            } else {
                $greske[] = 'Greška pri registraciji. Pokušaj ponovo.';
            }
        }
    }
}

$naslov_stranice = 'Registracija – GamerZone';
require __DIR__ . '/includes/header.php';
?>

<main class="auth">
    <div class="auth-box">
        <h1>Registracija</h1>

        <?php if ($greske): ?>
            <ul class="greske" role="alert">
                <?php foreach ($greske as $g): ?><li><?= e($g) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">

            <label for="ime">Ime</label>
            <input type="text" id="ime" name="ime" value="<?= e($ime) ?>" required maxlength="60" autocomplete="name">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required maxlength="120" autocomplete="email">

            <label for="lozinka">Lozinka</label>
            <input type="password" id="lozinka" name="lozinka" required minlength="8" autocomplete="new-password">

            <label for="potvrda">Potvrdi lozinku</label>
            <input type="password" id="potvrda" name="potvrda" required minlength="8" autocomplete="new-password">

            <button class="btn" type="submit">Napravi račun</button>
        </form>

        <p class="auth-alt">Već imaš račun? <a href="login.php">Prijavi se</a></p>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>