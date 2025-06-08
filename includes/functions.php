<?php
require_once 'db.php';

function getAllItems() {
    global $pdo;
    $stmt = $pdo->query("SELECT i.*, u.name FROM items i JOIN users u ON i.user_id = u.user_id ORDER BY date_reported DESC");
    return $stmt->fetchAll();
}

function reportItem($user_id, $title, $desc, $type, $loc) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO items (user_id, title, description, type, location) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $desc, $type, $loc]);
}

function claimItem($item_id, $claimant_id) {
    global $pdo;
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("CALL sp_claimItem(?, ?)");
        $stmt->execute([$claimant_id, $item_id]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}
?>
