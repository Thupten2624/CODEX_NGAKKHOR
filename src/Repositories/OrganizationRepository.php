<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class OrganizationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $statement = $this->db->query('SELECT id, name, description FROM organizations ORDER BY name ASC');
        return $statement->fetchAll();
    }

    public function findById(int $organizationId): ?array
    {
        $statement = $this->db->prepare('SELECT id, name, description FROM organizations WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $organizationId]);
        $result = $statement->fetch();

        return $result ?: null;
    }

    public function create(string $name, ?string $description = null): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO organizations (name, description) VALUES (:name, :description)'
        );

        $statement->bindValue('name', trim($name));

        if ($description === null || trim($description) === '') {
            $statement->bindValue('description', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue('description', trim($description));
        }

        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function existsByName(string $name): bool
    {
        $statement = $this->db->prepare(
            'SELECT id
             FROM organizations
             WHERE LOWER(name) = LOWER(:name)
             LIMIT 1'
        );
        $statement->execute(['name' => trim($name)]);

        return (bool) $statement->fetchColumn();
    }

    public function getMembers(int $organizationId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, email, role, created_at
             FROM users
             WHERE organization_id = :organization_id
             ORDER BY created_at DESC'
        );

        $statement->execute(['organization_id' => $organizationId]);

        return $statement->fetchAll();
    }

    public function getRoleCounts(int $organizationId): array
    {
        $sql = 'SELECT role, COUNT(*) AS total
                FROM users
                WHERE organization_id = :organization_id
                GROUP BY role';

        $statement = $this->db->prepare($sql);
        $statement->execute(['organization_id' => $organizationId]);

        $rows = $statement->fetchAll();
        $counts = [
            'practitioner' => 0,
            'teacher' => 0,
            'organization_admin' => 0,
        ];

        foreach ($rows as $row) {
            $role = (string) $row['role'];
            $counts[$role] = (int) $row['total'];
        }

        return $counts;
    }
}
