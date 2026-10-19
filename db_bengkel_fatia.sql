-- =====================================================
-- DATABASE SISTEM BENGKEL FATIA KAMAL
-- 15 SAMPLE DATA PER TABEL
-- =====================================================

DROP DATABASE IF EXISTS `db_bengkel_fatia`;
CREATE DATABASE `db_bengkel_fatia`;
USE `db_bengkel_fatia`;

-- ==================== TABEL ADMIN ====================
CREATE TABLE `admin` (
  `id_admin` VARCHAR(15) NOT NULL PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_telp` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `jenis_kelamin` ENUM('Laki-laki','Perempuan') DEFAULT 'Laki-laki',
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admin` VALUES
('ADM2501001', 'Admin Bengkel Fatia', '081111111111', 'Bangkalan', 'Laki-laki', 'admin', 'PLACEHOLDER');

-- ==================== TABEL OWNER ====================
CREATE TABLE `owner` (
  `id_owner` VARCHAR(15) NOT NULL PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `no_telp` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `jenis_kelamin` ENUM('Laki-laki','Perempuan') DEFAULT 'Laki-laki',
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `owner` VALUES
('OWN2501001', 'Bapak Fatia (Owner)', '083333333333', 'Bangkalan', 'Laki-laki', 'owner', 'PLACEHOLDER');

-- ==================== TABEL PELANGGAN (15 data) ====================
CREATE TABLE `pelanggan` (
  `id_pelanggan` VARCHAR(15) NOT NULL PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `alamat` TEXT NOT NULL,
  `no_telp` VARCHAR(20) NOT NULL,
  `jenis_kelamin` ENUM('Laki-laki','Perempuan') DEFAULT 'Laki-laki'
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pelanggan` VALUES
('CUST2501001', 'Cece Dini',    'Telang, Bangkalan',      '122645364563', 'Perempuan'),
('CUST2501002', 'Tari',         'Bangkalan Kota',         '081234567890', 'Perempuan'),
('CUST2501003', 'Irana',        'Kamal, Bangkalan',       '082345678901', 'Perempuan'),
('CUST2501004', 'Syahdi',       'Socah, Bangkalan',       '083456789012', 'Laki-laki'),
('CUST2501005', 'Sule',         'Arosbaya, Bangkalan',    '084567890123', 'Laki-laki'),
('CUST2501006', 'Samsul',       'Bangkalan Timur',        '085678901234', 'Laki-laki'),
('CUST2501007', 'Joko',         'Kwanyar, Bangkalan',     '086789012345', 'Laki-laki'),
('CUST2501008', 'Tira',         'Burneh, Bangkalan',      '087890123456', 'Perempuan'),
('CUST2501009', 'Budi Santoso', 'Blega, Bangkalan',       '088901234567', 'Laki-laki'),
('CUST2501010', 'Siti Aminah',  'Tanah Merah, Bangkalan', '089012345678', 'Perempuan'),
('CUST2501011', 'Rudi Hartono', 'Geger, Bangkalan',       '081122334455', 'Laki-laki'),
('CUST2501012', 'Dewi Lestari', 'Labang, Bangkalan',      '082233445566', 'Perempuan'),
('CUST2501013', 'Agus Setiawan','Tragah, Bangkalan',      '083344556677', 'Laki-laki'),
('CUST2501014', 'Rina Wati',    'Modung, Bangkalan',      '084455667788', 'Perempuan'),
('CUST2501015', 'Hendra Wijaya','Sepulu, Bangkalan',      '085566778899', 'Laki-laki');

-- ==================== TABEL MEKANIK (15 data) ====================
CREATE TABLE `mekanik` (
  `id_mekanik` VARCHAR(15) NOT NULL PRIMARY KEY,
  `nama_mekanik` VARCHAR(100) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `no_telp` VARCHAR(20) NOT NULL,
  `status_aktif` ENUM('Tersedia','Bertugas') DEFAULT 'Tersedia'
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `mekanik` VALUES
('MKN2501001', 'Jaemin',        'Bangkalan',        '081111111111', 'Tersedia'),
('MKN2501002', 'Jeno',          'Kamal',            '082222222222', 'Bertugas'),
('MKN2501003', 'Haikal',        'Ciwidey, Bandung', '082453383010', 'Tersedia'),
('MKN2501004', 'Shandy',        'Socah',            '084444444444', 'Tersedia'),
('MKN2501005', 'James',         'Bangkalan',        '085555555555', 'Bertugas'),
('MKN2501006', 'Rio',           'Arosbaya',         '086666666666', 'Tersedia'),
('MKN2501007', 'Fairuz',        'Bangkalan',        '087777777777', 'Tersedia'),
('MKN2501008', 'Bayu Pratama',  'Blega',            '088888888888', 'Tersedia'),
('MKN2501009', 'Dimas Aditya',  'Tanah Merah',      '089999999999', 'Bertugas'),
('MKN2501010', 'Fajar Nugroho', 'Geger',            '081010101010', 'Tersedia'),
('MKN2501011', 'Gilang Ramadhan','Labang',          '082020202020', 'Tersedia'),
('MKN2501012', 'Hendra Kusuma', 'Tragah',           '083030303030', 'Bertugas'),
('MKN2501013', 'Irfan Maulana', 'Modung',           '084040404040', 'Tersedia'),
('MKN2501014', 'Joko Susilo',   'Sepulu',           '085050505050', 'Tersedia'),
('MKN2501015', 'Krisna Bayu',   'Klampis',          '086060606060', 'Tersedia');

-- ==================== TABEL KENDARAAN (15 data) ====================
CREATE TABLE `kendaraan` (
  `id_kendaraan` VARCHAR(15) NOT NULL PRIMARY KEY,
  `id_pelanggan` VARCHAR(15) NOT NULL,
  `no_polisi` VARCHAR(15) NOT NULL UNIQUE,
  `jenis_motor` VARCHAR(50) NOT NULL,
  `tahun` YEAR NOT NULL,
  `keluhan` TEXT DEFAULT NULL,
  CONSTRAINT `fk_kendaraan_pelanggan` FOREIGN KEY (`id_pelanggan`) 
    REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kendaraan` VALUES
('MTR2501001', 'CUST2501001', 'W 1234 GF', 'Honda Beat',      2020, 'Mesin sulit dinyalakan'),
('MTR2501002', 'CUST2501002', 'W 2345 GH', 'Yamaha Vario',    2021, 'Oli bocor'),
('MTR2501003', 'CUST2501003', 'W 3456 IJ', 'Honda Supra',     2019, 'Rem blong'),
('MTR2501004', 'CUST2501004', 'W 4567 KL', 'Suzuki CBR',      2022, 'Aki soak'),
('MTR2501005', 'CUST2501005', 'W 5678 MN', 'Yamaha Aerox',    2023, 'Lampu mati'),
('MTR2501006', 'CUST2501006', 'W 6789 OP', 'Honda PCX',       2021, 'Knalpot berisik'),
('MTR2501007', 'CUST2501007', 'W 7890 QR', 'Kawasaki Ninmax', 2022, 'Servis rutin'),
('MTR2501008', 'CUST2501008', 'W 8901 ST', 'Honda Beat',      2020, 'Ganti oli'),
('MTR2501009', 'CUST2501009', 'W 9012 UV', 'Yamaha NMAX',     2023, 'Ganti oli'),
('MTR2501010', 'CUST2501010', 'W 0123 WX', 'Honda Vario',     2022, 'Lampu depan mati'),
('MTR2501011', 'CUST2501011', 'W 1234 XY', 'Suzuki Satria',   2021, 'Rantai kendor'),
('MTR2501012', 'CUST2501012', 'W 2345 YZ', 'Honda Scoopy',    2023, 'Servis rutin'),
('MTR2501013', 'CUST2501013', 'W 3456 ZA', 'Yamaha Mio',      2020, 'Rem depan blong'),
('MTR2501014', 'CUST2501014', 'W 4567 AB', 'Honda Beat',      2021, 'Busi mati'),
('MTR2501015', 'CUST2501015', 'W 5678 BC', 'Kawasaki KLX',    2022, 'Ganti oli');

-- ==================== TABEL SPAREPART (15 data) ====================
CREATE TABLE `sparepart` (
  `id_sparepart` VARCHAR(15) NOT NULL PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `stok` INT(11) NOT NULL,
  `harga_beli` DECIMAL(10,2) NOT NULL,
  `harga_jual` DECIMAL(10,2) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sparepart` VALUES
('SPR2501001', 'Spion',         50, 35000,  50000),
('SPR2501002', 'Velg',          50, 180000, 250000),
('SPR2501003', 'Aki',           50, 220000, 300000),
('SPR2501004', 'Lampu',         50, 25000,  35000),
('SPR2501005', 'Knalpot',       50, 140000, 200000),
('SPR2501006', 'Klakson',       50, 50000,  75000),
('SPR2501007', 'Oli',           50, 35000,  50000),
('SPR2501008', 'Ban',            8, 70000,  100000),
('SPR2501009', 'Rantai',         5, 100000, 150000),
('SPR2501010', 'Busi',          40, 15000,  25000),
('SPR2501011', 'Kampas Rem',    30, 45000,  65000),
('SPR2501012', 'Filter Udara',  25, 40000,  60000),
('SPR2501013', 'Bohlam LED',    35, 20000,  35000),
('SPR2501014', 'Kabel Gas',     20, 30000,  45000),
('SPR2501015', 'Minyak Rem',    45, 25000,  40000);

-- ==================== TABEL TRANSAKSI (15 data) ====================
CREATE TABLE `transaksi` (
  `id_transaksi` VARCHAR(15) NOT NULL PRIMARY KEY,
  `id_admin` VARCHAR(15) NOT NULL,
  `id_kendaraan` VARCHAR(15) NOT NULL,
  `id_mekanik` VARCHAR(15) NOT NULL,
  `tanggal_transaksi` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `catatan_keluhan` TEXT NOT NULL,
  `total_biaya` DECIMAL(10,2) NOT NULL,
  `status` ENUM('Proses','Selesai','Dibayar') DEFAULT 'Proses',
  CONSTRAINT `fk_transaksi_admin` FOREIGN KEY (`id_admin`) 
    REFERENCES `admin` (`id_admin`) ON DELETE CASCADE,
  CONSTRAINT `fk_transaksi_kendaraan` FOREIGN KEY (`id_kendaraan`) 
    REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE CASCADE,
  CONSTRAINT `fk_transaksi_mekanik` FOREIGN KEY (`id_mekanik`) 
    REFERENCES `mekanik` (`id_mekanik`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

INSERT INTO `transaksi` VALUES
('TRX2501001', 'ADM2501001', 'MTR2501001', 'MKN2501001', '2025-01-05 09:00:00', 'Mesin sulit dinyalakan', 150000, 'Dibayar'),
('TRX2501002', 'ADM2501001', 'MTR2501002', 'MKN2501002', '2025-01-06 10:00:00', 'Ganti oli',              75000,  'Dibayar'),
('TRX2501003', 'ADM2501001', 'MTR2501003', 'MKN2501003', '2025-01-07 11:00:00', 'Rem blong',             200000, 'Dibayar'),
('TRX2501004', 'ADM2501001', 'MTR2501004', 'MKN2501001', '2025-01-08 13:00:00', 'Aki soak',              350000, 'Dibayar'),
('TRX2501005', 'ADM2501001', 'MTR2501005', 'MKN2501004', '2025-01-09 14:00:00', 'Lampu mati',             50000, 'Selesai'),
('TRX2501006', 'ADM2501001', 'MTR2501006', 'MKN2501005', '2025-01-10 15:00:00', 'Knalpot berisik',       250000, 'Proses'),
('TRX2501007', 'ADM2501001', 'MTR2501007', 'MKN2501006', '2025-01-11 16:00:00', 'Servis rutin',          100000, 'Dibayar'),
('TRX2501008', 'ADM2501001', 'MTR2501008', 'MKN2501007', '2025-01-12 17:00:00', 'Ganti oli',              65000, 'Dibayar'),
('TRX2501009', 'ADM2501001', 'MTR2501009', 'MKN2501008', '2025-01-13 09:30:00', 'Ganti oli',              85000, 'Dibayar'),
('TRX2501010', 'ADM2501001', 'MTR2501010', 'MKN2501009', '2025-01-14 10:30:00', 'Lampu depan mati',       45000, 'Dibayar'),
('TRX2501011', 'ADM2501001', 'MTR2501011', 'MKN2501010', '2025-01-15 11:30:00', 'Rantai kendor',         120000, 'Dibayar'),
('TRX2501012', 'ADM2501001', 'MTR2501012', 'MKN2501011', '2025-01-16 13:30:00', 'Servis rutin',           95000, 'Selesai'),
('TRX2501013', 'ADM2501001', 'MTR2501013', 'MKN2501012', '2025-01-17 14:30:00', 'Rem depan blong',       180000, 'Dibayar'),
('TRX2501014', 'ADM2501001', 'MTR2501014', 'MKN2501013', '2025-01-18 15:30:00', 'Busi mati',              55000, 'Proses'),
('TRX2501015', 'ADM2501001', 'MTR2501015', 'MKN2501014', '2025-01-19 16:30:00', 'Ganti oli',              70000, 'Dibayar');

-- ==================== TABEL LAPORAN KEUANGAN ====================
CREATE TABLE `laporan_keuangan` (
  `id_laporan` VARCHAR(15) NOT NULL PRIMARY KEY,
  `id_admin` VARCHAR(15) NOT NULL,
  `id_owner` VARCHAR(15) NOT NULL,
  `tanggal_laporan` DATE NOT NULL,
  `uang_masuk` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `uang_keluar` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT `fk_laporan_admin` FOREIGN KEY (`id_admin`) 
    REFERENCES `admin` (`id_admin`) ON DELETE CASCADE,
  CONSTRAINT `fk_laporan_owner` FOREIGN KEY (`id_owner`) 
    REFERENCES `owner` (`id_owner`) ON DELETE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SELESAI
-- =====================================================