<?php
session_start();
require_once __DIR__ . '/cnx.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

try {
    // Récupérer les réservations de l'utilisateur
    $stmt = $conn->prepare("
        SELECT r.*, l.marque, l.modele, l.annee, l.prix_jour,
               (SELECT image_url FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image
        FROM reservations r
        JOIN louer l ON r.vehicule_id = l.id
        WHERE r.user_id = :user_id
        ORDER BY r.date_creation DESC
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les achats de l'utilisateur (à implémenter plus tard)
    $achats = [];

} catch(PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'components/scripts.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .account-section {
            padding: 40px 0;
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
        }
        .account-header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link {
            color: #1a237e;
            border-radius: 10px;
            padding: 10px 20px;
            margin: 5px;
        }
        .nav-pills .nav-link.active {
            background-color: #1a237e;
        }
        .reservation-card {
            background: white;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .reservation-header {
            background: #e8eaf6;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .reservation-body {
            padding: 20px;
        }
        .vehicle-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        .status-en_attente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-payé {
            background-color: #d4edda;
            color: #155724;
        }
        .status-annulé {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="account-section">
        <div class="container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="account-header">
                <h2 class="mb-4">Mes réservations</h2>
            </div>



            <div class="tab-content" id="accountTabContent">
                <div class="tab-pane fade show active" id="locations">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-car fa-3x text-muted mb-3"></i>
                            <h4>Vous n'avez pas encore de location</h4>
                            <p>Découvrez notre sélection de véhicules disponibles à la location.</p>
                            <a href="louer.php" class="btn btn-primary">Voir les véhicules</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach($reservations as $reservation): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="reservation-card">
                                        <div class="reservation-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>
                                                </h5>
                                                <span class="status-badge status-<?php echo $reservation['statut']; ?>">
                                                    <?php 
                                                    $statuts = [
                                                        'en_attente' => 'En attente',
                                                        'payé' => 'Payé',
                                                        'annulé' => 'Annulé'
                                                    ];
                                                    echo $statuts[$reservation['statut']] ?? $reservation['statut'];
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="reservation-body">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <?php if ($reservation['cover_image']): ?>
                                                        <img src="<?php echo htmlspecialchars($reservation['cover_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>"
                                                             class="vehicle-image">
                                                    <?php else: ?>
                                                        <div class="text-center py-4 bg-light rounded">
                                                            <i class="fas fa-car fa-3x text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-7">
                                                    <div class="mb-3">
                                                        <strong>Période de location :</strong><br>
                                                        Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?><br>
                                                        Au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Prix total :</strong><br>
                                                        <?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Mode de paiement :</strong><br>
                                                        <?php echo $reservation['mode_paiement'] === 'cash' ? 'Paiement à la livraison' : 'Paiement en ligne'; ?>
                                                    </div>
                                                    <?php if($reservation['statut'] === 'en_attente'): ?>
                                                        <form method="post" action="annuler-reservation.php" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times"></i> Annuler la réservation
                                                            </button>
                                                        </form>
                                                    <?php elseif($reservation['statut'] === 'payé'): ?>
                                                        <a href="telecharger-recu.php?id=<?php echo $reservation['id']; ?>" class="btn btn-success btn-sm">
                                                            <i class="fas fa-file-pdf"></i> Télécharger le reçu
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="achats">
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>Fonctionnalité à venir</h4>
                        <p>La section des achats sera bientôt disponible.</p>
                        <a href="acheter.php" class="btn btn-primary">Voir les véhicules à vendre</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
