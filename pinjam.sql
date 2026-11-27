-- Hapus database lama jika perlu
DROP DATABASE IF EXISTS peminjaman_alat;

-- Buat database baru
CREATE DATABASE peminjaman_alat;

-- Gunakan database yang baru dibuat
USE peminjaman_alat;

-- Tabel pengguna (users)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) UNIQUE NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Divisi Alat', 'Bendahara', 'Peminjam Alat') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, password, role) VALUES
('divisialat', 'divisialat@dtools.lab', 'divisialat123', 'Divisi Alat'),
('bendahara', 'bendahara@dtools.lab', 'bendahara123', 'Bendahara');

-- Tabel token reset password (disimpan dalam bentuk hash SHA-256)
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_user_created (user_id, created_at)
);

-- Tabel barang/item
CREATE TABLE item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel peminjaman
CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_proof BLOB,
    status ENUM('Pending','Dikonfirmasi','Menunggu Konfirmasi Pembayaran','Siap Diambil','Sedang Dipinjam','Menunggu Konfirmasi Pengembalian','Pengembalian Dikonfirmasi','Selesai') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel detail peminjaman
CREATE TABLE detail_peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_at DATE,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES item(id) ON DELETE CASCADE
);
