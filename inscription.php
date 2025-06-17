<?php
include('cnx.php');
$success_message='';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $name = $_POST['name'];
    $email = $_POST['email'];
    $tel = $_POST['tel'];
    $adresse = $_POST['adresse'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hachage du mot de passe

    try {
        // Préparer la requête SQL
        $sql = "INSERT INTO users (nom, email, numero, adresse, mot_de_passe) VALUES (:name, :email, :tel, :adresse, :password)";
        $stmt = $conn->prepare($sql);

        // Lier les paramètres
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':password', $password);

        // Exécuter la requête
        if ($stmt->execute()) {
            $success_message = "Nouvel utilisateur ajouté avec succès.";
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'connexion.php';
                    }, 3000);
                  </script>";
        } else {
            echo "Erreur lors de l'ajout de l'utilisateur.";
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }

    // Fermer la connexion
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: calc(100vh - 200px);
        }
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .auth-image {
            background: linear-gradient(135deg, rgba(26, 35, 126, 0.9) 0%, rgba(26, 35, 126, 0.7) 100%), url('images/auth-bg.jpg');
            background-size: cover;
            background-position: center;
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-form {
            padding: 40px;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        .btn-auth {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
        }
        .auth-separator {
            text-align: center;
            position: relative;
            margin: 25px 0;
        }
        .auth-separator::before, .auth-separator::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background-color: #dee2e6;
        }
        .auth-separator::before { left: 0; }
        .auth-separator::after { right: 0; }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <section class="auth-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="auth-card">
                        <div class="row g-0">
                            <div class="col-md-6 auth-image">
                                <h2 class="h1 mb-4">Rejoignez AutoWorld</h2>
                                <p class="lead">Créez votre compte pour profiter de tous nos services de location et d'achat de véhicules.</p>
                                <div class="mt-4">
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Accès à toutes nos offres</p>
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Réservations prioritaires</p>
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Offres exclusives membres</p>
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Historique des transactions</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="auth-form">
                                    <h3 class="mb-4">Créer un compte</h3>
                                    <?php if ($success_message): ?>
                                        <div class="alert alert-success" role="alert">
                                            <?php echo $success_message; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nom complet</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Votre nom complet" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tel" class="form-label">Numéro</label>
                                            <input type="tel" class="form-control" id="tel" name="tel" placeholder="Votre numéro" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" placeholder="Votre adresse" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Choisissez un mot de passe" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-auth">Créer mon compte</button>
                                    </form>
                                    
                                    <div class="auth-separator">
                                        <span class="px-3 bg-white">ou</span>
                                    </div>
                                    
                                    <div class="text-center">
                                        <p class="mb-0">Déjà membre ?</p>
                                        <a href="connexion.php" class="btn btn-outline-primary btn-auth">Se connecter</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <?php include 'components/footer.php'; ?>
</body>
</html>
