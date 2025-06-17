<?php
session_start();
require_once 'admin/cnx.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer les détails de la dernière réservation
if (isset($_SESSION['last_reservation_id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT r.*, v.marque, v.modele, 
            DATE_FORMAT(r.date_debut, '%d/%m/%Y') as date_debut_fr,
            DATE_FORMAT(r.date_fin, '%d/%m/%Y') as date_fin_fr,
            (SELECT image_url FROM images WHERE voiture_id = v.id AND is_cover = 1 LIMIT 1) as cover_image
            FROM reservations r 
            JOIN louer v ON r.vehicule_id = v.id 
            WHERE r.id = ? AND r.user_id = ?
        ");
        $stmt->execute([$_SESSION['last_reservation_id'], $_SESSION['user_id']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la récupération des détails de la réservation.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation Confirmée - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php include 'components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Card de confirmation -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- En-tête avec icône -->
                <div class="bg-green-500 p-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                        <i class="fas fa-check text-3xl text-green-500"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Félicitations !</h1>
                    <p class="text-white opacity-90 mt-2">Votre réservation a été confirmée avec succès</p>
                </div>

                <!-- Détails de la réservation -->
                <?php if (isset($reservation)): ?>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-start space-y-4 md:space-y-0 md:space-x-6">
                        <!-- Image du véhicule -->
                        <div class="w-full md:w-1/3">
                            <img src="<?php echo htmlspecialchars($reservation['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>"
                                 class="w-full h-48 object-cover rounded-lg">
                        </div>

                        <!-- Informations de la réservation -->
                        <div class="w-full md:w-2/3 space-y-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>
                            </h2>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Date de début</p>
                                    <p class="font-medium"><?php echo $reservation['date_debut_fr']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date de fin</p>
                                    <p class="font-medium"><?php echo $reservation['date_fin_fr']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Montant total</p>
                                    <p class="font-medium"><?php echo number_format($reservation['prix_total'], 2, ',', ' '); ?> FCFA</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Mode de paiement</p>
                                    <p class="font-medium"><?php echo $reservation['mode_paiement'] === 'en_ligne' ? 'Paiement en ligne' : 'Paiement à la livraison'; ?></p>
                                </div>
                            </div>

                            <?php if ($reservation['mode_paiement'] === 'livraison'): ?>
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mt-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Vous avez choisi le paiement à la livraison. Notre équipe vous contactera prochainement pour organiser la remise du véhicule.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="bg-gray-50 px-6 py-4 flex justify-center space-x-4">
                    <a href="mon-compte.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-900 hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-list-ul mr-2"></i>
                        Voir mes réservations
                    </a>
                    <a href="index.php" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <i class="fas fa-home mr-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>

            <!-- Email de confirmation -->
            <div class="mt-8 text-center text-gray-600">
                <p><i class="fas fa-envelope mr-2"></i> Un email de confirmation a été envoyé à votre adresse email</p>
            </div>
        </div>
    </div>

    <?php include 'components/footer.php'; ?>
</body>
</html>
