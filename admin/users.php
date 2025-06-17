<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Récupérer les utilisateurs avec leurs statistiques
    $sql = "SELECT u.*, 
            (SELECT COUNT(*) FROM reservations r WHERE r.user_id = u.id) as nb_reservations,
            (SELECT COUNT(*) FROM reservations r WHERE r.user_id = u.id AND r.statut = 'payé') as nb_reservations_payees
            FROM users u 
            ORDER BY u.created_at DESC";
    
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
</head>
<body class="bg-gray-100">
    <?php require_once 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
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

        <!-- Table des utilisateurs -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistiques
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date d'inscription
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-purple-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($user['nom']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['numero']); ?></div>
                                <?php if ($user['adresse']): ?>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['adresse']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <span class="font-semibold"><?php echo $user['nb_reservations']; ?></span> réservation(s)
                                </div>
                                <div class="text-sm text-gray-500">
                                    dont <span class="font-semibold"><?php echo $user['nb_reservations_payees']; ?></span> payée(s)
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button onclick="openUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-eye mr-1"></i> Détails
                                    </button>
                                    <button onclick="openDeleteModal(<?php echo $user['id']; ?>)" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <i class="fas fa-trash mr-1"></i> Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal des détails utilisateur -->
    <div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold" id="userModalTitle">Détails de l'utilisateur</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="userModalContent" class="space-y-4">
                <!-- Le contenu sera injecté par JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Confirmer la suppression</h3>
            <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
            <form id="deleteForm" action="delete-user.php" method="POST">
                <input type="hidden" name="user_id" id="deleteUserId">
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
    function openUserModal(user) {
        const content = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="font-semibold text-gray-700">Informations personnelles</h4>
                    <p class="mt-2"><span class="text-gray-500">Nom:</span> ${user.nom}</p>
                    <p><span class="text-gray-500">Email:</span> ${user.email}</p>
                    <p><span class="text-gray-500">Téléphone:</span> ${user.numero || 'Non renseigné'}</p>
                    <p><span class="text-gray-500">Adresse:</span> ${user.adresse || 'Non renseignée'}</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-700">Statistiques</h4>
                    <p class="mt-2"><span class="text-gray-500">Réservations totales:</span> ${user.nb_reservations}</p>
                    <p><span class="text-gray-500">Réservations payées:</span> ${user.nb_reservations_payees}</p>
                    <p><span class="text-gray-500">Date d'inscription:</span> ${new Date(user.created_at).toLocaleDateString()}</p>
                </div>
            </div>
        `;
        
        document.getElementById('userModalContent').innerHTML = content;
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('userModal').classList.add('flex');
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.remove('flex');
        document.getElementById('userModal').classList.add('hidden');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer les modales en cliquant en dehors
    document.getElementById('userModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUserModal();
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
