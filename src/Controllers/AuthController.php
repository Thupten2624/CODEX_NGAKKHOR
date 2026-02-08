<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Repositories\OrganizationRepository;
use App\Repositories\UserRepository;

final class AuthController
{
    private UserRepository $users;
    private OrganizationRepository $organizations;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->organizations = new OrganizationRepository();
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $form = consume_form_state('login');

        View::render('auth/login', [
            'pageTitle' => 'Iniciar sesión',
            'formInput' => $form['input'],
            'errors' => $form['errors'],
        ]);
    }

    public function login(): void
    {
        require_csrf_or_abort();

        $email = str_lower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $errors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Introduce un correo válido.';
        }
        if ($password === '') {
            $errors['password'] = 'La contraseña es obligatoria.';
        }

        if ($errors !== []) {
            set_form_state('login', ['email' => $email], $errors);
            redirect('login');
        }

        $user = $this->users->findByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            set_form_state('login', ['email' => $email], [
                'general' => 'Credenciales inválidas.',
            ]);
            redirect('login');
        }

        Auth::login((int) $user['id']);
        flash('success', 'Sesión iniciada correctamente.');
        redirect('dashboard');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $form = consume_form_state('register');

        View::render('auth/register', [
            'pageTitle' => 'Crear cuenta',
            'formInput' => $form['input'],
            'errors' => $form['errors'],
            'organizations' => $this->organizations->all(),
        ]);
    }

    public function register(): void
    {
        require_csrf_or_abort();

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = str_lower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $role = (string) ($_POST['role'] ?? 'practitioner');
        $organizationId = $_POST['organization_id'] ?? null;
        $organizationName = trim((string) ($_POST['organization_name'] ?? ''));

        $validRoles = ['practitioner', 'teacher', 'organization_admin'];
        $errors = [];

        if ($name === '' || str_length($name) < 3) {
            $errors['name'] = 'Escribe un nombre con al menos 3 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Introduce un correo válido.';
        } elseif ($this->users->emailExists($email)) {
            $errors['email'] = 'Este correo ya está registrado.';
        }

        if ($password === '' || str_length($password) < 8) {
            $errors['password'] = 'La contraseña debe tener mínimo 8 caracteres.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }

        if (!in_array($role, $validRoles, true)) {
            $errors['role'] = 'Selecciona un rol válido.';
        }

        if ($role === 'organization_admin' && $organizationName === '') {
            $errors['organization_name'] = 'Indica el nombre de la organización.';
        } elseif ($role === 'organization_admin' && $this->organizations->existsByName($organizationName)) {
            $errors['organization_name'] = 'Ya existe una organización con ese nombre.';
        }

        if (($role === 'practitioner' || $role === 'teacher') && $organizationId !== null && $organizationId !== '') {
            $organization = $this->organizations->findById((int) $organizationId);
            if ($organization === null) {
                $errors['organization_id'] = 'La organización seleccionada no existe.';
            }
        }

        if ($errors !== []) {
            set_form_state('register', [
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'organization_id' => $organizationId,
                'organization_name' => $organizationName,
            ], $errors);

            redirect('register');
        }

        $resolvedOrganizationId = null;
        if ($role === 'organization_admin') {
            $resolvedOrganizationId = $this->organizations->create($organizationName);
        } elseif ($organizationId !== null && $organizationId !== '') {
            $resolvedOrganizationId = (int) $organizationId;
        }

        $userId = $this->users->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'organization_id' => $resolvedOrganizationId,
        ]);

        Auth::login($userId);
        flash('success', 'Cuenta creada correctamente. Bienvenido/a.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        require_csrf_or_abort();
        Auth::logout();
        flash('success', 'Has cerrado sesión.');
        redirect('home');
    }
}
