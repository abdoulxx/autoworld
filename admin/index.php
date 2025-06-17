<?php
session_start();
require_once 'cnx.php';

if (isset($_POST['connexion'])) {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM admin WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['mot_de_passe'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nom'] = $admin['nom'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Identifiants incorrects";
        }
    } catch(PDOException $e) {
        $error = "Erreur de connexion: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - AutoWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
    </style>
</head>
<body class="min-h-screen login-container flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="glass-effect rounded-xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">AutoWorld Admin</h1>
                <p class="text-gray-200">Connectez-vous à votre espace administrateur</p>
            </div>

            <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-white text-sm font-semibold mb-2" for="email">
                        Email
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white bg-opacity-20 border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-300"
                            placeholder="admin@example.com">
                    </div>
                </div>

                <div>
                    <label class="block text-white text-sm font-semibold mb-2" for="password">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-300">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-10 pr-4 py-3 rounded-lg bg-white bg-opacity-20 border border-gray-300 focus:border-purple-500 focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-300"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" name="connexion"
                    class="w-full bg-white text-purple-600 font-semibold py-3 px-4 rounded-lg hover:bg-gray-100 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50">
                    <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="../" class="text-white hover:text-gray-300 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Retour au site
                </a>
            </div>
        </div>
    </div>

    <script>
        // Animation des champs de formulaire
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('transform', 'scale-105');
            });
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('transform', 'scale-105');
            });
        });
    </script>
</body>
</html>
