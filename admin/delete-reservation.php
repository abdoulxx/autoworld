<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['reservation_id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->execute([$_POST['reservation_id']]);
        
        $_SESSION['success'] = "La réservation a été supprimée avec succès.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

header('Location: reservations.php');
exit();
?>
