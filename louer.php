<?php
require_once __DIR__ . '/components/navbar.php';
require_once __DIR__ . '/cnx.php';

try {
    // Récupérer les véhicules disponibles à la location avec leurs images de couverture
    $sql = "SELECT l.*, 
           (SELECT image_url FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image
           FROM louer l WHERE l.disponibilite = 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les marques uniques
    $sql_marques = "SELECT DISTINCT marque FROM louer";
    $stmt = $conn->prepare($sql_marques);
    $stmt->execute();
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'components/scripts.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Location - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .rent-hero {
            background: linear-gradient(rgba(26, 35, 126, 0.9), rgba(26, 35, 126, 0.7)), url('images/elantra.png');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .car-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            margin-bottom: 30px;
            background: white;
        }
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }
        .car-img-container {
            position: relative;
            padding-top: 60%;
            overflow: hidden;
            background: #f8f9fa;
        }
        .car-img-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .car-card:hover .car-img-container img {
            transform: scale(1.05);
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a237e;
            margin-bottom: 1rem;
        }
        .car-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1rem 0;
            border-top: 1px solid #eee;
            margin-top: 0.5rem;
        }
        .car-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
            font-size: 0.95rem;
        }
        .car-feature i {
            color: #1a237e;
            font-size: 1.1rem;
        }
        .price-tag {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(26, 35, 126, 0.9);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            font-weight: 600;
            backdrop-filter: blur(5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .category-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            color: #1a237e;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .filter-btn {
            border: 1px solid #dee2e6;
            background: white;
            color: #1a237e;
            padding: 8px 20px;
            border-radius: 20px;
            margin: 5px;
            transition: all 0.3s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: #1a237e;
            color: white;
            border-color: #1a237e;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="rent-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Location de Véhicules</h1>
            <p class="lead mb-4">Découvrez notre sélection de véhicules disponibles à la location.<br>Des voitures de qualité pour tous vos besoins.</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Filters -->
        <div class="filters">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Marque</label>
                    <div class="d-flex flex-wrap">
                        <button class="filter-btn active" data-filter="marque-all">Toutes</button>
                        <?php foreach($marques as $marque): ?>
                            <button class="filter-btn" data-filter="marque-<?php echo strtolower($marque['marque']); ?>">
                                <?php echo $marque['marque']; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prix</label>
                    <div class="d-flex flex-wrap">
                        <button class="filter-btn active" data-filter="prix-all">Tous</button>
                        <button class="filter-btn" data-filter="prix-low">< 50,000 FCFA</button>
                        <button class="filter-btn" data-filter="prix-mid">50,000 - 150,000 FCFA</button>
                        <button class="filter-btn" data-filter="prix-high">> 150,000 FCFA</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicles Grid -->
        <div class="row">
            <?php foreach($vehicules as $vehicule): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="car-card">
                    <div class="car-img-container">
                        <?php if ($vehicule['cover_image']): ?>
                            <img src="<?php echo $vehicule['cover_image']; ?>" alt="<?php echo $vehicule['marque'] . ' ' . $vehicule['modele']; ?>" class="car-img">
                        <?php else: ?>
                            <div class="car-img d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-car fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="price-tag"><?php echo number_format($vehicule['prix_jour'], 0, ',', ' '); ?> FCFA/jour</div>
                        <div class="category-badge"><?php echo $vehicule['marque']; ?></div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-3"><?php echo $vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['annee'] . ')'; ?></h5>
                        <div class="car-features">
                            <div class="car-feature">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Année <?php echo $vehicule['annee']; ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-car-side"></i>
                                <span><?php echo $vehicule['modele']; ?></span>
                            </div>
                            <div class="car-feature">
                                <i class="fas fa-check-circle"></i>
                                <span>Disponible</span>
                            </div>
                        </div>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="reserver.php?id=<?php echo $vehicule['id']; ?>" class="btn btn-primary w-100 mt-3">Réserver maintenant</a>
                        <?php else: ?>
                            <a href="connexion.php" class="btn btn-primary w-100 mt-3">Connectez-vous pour réserver</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>






    <?php include 'components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtres
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Enlever la classe active des autres boutons du même groupe
                this.parentElement.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                // Récupérer les filtres actifs
                const activeFilters = {
                    marque: document.querySelector('[data-filter^="marque-"].active').dataset.filter,
                    prix: document.querySelector('[data-filter^="prix-"].active').dataset.filter
                };

                // Filtrer les véhicules
                const vehicules = document.querySelectorAll('.car-card');
                vehicules.forEach(vehicule => {
                    let show = true;
                    
                    // Filtre par marque
                    if (activeFilters.marque !== 'marque-all') {
                        const marque = vehicule.querySelector('.category-badge').textContent.toLowerCase();
                        if (!marque.includes(activeFilters.marque.replace('marque-', ''))) {
                            show = false;
                        }
                    }
                    
                    // Filtre par prix
                    if (show && activeFilters.prix !== 'prix-all') {
                        const prixText = vehicule.querySelector('.price-tag').textContent;
                        const prix = parseInt(prixText.replace(/[^0-9]/g, ''));
                        
                        switch(activeFilters.prix) {
                            case 'prix-low':
                                if (prix >= 50000) show = false;
                                break;
                            case 'prix-mid':
                                if (prix < 50000 || prix > 150000) show = false;
                                break;
                            case 'prix-high':
                                if (prix <= 150000) show = false;
                                break;
                        }
                    }
                    
                    vehicule.closest('.col-lg-4').style.display = show ? 'block' : 'none';
                });
            });
        });
    </script>
</body>
</html>
