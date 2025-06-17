<?php
session_start();
include('cnx.php');

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire en utilisant filter_input pour nettoyer les entrées
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars($_POST['password'] ?? '', ENT_QUOTES, 'UTF-8');

    try {
        // Préparer la requête SQL avec un placeholder pour l'email
        $sql = "SELECT id, nom, mot_de_passe FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Vérifier le mot de passe
            if (password_verify($password, $user['mot_de_passe'])) {
                // Démarrer la session et enregistrer les informations de l'utilisateur
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login'] = $email; // Stocker l'email de l'utilisateur
                $_SESSION['nom'] = $user['nom'];
                // Rediriger vers la page louer.php après 1 seconde
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 1000);
                      </script>";
                $success_message = "Connexion réussie. Redirection en cours...";
            } else {
                $error_message = "Mot de passe incorrect.";
            }
        } else {
            $error_message = "Aucun utilisateur trouvé avec cet email.";
        }
    } catch (PDOException $e) {
        $error_message = "Erreur: " . $e->getMessage();
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
    <title>Connexion - AutoWorld</title>
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
                                <h2 class="h1 mb-4">Bienvenue chez AutoWorld</h2>
                                <p class="lead">Connectez-vous pour accéder à votre espace personnel et gérer vos locations et achats de véhicules.</p>
                                <div class="mt-4">
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Réservations simplifiées</p>
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Suivi de vos locations</p>
                                    <p class="mb-1"><i class="fas fa-check me-2"></i> Offres exclusives</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="auth-form">
                                    <h3 class="mb-4">Connexion</h3>
                                    <?php if ($error_message): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <?php echo htmlspecialchars($error_message); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($success_message)): ?>
                                        <div class="alert alert-success" role="alert">
                                            <?php echo htmlspecialchars($success_message); ?>
                                        </div>
                                    <?php endif; ?>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Votre email" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="remember">
                                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-auth">Se connecter</button>
                                    </form>
                                    
                                    <div class="auth-separator">
                                        <span class="px-3 bg-white">ou</span>
                                    </div>
                                    
                                    <div class="text-center">
                                        <p class="mb-0">Pas encore de compte ?</p>
                                        <a href="inscription.php" class="btn btn-outline-primary btn-auth">Créer un compte</a>
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


<style>
    /* General Styles */
    body, html {
        height: 100%;
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
    }

    /* Navbar Custom */
    .navbar-custom {
        background-color: #000;
    }

    .navbar-custom .nav-link {
        color: #fff;
    }

    .navbar-custom .nav-link:hover {
        color: #ffcccb;
    }

    /* Logo Section */
    .logo-section {
        text-align: center;
    }

    .logo-section .logo {
        max-width: 80%;
        height: auto;
    }

    /* Form Section */
    .form-section {
        background-color: #fff;
        padding: 40px;
    }

    .form-container {
        max-width: 400px;
        width: 100%;
        text-align: center;
    }

    .form-container h2 {
        margin-bottom: 30px;
        color: #333;
    }

    .form-container .form-control {
        border-radius: 20px;
        padding: 10px 20px;
    }

    .form-container .btn-primary {
        background-color: #ff1100d8;
        border-color: #ff1100d8;
        border-radius: 20px;
        padding: 10px 20px;
    }

    .form-container .btn-primary:hover {
        background-color: #ff1100e0;
        border-color: #ff1100e0;
    }

    .form-container p {
        margin-top: 20px;
    }

    .form-container p a {
        color: #ff1100d8;
    }

    .form-container p a:hover {
        color: #ff1100e0;
    }
</style>
