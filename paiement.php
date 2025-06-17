<?php
session_start();
require_once __DIR__ . '/cnx.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Vérifier si l'ID de réservation est fourni
$reservation_id = filter_input(INPUT_GET, 'reservation_id', FILTER_VALIDATE_INT);
if (!$reservation_id) {
    header('Location: mon-compte.php');
    exit();
}

try {
    // Récupérer les informations de la réservation
    $stmt = $conn->prepare("
        SELECT r.*, l.marque, l.modele, l.annee, 
               (SELECT image_url FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image
        FROM reservations r
        JOIN louer l ON r.vehicule_id = l.id
        WHERE r.id = :reservation_id AND r.user_id = :user_id
    ");
    $stmt->bindParam(':reservation_id', $reservation_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        header('Location: mon-compte.php');
        exit();
    }

    // Configuration KkiaPay
    $public_key = 'ton api key ici';
    $success_url = 'http://' . $_SERVER['HTTP_HOST'] . '/autoworld/paiement-success.php';
    $cancel_url = 'http://' . $_SERVER['HTTP_HOST'] . '/autoworld/paiement-cancel.php';
    
    // Données personnalisées pour KkiaPay
    $custom_data = [
        'reservation_id' => $reservation_id,
        'user_id' => $_SESSION['user_id'],
        'vehicle' => $reservation['marque'] . ' ' . $reservation['modele']
    ];

} catch(PDOException $e) {
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: mon-compte.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-section {
            padding: 40px 0;
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .vehicle-details {
            background: #e8eaf6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-pay {
            padding: 15px 30px;
            font-size: 18px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>

    <div class="payment-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="payment-card">
                        <h2 class="text-center mb-4">Paiement de la réservation</h2>
                        
                        <div class="vehicle-details">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php if ($reservation['cover_image']): ?>
                                        <img src="<?php echo htmlspecialchars($reservation['cover_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>"
                                             class="img-fluid rounded">
                                    <?php else: ?>
                                        <div class="text-center py-4 bg-light rounded">
                                            <i class="fas fa-car fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <h4><?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele'] . ' (' . $reservation['annee'] . ')'); ?></h4>
                                    <p><strong>Période de location :</strong><br>
                                    Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?><br>
                                    Au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></p>
                                    <h5>Montant total : <?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA</h5>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary btn-pay" id="kkiapay-button">
                                <i class="fas fa-credit-card me-2"></i>Procéder au paiement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.kkiapay.me/k.js"></script>

    <script>
    window.onload = () => {
        document
            .getElementById('kkiapay-button')
            .addEventListener('click', () => {
                const amount = <?php echo (int) $reservation['prix_total']; ?>;
                const apiKey = <?php echo json_encode($public_key); ?>;
                const callback = <?php echo json_encode($success_url); ?>;
                const data = <?php echo json_encode($custom_data); ?>;

                openKkiapayWidget({
                    amount: amount,
                    key: apiKey,
                    sandbox: true,
                    callback: callback,
                    data: data,
                    position: 'center',
                    theme: 'blue'
                });
            });

        addKkiapayListener('success', resp => {
            console.log('Transaction OK :', resp.transactionId);
            fetch('store-transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'transaction_id=' + resp.transactionId + 
                      '&reservation_id=<?php echo $reservation_id; ?>&' +
                      'montant=<?php echo urlencode($reservation['prix_total']); ?>'
            })
            .then(response => response.text())
            .then(data => {
                console.log('Données de transaction stockées:', data);
                window.location.href = '<?php echo $success_url; ?>';
            })
            .catch(error => console.error('Erreur:', error));
        });
    };
    </script>
</body>
</html>
