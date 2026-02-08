<?php

declare(strict_types=1);

use App\Core\Auth;

$flashMessages = consume_flash();
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? config('app.name', 'Vajrayana Tracker')); ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('assets/css/styles.css')); ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url_for('home')); ?>">Vajrayana Path Tracker</a>

        <nav class="nav">
            <a class="nav-link <?= is_active_route('home') ? 'active' : ''; ?>" href="<?= e(url_for('home')); ?>">Inicio</a>

            <?php if ($user !== null): ?>
                <a class="nav-link <?= is_active_route('dashboard') ? 'active' : ''; ?>" href="<?= e(url_for('dashboard')); ?>">Panel</a>

                <form class="inline-form" method="post" action="<?= e(url_for('logout')); ?>">
                    <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">
                    <button class="btn btn-secondary" type="submit">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <a class="nav-link <?= is_active_route('login') ? 'active' : ''; ?>" href="<?= e(url_for('login')); ?>">Entrar</a>
                <a class="btn btn-primary" href="<?= e(url_for('register')); ?>">Crear cuenta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container main-content">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert-<?= e((string) $flash['type']); ?>">
            <?= e((string) $flash['message']); ?>
        </div>
    <?php endforeach; ?>

    <?= $content; ?>
</main>

<script src="<?= e(asset_url('assets/js/app.js')); ?>"></script>
</body>
</html>
