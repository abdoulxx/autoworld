<?php
session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}
// Mise à jour du statut si demandé
if (isset($_POST['action']) && isset($_POST['demande_id'])) {
    $sql = "UPDATE demandes_essai SET statut = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_POST['statut'], $_POST['demande_id']]);
}

// Récupération des demandes d'essai
$sql = "SELECT de.*, v.marque, v.modele, v.annee 
        FROM demandes_essai de 
        JOIN vendre v ON de.vehicule_id = v.id 
        ORDER BY de.date_demande DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$demandes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes d'essai - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-badge {
            font-size: 0.9em;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
        }
        .status-en_attente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmé {
            background-color: #d4edda;
            color: #155724;
        }
        .status-annulé {
            background-color: #f8d7da;
            color: #721c24;
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
                <a href="dashboard.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
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
                        <a href="demandes-essais.php" class="block px-4 py-2 text-gray-600 hover:bg-gray-100 bg-gray-100">
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
        </div>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-semibold text-gray-800">Demandes d'essai</h1>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white rounded-lg overflow-hidden">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-gray-600 text-sm uppercase tracking-wider">
                                            <th class="px-6 py-3">Date demande</th>
                                            <th class="px-6 py-3">Véhicule</th>
                                            <th class="px-6 py-3">Client</th>
                                            <th class="px-6 py-3">Contact</th>
                                            <th class="px-6 py-3">Date essai</th>
                                            <th class="px-6 py-3">Heure</th>
                                            <th class="px-6 py-3">Statut</th>
                                            <th class="px-6 py-3">Actions</th>
                                        </tr>
                                    </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach($demandes as $demande): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php echo htmlspecialchars($demande['marque'] . ' ' . $demande['modele'] . ' (' . $demande['annee'] . ')'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo htmlspecialchars($demande['nom']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="text-gray-900"><?php echo htmlspecialchars($demande['email']); ?></div>
                                            <div class="text-gray-600"><?php echo htmlspecialchars($demande['telephone']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('d/m/Y', strtotime($demande['date_essai'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo date('H:i', strtotime($demande['heure_essai'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="status-badge status-<?php echo $demande['statut']; ?>">
                                                <?php echo ucfirst($demande['statut']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button onclick="openActionModal(<?php echo $demande['id']; ?>, '<?php echo htmlspecialchars($demande['marque'] . ' ' . $demande['modele']); ?>')" class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-cog"></i> Actions
                                            </button>
                                        </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php if (empty($demandes)): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Aucune demande d'essai pour le moment</td>
                                    </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
        </div>
    </div>

    <!-- Modal des actions -->
    <div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Actions pour la demande</h3>
            <p id="vehicleInfo" class="text-gray-600 mb-6"></p>
            <div class="space-y-4">
                <form method="POST" class="w-full">
                    <input type="hidden" name="demande_id" id="modalDemandeId">
                    <input type="hidden" name="statut" value="confirmé">
                    <button type="submit" name="action" class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-2">
                        <i class="fas fa-check mr-2"></i>Confirmer la demande
                    </button>
                </form>
                <form method="POST" class="w-full">
                    <input type="hidden" name="demande_id" id="modalDemandeIdCancel">
                    <input type="hidden" name="statut" value="annulé">
                    <button type="submit" name="action" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        <i class="fas fa-times mr-2"></i>Annuler la demande
                    </button>
                </form>
                <button onclick="closeActionModal()" class="w-full mt-4 px-4 py-2 text-gray-600 hover:text-gray-800">
                    Fermer
                </button>
            </div>
        </div>
    </div>

    <script>
        function openActionModal(demandeId, vehicleInfo) {
            document.getElementById('modalDemandeId').value = demandeId;
            document.getElementById('modalDemandeIdCancel').value = demandeId;
            document.getElementById('vehicleInfo').textContent = 'Véhicule : ' + vehicleInfo;
            document.getElementById('actionModal').classList.remove('hidden');
            document.getElementById('actionModal').classList.add('flex');
        }

        function closeActionModal() {
            document.getElementById('actionModal').classList.remove('flex');
            document.getElementById('actionModal').classList.add('hidden');
        }

        // Fermer la modal en cliquant en dehors
        document.getElementById('actionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeActionModal();
            }
        });

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
