<?php
session_start();
require_once __DIR__ . '/cnx.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupérer l'ID du véhicule
$vehicule_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$vehicule_id) {
    header('Location: louer.php');
    exit();
}

// Récupérer les informations du véhicule
try {
    $stmt = $conn->prepare("SELECT l.*, 
        (SELECT image_url FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image 
        FROM louer l WHERE l.id = :id");
    $stmt->bindParam(':id', $vehicule_id);
    $stmt->execute();
    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        header('Location: louer.php');
        exit();
    }
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

// Traitement du formulaire de réservation
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_debut = htmlspecialchars($_POST['date_debut'] ?? '', ENT_QUOTES, 'UTF-8');
    $date_fin = htmlspecialchars($_POST['date_fin'] ?? '', ENT_QUOTES, 'UTF-8');
    $mode_paiement = htmlspecialchars($_POST['mode_paiement'] ?? '', ENT_QUOTES, 'UTF-8');

    // Calculer le nombre de jours
    $debut = new DateTime($date_debut);
    $fin = new DateTime($date_fin);
    $interval = $debut->diff($fin);
    $nb_jours = $interval->days + 1;

    // Calculer le prix total
    $prix_total = $vehicule['prix_jour'] * $nb_jours;

    try {
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, vehicule_id, date_debut, date_fin, prix_total, mode_paiement, statut) VALUES (:user_id, :vehicule_id, :date_debut, :date_fin, :prix_total, :mode_paiement, :statut)");
        
        $statut = ($mode_paiement === 'cash') ? 'en_attente' : 'payé';
        
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':vehicule_id', $vehicule_id);
        $stmt->bindParam(':date_debut', $date_debut);
        $stmt->bindParam(':date_fin', $date_fin);
        $stmt->bindParam(':prix_total', $prix_total);
        $stmt->bindParam(':mode_paiement', $mode_paiement);
        $stmt->bindParam(':statut', $statut);
        
        $stmt->execute();
        
        // Redirection vers la page mon-compte avec un message de succès
        $_SESSION['success_message'] = "Réservation effectuée avec succès!" . 
            ($mode_paiement === 'cash' ? " Un agent vous contactera pour la livraison." : "");
        
        // Sauvegarder l'ID de la dernière réservation
        $_SESSION['last_reservation_id'] = $conn->lastInsertId();

        if ($mode_paiement === 'online') {
            header('Location: paiement.php?reservation_id=' . $_SESSION['last_reservation_id']);
        } else {
            header('Location: reservation-confirmee.php');
        }
        exit();
    } catch(PDOException $e) {
        $error = "Erreur lors de la réservation : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réserver - <?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reservation-section {
            padding: 40px 0;
            background-color: #f8f9fa;
        }
        .vehicle-preview {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .vehicle-preview img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .reservation-form {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .price-summary {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .payment-options {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .payment-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            border-color: #1a237e;
        }
        .payment-option.selected {
            border-color: #1a237e;
            background-color: #e8eaf6;
        }
        .payment-option i {
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="reservation-section">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Aperçu du véhicule -->
                <div class="col-md-5">
                    <div class="vehicle-preview">
                        <?php if ($vehicule['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars($vehicule['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>" 
                                 class="img-fluid rounded">
                        <?php else: ?>
                            <div class="text-center py-5 bg-light rounded">
                                <i class="fas fa-car fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['annee'] . ')'); ?></h3>
                        <div class="car-features mt-3">
                            <div class="car-feature">
                                <i class="fas fa-calendar"></i>
                                <span>Année <?php echo htmlspecialchars($vehicule['annee']); ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($vehicule['categorie']); ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-check-circle"></i>
                                <span><?php echo $vehicule['disponibilite'] ? 'Disponible' : 'Indisponible'; ?></span>
                            </div>
                        </div>
                        <div class="price-tag mt-3">
                            <?php echo number_format($vehicule['prix_jour'], 0, ',', ' '); ?> FCFA/jour
                        </div>
                    </div>
                </div>

                <!-- Formulaire de réservation -->
                <div class="col-md-7">
                    <div class="reservation-form">
                        <h4 class="mb-4">Détails de la réservation</h4>
                        <form method="post" id="reservationForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date de début</label>
                                    <input type="text" class="form-control datepicker" id="date_debut" name="date_debut" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin</label>
                                    <input type="text" class="form-control datepicker" id="date_fin" name="date_fin" required>
                                </div>
                            </div>

                            <div class="price-summary">
                                <h5>Récapitulatif</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Prix par jour:</span>
                                    <span><?php echo number_format($vehicule['prix_jour'], 0, ',', ' '); ?> FCFA</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Nombre de jours:</span>
                                    <span id="nbJours">-</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Prix total:</span>
                                    <span id="prixTotal">-</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h5>Mode de paiement</h5>
                                <div class="payment-options">
                                    <div class="payment-option" data-payment="cash">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <div>Paiement à la livraison</div>
                                    </div>
                                    <div class="payment-option" data-payment="online">
                                        <i class="fas fa-credit-card"></i>
                                        <div>Paiement en ligne</div>
                                    </div>
                                </div>
                                <input type="hidden" name="mode_paiement" id="mode_paiement" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-4">Confirmer la réservation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialisation du sélecteur de dates
        const today = new Date();
        const dateConfig = {
            minDate: "today",
            dateFormat: "Y-m-d",
            locale: "fr"
        };
        
        flatpickr("#date_debut", dateConfig);
        flatpickr("#date_fin", dateConfig);

        // Calcul du prix total
        function updatePrix() {
            const debut = new Date(document.getElementById('date_debut').value);
            const fin = new Date(document.getElementById('date_fin').value);
            
            if (debut && fin) {
                const diffTime = Math.abs(fin - debut);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                const prixJour = <?php echo $vehicule['prix_jour']; ?>;
                const prixTotal = diffDays * prixJour;
                
                document.getElementById('nbJours').textContent = diffDays + ' jour' + (diffDays > 1 ? 's' : '');
                document.getElementById('prixTotal').textContent = new Intl.NumberFormat('fr-FR').format(prixTotal) + ' FCFA';
            }
        }

        document.getElementById('date_debut').addEventListener('change', updatePrix);
        document.getElementById('date_fin').addEventListener('change', updatePrix);

        // Gestion des options de paiement
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('mode_paiement').value = this.dataset.payment;
            });
        });

        // Validation du formulaire
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            if (!document.getElementById('mode_paiement').value) {
                e.preventDefault();
                alert('Veuillez sélectionner un mode de paiement');
            }
        });
    </script>
</body>
</html>
