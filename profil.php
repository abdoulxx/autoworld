<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $tel = trim($_POST['tel']);
        $adresse = trim($_POST['adresse']);

        $sql = "UPDATE users SET nom = ?, email = ?, numero = ?, adresse = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $email, $tel, $adresse, $user_id]);

        $_SESSION['nom'] = $name;
        $success_message = "Profil mis à jour avec succès.";
    } elseif (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $sql = "SELECT mot_de_passe FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (password_verify($old_password, $user['mot_de_passe'])) {
            if ($new_password === $confirm_password) {
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET mot_de_passe = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$new_password_hashed, $user_id]);

                $success_message = "Mot de passe mis à jour avec succès.";
            } else {
                $error_message = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
            }
        } else {
            $error_message = "L'ancien mot de passe est incorrect.";
        }
    }
}

$sql = "SELECT nom, email, numero, adresse FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once 'components/scripts.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .hidden-section {
            display: none;
        }
        .profile-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .list-group-item.active {
            background-color: #1a237e !important;
            border-color: #1a237e !important;
        }
        .btn-primary {
            background-color: #1a237e;
            border-color: #1a237e;
        }
        .btn-primary:hover {
            background-color: #0e1859;
            border-color: #0e1859;
        }
    </style>
</head>
<body>
    <?php require_once 'components/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group mb-4">
                    <a href="#" id="showProfile" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user-edit me-2"></i>Modifier mes informations
                    </a>
                    <a href="#" id="showPassword" class="list-group-item list-group-item-action">
                        <i class="fas fa-key me-2"></i>Modifier mon mot de passe
                    </a>
                </div>
            </div>

            <div class="col-md-9">
                <div id="profileSection" class="profile-section">
                    <h3 class="mb-4"><i class="fas fa-user-circle me-2"></i>Modifier mes informations</h3>
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="profil.php" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="tel" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="tel" name="tel" value="<?php echo htmlspecialchars($user['numero']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse']); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>

                <div id="passwordSection" class="profile-section hidden-section">
                    <h3 class="mb-4"><i class="fas fa-lock me-2"></i>Modifier mon mot de passe</h3>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="profil.php" method="post">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Ancien mot de passe</label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'components/scripts.php'; ?>
    <script>
        document.getElementById('showProfile').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('profileSection').classList.remove('hidden-section');
            document.getElementById('passwordSection').classList.add('hidden-section');
            this.classList.add('active');
            document.getElementById('showPassword').classList.remove('active');
        });

        document.getElementById('showPassword').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('passwordSection').classList.remove('hidden-section');
            document.getElementById('profileSection').classList.add('hidden-section');
            this.classList.add('active');
            document.getElementById('showProfile').classList.remove('active');
        });
    </script>
</body>
</html>
