<?php
session_start();
require_once 'cnx.php';

if (!isset($_GET['id'])) {
    header('Location: acheter.php');
    exit();
}

// Récupérer les détails du véhicule
$sql = "SELECT *, COALESCE(image_ext, 'jpg') as image_ext FROM vendre WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_GET['id']]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    header('Location: acheter.php');
    exit();
}

// Traitement du formulaire de demande d'essai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'essai') {
        $date_essai = $_POST['date_essai'];
        $heure_essai = $_POST['heure_essai'];
        $nom = $_POST['nom'];
        $email = $_POST['email'];
        $telephone = $_POST['telephone'];
        $message = $_POST['message'];

        $sql = "INSERT INTO demandes_essai (vehicule_id, date_essai, heure_essai, nom, email, telephone, message, date_demande) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_GET['id'], $date_essai, $heure_essai, $nom, $email, $telephone, $message]);

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'components/scripts.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?> - AutoWorld</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .vehicle-image {
            height: 400px;
            width: 100%;
            object-fit: cover;
            object-position: center;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .specs-list {
            list-style: none;
            padding: 0;
        }
        .specs-list li {
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }
        .specs-list i {
            width: 24px;
            margin-right: 12px;
            color: #1a237e;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #1a237e;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
        }
        .contact-form {
            background: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php require_once 'components/navbar.php'; ?>

    <div class="container mt-5">
        <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Demande envoyée !</strong> Nous vous contacterons bientôt pour confirmer votre essai.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Image et détails principaux -->
            <div class="col-lg-8">
                <?php
                    $image_path = "images/vehicles/" . $vehicule['id'] . '.' . $vehicule['image_ext'];
                    $default_image = "images/default-car.jpg";
                ?>
                <img src="<?php echo file_exists($image_path) ? $image_path : $default_image; ?>" 
                     class="vehicle-image mb-4" 
                     alt="<?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>">

                <h1 class="mb-4"><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?></h1>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <ul class="specs-list">
                            <li><i class="fas fa-calendar"></i> Année : <?php echo htmlspecialchars($vehicule['annee']); ?></li>
                            <li><i class="fas fa-road"></i> Kilométrage : <?php echo number_format($vehicule['kilometrage'], 0, ',', ' '); ?> km</li>
                            <li><i class="fas fa-gas-pump"></i> Carburant : <?php echo htmlspecialchars($vehicule['carburant']); ?></li>
                            <li><i class="fas fa-cog"></i> Transmission : <?php echo htmlspecialchars($vehicule['transmission']); ?></li>
                            <li><i class="fas fa-palette"></i> Couleur : <?php echo htmlspecialchars($vehicule['couleur']); ?></li>
                            <li><i class="fas fa-users"></i> Places : <?php echo htmlspecialchars($vehicule['places']); ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <div class="price-tag mb-4">
                            À partir de <?php echo number_format($vehicule['prix'], 0, ',', ' '); ?> FCFA
                            <?php if ($vehicule['prix_negociable']): ?>
                                <div class="text-muted fs-6 mt-2">(Prix discutable)</div>
                            <?php endif; ?>
                        </div>
                        <div class="description">
                            <h4>Description</h4>
                            <p><?php echo nl2br(htmlspecialchars($vehicule['description'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire de demande d'essai -->
            <div class="col-lg-4">
                <div class="contact-form">
                    <h3 class="mb-4">Demander un essai</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="essai">
                        
                        <div class="mb-3">
                            <label class="form-label">Date souhaitée</label>
                            <input type="date" name="date_essai" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Heure souhaitée</label>
                            <select name="heure_essai" class="form-select" required>
                                <option value="">Choisir une heure</option>
                                <?php
                                for ($h = 9; $h <= 17; $h++) {
                                    printf(
                                        '<option value="%02d:00">%02d:00</option>',
                                        $h, $h
                                    );
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message (optionnel)</label>
                            <textarea name="message" class="form-control" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-calendar-check me-2"></i>Demander un essai
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Définir la date minimale à aujourd'hui
        const dateInput = document.querySelector('input[type="date"]');
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Initialiser les composants Bootstrap
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            new bootstrap.Alert(alert);
        });
    });
    </script>
</body>
</html>
