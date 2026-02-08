<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\PathRepository;
use App\Repositories\PracticeLogRepository;
use App\Repositories\UserRepository;

final class PracticeController
{
    private PracticeLogRepository $logs;
    private PathRepository $paths;
    private UserRepository $users;

    public function __construct()
    {
        $this->logs = new PracticeLogRepository();
        $this->paths = new PathRepository();
        $this->users = new UserRepository();
    }

    public function create(): void
    {
        Auth::requireRole('practitioner');
        require_csrf_or_abort();

        $user = Auth::user();
        if ($user === null) {
            redirect('login');
        }

        $stageId = (int) ($_POST['stage_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'in_progress');
        $hours = (float) ($_POST['hours_practiced'] ?? 0);
        $datePracticed = (string) ($_POST['date_practiced'] ?? date('Y-m-d'));
        $teacherId = $_POST['teacher_id'] ?? null;

        $errors = [];
        $validStatus = ['pending', 'in_progress', 'completed'];

        if ($stageId <= 0 || $this->paths->find($stageId) === null) {
            $errors[] = 'Selecciona una etapa válida.';
        }
        if ($title === '' || str_length($title) < 4) {
            $errors[] = 'El título debe tener al menos 4 caracteres.';
        }
        if (!in_array($status, $validStatus, true)) {
            $errors[] = 'Estado inválido.';
        }
        if ($hours < 0 || $hours > 24) {
            $errors[] = 'Las horas deben estar entre 0 y 24.';
        }
        $date = date_create($datePracticed);
        if ($date === false) {
            $errors[] = 'Fecha inválida.';
        }

        if ($teacherId !== null && $teacherId !== '') {
            $teacherId = (int) $teacherId;
            $allowedTeachers = $this->users->getTeachersForPractitioner((int) $user['id']);
            $allowedIds = array_map(static fn (array $teacher): int => (int) $teacher['id'], $allowedTeachers);
            if (!in_array($teacherId, $allowedIds, true)) {
                $errors[] = 'El maestro seleccionado no está asignado a tu perfil.';
            }
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('dashboard');
        }

        $this->logs->create([
            'user_id' => (int) $user['id'],
            'stage_id' => $stageId,
            'teacher_id' => $teacherId,
            'title' => $title,
            'notes' => $notes,
            'status' => $status,
            'hours_practiced' => $hours,
            'date_practiced' => $datePracticed,
        ]);

        flash('success', 'Práctica registrada correctamente.');
        redirect('dashboard');
    }
}
