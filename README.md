Project: University IT Library - Past Questions Repository

Stack: HTML, CSS, JavaScript, PHP, MySQL

Features
- Admin-only upload/edit/delete of files; students can search and download
- File types: PDF and selected document formats; no images allowed
- Search and filter by program (HND, Diploma, Bachelor) and subject
- Inline CSS/JS for auth pages per requirement; shared styles for the rest

Setup
1) Create a MySQL database and user.
2) Update `config.php` with your DB credentials.
3) Import `init_db.sql`.
4) Ensure `/uploads` is writable by the web server.
5) Run via PHP built-in server or Apache/Nginx with PHP.

Security Notes
- Uses prepared statements and password hashing.
- Validates MIME types and extensions to block images; stores outside web root where possible.

# CODE
For website development 
