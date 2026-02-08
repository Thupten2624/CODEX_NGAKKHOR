<section class="dashboard-head">
    <h1>Panel de organización</h1>
    <p>
        <?= e((string) ($organization['name'] ?? 'Organización')); ?>
        | Practicantes: <?= e((string) ($roleCounts['practitioner'] ?? 0)); ?>,
        Maestros: <?= e((string) ($roleCounts['teacher'] ?? 0)); ?>,
        Admins: <?= e((string) ($roleCounts['organization_admin'] ?? 0)); ?>
    </p>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Crear miembro</h2>
        <form method="post" action="<?= e(url_for('organization-member-create')); ?>" class="stack-form">
            <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

            <label for="member_name">Nombre</label>
            <input id="member_name" name="name" type="text" required placeholder="Nombre completo">

            <label for="member_email">Correo</label>
            <input id="member_email" name="email" type="email" required placeholder="miembro@email.com">

            <label for="member_role">Rol</label>
            <select id="member_role" name="role" required>
                <option value="practitioner">Practicante</option>
                <option value="teacher">Maestro/a</option>
            </select>

            <label for="member_password">Contraseña temporal</label>
            <input id="member_password" name="password" type="password" required placeholder="Mínimo 8 caracteres">

            <button class="btn btn-primary" type="submit">Crear miembro</button>
        </form>
    </article>

    <article class="card">
        <h2>Progreso por etapa</h2>
        <?php if ($stageSummary === []): ?>
            <p>No hay datos de progreso todavía.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Etapa</th>
                        <th>Practicantes activos</th>
                        <th>Practicantes completados</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($stageSummary as $row): ?>
                        <tr>
                            <td><?= e((string) $row['name']); ?></td>
                            <td><?= e((string) $row['practitioners_active']); ?></td>
                            <td><?= e((string) $row['practitioners_completed']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>

<section class="card">
    <h2>Miembros de la organización</h2>
    <?php if ($members === []): ?>
        <p>No hay miembros registrados.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Alta</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?= e((string) $member['name']); ?></td>
                        <td><?= e((string) $member['email']); ?></td>
                        <td><?= e((string) $member['role']); ?></td>
                        <td><?= e((string) $member['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
