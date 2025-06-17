<?php
session_start();
require_once '../cnx.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $marque = $_POST['marque'];
                $modele = $_POST['modele'];
                $annee = $_POST['annee'];
                $kilometrage = $_POST['kilometrage'];
                $prix = $_POST['prix'];
                $carburant = $_POST['carburant'];
                $transmission = $_POST['transmission'];
                $description = $_POST['description'];
                $places = $_POST['places'];
                $couleur = $_POST['couleur'];

                $sql = "INSERT INTO vendre (marque, modele, annee, kilometrage, prix, carburant, transmission, description, places, couleur) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$marque, $modele, $annee, $kilometrage, $prix, $carburant, $transmission, $description, $places, $couleur]);

                // Gestion de l'image
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $vehicule_id = $conn->lastInsertId();
                    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $target_file = "../images/vehicles/" . $vehicule_id . "." . $extension;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        // Enregistrer l'extension dans la base de données
                        $sql = "UPDATE vendre SET image_ext = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$extension, $vehicule_id]);
                    }
                }
                break;

            case 'edit':
                $id = $_POST['id'];
                $marque = $_POST['marque'];
                $modele = $_POST['modele'];
                $annee = $_POST['annee'];
                $kilometrage = $_POST['kilometrage'];
                $prix = $_POST['prix'];
                $carburant = $_POST['carburant'];
                $transmission = $_POST['transmission'];
                $description = $_POST['description'];
                $places = $_POST['places'];
                $couleur = $_POST['couleur'];
                $disponible = isset($_POST['disponible']) ? 1 : 0;

                $sql = "UPDATE vendre SET marque = ?, modele = ?, annee = ?, kilometrage = ?, prix = ?, 
                        carburant = ?, transmission = ?, description = ?, places = ?, couleur = ?, disponible = ? 
                        WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$marque, $modele, $annee, $kilometrage, $prix, $carburant, $transmission, 
                              $description, $places, $couleur, $disponible, $id]);

                // Gestion de l'image
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $target_file = "../images/vehicles/" . $id . "." . $extension;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        // Mettre à jour l'extension dans la base de données
                        $sql = "UPDATE vendre SET image_ext = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$extension, $id]);
                    }
                }
                break;

            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM vendre WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$id]);

                // Supprimer l'image associée
                $image_path = "../images/vehicles/" . $id . ".jpg";
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                break;
        }
    }
}

