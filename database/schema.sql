-- Database: university_it_library

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','student') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS files (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  level ENUM('HND','Diploma','Bachelor') NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(120) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  description TEXT,
  uploaded_by INT UNSIGNED NOT NULL,
  download_count INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (level),
  INDEX (title)
);

-- Seed a default admin (change password after first login)
INSERT INTO users (name, email, password_hash, role)
VALUES ('Admin', 'admin@example.com', '$2y$10$OaH0x0YQf0N6qk5KCEb.6u2hP8o8k3JcL5b0k6QxqS0x2n2nq6VfW', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Seed example subjects
INSERT IGNORE INTO subjects (name) VALUES
('Programming Fundamentals'),
('Data Structures'),
('Databases'),
('Networking'),
('Operating Systems'),
('Software Engineering');

