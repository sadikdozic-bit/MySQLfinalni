<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function trebaPrijava(): void
{
    if (!isset($_SESSION['korisnik_id'])) {
        $_SESSION['poruka'] = 'Prvo se prijavi.';
        header('Location: login.php');
        exit;
    }
}

function trebaAdmina(): void
{
    trebaPrijava();
    if (($_SESSION['uloga'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Ova stranica je dostupna samo adminu.');
    }
}

function csrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function provjeriCsrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Neispravan zahtjev.');
    }
}

function samoPost(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Dozvoljen je samo POST.');
    }
}