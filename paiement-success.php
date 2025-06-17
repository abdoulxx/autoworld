<?php
session_start();
require_once __DIR__ . '/cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer l'ID de la réservation depuis la transaction
try {
    $stmt = $conn->prepare("SELECT reservation_id FROM transactions WHERE id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$_GET['transaction_id'] ?? null, $_SESSION['user_id']]);
    if ($row = $stmt->fetch()) {
        $_SESSION['last_reservation_id'] = $row['reservation_id'];
    }
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération de la réservation : " . $e->getMessage());
}

header('Location: reservation-confirmee.php');
exit();
?>
