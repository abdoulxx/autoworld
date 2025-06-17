<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

// Récupérer l'ID du véhicule
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['error'] = "ID du véhicule non spécifié";
    header('Location: vehicules.php');
    exit();
}

// Récupérer les informations du véhicule
try {
    $stmt = $conn->prepare("SELECT * FROM louer WHERE id = ?");
    $stmt->execute([$id]);
    $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehicule) {
        $_SESSION['error'] = "Véhicule non trouvé";
        header('Location: vehicules.php');
        exit();
    }

    // Récupérer les images du véhicule
    $stmt = $conn->prepare("SELECT * FROM images WHERE voiture_id = ? ORDER BY is_cover DESC");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur lors de la récupération du véhicule: " . $e->getMessage();
    header('Location: vehicules.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Mise à jour des informations du véhicule
        $stmt = $conn->prepare("
            UPDATE louer 
            SET marque = ?, modele = ?, prix_jour = ?, annee = ?, 
                categorie = ?, disponibilite = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['marque'],
            $_POST['modele'],
            $_POST['prix_jour'],
            $_POST['annee'],
            $_POST['categorie'],
            isset($_POST['disponibilite']) ? 1 : 0,
            $id
        ]);

        // Suppression des images marquées
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $image_id) {
                $stmt = $conn->prepare("SELECT image_url FROM images WHERE id = ? AND voiture_id = ?");
                $stmt->execute([$image_id, $id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($image) {
                    // Supprimer le fichier physique
                    $file_path = '../' . $image['image_url'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }

                    // Supprimer l'entrée de la base de données
                    $stmt = $conn->prepare("DELETE FROM images WHERE id = ?");
                    $stmt->execute([$image_id]);
                }
            }
        }

        // Ajout de nouvelles images
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = '../uploads/vehicules/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($file_ext, $allowed_ext)) {
                    $new_name = uniqid() . '_' . $file_name;
                    $destination = $upload_dir . $new_name;

                    if (move_uploaded_file($tmp_name, $destination)) {
                        // Si pas d'image de couverture, définir celle-ci comme couverture
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM images WHERE voiture_id = ?");
                        $stmt->execute([$id]);
                        $is_cover = ($stmt->fetchColumn() === 0) ? 1 : 0;
                        
                        $stmt = $conn->prepare("
                            INSERT INTO images (voiture_id, image_url, is_cover)
                            VALUES (?, ?, ?)
                        ");
                        $stmt->execute([
                            $id,
                            'uploads/vehicules/' . $new_name,
                            $is_cover
                        ]);
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['success'] = "Véhicule modifié avec succès";
        header('Location: vehicules.php');
        exit();

    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur lors de la modification du véhicule: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Véhicule - AutoWorld Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="vehicules.php" class="flex items-center p-3 text-gray-700 bg-gray-100 rounded-lg">
                    <i class="fas fa-car w-6"></i>
                    <span>Véhicules</span>
                </a>
                <a href="reservations.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-calendar-check w-6"></i>
                    <span>Réservations</span>
                </a>
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
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Modifier le Véhicule</h1>
                    <p class="text-gray-600">
                        <?php echo htmlspecialchars($vehicule['marque'] . ' ' . $vehicule['modele']); ?>
                    </p>
                </div>
                <a href="vehicules.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $_SESSION['error']; ?></p>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Formulaire -->
            <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="marque">
                            Marque
                        </label>
                        <input type="text" id="marque" name="marque" required
                            value="<?php echo htmlspecialchars($vehicule['marque']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="modele">
                            Modèle
                        </label>
                        <input type="text" id="modele" name="modele" required
                            value="<?php echo htmlspecialchars($vehicule['modele']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="prix_jour">
                            Prix par jour (FCFA)
                        </label>
                        <input type="number" id="prix_jour" name="prix_jour" required
                            value="<?php echo htmlspecialchars($vehicule['prix_jour']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="annee">
                            Année
                        </label>
                        <input type="number" id="annee" name="annee" required
                            value="<?php echo htmlspecialchars($vehicule['annee']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="categorie">
                            Catégorie
                        </label>
                        <select id="categorie" name="categorie" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                            <option value="">Sélectionner une catégorie</option>
                            <?php
                            $categories = ['SUV', 'Berline', '4x4', 'Citadine', 'Luxe'];
                            foreach ($categories as $cat) {
                                $selected = ($vehicule['categorie'] === $cat) ? 'selected' : '';
                                echo "<option value=\"$cat\" $selected>$cat</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center space-x-3">
                            <input type="checkbox" name="disponibilite" 
                                <?php echo $vehicule['disponibilite'] ? 'checked' : ''; ?>
                                class="form-checkbox h-5 w-5 text-purple-600">
                            <span class="text-gray-700 font-bold">Disponible</span>
                        </label>
                    </div>
                </div>

                <!-- Images existantes -->
                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Images actuelles
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($images as $image): ?>
                        <div class="relative group">
                            <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" 
                                 class="w-full h-32 object-cover rounded-lg">
                            <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-lg">
                                <label class="flex items-center space-x-2 text-white cursor-pointer">
                                    <input type="checkbox" name="delete_images[]" value="<?php echo $image['id']; ?>"
                                           class="form-checkbox h-5 w-5 text-red-600">
                                    <span>Supprimer</span>
                                </label>
                            </div>
                            <?php if ($image['is_cover']): ?>
                            <span class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                Couverture
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Upload de nouvelles images -->
                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Ajouter de nouvelles images
                    </label>
                    <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="images" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                    <span>Télécharger des fichiers</span>
                                    <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP jusqu'à 10MB</p>
                        </div>
                    </div>
                    <div id="image-preview" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="window.location.href='vehicules.php'"
                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                        <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Prévisualisation des nouvelles images
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        preview.innerHTML = '';
        
        [...e.target.files].forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative aspect-w-16 aspect-h-9';
                div.innerHTML = `
                    <img src="${e.target.result}" class="rounded-lg object-cover w-full h-32">
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    });
    </script>
</body>
</html>
