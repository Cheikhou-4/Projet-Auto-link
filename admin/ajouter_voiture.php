<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $annee = intval($_POST['annee'] ?? 0);
    $prix = floatval($_POST['prix'] ?? 0);
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $vedette = isset($_POST['vedette']) ? 1 : 0;
    $image = '';
    $ville = trim($_POST['ville'] ?? '');
    $categorie = $_POST['categorie'] ?? 'voiture';
    $kilometrage = isset($_POST['kilometrage']) ? intval($_POST['kilometrage']) : null;
    $transmission = trim($_POST['transmission'] ?? '');
    $carburant = trim($_POST['carburant'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    // Vérification des champs
    if (empty($marque) || empty($modele) || !$annee || !$prix || empty($type) || empty($ville) || empty($categorie)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        // Gestion de l'upload de plusieurs images
        $uploadedImages = [];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!empty($_FILES['images']['name'][0])) {
            $target_dir = '../uploads/';
            foreach ($_FILES['images']['name'] as $key => $imgName) {
                $tmpName = $_FILES['images']['tmp_name'][$key];
                $filetype = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
                if (in_array($filetype, $allowed)) {
                    $filename = uniqid() . '_' . basename($imgName);
                    $target_file = $target_dir . $filename;
                    if (move_uploaded_file($tmpName, $target_file)) {
                        $uploadedImages[] = $filename;
                    } else {
                        $error = "Erreur lors de l'upload de l'image : $imgName.";
                        break;
                    }
                } else {
                    $error = "Format d'image non autorisé pour : $imgName.";
                    break;
                }
            }
        }
        // On prend la première image comme image principale
        if (empty($error) && count($uploadedImages) > 0) {
            $image = $uploadedImages[0];
        }
        if (empty($error)) {
            $stmt = $pdo->prepare('INSERT INTO voitures (marque, modele, annee, prix, type, description, image, vedette, ville, categorie, kilometrage, transmission, carburant, prenom, nom, telephone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$marque, $modele, $annee, $prix, $type, $description, $image, $vedette, $ville, $categorie, $kilometrage, $transmission, $carburant, $prenom, $nom, $telephone]);
            $voiture_id = $pdo->lastInsertId();
            // Enregistrer toutes les images dans images_voiture
            if ($voiture_id && count($uploadedImages) > 0) {
                $stmtImg = $pdo->prepare('INSERT INTO images_voiture (voiture_id, image) VALUES (?, ?)');
                foreach ($uploadedImages as $img) {
                    $stmtImg->execute([$voiture_id, $img]);
                }
            }
            $success = true;
            header('Location: voitures.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une voiture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="voitures.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour à la gestion des voitures</a>
        <h2 class="mb-4">Ajouter une voiture</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="marque" class="form-label">Marque *</label>
                <input type="text" class="form-control" id="marque" name="marque" required>
            </div>
                <div class="mb-3">
                    <label for="constructeur" class="form-label">Constructeur *</label>
                    <select class="form-select" id="constructeur" name="constructeur" onchange="afficherLogo()" required>
                        <option value="">-- Choisir --</option>
                        <option value="citroen">Citroën</option>
                        <option value="hyundai">Hyundai</option>
                        <option value="kia">Kia</option>
                        <option value="mazda">Mazda</option>
                        <option value="mitsubishi">Mitsubishi</option>
                        <option value="nissan">Nissan</option>
                        <option value="peugeot">Peugeot</option>
                        <option value="renault">Renault</option>
                        <option value="toyota">Toyota</option>
                        <option value="volkswagen">Volkswagen</option>
                    </select>
                </div>
                <div class="mb-3" id="logo-constructeur" style="display:none;">
                    <label class="form-label">Logo du constructeur :</label><br>
                    <img id="img-logo" src="" alt="Logo constructeur" style="max-height:60px;">
                </div>
    <script>
        function afficherLogo() {
            var select = document.getElementById('constructeur');
            var logoDiv = document.getElementById('logo-constructeur');
            var imgLogo = document.getElementById('img-logo');
            var value = select.value;
            if (value) {
                imgLogo.src = '../public/assets/images/logos/' + value + '.png';
                logoDiv.style.display = 'block';
            } else {
                imgLogo.src = '';
                logoDiv.style.display = 'none';
            }
        }
    </script>
            <div class="mb-3">
                <label for="modele" class="form-label">Modèle *</label>
                <input type="text" class="form-control" id="modele" name="modele" required>
            </div>
            <div class="mb-3">
                <label for="annee" class="form-label">Année *</label>
                <input type="number" class="form-control" id="annee" name="annee" required min="1900" max="2100">
            </div>
            <div class="mb-3">
                <label for="prix" class="form-label">Prix (€) *</label>
                <input type="number" class="form-control" id="prix" name="prix" required min="0" step="0.01">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">-- Choisir --</option>
                    <option value="vente">Vente</option>
                    <option value="location">Location</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ville" class="form-label">Ville *</label>
                <input type="text" class="form-control" id="ville" name="ville" required>
            </div>
            <div class="mb-3">
                <label for="categorie" class="form-label">Catégorie *</label>
                <select class="form-select" id="categorie" name="categorie" required>
                    <option value="">-- Choisir --</option>
                    <option value="voiture">Voiture</option>
                    <option value="camion">Camion</option>
                    <option value="moto">Moto</option>
                    <option value="remorque">Remorque</option>
                    <option value="bus">Bus</option>
                    <option value="utilitaire">Utilitaire</option>
                    <option value="engin">Engin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label">Images (plusieurs possibles)</label>
                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                <small class="text-muted">Vous pouvez sélectionner plusieurs images (JPG, PNG, WEBP).</small>
            </div>
            <div class="mb-3">
                <label for="kilometrage" class="form-label">Kilométrage</label>
                <input type="number" class="form-control" id="kilometrage" name="kilometrage" min="0" step="1">
            </div>
            <div class="mb-3">
                <label for="transmission" class="form-label">Transmission</label>
                <select class="form-select" id="transmission" name="transmission">
                    <option value="">-- Choisir --</option>
                    <option value="Manuelle">Manuelle</option>
                    <option value="Automatique">Automatique</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="carburant" class="form-label">Carburant</label>
                <select class="form-select" id="carburant" name="carburant">
                    <option value="">-- Choisir --</option>
                    <option value="Essence">Essence</option>
                    <option value="Gasoil">Gasoil</option>
                    <option value="Hybride">Hybride</option>
                    <option value="Électrique">Électrique</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom du propriétaire</label>
                <input type="text" class="form-control" id="prenom" name="prenom">
            </div>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du propriétaire</label>
                <input type="text" class="form-control" id="nom" name="nom">
            </div>
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone du propriétaire</label>
                <input type="text" class="form-control" id="telephone" name="telephone">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="vedette" name="vedette">
                <label class="form-check-label" for="vedette">Mettre en vedette</label>
            </div>
            <button type="submit" class="btn btn-success">Ajouter</button>
            <a href="voitures.php" class="btn btn-secondary ms-2">Annuler</a>
        </form>
    </div>
</body>
</html>
