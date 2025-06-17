<?php
session_start();
require_once 'cnx.php';

// Préparation des filtres
$where = ['disponible = true'];
$params = [];

if (isset($_GET['marque']) && !empty($_GET['marque'])) {
    $where[] = 'marque = ?';
    $params[] = $_GET['marque'];
}

if (isset($_GET['prix_max']) && !empty($_GET['prix_max'])) {
    $where[] = 'prix <= ?';
    $params[] = $_GET['prix_max'];
}

if (isset($_GET['annee_min']) && !empty($_GET['annee_min'])) {
    $where[] = 'annee >= ?';
    $params[] = $_GET['annee_min'];
}

if (isset($_GET['km_max']) && !empty($_GET['km_max'])) {
    $where[] = 'kilometrage <= ?';
    $params[] = $_GET['km_max'];
}

// Récupérer les marques uniques pour le filtre
$sql_marques = "SELECT DISTINCT marque FROM vendre WHERE disponible = true ORDER BY marque";
$stmt_marques = $conn->prepare($sql_marques);
$stmt_marques->execute();
$marques = $stmt_marques->fetchAll(PDO::FETCH_COLUMN);

// Construire la requête principale
$sql = "SELECT *, COALESCE(image_ext, 'jpg') as image_ext FROM vendre WHERE " . implode(' AND ', $where) . " ORDER BY date_ajout DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$vehicules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'components/scripts.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acheter un véhicule - AutoWorld</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .vehicle-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        .vehicle-card:hover {
            transform: translateY(-5px);
        }
        .vehicle-img {
            height: 200px;
            object-fit: cover;
            object-position: center;
            border-radius: 10px 10px 0 0;
        }
        .specs-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .specs-list li {
            margin-bottom: 8px;
            color: #666;
        }
        .specs-list i {
            width: 20px;
            color: #1a237e;
        }
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1a237e;
        }
        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php require_once 'components/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-5">Véhicules à Vendre</h1>

        <!-- Filtres -->
        <div class="filters">
            <form class="row g-3" method="GET" action="">
                <div class="col-md-3">
                    <label class="form-label">Marque</label>
                    <select name="marque" class="form-select">
                        <option value="">Toutes les marques</option>
                        <?php foreach($marques as $marque): ?>
                            <option value="<?php echo htmlspecialchars($marque); ?>" 
                                    <?php echo (isset($_GET['marque']) && $_GET['marque'] === $marque) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($marque); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prix max</label>
                    <input type="number" name="prix_max" class="form-control" 
                           placeholder="Prix maximum" 
                           value="<?php echo isset($_GET['prix_max']) ? htmlspecialchars($_GET['prix_max']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Année min</label>
                    <input type="number" name="annee_min" class="form-control" 
                           placeholder="Année minimum" 
                           value="<?php echo isset($_GET['annee_min']) ? htmlspecialchars($_GET['annee_min']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kilométrage max</label>
                    <input type="number" name="km_max" class="form-control" 
                           placeholder="Kilométrage maximum" 
                           value="<?php echo isset($_GET['km_max']) ? htmlspecialchars($_GET['km_max']) : ''; ?>">
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-search me-2"></i>Rechercher
                    </button>
                    <a href="acheter.php" class="btn btn-outline-secondary px-4 ms-2">
                        <i class="fas fa-undo me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des véhicules -->
        <div class="row">
            <?php foreach($vehicules as $vehicule): ?>
            <div class="col-md-4">
                <div class="card vehicle-card">
                    <?php
                        $image_path = "images/vehicles/" . $vehicule['id'] . '.' . $vehicule['image_ext'];
                        $default_image = "images/default-car.jpg";
                    ?>
                    <img src="<?php echo file_exists($image_path) ? $image_path : $default_image; ?>" 
                         class="card-img-top vehicle-img" 
                         alt="<?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?></h5>
                        <ul class="specs-list">
                            <li><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($vehicule['annee']); ?></li>
                            <li><i class="fas fa-road"></i> <?php echo number_format($vehicule['kilometrage'], 0, ',', ' '); ?> km</li>
                            <li><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($vehicule['carburant']); ?></li>
                            <li><i class="fas fa-cog"></i> <?php echo htmlspecialchars($vehicule['transmission']); ?></li>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="price-tag">
                                À partir de <?php echo number_format($vehicule['prix'], 0, ',', ' '); ?> FCFA
                                <?php if ($vehicule['prix_negociable']): ?>
                                    <br><small class="text-muted">(Prix discutable)</small>
                                <?php endif; ?>
                            </span>
                            <a href="details-vente.php?id=<?php echo $vehicule['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i> Détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($vehicules)): ?>
            <div class="col-12 text-center">
                <p class="lead">Aucun véhicule disponible à la vente pour le moment.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once 'components/footer.php'; ?>
</body>
</html>
