<section class="dashboard-head">
    <h1>Panel de practicante</h1>
    <p>
        Bienvenido/a, <?= e((string) $user['name']); ?>.
        Etapas completadas: <?= e((string) ($stats['completed_stages'] ?? 0)); ?>/<?= e((string) ($stats['total_stages'] ?? 0)); ?>.
        Horas acumuladas: <?= e(number_format((float) ($stats['total_hours'] ?? 0), 1)); ?>.
    </p>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Registrar práctica</h2>
        <form method="post" action="<?= e(url_for('practice-create')); ?>" class="stack-form">
            <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

            <label for="stage_id">Etapa</label>
            <select id="stage_id" name="stage_id" required>
                <?php foreach ($stages as $stage): ?>
                    <option value="<?= e((string) $stage['id']); ?>">
                        <?= e((string) $stage['sequence_order']); ?>. <?= e((string) $stage['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="title">Título</label>
            <input id="title" name="title" type="text" required placeholder="Ej. Sesión matinal de ngöndro">

            <label for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Reflexiones, obstáculos, insights..."></textarea>

            <label for="status">Estado</label>
            <select id="status" name="status" required>
                <option value="pending">Pendiente</option>
                <option value="in_progress" selected>En progreso</option>
                <option value="completed">Completada</option>
            </select>

            <label for="hours_practiced">Horas dedicadas</label>
            <input id="hours_practiced" name="hours_practiced" type="number" min="0" max="24" step="0.25" value="1">

            <label for="date_practiced">Fecha</label>
            <input id="date_practiced" name="date_practiced" type="date" value="<?= e(date('Y-m-d')); ?>" required>

            <label for="teacher_id">Maestro/a (opcional)</label>
            <select id="teacher_id" name="teacher_id">
                <option value="">Sin asignar</option>
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= e((string) $teacher['id']); ?>"><?= e((string) $teacher['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary" type="submit">Guardar práctica</button>
        </form>
    </article>

    <article class="card">
        <h2>Etapas del camino</h2>
        <div class="timeline">
            <?php foreach ($stages as $stage): ?>
                <div class="timeline-item">
                    <h3><?= e((string) $stage['sequence_order']); ?>. <?= e((string) $stage['name']); ?></h3>
                    <p><?= e((string) $stage['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Últimos registros</h2>
        <?php if ($logs === []): ?>
            <p>No hay registros aún.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Etapa</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Horas</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e((string) $log['date_practiced']); ?></td>
                            <td><?= e((string) $log['stage_name']); ?></td>
                            <td><?= e((string) $log['title']); ?></td>
                            <td><?= e((string) $log['status']); ?></td>
                            <td><?= e((string) $log['hours_practiced']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Feedback de maestros</h2>
        <?php if ($feedback === []): ?>
            <p>Todavía no has recibido feedback.</p>
        <?php else: ?>
            <ul class="feedback-list">
                <?php foreach ($feedback as $item): ?>
                    <li>
                        <strong><?= e((string) $item['teacher_name']); ?></strong>
                        <span><?= e((string) $item['created_at']); ?></span>
                        <p><?= e((string) $item['feedback_text']); ?></p>
                        <small>
                            Etapa: <?= e((string) ($item['stage_name'] ?? 'General')); ?> |
                            Sugerencia: <?= e((string) $item['status_suggestion']); ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </article>
</section>