// Récupération des véhicules avec leur extension d'image
$sql = "SELECT *, COALESCE(image_ext, 'jpg') as image_ext FROM vendre ORDER BY date_ajout DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$vehicules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Véhicules à Vendre - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .vehicle-img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            object-position: center;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once 'components/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
            <div class="container mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <h3 class="text-gray-700 text-3xl font-medium">Véhicules à vendre</h3>
                    <button onclick="document.getElementById('addVehicleModal').classList.remove('hidden')" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-plus mr-2"></i>Ajouter un véhicule
                    </button>
                </div>

                <div class="flex flex-col mt-8">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marque</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modèle</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kilométrage</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponible</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($vehicules as $vehicule): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $image_path = "../images/vehicles/" . $vehicule['id'] . '.' . $vehicule['image_ext'];
                                            $default_image = "../images/default-car.jpg";
                                        ?>
                                        <img src="<?php echo file_exists($image_path) ? $image_path : $default_image; ?>" 
                                             class="vehicle-img" 
                                             alt="<?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>">

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($vehicule['marque']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($vehicule['modele']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($vehicule['annee']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($vehicule['kilometrage'], 0, ',', ' '); ?> km</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo number_format($vehicule['prix'], 0, ',', ' '); ?> FCFA</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $vehicule['disponible'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $vehicule['disponible'] ? 'Disponible' : 'Non disponible'; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <button onclick="editVehicle(<?= htmlspecialchars(json_encode($vehicule)) ?>)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">Modifier</button>
                                        <button onclick="deleteVehicle(<?= $vehicule['id'] ?>)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Supprimer</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Ajout Véhicule -->
    <div class="modal fixed z-10 inset-0 overflow-y-auto hidden" id="addVehicleModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="modal-overlay fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Ajouter un véhicule</h3>
                            <button type="button" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500" onclick="closeModal('addVehicleModal');">
                                <span class="sr-only">Fermer</span>
                                <i class="fas fa-times"></i>
                            </button>
                            <form action="" method="POST" enctype="multipart/form-data" class="mt-4">
                                <input type="hidden" name="action" value="add">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Marque</label>
                                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="marque" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Modèle</label>
                                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="modele" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Année</label>
                                        <input type="number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="annee" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Kilométrage</label>
                                        <input type="number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="kilometrage" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Prix</label>
                                        <input type="number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="prix" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Carburant</label>
                                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="carburant" required>
                                            <option value="Essence">Essence</option>
                                            <option value="Diesel">Diesel</option>
                                            <option value="Hybride">Hybride</option>
                                            <option value="Électrique">Électrique</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Transmission</label>
                                        <select class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="transmission" required>
                                            <option value="Manuelle">Manuelle</option>
                                            <option value="Automatique">Automatique</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Places</label>
                                        <input type="number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="places" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Couleur</label>
                                        <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="couleur" required>
                                    </div>
                                    <div class="col-span-2 mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="description" rows="3" required></textarea>
                                    </div>
                                    <div class="col-span-2 mb-3">
                                        <label class="block text-sm font-medium text-gray-700">Image</label>
                                        <input type="file" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500" name="image" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Ajouter</button>
                                    <button type="button" class="modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Édition Véhicule -->
    <div class="modal fixed z-10 inset-0 overflow-y-auto hidden" id="editVehicleModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="modal-overlay fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Modifier un véhicule</h3>
                            <form action="" method="POST" enctype="multipart/form-data" class="mt-2">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_marque" class="block text-sm font-medium text-gray-700">Marque</label>
                                        <input type="text" name="marque" id="edit_marque" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_modele" class="block text-sm font-medium text-gray-700">Modèle</label>
                                        <input type="text" name="modele" id="edit_modele" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_annee" class="block text-sm font-medium text-gray-700">Année</label>
                                        <input type="number" name="annee" id="edit_annee" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_kilometrage" class="block text-sm font-medium text-gray-700">Kilométrage</label>
                                        <input type="number" name="kilometrage" id="edit_kilometrage" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_prix" class="block text-sm font-medium text-gray-700">Prix</label>
                                        <input type="number" name="prix" id="edit_prix" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_carburant" class="block text-sm font-medium text-gray-700">Carburant</label>
                                        <select name="carburant" id="edit_carburant" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="Essence">Essence</option>
                                            <option value="Diesel">Diesel</option>
                                            <option value="Électrique">Électrique</option>
                                            <option value="Hybride">Hybride</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit_transmission" class="block text-sm font-medium text-gray-700">Transmission</label>
                                        <select name="transmission" id="edit_transmission" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="Manuelle">Manuelle</option>
                                            <option value="Automatique">Automatique</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="edit_places" class="block text-sm font-medium text-gray-700">Nombre de places</label>
                                        <input type="number" name="places" id="edit_places" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                    <div>
                                        <label for="edit_couleur" class="block text-sm font-medium text-gray-700">Couleur</label>
                                        <input type="text" name="couleur" id="edit_couleur" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="edit_description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                </div>
                                <div class="mt-4">
                                    <label for="edit_image" class="block text-sm font-medium text-gray-700">Image</label>
                                    <input type="file" name="image" id="edit_image" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" accept="image/*">
                                </div>
                                <div class="mt-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="disponible" id="edit_disponible" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2">Disponible</span>
                                    </label>
                                </div>
                                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">Enregistrer</button>
                                    <button type="button" class="modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Suppression Véhicule -->
    <div class="modal fixed z-10 inset-0 overflow-y-auto hidden" id="deleteVehicleModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="modal-overlay fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Supprimer le véhicule</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="" method="POST" class="w-full">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <div class="sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Supprimer</button>
                            <button type="button" class="modal-close mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/forms@0.3.4/dist/forms.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation des modales
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });

            // Fonction pour ouvrir une modale
            window.openModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }

            // Fonction pour fermer une modale
            window.closeModal = function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Gestionnaire pour la modale d'édition
            window.editVehicle = function(vehicule) {
                document.getElementById('edit_id').value = vehicule.id;
                document.getElementById('edit_marque').value = vehicule.marque;
                document.getElementById('edit_modele').value = vehicule.modele;
                document.getElementById('edit_annee').value = vehicule.annee;
                document.getElementById('edit_kilometrage').value = vehicule.kilometrage;
                document.getElementById('edit_prix').value = vehicule.prix;
                document.getElementById('edit_carburant').value = vehicule.carburant;
                document.getElementById('edit_transmission').value = vehicule.transmission;
                document.getElementById('edit_places').value = vehicule.places;
                document.getElementById('edit_couleur').value = vehicule.couleur;
                document.getElementById('edit_description').value = vehicule.description;
                document.getElementById('edit_disponible').checked = vehicule.disponible == 1;
                
                openModal('editVehicleModal');
            }

            // Gestionnaire pour la modale de suppression
            window.deleteVehicle = function(id) {
                document.getElementById('delete_id').value = id;
                openModal('deleteVehicleModal');
            }

            // Fermer les modales quand on clique sur l'arrière-plan
            document.querySelectorAll('.modal-overlay').forEach(overlay => {
                overlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        const modal = this.closest('.modal');
                        if (modal) {
                            closeModal(modal.id);
                        }
                    }
                });
            });

            // Fermer les modales avec la touche Echap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('.modal:not(.hidden)').forEach(modal => {
                        closeModal(modal.id);
                    });
                }
            });

            // Gestionnaires pour les boutons de fermeture
            document.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    if (modal) {
                        closeModal(modal.id);
                    }
                });
            });
        });
    </script>
    <script src="components/dropdown.js"></script>
</body>
</html>
