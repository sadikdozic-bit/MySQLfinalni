<?php
require __DIR__ . '/config/db.php';

$naslov_stranice = 'GamerZone';
require __DIR__ . '/includes/header.php';
?>

<main class="hero">
    <h1 class="wave" aria-label="GamerZone">
        <?php foreach (mb_str_split('GamerZone') as $i => $slovo): ?>
            <span style="--i:<?= $i ?>" aria-hidden="true"><?= e($slovo) ?></span>
        <?php endforeach; ?>
    </h1>

    <a class="btn btn-lg" href="showroom.php">Showroom</a>

    <p class="credit">Created by <strong>Sadik Đozic</strong></p>
</main>

</body>
</html>