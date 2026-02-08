<section class="card auth-card">
    <h1>Iniciar sesión</h1>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= e((string) $errors['general']); ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url_for('login')); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

        <label for="email">Correo</label>
        <input
            id="email"
            name="email"
            type="email"
            required
            value="<?= e((string) ($formInput['email'] ?? '')); ?>"
            placeholder="tu@email.com"
        >
        <?php if (!empty($errors['email'])): ?>
            <p class="field-error"><?= e((string) $errors['email']); ?></p>
        <?php endif; ?>

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required placeholder="********">
        <?php if (!empty($errors['password'])): ?>
            <p class="field-error"><?= e((string) $errors['password']); ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Entrar</button>
    </form>

    <p class="auth-footer">
        ¿No tienes cuenta?
        <a href="<?= e(url_for('register')); ?>">Regístrate aquí</a>
    </p>
</section>
