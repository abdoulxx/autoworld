<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Vérifier si les données nécessaires sont présentes
if (!isset($_POST['reservation_id'], $_POST['new_status'])) {
    $_SESSION['error_message'] = "Données manquantes pour la mise à jour.";
    header('Location: reservations.php');
    exit();
}

$reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
$new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

// Valider le nouveau statut
$valid_statuses = ['en_attente', 'payé', 'annulé'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['error_message'] = "Statut invalide.";
    header('Location: reservations.php');
    exit();
}

try {
    // Mettre à jour le statut de la réservation
    $stmt = $conn->prepare("UPDATE reservations SET statut = :statut WHERE id = :id");
    $stmt->bindParam(':statut', $new_status);
    $stmt->bindParam(':id', $reservation_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Le statut de la réservation a été mis à jour avec succès.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour du statut.";
    }

} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
}

header('Location: reservations.php');
exit();
