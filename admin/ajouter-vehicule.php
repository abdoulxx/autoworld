<?php
session_start();
require_once 'cnx.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $required_fields = ['marque', 'modele', 'prix_jour', 'annee', 'categorie'];
        $error = false;
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = "Le champ $field est requis";
                $error = true;
                break;
            }
        }

        if (!$error) {
            $conn->beginTransaction();

            // Insertion du véhicule
            $stmt = $conn->prepare("
                INSERT INTO louer (marque, modele, prix_jour, annee, categorie, disponibilite)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $_POST['marque'],
                $_POST['modele'],
                $_POST['prix_jour'],
                $_POST['annee'],
                $_POST['categorie']
            ]);
            
            $vehicule_id = $conn->lastInsertId();

            // Traitement des images
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
                            // Première image comme couverture
                            $is_cover = ($key === 0) ? 1 : 0;
                            
                            $stmt = $conn->prepare("
                                INSERT INTO images (voiture_id, image_url, is_cover)
                                VALUES (?, ?, ?)
                            ");
                            $stmt->execute([
                                $vehicule_id,
                                'uploads/vehicules/' . $new_name,
                                $is_cover
                            ]);
                        }
                    }
                }
            }

            $conn->commit();
            $_SESSION['success'] = "Véhicule ajouté avec succès";
            header('Location: vehicules.php');
            exit();
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur lors de l'ajout du véhicule: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Véhicule - AutoWorld Admin</title>
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
                    <h1 class="text-2xl font-bold text-gray-800">Ajouter un Véhicule</h1>
                    <p class="text-gray-600">Remplissez le formulaire pour ajouter un nouveau véhicule</p>
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                            placeholder="Ex: Toyota">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="modele">
                            Modèle
                        </label>
                        <input type="text" id="modele" name="modele" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                            placeholder="Ex: Camry">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="prix_jour">
                            Prix par jour (FCFA)
                        </label>
                        <input type="number" id="prix_jour" name="prix_jour" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                            placeholder="Ex: 50000">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="annee">
                            Année
                        </label>
                        <input type="number" id="annee" name="annee" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500"
                            placeholder="Ex: 2023">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="categorie">
                            Catégorie
                        </label>
                        <select id="categorie" name="categorie" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
                            <option value="">Sélectionner une catégorie</option>
                            <option value="SUV">SUV</option>
                            <option value="Berline">Berline</option>
                            <option value="4x4">4x4</option>
                            <option value="Citadine">Citadine</option>
                            <option value="Luxe">Luxe</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Images
                        <span class="text-gray-500 font-normal">(La première image sera la couverture)</span>
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
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Prévisualisation des images
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
