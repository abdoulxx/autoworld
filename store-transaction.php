<?php
session_start();
require_once __DIR__ . '/cnx.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Non autorisé');
}

$transaction_id = $_POST['transaction_id'] ?? null;
$reservation_id = $_POST['reservation_id'] ?? null;
$montant = $_POST['montant'] ?? null;

if (!$transaction_id || !$reservation_id || !$montant) {
    http_response_code(400);
    exit('Données manquantes');
}

try {
    // Vérifier que la réservation appartient à l'utilisateur
    $stmt = $conn->prepare("
        SELECT id FROM reservations 
        WHERE id = :reservation_id 
        AND user_id = :user_id
    ");
    $stmt->bindParam(':reservation_id', $reservation_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        http_response_code(403);
        exit('Réservation non trouvée');
    }

    // Mettre à jour le statut de la réservation
    $stmt = $conn->prepare("
        UPDATE reservations 
        SET statut = 'payé',
            transaction_id = :transaction_id
        WHERE id = :reservation_id
    ");
    $stmt->bindParam(':transaction_id', $transaction_id);
    $stmt->bindParam(':reservation_id', $reservation_id);
    $stmt->execute();

    echo 'Success';

} catch(PDOException $e) {
    http_response_code(500);
    exit('Erreur : ' . $e->getMessage());
}
?>
