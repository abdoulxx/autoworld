<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'cnx.php';

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

try {
    // Vérifier la connexion
    if (!$conn) {
        throw new Exception("La connexion à la base de données n'est pas établie");
    }

    // Requête SQL
    $sql = "SELECT r.*, u.nom as user_nom, l.marque, l.modele, r.prix_total, r.statut, 
            DATE_FORMAT(r.date_debut, '%d/%m/%Y') as date_debut_fr,
            DATE_FORMAT(r.date_fin, '%d/%m/%Y') as date_fin_fr,
            (SELECT CONCAT('../', image_url) FROM images WHERE voiture_id = l.id AND is_cover = 1 LIMIT 1) as cover_image,
            u.email, u.numero
            FROM reservations r 
            JOIN users u ON r.user_id = u.id 
            JOIN louer l ON r.vehicule_id = l.id 
            ORDER BY r.date_creation DESC";

    // Exécuter la requête
    $stmt = $conn->query($sql);
    if (!$stmt) {
        throw new Exception("Erreur lors de l'exécution de la requête");
    }

    // Récupérer les résultats
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($reservations)) {
        $reservations = [];
    }
} catch(PDOException $e) {
    $error = "Erreur PDO lors de la récupération des réservations : " . $e->getMessage();
} catch(Exception $e) {
    $error = "Erreur lors de la récupération des réservations : " . $e->getMessage();
}

// Fonction pour obtenir la classe CSS du badge de statut
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'en_attente':
            return 'bg-yellow-100 text-yellow-800';
        case 'payé':
            return 'bg-green-100 text-green-800';
        case 'annulé':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Fonction pour formater le statut
function formatStatus($status) {
    switch ($status) {
        case 'en_attente':
            return 'En attente';
        case 'payé':
            return 'Payé';
        case 'annulé':
            return 'Annulé';
        default:
            return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .vehicle-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
        }
    </style>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-100">
    <?php require_once 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Gestion des réservations</h1>
        </div>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erreur!</strong>
                <span class="block sm:inline"> <?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">Aucune réservation trouvée.</span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Véhicule</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode paiement</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date création</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservations as $reservation): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($reservation['cover_image']): ?>
                                            <img src="<?php echo htmlspecialchars($reservation['cover_image']); ?>" alt="<?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>" class="vehicle-image mr-4">
                                        <?php else: ?>
                                            <div class="vehicle-image mr-4 bg-gray-200 flex items-center justify-center">
                                                <i class="fas fa-car text-gray-400 text-3xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($reservation['user_nom']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($reservation['email']); ?></div>
                                        <div class="text-gray-500"><?php echo htmlspecialchars($reservation['numero']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div>Du <?php echo $reservation['date_debut_fr']; ?></div>
                                        <div>Au <?php echo $reservation['date_fin_fr']; ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $reservation['mode_paiement'] === 'cash' ? 'Espèces' : 'En ligne'; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getStatusBadgeClass($reservation['statut']); ?>">
                                        <?php echo formatStatus($reservation['statut']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($reservation['date_creation'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button onclick="openStatusModal(<?php echo $reservation['id']; ?>, '<?php echo $reservation['statut']; ?>')" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Statut
                                        </button>
                                        <button onclick="openDeleteModal(<?php echo $reservation['id']; ?>)" 
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
        </div>
    </div>
    <script>
        function updateStatus(reservationId, newStatus) {
            if (confirm('Êtes-vous sûr de vouloir modifier le statut de cette réservation ?')) {
                // Créer un formulaire dynamique
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'update-reservation-status.php';

                // Ajouter les champs cachés
                const reservationIdInput = document.createElement('input');
                reservationIdInput.type = 'hidden';
                reservationIdInput.name = 'reservation_id';
                reservationIdInput.value = reservationId;
                form.appendChild(reservationIdInput);

                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'new_status';
                statusInput.value = newStatus;
                form.appendChild(statusInput);

                // Ajouter le formulaire au document et le soumettre
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <!-- Modal de modification du statut -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Modifier le statut</h3>
            <form id="statusForm" action="update-reservation-status.php" method="POST">
                <input type="hidden" name="reservation_id" id="statusReservationId">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                        Nouveau statut
                    </label>
                    <select name="new_status" id="statusSelect" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="en_attente">En attente</option>
                        <option value="payé">Payé</option>
                        <option value="annulé">Annulé</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeStatusModal()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-xl font-bold mb-4">Confirmer la suppression</h3>
            <p class="text-gray-600 mb-6">Êtes-vous sûr de vouloir supprimer cette réservation ? Cette action est irréversible.</p>
            <form id="deleteForm" action="delete-reservation.php" method="POST">
                <input type="hidden" name="reservation_id" id="deleteReservationId">
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
    function openStatusModal(id, currentStatus) {
        document.getElementById('statusReservationId').value = id;
        document.getElementById('statusSelect').value = currentStatus;
        document.getElementById('statusModal').classList.remove('hidden');
        document.getElementById('statusModal').classList.add('flex');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.remove('flex');
        document.getElementById('statusModal').classList.add('hidden');
    }

    function openDeleteModal(id) {
        document.getElementById('deleteReservationId').value = id;
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('flex');
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer les modales en cliquant en dehors
    document.getElementById('statusModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeStatusModal();
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
