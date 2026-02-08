<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\UserRepository;

final class OrganizationController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function createMember(): void
    {
        Auth::requireRole('organization_admin');
        require_csrf_or_abort();

        $orgAdmin = Auth::user();
        if ($orgAdmin === null || $orgAdmin['organization_id'] === null) {
            flash('error', 'Tu cuenta no está asociada a una organización.');
            redirect('dashboard');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = str_lower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'practitioner');

        $validRoles = ['practitioner', 'teacher'];
        $errors = [];

        if ($name === '' || str_length($name) < 3) {
            $errors[] = 'Nombre inválido.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo inválido.';
        } elseif ($this->users->emailExists($email)) {
            $errors[] = 'Este correo ya está registrado.';
        }
        if ($password === '' || str_length($password) < 8) {
            $errors[] = 'La contraseña debe tener mínimo 8 caracteres.';
        }
        if (!in_array($role, $validRoles, true)) {
            $errors[] = 'Rol inválido para nuevo miembro.';
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                flash('error', $error);
            }
            redirect('dashboard');
        }

        $this->users->create([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'organization_id' => (int) $orgAdmin['organization_id'],
        ]);

        flash('success', 'Miembro creado correctamente.');
        redirect('dashboard');
    }
}
