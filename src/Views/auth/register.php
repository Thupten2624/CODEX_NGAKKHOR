<section class="card auth-card wide">
    <h1>Crear cuenta</h1>

    <form method="post" action="<?= e(url_for('register')); ?>" class="stack-form">
        <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

        <label for="name">Nombre completo</label>
        <input
            id="name"
            name="name"
            type="text"
            required
            value="<?= e((string) ($formInput['name'] ?? '')); ?>"
            placeholder="Ej. Dorje Lhamo"
        >
        <?php if (!empty($errors['name'])): ?>
            <p class="field-error"><?= e((string) $errors['name']); ?></p>
        <?php endif; ?>

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

        <label for="role">Rol</label>
        <select id="role" name="role" required>
            <?php $selectedRole = (string) ($formInput['role'] ?? 'practitioner'); ?>
            <option value="practitioner" <?= $selectedRole === 'practitioner' ? 'selected' : ''; ?>>Practicante</option>
            <option value="teacher" <?= $selectedRole === 'teacher' ? 'selected' : ''; ?>>Maestro/a</option>
            <option value="organization_admin" <?= $selectedRole === 'organization_admin' ? 'selected' : ''; ?>>Organización</option>
        </select>
        <?php if (!empty($errors['role'])): ?>
            <p class="field-error"><?= e((string) $errors['role']); ?></p>
        <?php endif; ?>

        <div id="organization-select-wrap">
            <label for="organization_id">Organización existente (opcional)</label>
            <select id="organization_id" name="organization_id">
                <option value="">Sin organización</option>
                <?php foreach ($organizations as $organization): ?>
                    <option
                        value="<?= e((string) $organization['id']); ?>"
                        <?= (string) ($formInput['organization_id'] ?? '') === (string) $organization['id'] ? 'selected' : ''; ?>
                    >
                        <?= e((string) $organization['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['organization_id'])): ?>
                <p class="field-error"><?= e((string) $errors['organization_id']); ?></p>
            <?php endif; ?>
        </div>

        <div id="organization-create-wrap">
            <label for="organization_name">Nombre de tu organización</label>
            <input
                id="organization_name"
                name="organization_name"
                type="text"
                value="<?= e((string) ($formInput['organization_name'] ?? '')); ?>"
                placeholder="Ej. Centro Vajrayana Samaya"
            >
            <?php if (!empty($errors['organization_name'])): ?>
                <p class="field-error"><?= e((string) $errors['organization_name']); ?></p>
            <?php endif; ?>
        </div>

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required placeholder="Mínimo 8 caracteres">
        <?php if (!empty($errors['password'])): ?>
            <p class="field-error"><?= e((string) $errors['password']); ?></p>
        <?php endif; ?>

        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="Repite la contraseña">
        <?php if (!empty($errors['password_confirmation'])): ?>
            <p class="field-error"><?= e((string) $errors['password_confirmation']); ?></p>
        <?php endif; ?>

        <button class="btn btn-primary" type="submit">Crear cuenta</button>
    </form>

    <p class="auth-footer">
        ¿Ya tienes cuenta?
        <a href="<?= e(url_for('login')); ?>">Inicia sesión</a>
    </p>
</section>
