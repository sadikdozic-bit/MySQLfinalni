<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$platforme_nav = $pdo->query("SELECT id, naziv FROM platforme ORDER BY id")->fetchAll();
$u_korpi = isset($_SESSION['korpa']) ? array_sum($_SESSION['korpa']) : 0;
$ulogovan = isset($_SESSION['korisnik_id']);
?>
<!DOCTYPE html>
<html lang="bs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslov_stranice ?? 'GamerZone') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=Figtree:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site">
    <div class="wrap nav">
        <a class="logo" href="index.php">GamerZone</a>

        <a class="nav-link" href="showroom.php">Showroom</a>

        <div class="dd">
            <button type="button" class="dd-btn" aria-haspopup="true">Konzole</button>
            <div class="dd-menu">
                <?php foreach ($platforme_nav as $p): ?>
                    <a href="konzola.php?id=<?= (int)$p['id'] ?>"><?= e($p['naziv']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <form class="search" action="pretraga.php" method="get" role="search">
            <input type="search" name="q" placeholder="Pretraži igre" aria-label="Pretraži igre">
            <button type="submit">Traži</button>
        </form>

        <nav class="links" aria-label="Glavni meni">
            <a href="korpa.php">Korpa<?php if ($u_korpi > 0): ?> <b class="count"><?= (int)$u_korpi ?></b><?php endif; ?></a>
            <?php if ($ulogovan): ?>
                <a href="biblioteka.php">Moja biblioteka</a>
                <a href="profil.php"><?= e($_SESSION['ime'] ?? 'Profil') ?></a>
                <?php if (($_SESSION['uloga'] ?? '') === 'admin'): ?>
                    <a href="dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Odjava</a>
            <?php else: ?>
                <a href="login.php">Prijava</a>
                <a class="btn-light" href="signin.php">Registracija</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<?php if (!empty($_SESSION['poruka'])): ?>
    <div class="flash" role="status"><div class="wrap"><?= e($_SESSION['poruka']) ?></div></div>
    <?php unset($_SESSION['poruka']); ?>
<?php endif; ?>
<a href="biblioteka.php">Biblioteka</a>
<a href="approved.php">Narudžbe</a>