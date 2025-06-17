<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Récupérer les statistiques
// Initialiser les variables
$vehicules = 0;
$reservations = 0;
$users = 0;
$recent_reservations = [];

try {
    // Nombre total de véhicules
    $stmt = $conn->query("SELECT COUNT(*) as total FROM louer");
    $vehicules = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Nombre total de réservations
    $stmt = $conn->query("SELECT COUNT(*) as total FROM reservations");
    $reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Nombre total d'utilisateurs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Dernières réservations
    $stmt = $conn->query("SELECT r.*, u.nom as user_nom, l.marque, l.modele, r.prix_total, r.statut, 
                         DATE_FORMAT(r.date_debut, '%d/%m/%Y') as date_debut_fr,
                         DATE_FORMAT(r.date_fin, '%d/%m/%Y') as date_fin_fr
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.id 
                         JOIN louer l ON r.vehicule_id = l.id 
                         ORDER BY r.date_creation DESC LIMIT 5");
    $recent_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
        <div class="flex flex-col h-full">
            <div class="p-4 bg-purple-600">
                <h2 class="text-white text-xl font-bold">AutoWorld Admin</h2>
            </div>
            
            <nav class="flex-1 p-4 space-y-2">
                <a href="dashboard.php" class="flex items-center p-3 text-gray-700 bg-gray-100 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Menu Location avec dropdown -->
                <div class="relative dropdown-menu">
                    <button class="w-full flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg dropdown-toggle">
                        <i class="fas fa-car w-6"></i>
                        <span class="flex-1">Location</span>
                        <i class="fas fa-chevron-down ml-2 transition-transform duration-200"></i>
                    </button>
                    <div class="hidden absolute left-0 w-full bg-white shadow-lg rounded-lg mt-1 py-2 z-50 dropdown-content">
                        <a href="vehicules.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                            <i class="fas fa-car-side w-6"></i> Véhicules
                        </a>
                        <a href="reservations.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                            <i class="fas fa-calendar-check w-6"></i> Réservations
                        </a>
                    </div>
                </div>

                <!-- Menu Vente avec dropdown -->
                <div class="relative dropdown-menu">
                    <button class="w-full flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg dropdown-toggle">
                        <i class="fas fa-tags w-6"></i>
                        <span class="flex-1">Vente</span>
                        <i class="fas fa-chevron-down ml-2 transition-transform duration-200"></i>
                    </button>
                    <div class="hidden absolute left-0 w-full bg-white shadow-lg rounded-lg mt-1 py-2 z-50 dropdown-content">
                        <a href="vente.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                            <i class="fas fa-car w-6"></i> Véhicules
                        </a>
                        <a href="demandes-essais.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100">
                            <i class="fas fa-clipboard-check w-6"></i> Demandes d'essai
                        </a>
                    </div>
                </div>

                <a href="users.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-users w-6"></i>
                    <span>Utilisateurs</span>
                </a>

                <a href="settings.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-cog w-6"></i>
                    <span>Paramètres</span>
                </a>
            </nav>

            <div class="p-4 border-t">
                <a href="logout.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 rounded-lg">
                    <i class="fas fa-sign-out-alt w-6"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_nom']); ?></p>
            </div>
            <div class="flex space-x-4">
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="gradient-card rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-white bg-opacity-30 rounded-full">
                        <i class="fas fa-car text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-white text-lg">Véhicules</h3>
                        <p class="text-white text-2xl font-bold"><?php echo $vehicules; ?></p>
                    </div>
                </div>
            </div>

            <div class="gradient-card rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-white bg-opacity-30 rounded-full">
                        <i class="fas fa-calendar-check text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-white text-lg">Réservations</h3>
                        <p class="text-white text-2xl font-bold"><?php echo $reservations; ?></p>
                    </div>
                </div>
            </div>

            <div class="gradient-card rounded-lg shadow-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-white bg-opacity-30 rounded-full">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-white text-lg">Utilisateurs</h3>
                        <p class="text-white text-2xl font-bold"><?php echo $users; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reservations -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Dernières Réservations</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Véhicule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($recent_reservations)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune réservation trouvée</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent_reservations as $reservation): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reservation['user_nom']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    Du <?php echo $reservation['date_debut_fr']; ?><br>
                                    au <?php echo $reservation['date_fin_fr']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClass = [
                                    'en_attente' => 'bg-yellow-100 text-yellow-800',
                                    'payé' => 'bg-green-100 text-green-800',
                                    'annulé' => 'bg-red-100 text-red-800'
                                ][$reservation['statut']];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($reservation['statut']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="reservation-details.php?id=<?php echo $reservation['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" onclick="confirmDelete(<?php echo $reservation['id']; ?>)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer les dropdowns
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            
            // Fermer tous les dropdowns quand on clique ailleurs
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-menu')) {
                    dropdownToggles.forEach(toggle => {
                        const content = toggle.nextElementSibling;
                        const arrow = toggle.querySelector('.fa-chevron-down');
                        content.classList.add('hidden');
                        arrow.style.transform = 'rotate(0deg)';
                    });
                }
            });

            // Gérer le clic sur chaque dropdown
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const content = this.nextElementSibling;
                    const arrow = this.querySelector('.fa-chevron-down');
                    
                    // Fermer les autres dropdowns
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== toggle) {
                            const otherContent = otherToggle.nextElementSibling;
                            const otherArrow = otherToggle.querySelector('.fa-chevron-down');
                            otherContent.classList.add('hidden');
                            otherArrow.style.transform = 'rotate(0deg)';
                        }
                    });
                    
                    // Basculer le dropdown actuel
                    content.classList.toggle('hidden');
                    arrow.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                });
            });
        });
    </script>
    
</body>
</html>
