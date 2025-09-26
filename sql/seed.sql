USE university_it_library;

-- Sample files (no actual files, just metadata for initial view)
INSERT INTO files (title, subject, level, year, description, original_name, stored_name, mime_type, file_size, uploaded_by)
VALUES
('Data Structures Exam 2023', 'Data Structures', 'Bachelor', 2023, 'Past exam paper', 'ds-2023.pdf', 'sample-ds-2023.pdf', 'application/pdf', 123456, NULL),
('Networking Quiz 2022', 'Computer Networks', 'Diploma', 2022, 'Quiz paper', 'net-quiz-2022.pdf', 'sample-net-quiz-2022.pdf', 'application/pdf', 98765, NULL)
ON DUPLICATE KEY UPDATE title = title;
