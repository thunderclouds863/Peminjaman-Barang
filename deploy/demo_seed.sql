-- Akun & data demo (aplikasi ini menyimpan password plaintext, sesuai kode asli)
-- Akun bawaan pinjam.sql: divisialat/divisialat123, bendahara/bendahara123
USE peminjaman_alat;
INSERT INTO users (username, password, role) VALUES ('peminjam1', 'demo123', 'Peminjam Alat');
INSERT INTO item (id, name, description, price, stock) VALUES
(1, 'Pulse Meter', 'Alat Fisiologi - sewa per minggu', 10000, 4),
(2, 'Blood Pressure Monitor', 'Alat Fisiologi - sewa per minggu', 10000, 5),
(3, 'Infrared Ear Thermometer', 'Alat Fisiologi - sewa per minggu', 10000, 6),
(4, 'Hand Dynamometer', 'Alat Fisiologi - sewa per minggu', 10000, 3),
(5, 'Heart Rate Monitor (Polar Vantage V2)', 'Alat Fisiologi - sewa per minggu', 10000, 4),
(6, 'Electroencephalograph (EEG)', 'Alat Fisiologi - sewa per minggu', 15000, 5);
INSERT INTO peminjaman (id, user_id, total_amount, payment_method, status) VALUES
(1, 3, 20000, 'Transfer', 'Selesai'),
(2, 3, 10000, 'Transfer', 'Sedang Dipinjam'),
(3, 3, 10000, 'Cash', 'Pending');
INSERT INTO detail_peminjaman (peminjaman_id, item_id, quantity, price, return_at) VALUES
(1, 1, 2, 10000, CURDATE() - INTERVAL 7 DAY), (2, 2, 1, 10000, CURDATE() + INTERVAL 5 DAY), (3, 3, 1, 10000, CURDATE() + INTERVAL 7 DAY);
