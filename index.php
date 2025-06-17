<?php

include 'components/navbar.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoWorld - Location et Vente de Véhicules</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <?php require_once 'components/scripts.php'; ?>
</head>
<body class="fade-in">
        <!-- Hero Section -->
    <section class="hero">
        <div class="container position-relative">
            <div class="row align-items-center" style="min-height: 80vh;">
                <div class="col-lg-6">
                    <h1>Bienvenue chez AutoWorld</h1>
                    <p>Découvrez notre sélection exclusive de véhicules pour la location et la vente. Une expérience premium pour tous vos besoins automobiles.</p>
                    <div class="d-flex gap-3">
                        <a href="louer.php" class="btn btn-primary btn-lg">Louer un véhicule</a>
                        <a href="acheter.php" class="btn btn-outline-primary btn-lg">Acheter un véhicule</a>
                    </div>
                </div>
            </div>
            <img src="images/elantra.png" alt="Voiture de luxe" class="hero-image">
        </div>
    </section>

    <!-- Services Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Nos Services</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-car fa-3x text-primary mb-3"></i>
                            <h4 class="mb-3">Location de Véhicules</h4>
                            <p>Large gamme de véhicules disponibles pour la location courte et longue durée.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h4 class="mb-3">Vente de Véhicules</h4>
                            <p>Achetez votre véhicule de rêve parmi notre sélection de qualité.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                            <h4 class="mb-3">Service 24/7</h4>
                            <p>Support client disponible 24h/24 et 7j/7 pour vous accompagner.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- location Vehicles Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Véhicules en location</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/misti.png" class="card-img-top" alt="Mitsubishi">
                        <div class="card-body">
                            <h5 class="card-title">Mitsubishi Eclipse Cross</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Location</span>
                                <span class="h5 mb-0">55.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 5 places</small>
                                <small><i class="fas fa-gas-pump"></i> Essence</small>
                                <small><i class="fas fa-cog"></i> Automatique</small>
                            </div>
                            <a href="louer.php" class="btn btn-primary w-100">Réserver maintenant</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/audi.png" class="card-img-top" alt="Audi">
                        <div class="card-body">
                            <h5 class="card-title">Audi Q5</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Location</span>
                                <span class="h5 mb-0">30.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 5 places</small>
                                <small><i class="fas fa-gas-pump"></i> Diesel</small>
                                <small><i class="fas fa-cog"></i> Automatique</small>
                            </div>
                            <a href="louer.php" class="btn btn-primary w-100">Réserver maintenant</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/jeep.png" class="card-img-top" alt="Jeep">
                        <div class="card-body">
                            <h5 class="card-title">Jeep Wrangler</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Location</span>
                                <span class="h5 mb-0">20.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 4 places</small>
                                <small><i class="fas fa-gas-pump"></i> Diesel</small>
                                <small><i class="fas fa-cog"></i> Manuel</small>
                            </div>
                            <a href="louer.php" class="btn btn-primary w-100">Réserver maintenant</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="louer.php" class="btn btn-outline-primary btn-lg">Voir tous les véhicules disponible pour la location</a>
            </div>
        </div>
    </section>


     <!-- achat Vehicles Section -->
     <section class="section">
        <div class="container">
            <h2 class="section-title">Véhicules en vente</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/merco.png" class="card-img-top" alt="Mercedes">
                        <div class="card-body">
                            <h5 class="card-title">Mercedes c300</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Vente</span>
                                <span class="h5 mb-0">55.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 5 places</small>
                                <small><i class="fas fa-gas-pump"></i> Essence</small>
                                <small><i class="fas fa-cog"></i> Automatique</small>
                            </div>
                            <a href="louer.php" class="btn btn-primary w-100">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/tucson.png" class="card-img-top" alt="Tucson">
                        <div class="card-body">
                            <h5 class="card-title">Tucson</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Vente</span>
                                <span class="h5 mb-0">30.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 5 places</small>
                                <small><i class="fas fa-gas-pump"></i> Diesel</small>
                                <small><i class="fas fa-cog"></i> Automatique</small>
                            </div>
                            <a href="acheter.php" class="btn btn-primary w-100">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="images/elantra.png" class="card-img-top" alt="Elantra">
                        <div class="card-body">
                            <h5 class="card-title">Elantra</h5>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">Vente</span>
                                <span class="h5 mb-0">20.000 FCFA/jour</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <small><i class="fas fa-user-friends"></i> 4 places</small>
                                <small><i class="fas fa-gas-pump"></i> Diesel</small>
                                <small><i class="fas fa-cog"></i> Manuel</small>
                            </div>
                            <a href="acheter.php" class="btn btn-primary w-100">Acheter maintenant</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="acheter.php" class="btn btn-outline-primary btn-lg">Voir tous les véhicules disponible pour la vente</a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Pourquoi Nous Choisir</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Sécurité Garantie</h4>
                        <p>Tous nos véhicules sont régulièrement entretenus et assurés.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-money-bill-wave fa-3x text-primary mb-3"></i>
                        <h4>Prix Compétitifs</h4>
                        <p>Les meilleurs tarifs du marché pour un service premium.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h4>Support 24/7</h4>
                        <p>Notre équipe est disponible à tout moment pour vous aider.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <i class="fas fa-car-side fa-3x text-primary mb-3"></i>
                        <h4>Large Sélection</h4>
                        <p>Une gamme variée de véhicules pour tous les besoins.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    </section>
    <?php include 'components/footer.php'; ?>