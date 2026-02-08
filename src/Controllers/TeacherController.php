<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\PathRepository;
use App\Repositories\PracticeLogRepository;
use App\Repositories\UserRepository;

final class TeacherController
{
    private UserRepository $users;
    private PracticeLogRepository $logs;
    private PathRepository $paths;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->logs = new PracticeLogRepository();
        $this->paths = new PathRepository();
    }

    public function assignPractitioner(): void
    {
        Auth::requireRole('teacher');
        require_csrf_or_abort();

        $teacher = Auth::user();
        if ($teacher === null) {
            redirect('login');
        }

        $practitionerId = (int) ($_POST['practitioner_id'] ?? 0);
        $practitioner = $practitionerId > 0 ? $this->users->findPractitionerById($practitionerId) : null;

        if ($practitioner === null) {
            flash('error', 'Selecciona un practicante válido.');
            redirect('dashboard');
        }

        if (
            $teacher['organization_id'] !== null
            && (string) $teacher['organization_id'] !== (string) ($practitioner['organization_id'] ?? '')
        ) {
            flash('error', 'Solo puedes asignar practicantes de tu organización.');
            redirect('dashboard');
        }

        $assigned = $this->users->assignPractitioner((int) $teacher['id'], $practitionerId);

        if ($assigned) {
            flash('success', 'Practicante asignado correctamente.');
        } else {
            flash('error', 'Ese practicante ya estaba asignado a tu perfil.');
        }

        redirect('dashboard');
    }

    public function addFeedback(): void
    {
        Auth::requireRole('teacher');
        require_csrf_or_abort();

        $teacher = Auth::user();
        if ($teacher === null) {
            redirect('login');
        }

        $practitionerId = (int) ($_POST['practitioner_id'] ?? 0);
        $stageIdRaw = $_POST['stage_id'] ?? null;
        $stageId = ($stageIdRaw === null || $stageIdRaw === '') ? null : (int) $stageIdRaw;
        $statusSuggestion = (string) ($_POST['status_suggestion'] ?? 'in_progress');
        $feedbackText = trim((string) ($_POST['feedback_text'] ?? ''));

        $validStatus = ['pending', 'in_progress', 'completed'];
        $errors = [];

        if ($practitionerId <= 0 || !$this->users->practitionerExists($practitionerId)) {
            $errors[] = 'Practicante inválido.';
        }
        if (
            $practitionerId > 0
            && !$this->users->isPractitionerAssignedToTeacher((int) $teacher['id'], $practitionerId)
        ) {
            $errors[] = 'Solo puedes enviar feedback a practicantes asignados.';
        }
        if (!in_array($statusSuggestion, $validStatus, true)) {
            $errors[] = 'Estado sugerido inválido.';
        }
        if ($feedbackText === '' || str_length($feedbackText) < 10) {
            $errors[] = 'El feedback debe tener al menos 10 caracteres.';
        }
        if ($stageId !== null && $this->paths->find($stageId) === null) {
            $errors[] = 'Etapa inválida.';
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('dashboard');
        }

        $this->logs->createFeedback([
            'teacher_id' => (int) $teacher['id'],
            'practitioner_id' => $practitionerId,
            'stage_id' => $stageId,
            'feedback_text' => $feedbackText,
            'status_suggestion' => $statusSuggestion,
        ]);

        flash('success', 'Feedback enviado correctamente.');
        redirect('dashboard');
    }
}
