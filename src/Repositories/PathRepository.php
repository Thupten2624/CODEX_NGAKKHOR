<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PathRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function all(): array
    {
        $statement = $this->db->query(
            'SELECT id, name, description, sequence_order, recommended_duration_days
             FROM vajrayana_stages
             ORDER BY sequence_order ASC'
        );

        return $statement->fetchAll();
    }

    public function find(int $stageId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, name, description, sequence_order, recommended_duration_days
             FROM vajrayana_stages
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $stageId]);
        $result = $statement->fetch();

        return $result ?: null;
    }
}
