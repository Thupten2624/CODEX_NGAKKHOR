CREATE DATABASE IF NOT EXISTS vajrayana_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vajrayana_tracker;

CREATE TABLE IF NOT EXISTS organizations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('practitioner', 'teacher', 'organization_admin') NOT NULL,
    organization_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_organization
        FOREIGN KEY (organization_id)
        REFERENCES organizations(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_users_role (role),
    INDEX idx_users_organization (organization_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vajrayana_stages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    sequence_order TINYINT UNSIGNED NOT NULL,
    recommended_duration_days SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_stage_sequence (sequence_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS practice_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    stage_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED NULL,
    title VARCHAR(190) NOT NULL,
    notes TEXT NULL,
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'in_progress',
    hours_practiced DECIMAL(5,2) NOT NULL DEFAULT 0,
    date_practiced DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_practice_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_practice_stage
        FOREIGN KEY (stage_id)
        REFERENCES vajrayana_stages(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_practice_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_practice_user_date (user_id, date_practiced),
    INDEX idx_practice_stage (stage_id),
    INDEX idx_practice_teacher (teacher_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_practitioner_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    practitioner_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_assignment_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_assignment_practitioner
        FOREIGN KEY (practitioner_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE KEY uq_teacher_practitioner (teacher_id, practitioner_id),
    INDEX idx_assignment_practitioner (practitioner_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teacher_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    practitioner_id INT UNSIGNED NOT NULL,
    stage_id INT UNSIGNED NULL,
    feedback_text TEXT NOT NULL,
    status_suggestion ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'in_progress',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_teacher
        FOREIGN KEY (teacher_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_feedback_practitioner
        FOREIGN KEY (practitioner_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_feedback_stage
        FOREIGN KEY (stage_id)
        REFERENCES vajrayana_stages(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_feedback_practitioner (practitioner_id),
    INDEX idx_feedback_teacher (teacher_id)
) ENGINE=InnoDB;

INSERT INTO vajrayana_stages (name, description, sequence_order, recommended_duration_days)
VALUES
    ('Refugio y Bodhicitta', 'Toma de refugio, cultivo de motivación altruista y base ética.', 1, 90),
    ('Ngöndro preliminar', 'Prácticas preliminares: postraciones, Vajrasattva, mandala y guru yoga.', 2, 365),
    ('Empoderamiento y samaya', 'Recepción de iniciaciones y establecimiento de compromisos tántricos.', 3, 60),
    ('Sadhana de yidam', 'Práctica estable de deidad con visualización, mantra y dedicación.', 4, 240),
    ('Guru Yoga profundo', 'Integración de la mente del maestro y devoción no dual.', 5, 180),
    ('Seis yogas o prácticas internas', 'Trabajo con canales, vientos y esencias según linaje.', 6, 300),
    ('Mahamudra / Dzogchen', 'Reconocimiento directo de la naturaleza de la mente.', 7, 365),
    ('Integración y servicio', 'Maduración del camino en vida cotidiana, actividad compasiva y transmisión.', 8, 365)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    recommended_duration_days = VALUES(recommended_duration_days);
