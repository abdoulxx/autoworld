<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire d'ajout d'admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_admin') {
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];
    
    // Validation
    if (empty($nom)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($password)) $errors[] = "Le mot de passe est requis";
    if ($password !== $confirm_password) $errors[] = "Les mots de passe ne correspondent pas";
    
    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $conn->prepare("SELECT COUNT(*) FROM admin WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error'] = "Cet email est déjà utilisé";
            } else {
                // Hasher le mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insérer le nouvel admin
                $stmt = $conn->prepare("INSERT INTO admin (nom, email, mot_de_passe) VALUES (?, ?, ?)");
                $stmt->execute([$nom, $email, $hashed_password]);
                
                $_SESSION['success'] = "L'administrateur a été ajouté avec succès";
                header('Location: settings.php');
                exit();
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'administrateur : " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Récupérer la liste des admins
try {
    $stmt = $conn->query("SELECT id, nom, email, created_at FROM admin ORDER BY created_at DESC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des administrateurs : " . $e->getMessage();
    $admins = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php require_once 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Paramètres</h1>
            <button onclick="openAddAdminModal()" 
                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i>Ajouter un administrateur
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Liste des administrateurs -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Administrateurs</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nom
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date d'ajout
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-shield text-purple-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($admin['nom']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($admin['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($admin['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                    <button onclick="openDeleteModal(<?php echo $admin['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal d'ajout d'admin -->
    <div id="addAdminModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Ajouter un administrateur</h3>
                <button onclick="closeAddAdminModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="settings.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_admin">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nom">
                        Nom
                    </label>
                    <input type="text" name="nom" id="nom" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input type="email" name="email" id="email" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Mot de passe
                    </label>
                    <input type="password" name="password" id="password" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                        Confirmer le mot de passe
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password" required
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeAddAdminModal()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Confirmer la suppression</h3>
            <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer cet administrateur ? Cette action est irréversible.</p>
            <form id="deleteForm" action="delete-admin.php" method="POST">
                <input type="hidden" name="admin_id" id="deleteAdminId">
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Confirmer la suppression
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openAddAdminModal() {
        document.getElementById('addAdminModal').classList.remove('hidden');
        document.getElementById('addAdminModal').classList.add('flex');
    }

    function closeAddAdminModal() {
        document.getElementById('addAdminModal').classList.remove('flex');
        document.getElementById('addAdminModal').classList.add('hidden');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteAdminId').value = id;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer les modales en cliquant en dehors
    document.getElementById('addAdminModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddAdminModal();
        }
    });

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    </script>
    <script src="components/dropdown.js"></script>
</body>
</html>
