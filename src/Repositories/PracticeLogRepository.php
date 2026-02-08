<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PracticeLogRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO practice_logs (
                    user_id,
                    stage_id,
                    teacher_id,
                    title,
                    notes,
                    status,
                    hours_practiced,
                    date_practiced
                ) VALUES (
                    :user_id,
                    :stage_id,
                    :teacher_id,
                    :title,
                    :notes,
                    :status,
                    :hours_practiced,
                    :date_practiced
                )';

        $statement = $this->db->prepare($sql);
        $statement->bindValue('user_id', (int) $data['user_id'], PDO::PARAM_INT);
        $statement->bindValue('stage_id', (int) $data['stage_id'], PDO::PARAM_INT);

        $teacherId = $data['teacher_id'] ?? null;
        if ($teacherId === null || $teacherId === '') {
            $statement->bindValue('teacher_id', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue('teacher_id', (int) $teacherId, PDO::PARAM_INT);
        }

        $statement->bindValue('title', trim((string) $data['title']));
        $statement->bindValue('notes', trim((string) ($data['notes'] ?? '')));
        $statement->bindValue('status', (string) $data['status']);
        $statement->bindValue('hours_practiced', (float) $data['hours_practiced']);
        $statement->bindValue('date_practiced', (string) $data['date_practiced']);

        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function getByUser(int $userId, int $limit = 25): array
    {
        $limit = max(1, min($limit, 200));

        $sql = 'SELECT pl.id, pl.title, pl.notes, pl.status, pl.hours_practiced, pl.date_practiced, pl.created_at,
                       s.name AS stage_name,
                       t.name AS teacher_name
                FROM practice_logs pl
                INNER JOIN vajrayana_stages s ON s.id = pl.stage_id
                LEFT JOIN users t ON t.id = pl.teacher_id
                WHERE pl.user_id = :user_id
                ORDER BY pl.date_practiced DESC, pl.id DESC
                LIMIT ' . $limit;

        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function getCompletionStatsByUser(int $userId): array
    {
        $sql = 'SELECT
                    (SELECT COUNT(*) FROM vajrayana_stages) AS total_stages,
                    COUNT(DISTINCT CASE WHEN pl.status = "completed" THEN pl.stage_id END) AS completed_stages,
                    COALESCE(SUM(pl.hours_practiced), 0) AS total_hours,
                    MAX(pl.date_practiced) AS last_practice_date
                FROM practice_logs pl
                WHERE pl.user_id = :user_id';

        $statement = $this->db->prepare($sql);
        $statement->execute(['user_id' => $userId]);

        $stats = $statement->fetch();

        if (!$stats) {
            return [
                'total_stages' => 0,
                'completed_stages' => 0,
                'total_hours' => 0,
                'last_practice_date' => null,
            ];
        }

        return $stats;
    }

    public function getRecentForTeacher(int $teacherId, int $limit = 30): array
    {
        $limit = max(1, min($limit, 200));

        $sql = 'SELECT pl.id, pl.title, pl.status, pl.hours_practiced, pl.date_practiced,
                       p.name AS practitioner_name,
                       s.name AS stage_name
                FROM practice_logs pl
                INNER JOIN teacher_practitioner_assignments a
                    ON a.practitioner_id = pl.user_id
                INNER JOIN users p ON p.id = pl.user_id
                INNER JOIN vajrayana_stages s ON s.id = pl.stage_id
                WHERE a.teacher_id = :teacher_id
                ORDER BY pl.date_practiced DESC, pl.id DESC
                LIMIT ' . $limit;

        $statement = $this->db->prepare($sql);
        $statement->execute(['teacher_id' => $teacherId]);

        return $statement->fetchAll();
    }

    public function getOrganizationStageSummary(int $organizationId): array
    {
        $sql = 'SELECT s.id, s.name,
                       COUNT(DISTINCT CASE
                           WHEN u.id IS NOT NULL AND pl.status = "completed"
                           THEN pl.user_id
                       END) AS practitioners_completed,
                       COUNT(DISTINCT CASE
                           WHEN u.id IS NOT NULL AND pl.status IN ("in_progress", "completed")
                           THEN pl.user_id
                       END) AS practitioners_active
                FROM vajrayana_stages s
                LEFT JOIN practice_logs pl ON pl.stage_id = s.id
                LEFT JOIN users u ON u.id = pl.user_id AND u.organization_id = :organization_id
                GROUP BY s.id, s.name
                ORDER BY s.id ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute(['organization_id' => $organizationId]);

        return $statement->fetchAll();
    }

    public function createFeedback(array $data): int
    {
        $sql = 'INSERT INTO teacher_feedback (
                    teacher_id,
                    practitioner_id,
                    stage_id,
                    feedback_text,
                    status_suggestion
                ) VALUES (
                    :teacher_id,
                    :practitioner_id,
                    :stage_id,
                    :feedback_text,
                    :status_suggestion
                )';

        $statement = $this->db->prepare($sql);
        $statement->bindValue('teacher_id', (int) $data['teacher_id'], PDO::PARAM_INT);
        $statement->bindValue('practitioner_id', (int) $data['practitioner_id'], PDO::PARAM_INT);

        $stageId = $data['stage_id'] ?? null;
        if ($stageId === null || $stageId === '') {
            $statement->bindValue('stage_id', null, PDO::PARAM_NULL);
        } else {
            $statement->bindValue('stage_id', (int) $stageId, PDO::PARAM_INT);
        }

        $statement->bindValue('feedback_text', trim((string) $data['feedback_text']));
        $statement->bindValue('status_suggestion', (string) $data['status_suggestion']);

        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function getFeedbackForPractitioner(int $practitionerId, int $limit = 20): array
    {
        $limit = max(1, min($limit, 200));

        $sql = 'SELECT tf.id, tf.feedback_text, tf.status_suggestion, tf.created_at,
                       t.name AS teacher_name,
                       s.name AS stage_name
                FROM teacher_feedback tf
                INNER JOIN users t ON t.id = tf.teacher_id
                LEFT JOIN vajrayana_stages s ON s.id = tf.stage_id
                WHERE tf.practitioner_id = :practitioner_id
                ORDER BY tf.id DESC
                LIMIT ' . $limit;

        $statement = $this->db->prepare($sql);
        $statement->execute(['practitioner_id' => $practitionerId]);

        return $statement->fetchAll();
    }
}
