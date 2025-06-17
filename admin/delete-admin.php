<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['admin_id'])) {
    // Empêcher l'auto-suppression
    if ($_POST['admin_id'] == $_SESSION['admin_id']) {
        $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
        header('Location: settings.php');
        exit();
    }

    try {
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->execute([$_POST['admin_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "L'administrateur a été supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Administrateur non trouvé.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

header('Location: settings.php');
exit();
?>
