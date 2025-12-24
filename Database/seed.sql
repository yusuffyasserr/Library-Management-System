-- Database/seed.sql
USE bookstore_db;

-- Publishers
INSERT INTO publishers (name, phone, address) VALUES
('Pearson',   '01000000001', 'Cairo'),
('MIT Press', '01000000002', 'Alexandria'),
('Penguin',   '01000000003', 'Giza'),
('Oxford',    '01000000004', 'Cairo'),
('Harper',    '01000000005', 'Tanta')
ON DUPLICATE KEY UPDATE name=name;

-- Users (passwords: admin/admin, chaly/1234)
-- NOTE: these are bcrypt hashes. You can regenerate later if needed.
INSERT INTO users (username, password_hash, full_name, email, role) VALUES
('admin', '$2y$10$wqk5c0U6pB3w6cE8dKQqOe0lQ3Qm7m0RZK3mQmM0R2v7qf4b8wW8e', 'Admin', 'admin@demo.com', 'admin'),
('chaly', '$2y$10$TQm6P2m4c2i2lH1F8gK5Eehb3vGk8e6m7KqQqYv3o8xqN0rV7yq1W', 'Chaly', 'chaly@demo.com', 'customer')
ON DUPLICATE KEY UPDATE username=username;

-- Books (map publisher names -> ids)
INSERT INTO books (isbn, title, authors, category, published_year, price, stock, publisher_id) VALUES
('9780131103627', 'C Programming Language', 'Kernighan, Ritchie', 'Science', 1988, 450, 5,
  (SELECT id FROM publishers WHERE name='Pearson')),
('9780262033848', 'Introduction to Algorithms', 'Cormen, Leiserson, Rivest, Stein', 'Science', 2009, 750, 2,
  (SELECT id FROM publishers WHERE name='MIT Press')),
('9780140449136', 'The Odyssey', 'Homer', 'History', 2003, 240, 0,
  (SELECT id FROM publishers WHERE name='Penguin')),
('9780061120084', 'To Kill a Mockingbird', 'Harper Lee', 'Art', 2006, 320, 5,
  (SELECT id FROM publishers WHERE name='Harper')),
('9780141439518', 'Pride and Prejudice', 'Jane Austen', 'Art', 2003, 280, 2,
  (SELECT id FROM publishers WHERE name='Penguin')),
('9780199535569', 'A Brief History of Time', 'Stephen Hawking', 'Geography', 2011, 390, 5,
  (SELECT id FROM publishers WHERE name='Oxford'))
ON DUPLICATE KEY UPDATE isbn=isbn;
