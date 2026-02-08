<section class="dashboard-head">
    <h1>Panel de maestro/a</h1>
    <p>Gestiona practicantes asignados, revisa actividad y emite feedback.</p>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Asignar practicante</h2>
        <?php if ($availablePractitioners === []): ?>
            <p>No hay practicantes disponibles para asignar.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url_for('teacher-assign')); ?>" class="stack-form">
                <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

                <label for="practitioner_id">Practicante disponible</label>
                <select id="practitioner_id" name="practitioner_id" required>
                    <?php foreach ($availablePractitioners as $practitioner): ?>
                        <option value="<?= e((string) $practitioner['id']); ?>">
                            <?= e((string) $practitioner['name']); ?> (<?= e((string) $practitioner['email']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button class="btn btn-primary" type="submit">Asignar</button>
            </form>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Enviar feedback</h2>
        <?php if ($assignedPractitioners === []): ?>
            <p>Primero asigna practicantes para poder enviar feedback.</p>
        <?php else: ?>
            <form method="post" action="<?= e(url_for('teacher-feedback')); ?>" class="stack-form">
                <input type="hidden" name="_token" value="<?= e(csrf_token()); ?>">

                <label for="feedback_practitioner_id">Practicante</label>
                <select id="feedback_practitioner_id" name="practitioner_id" required>
                    <?php foreach ($assignedPractitioners as $practitioner): ?>
                        <option value="<?= e((string) $practitioner['id']); ?>">
                            <?= e((string) $practitioner['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="feedback_stage_id">Etapa (opcional)</label>
                <select id="feedback_stage_id" name="stage_id">
                    <option value="">General</option>
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= e((string) $stage['id']); ?>">
                            <?= e((string) $stage['sequence_order']); ?>. <?= e((string) $stage['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="status_suggestion">Estado sugerido</label>
                <select id="status_suggestion" name="status_suggestion" required>
                    <option value="pending">Pendiente</option>
                    <option value="in_progress" selected>En progreso</option>
                    <option value="completed">Completado</option>
                </select>

                <label for="feedback_text">Feedback</label>
                <textarea id="feedback_text" name="feedback_text" rows="4" required placeholder="Observaciones y recomendaciones..."></textarea>

                <button class="btn btn-primary" type="submit">Enviar feedback</button>
            </form>
        <?php endif; ?>
    </article>
</section>

<section class="grid two-col">
    <article class="card">
        <h2>Practicantes asignados</h2>
        <?php if ($assignedPractitioners === []): ?>
            <p>Aún no hay practicantes asignados.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Organización</th>
                        <th>Última práctica</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($assignedPractitioners as $practitioner): ?>
                        <tr>
                            <td><?= e((string) $practitioner['name']); ?></td>
                            <td><?= e((string) $practitioner['email']); ?></td>
                            <td><?= e((string) ($practitioner['organization_name'] ?? 'Sin organización')); ?></td>
                            <td><?= e((string) ($practitioner['last_practice_date'] ?? '-')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>

    <article class="card">
        <h2>Actividad reciente</h2>
        <?php if ($recentLogs === []): ?>
            <p>No hay actividad reciente de tus practicantes.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Practicante</th>
                        <th>Etapa</th>
                        <th>Estado</th>
                        <th>Horas</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?= e((string) $log['date_practiced']); ?></td>
                            <td><?= e((string) $log['practitioner_name']); ?></td>
                            <td><?= e((string) $log['stage_name']); ?></td>
                            <td><?= e((string) $log['status']); ?></td>
                            <td><?= e((string) $log['hours_practiced']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </article>
</section>
