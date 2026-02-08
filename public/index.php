<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OrganizationController;
use App\Controllers\PracticeController;
use App\Controllers\TeacherController;

$authController = new AuthController();
$dashboardController = new DashboardController();
$practiceController = new PracticeController();
$teacherController = new TeacherController();
$organizationController = new OrganizationController();

$route = current_route();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

try {
    switch ($route) {
        case 'home':
            $dashboardController->home();
            break;

        case 'login':
            if ($method === 'POST') {
                $authController->login();
            }
            $authController->showLogin();
            break;

        case 'register':
            if ($method === 'POST') {
                $authController->register();
            }
            $authController->showRegister();
            break;

        case 'logout':
            if ($method !== 'POST') {
                http_response_code(405);
                echo 'Método no permitido.';
                break;
            }
            $authController->logout();
            break;

        case 'dashboard':
            $dashboardController->dashboard();
            break;

        case 'practice-create':
            if ($method !== 'POST') {
                http_response_code(405);
                echo 'Método no permitido.';
                break;
            }
            $practiceController->create();
            break;

        case 'teacher-assign':
            if ($method !== 'POST') {
                http_response_code(405);
                echo 'Método no permitido.';
                break;
            }
            $teacherController->assignPractitioner();
            break;

        case 'teacher-feedback':
            if ($method !== 'POST') {
                http_response_code(405);
                echo 'Método no permitido.';
                break;
            }
            $teacherController->addFeedback();
            break;

        case 'organization-member-create':
            if ($method !== 'POST') {
                http_response_code(405);
                echo 'Método no permitido.';
                break;
            }
            $organizationController->createMember();
            break;

        default:
            http_response_code(404);
            echo 'Ruta no encontrada.';
            break;
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'Error interno de la aplicación.';

    if ((bool) config('app.debug', false)) {
        echo '<pre>' . e($exception->getMessage()) . "\n" . e($exception->getTraceAsString()) . '</pre>';
    }
}
