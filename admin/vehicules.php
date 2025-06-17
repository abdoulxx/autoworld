<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Gestion de la suppression
if (isset($_POST['delete_id'])) {
    try {
        // Supprimer d'abord les images associées
        $stmt = $conn->prepare("DELETE FROM images WHERE voiture_id = ?");
        $stmt->execute([$_POST['delete_id']]);
        
        // Puis supprimer le véhicule
        $stmt = $conn->prepare("DELETE FROM louer WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        
        $_SESSION['success'] = "Véhicule supprimé avec succès";
        header('Location: vehicules.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Récupération des véhicules avec leurs images
try {
    $stmt = $conn->query("
        SELECT l.*, 
               (SELECT image_url FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image,
               (SELECT COUNT(*) FROM reservations WHERE vehicule_id = l.id AND statut != 'annulé') as nb_reservations
        FROM louer l
        ORDER BY l.created_at DESC
    ");
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération des véhicules: " . $e->getMessage();
    $vehicules = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Gestion des Véhicules</h1>
                <p class="text-gray-600">Gérez votre flotte de véhicules</p>
            </div>
            <a href="ajouter-vehicule.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                <i class="fas fa-plus mr-2"></i>Ajouter un véhicule
            </a>
        </div>

        <!-- Messages de notification -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $_SESSION['success']; ?></p>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $_SESSION['error']; ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Grid des véhicules -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($vehicules as $vehicule): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="relative pb-48">
                    <?php if ($vehicule['cover_image']): ?>
                        <img class="absolute h-full w-full object-cover" src="../<?php echo htmlspecialchars($vehicule['cover_image']); ?>" alt="<?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>">
                    <?php else: ?>
                        <div class="absolute h-full w-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-car text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-0 right-0 p-2">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $vehicule['disponibilite'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $vehicule['disponibilite'] ? 'Disponible' : 'Indisponible'; ?>
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <h3 class="text-xl font-semibold text-gray-800">
                        <?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>
                    </h3>
                    <p class="text-gray-600">Année: <?php echo htmlspecialchars($vehicule['annee']); ?></p>
                    <p class="text-purple-600 font-bold mt-2">
                        <?php echo number_format($vehicule['prix_jour'], 0, ',', ' '); ?> FCFA/jour
                    </p>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="mr-4">
                            <i class="fas fa-calendar-check mr-1"></i>
                            <?php echo $vehicule['nb_reservations']; ?> réservation(s)
                        </span>
                        <span>
                            <i class="fas fa-tag mr-1"></i>
                            <?php echo htmlspecialchars($vehicule['categorie']); ?>
                        </span>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex justify-between">
                        <a href="modifier-vehicule.php?id=<?php echo $vehicule['id']; ?>" 
                           class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-edit mr-1"></i>Modifier
                        </a>
                        <button onclick="confirmDelete(<?php echo $vehicule['id']; ?>)" 
                                class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash mr-1"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Confirmer la suppression</h3>
            <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.</p>
            <form id="deleteForm" method="POST">
                <input type="hidden" name="delete_id" id="deleteId">
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
    function confirmDelete(id) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer le modal en cliquant en dehors
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });
    </script>
    <script src="components/dropdown.js"></script>
</body>
</html>
