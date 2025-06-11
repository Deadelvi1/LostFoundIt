-- Update tabel items untuk menambah kolom type jika belum ada
SET @dbname = 'lostfoundit';
SET @tablename = 'items';
SET @columnname = 'type';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE 
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column type already exists in items'",
  "ALTER TABLE items ADD COLUMN type VARCHAR(20) DEFAULT 'lost' AFTER description"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update tabel claims untuk menambah kolom claim_date jika belum ada
SET @tablename = 'claims';
SET @columnname = 'claim_date';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE 
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column claim_date already exists in claims'",
  "ALTER TABLE claims ADD COLUMN claim_date DATETIME DEFAULT CURRENT_TIMESTAMP AFTER status"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Buat tabel activity_logs jika belum ada
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Hapus stored procedure jika sudah ada
DROP PROCEDURE IF EXISTS sp_claimItem;

-- Buat stored procedure baru
DELIMITER //

CREATE PROCEDURE `sp_claimItem` (IN `p_user_id` INT, IN `p_item_id` INT)  
BEGIN
    DECLARE item_status VARCHAR(20);
    DECLARE item_type VARCHAR(20);
    DECLARE existing_claim INT;
    
    -- Deklarasi handler untuk menangkap error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;  -- Rollback jika terjadi error
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Terjadi kesalahan saat mengklaim barang.';
    END;

    START TRANSACTION;  -- Mulai transaction

    -- Cek dan lock data barang
    SELECT status, type INTO item_status, item_type
    FROM items 
    WHERE item_id = p_item_id 
    FOR UPDATE;  -- Lock row untuk mencegah race condition

    -- Validasi dengan rollback eksplisit
    IF item_status IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang tidak ditemukan.';
    END IF;

    IF item_status != 'available' THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang tidak tersedia untuk diklaim.';
    END IF;

    -- Cek apakah user sudah pernah mengklaim barang ini
    SELECT COUNT(*) INTO existing_claim
    FROM claims 
    WHERE item_id = p_item_id 
    AND claimant_id = p_user_id;

    IF existing_claim > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Anda sudah mengklaim barang ini sebelumnya.';
    END IF;

    -- Proses klaim
    INSERT INTO claims (item_id, claimant_id, status, claim_date) 
    VALUES (p_item_id, p_user_id, 'pending', NOW());

    UPDATE items 
    SET status = 'claimed' 
    WHERE item_id = p_item_id;

    -- Log aktivitas
    INSERT INTO activity_logs (user_id, item_id, activity_type, activity_date)
    VALUES (p_user_id, p_item_id, 'claim_item', NOW());

    COMMIT;  -- Commit jika semua berhasil
END //

DELIMITER ;

-- Hapus function jika sudah ada
DROP FUNCTION IF EXISTS fn_isItemClaimable;

-- Buat function baru
DELIMITER //

CREATE FUNCTION `fn_isItemClaimable` (`p_item_id` INT) 
RETURNS TINYINT(1) DETERMINISTIC
BEGIN
    DECLARE claimable BOOLEAN;
    SELECT status = 'available' INTO claimable
    FROM items
    WHERE item_id = p_item_id;
    RETURN IFNULL(claimable, FALSE);
END //

DELIMITER ;

-- Hapus trigger jika sudah ada
DROP TRIGGER IF EXISTS trg_after_claim;

-- Buat trigger baru
DELIMITER //

CREATE TRIGGER `trg_after_claim` 
AFTER INSERT ON `claims` 
FOR EACH ROW 
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE items 
        SET status = 'claimed' 
        WHERE item_id = NEW.item_id;
    END IF;
END //

DELIMITER ; 