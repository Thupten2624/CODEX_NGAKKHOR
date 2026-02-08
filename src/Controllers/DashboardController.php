<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\OrganizationRepository;
use App\Repositories\PathRepository;
use App\Repositories\PracticeLogRepository;
use App\Repositories\UserRepository;

final class DashboardController
{
    private PathRepository $paths;
    private PracticeLogRepository $logs;
    private UserRepository $users;
    private OrganizationRepository $organizations;

    public function __construct()
    {
        $this->paths = new PathRepository();
        $this->logs = new PracticeLogRepository();
        $this->users = new UserRepository();
        $this->organizations = new OrganizationRepository();
    }

    public function home(): void
    {
        View::render('home', [
            'pageTitle' => 'Registro del camino Vajrayana',
        ]);
    }

    public function dashboard(): void
    {
        Auth::requireAuth();

        $user = Auth::user();
        if ($user === null) {
            redirect('login');
        }

        if ($user['role'] === 'practitioner') {
            $stages = $this->paths->all();
            $logs = $this->logs->getByUser((int) $user['id'], 30);
            $feedback = $this->logs->getFeedbackForPractitioner((int) $user['id'], 20);
            $stats = $this->logs->getCompletionStatsByUser((int) $user['id']);
            $teachers = $this->users->getTeachersForPractitioner((int) $user['id']);

            View::render('dashboard/practitioner', [
                'pageTitle' => 'Panel de practicante',
                'user' => $user,
                'stages' => $stages,
                'logs' => $logs,
                'feedback' => $feedback,
                'stats' => $stats,
                'teachers' => $teachers,
            ]);
            return;
        }

        if ($user['role'] === 'teacher') {
            $organizationId = $user['organization_id'] !== null ? (int) $user['organization_id'] : null;
            $assignedPractitioners = $this->users->getAssignedPractitioners((int) $user['id']);
            $availablePractitioners = $this->users->getAvailablePractitionersForTeacher((int) $user['id'], $organizationId);
            $recentLogs = $this->logs->getRecentForTeacher((int) $user['id'], 40);
            $stages = $this->paths->all();

            View::render('dashboard/teacher', [
                'pageTitle' => 'Panel de maestro/a',
                'user' => $user,
                'assignedPractitioners' => $assignedPractitioners,
                'availablePractitioners' => $availablePractitioners,
                'recentLogs' => $recentLogs,
                'stages' => $stages,
            ]);
            return;
        }

        if ($user['role'] === 'organization_admin') {
            $organizationId = $user['organization_id'] !== null ? (int) $user['organization_id'] : null;
            if ($organizationId === null) {
                flash('error', 'Tu cuenta no tiene organización vinculada.');
                redirect('home');
            }

            $organization = $this->organizations->findById($organizationId);
            $members = $this->organizations->getMembers($organizationId);
            $roleCounts = $this->organizations->getRoleCounts($organizationId);
            $stageSummary = $this->logs->getOrganizationStageSummary($organizationId);

            View::render('dashboard/organization', [
                'pageTitle' => 'Panel de organización',
                'user' => $user,
                'organization' => $organization,
                'members' => $members,
                'roleCounts' => $roleCounts,
                'stageSummary' => $stageSummary,
            ]);
            return;
        }

        flash('error', 'Rol no reconocido.');
        redirect('home');
    }
}
