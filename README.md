# 🎒 Lost&Found IT 

Lost&Found IT adalah aplikasi web untuk pelaporan dan klaim barang hilang atau ditemukan di lingkungan IT. Aplikasi ini dirancang untuk memastikan proses pelaporan dan klaim dilakukan secara aman, transparan, dan terkonsolidasi, dengan memanfaatkan stored procedure, trigger, transaction, dan stored function di tingkat database. Sistem ini juga memperhatikan validasi peran dan status barang secara otomatis demi menjaga integritas data.

## 📋 Fitur Utama

- 🔐 Sistem Autentikasi (Login/Register)
- 📝 Pelaporan Barang Hilang
- 🔍 Pelaporan Barang Ditemukan
- ✅ Sistem Klaim Barang
- 👥 Manajemen Pengguna
- 📊 Dashboard Admin
- 📸 Upload Foto Barang
- 📱 Responsive Design

## 🛠️ Teknologi yang Digunakan

- PHP 8.0
- MySQL/MariaDB
- HTML5, CSS3, JavaScript
- Bootstrap 5
- PDO Database Connection

## 📁 Struktur Proyek

```
LostFoundIt/
├── database/
│   └── lostfoundit.sql
├── includes/
│   ├── config.php
│   ├── functions.php
│   └── header.php
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── report_lost.php
│   ├── report_found.php
│   ├── claim.php
│   ├── item_detail.php
│   ├── laporan.php
│   ├── get_image.php
│   ├── backup_db.php
│   └── logout.php
└── uploads/
    └── items/
```

## 📌 Detail Konsep

### 🧠 Stored Procedure

Stored procedure digunakan untuk mengelola proses klaim barang secara aman dan terstruktur. Dengan menyimpan prosedur ini di sisi database, sistem menjamin konsistensi eksekusi terlepas dari bagaimana aplikasi frontend atau backend memanggilnya.

#### `sp_claimItem(p_user_id, p_item_id)`

```php
$stmt = $pdo->prepare("CALL sp_claimItem(?, ?)");
$stmt->execute([$user_id, $item_id]);
```

Tugas prosedur:
* Memastikan user bukan admin
* Memvalidasi status barang (harus 'available')
* Memastikan user belum pernah mengklaim barang yang sama
* Menyimpan klaim baru dengan status `pending`
* Mengubah status barang menjadi `claimed`
* Menyimpan log aktivitas

### 🚨 Trigger

Trigger `trg_after_claim` dijalankan secara otomatis **setelah klaim disimpan**. Fungsinya mirip sistem otomatisasi yang menjaga agar status barang selalu sinkron dengan aksi pengguna.

#### `trg_after_claim`

```sql
CREATE TRIGGER trg_after_claim 
AFTER INSERT ON claims 
FOR EACH ROW 
BEGIN
    UPDATE items 
    SET status = 'claimed' 
    WHERE item_id = NEW.item_id;
END
```

### 🔄 Transaction

Transaksi digunakan untuk menjamin integritas penuh ketika pengguna melakukan klaim. Proses klaim bukan hanya satu langkah, melainkan gabungan dari berbagai validasi dan penyimpanan data.

#### Implementasi di `claim.php`

```php
try {
    $check = $pdo->prepare("SELECT fn_isItemClaimable(?) as is_claimable");
    $check->execute([$item_id]);
    $is_claimable = $check->fetchColumn();

    if (!$is_claimable) {
        throw new Exception("Barang tidak tersedia untuk diklaim.");
    }

    $stmt = $pdo->prepare("CALL sp_claimItem(?, ?)");
    $stmt->execute([$user_id, $item_id]);

    $success = "Klaim berhasil dikirim.";
} catch (Exception $e) {
    $error = $e->getMessage();
}
```

### 📺 Stored Function

Stored function digunakan untuk melakukan validasi kelayakan klaim suatu barang.

#### `fn_isItemClaimable(p_item_id)`

```sql
CREATE FUNCTION fn_isItemClaimable(p_item_id INT)
RETURNS TINYINT(1) DETERMINISTIC
BEGIN
    DECLARE claimable BOOLEAN;
    SELECT status = 'available' INTO claimable
    FROM items
    WHERE item_id = p_item_id;
    RETURN IFNULL(claimable, FALSE);
END
```

### 🔐 Sistem Autentikasi

Sistem menggunakan password hashing dan session handling untuk menjaga keamanan data pengguna.

#### Fitur Autentikasi:
* Validasi user berdasarkan email dan password
* Password disimpan dengan `password_hash()`
* Login aman menggunakan prepared statements
* Session menyimpan identitas user dan role
* Akses dibedakan berdasarkan role (`admin`, `user`)

### 💾 Backup Database

Sistem dilengkapi dengan fitur backup database otomatis yang dapat diakses melalui `backup_db.php`.

```php
$date = date('Y-m-d_H-i-s');
$backupFile = __DIR__ . "/storage/backups/backup_$date.sql";
$command = "mysqldump -u root lostfound_it > $backupFile";
exec($command);
```

## 🚀 Cara Menjalankan Proyek

1. Clone repository ini
2. Import database menggunakan file `database/lostfoundit.sql`
3. Konfigurasi koneksi database di `includes/config.php`
4. Pastikan web server (Apache/Nginx) dan MySQL berjalan
5. Akses aplikasi melalui browser

## 🔒 Keamanan

* Password di-hash menggunakan `password_hash()`
* Menggunakan prepared statements untuk mencegah SQL injection
* Validasi input di sisi server
* Session management yang aman
* Role-based access control

## 📝 Lisensi

Proyek ini dilisensikan di bawah MIT License.