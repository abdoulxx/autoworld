<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['user_id'])) {
    try {
        // Supprimer d'abord les réservations de l'utilisateur
        $stmt = $conn->prepare("DELETE FROM reservations WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Puis supprimer l'utilisateur
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        $_SESSION['success'] = "L'utilisateur et ses réservations ont été supprimés avec succès.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

header('Location: users.php');
exit();
?>
