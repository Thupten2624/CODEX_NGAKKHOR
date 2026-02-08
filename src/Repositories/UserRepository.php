<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT u.*, o.name AS organization_name
                FROM users u
                LEFT JOIN organizations o ON o.id = u.organization_id
                WHERE u.id = :id
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['id' => $id]);
        $result = $statement->fetch();

        return $result ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.*, o.name AS organization_name
                FROM users u
                LEFT JOIN organizations o ON o.id = u.organization_id
                WHERE u.email = :email
                LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->execute(['email' => str_lower(trim($email))]);
        $result = $statement->fetch();

        return $result ?: null;
    }

    public function emailExists(string $email): bool
    {
        $statement = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => str_lower(trim($email))]);

        return (bool) $statement->fetchColumn();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (name, email, password_hash, role, organization_id)
                VALUES (:name, :email, :password_hash, :role, :organization_id)';

        $statement = $this->db->prepare($sql);
        $statement->bindValue('name', trim((string) $data['name']));
        $statement->bindValue('email', str_lower(trim((string) $data['email'])));
        $statement->bindValue('password_hash', (string) $data['password_hash']);
        $statement->bindValue('role', (string) $data['role']);

        $organizationId = $data['organization_id'] ?? null;
        if ($organizationId === null || $organizationId === '') {
            $statement->bindValue('organization_id', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue('organization_id', (int) $organizationId, PDO::PARAM_INT);
        }

        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function getTeachersForPractitioner(int $practitionerId): array
    {
        $sql = 'SELECT t.id, t.name, t.email
                FROM teacher_practitioner_assignments a
                INNER JOIN users t ON t.id = a.teacher_id
                WHERE a.practitioner_id = :practitioner_id
                ORDER BY t.name ASC';
        $statement = $this->db->prepare($sql);
        $statement->execute(['practitioner_id' => $practitionerId]);

        return $statement->fetchAll();
    }

    public function getAssignedPractitioners(int $teacherId): array
    {
        $sql = 'SELECT p.id, p.name, p.email, p.organization_id, o.name AS organization_name,
                       MAX(pl.date_practiced) AS last_practice_date
                FROM teacher_practitioner_assignments a
                INNER JOIN users p ON p.id = a.practitioner_id
                LEFT JOIN organizations o ON o.id = p.organization_id
                LEFT JOIN practice_logs pl ON pl.user_id = p.id
                WHERE a.teacher_id = :teacher_id
                GROUP BY p.id, p.name, p.email, p.organization_id, o.name
                ORDER BY p.name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute(['teacher_id' => $teacherId]);

        return $statement->fetchAll();
    }

    public function getAvailablePractitionersForTeacher(int $teacherId, ?int $organizationId = null): array
    {
        $sql = 'SELECT p.id, p.name, p.email
                FROM users p
                LEFT JOIN teacher_practitioner_assignments a
                    ON a.practitioner_id = p.id AND a.teacher_id = :teacher_id
                WHERE p.role = :role
                  AND a.id IS NULL';

        $params = [
            'teacher_id' => $teacherId,
            'role' => 'practitioner',
        ];

        if ($organizationId !== null) {
            $sql .= ' AND p.organization_id = :organization_id';
            $params['organization_id'] = $organizationId;
        }

        $sql .= ' ORDER BY p.name ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function assignPractitioner(int $teacherId, int $practitionerId): bool
    {
        $sql = 'INSERT IGNORE INTO teacher_practitioner_assignments (teacher_id, practitioner_id)
                VALUES (:teacher_id, :practitioner_id)';

        $statement = $this->db->prepare($sql);
        $statement->execute([
            'teacher_id' => $teacherId,
            'practitioner_id' => $practitionerId,
        ]);

        return $statement->rowCount() > 0;
    }

    public function practitionerExists(int $userId): bool
    {
        $statement = $this->db->prepare('SELECT id FROM users WHERE id = :id AND role = :role LIMIT 1');
        $statement->execute([
            'id' => $userId,
            'role' => 'practitioner',
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function findPractitionerById(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, email, role, organization_id
             FROM users
             WHERE id = :id AND role = :role
             LIMIT 1'
        );
        $statement->execute([
            'id' => $userId,
            'role' => 'practitioner',
        ]);

        $result = $statement->fetch();
        return $result ?: null;
    }

    public function isPractitionerAssignedToTeacher(int $teacherId, int $practitionerId): bool
    {
        $statement = $this->db->prepare(
            'SELECT id
             FROM teacher_practitioner_assignments
             WHERE teacher_id = :teacher_id
               AND practitioner_id = :practitioner_id
             LIMIT 1'
        );

        $statement->execute([
            'teacher_id' => $teacherId,
            'practitioner_id' => $practitionerId,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function getByOrganization(int $organizationId): array
    {
        $sql = 'SELECT id, name, email, role, created_at
                FROM users
                WHERE organization_id = :organization_id
                ORDER BY created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute(['organization_id' => $organizationId]);

        return $statement->fetchAll();
    }
}
