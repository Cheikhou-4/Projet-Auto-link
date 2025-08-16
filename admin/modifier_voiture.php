<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

// Vérifier l'ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: voitures.php');
    exit();
}
$id = (int)$_GET['id'];

// Récupérer la voiture
$stmt = $pdo->prepare('SELECT * FROM voitures WHERE id = ?');
$stmt->execute([$id]);
$voiture = $stmt->fetch();
if (!$voiture) {
    header('Location: voitures.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marque = trim($_POST['marque'] ?? '');
    $modele = trim($_POST['modele'] ?? '');
    $annee = intval($_POST['annee'] ?? 0);
    $prix = floatval($_POST['prix'] ?? 0);
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $vedette = isset($_POST['vedette']) ? 1 : 0;
    $ville = trim($_POST['ville'] ?? $voiture['ville']);
    $categorie = $_POST['categorie'] ?? $voiture['categorie'];
    $image = $voiture['image'];
    $kilometrage = isset($_POST['kilometrage']) ? intval($_POST['kilometrage']) : null;
    $transmission = trim($_POST['transmission'] ?? '');
    $carburant = trim($_POST['carburant'] ?? '');
    $prenom = trim($_POST['prenom'] ?? $voiture['prenom']);
    $nom = trim($_POST['nom'] ?? $voiture['nom']);
    $telephone = trim($_POST['telephone'] ?? $voiture['telephone']);

    // Vérification des champs
    if (empty($marque) || empty($modele) || !$annee || !$prix || empty($type) || empty($ville) || empty($categorie)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        // 1. Suppression des images cochées
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imgId) {
                $stmtDel = $pdo->prepare('SELECT image FROM images_voiture WHERE id = ? AND voiture_id = ?');
                $stmtDel->execute([$imgId, $id]);
                $imgToDelete = $stmtDel->fetchColumn();
                if ($imgToDelete && file_exists('../uploads/' . $imgToDelete)) {
                    unlink('../uploads/' . $imgToDelete);
                }
                $pdo->prepare('DELETE FROM images_voiture WHERE id = ? AND voiture_id = ?')->execute([$imgId, $id]);
            }
        }
        // 2. Upload de nouvelles images
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
                        $pdo->prepare('INSERT INTO images_voiture (voiture_id, image) VALUES (?, ?)')->execute([$id, $filename]);
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
        // 3. Mettre à jour l'image principale (table voitures)
        // On prend la première image restante dans images_voiture
        $stmtImgs = $pdo->prepare('SELECT image FROM images_voiture WHERE voiture_id = ? ORDER BY id ASC');
        $stmtImgs->execute([$id]);
        $allImages = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($allImages)) {
            $image = $allImages[0];
        } else {
            $image = '';
        }
        if (empty($error)) {
            $stmt = $pdo->prepare('UPDATE voitures SET marque=?, modele=?, annee=?, prix=?, type=?, description=?, image=?, vedette=?, ville=?, categorie=?, kilometrage=?, transmission=?, carburant=?, prenom=?, nom=?, telephone=? WHERE id=?');
            $stmt->execute([$marque, $modele, $annee, $prix, $type, $description, $image, $vedette, $ville, $categorie, $kilometrage, $transmission, $carburant, $prenom, $nom, $telephone, $id]);
            header('Location: voitures.php');
            exit();
        }
    }
}

// Récupérer les images associées à la voiture
$stmtImgs = $pdo->prepare('SELECT id, image FROM images_voiture WHERE voiture_id = ?');
$stmtImgs->execute([$id]);
$images = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une voiture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="voitures.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour à la gestion des voitures</a>
        <h2 class="mb-4">Modifier la voiture</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="marque" class="form-label">Marque *</label>
                <input type="text" class="form-control" id="marque" name="marque" required value="<?php echo htmlspecialchars($voiture['marque']); ?>">
            </div>
            <div class="mb-3">
                <label for="modele" class="form-label">Modèle *</label>
                <input type="text" class="form-control" id="modele" name="modele" required value="<?php echo htmlspecialchars($voiture['modele']); ?>">
            </div>
            <div class="mb-3">
                <label for="annee" class="form-label">Année *</label>
                <input type="number" class="form-control" id="annee" name="annee" required min="1900" max="2100" value="<?php echo htmlspecialchars($voiture['annee']); ?>">
            </div>
            <div class="mb-3">
                <label for="prix" class="form-label">Prix (€) *</label>
                <input type="number" class="form-control" id="prix" name="prix" required min="0" step="0.01" value="<?php echo htmlspecialchars($voiture['prix']); ?>">
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type *</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">-- Choisir --</option>
                    <option value="vente" <?php if ($voiture['type'] === 'vente') echo 'selected'; ?>>Vente</option>
                    <option value="location" <?php if ($voiture['type'] === 'location') echo 'selected'; ?>>Location</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ville" class="form-label">Ville *</label>
                <input type="text" class="form-control" id="ville" name="ville" required value="<?php echo isset($voiture['ville']) ? htmlspecialchars($voiture['ville']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="categorie" class="form-label">Catégorie *</label>
                <select class="form-select" id="categorie" name="categorie" required>
                    <option value="">-- Choisir --</option>
                    <option value="voiture" <?php if ($voiture['categorie'] === 'voiture') echo 'selected'; ?>>Voiture</option>
                    <option value="camion" <?php if ($voiture['categorie'] === 'camion') echo 'selected'; ?>>Camion</option>
                    <option value="moto" <?php if ($voiture['categorie'] === 'moto') echo 'selected'; ?>>Moto</option>
                    <option value="remorque" <?php if ($voiture['categorie'] === 'remorque') echo 'selected'; ?>>Remorque</option>
                    <option value="bus" <?php if ($voiture['categorie'] === 'bus') echo 'selected'; ?>>Bus</option>
                    <option value="utilitaire" <?php if ($voiture['categorie'] === 'utilitaire') echo 'selected'; ?>>Utilitaire</option>
                    <option value="engin" <?php if ($voiture['categorie'] === 'engin') echo 'selected'; ?>>Engin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($voiture['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image (laisser vide pour ne pas changer)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <?php if (!empty($voiture['image'])): ?>
                    <img src="<?php echo '../uploads/' . htmlspecialchars($voiture['image']); ?>" alt="Image actuelle" class="mt-2" style="max-width:150px;">
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="images" class="form-label">Ajouter des images (plusieurs possibles)</label>
                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                <small class="text-muted">Vous pouvez sélectionner plusieurs images (JPG, PNG, WEBP).</small>
            </div>
            <?php if (!empty($images)): ?>
            <div class="mb-3">
                <label class="form-label">Images existantes</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($images as $img): ?>
                        <div class="position-relative" style="display:inline-block;">
                            <img src="<?php echo '../uploads/' . htmlspecialchars($img['image']); ?>" alt="Image" style="max-width:90px;max-height:90px;border-radius:6px;">
                            <label class="form-check-label position-absolute top-0 end-0" style="background:rgba(0,0,0,0.6);padding:2px 6px;border-radius:0 6px 0 6px;">
                                <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>"> <span style="color:#fff;font-size:0.8em;">Supprimer</span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="kilometrage" class="form-label">Kilométrage</label>
                <input type="number" class="form-control" id="kilometrage" name="kilometrage" min="0" step="1" value="<?php echo isset($voiture['kilometrage']) ? htmlspecialchars($voiture['kilometrage']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="transmission" class="form-label">Transmission</label>
                <select class="form-select" id="transmission" name="transmission">
                    <option value="">-- Choisir --</option>
                    <option value="Manuelle" <?php if (($voiture['transmission'] ?? '') === 'Manuelle') echo 'selected'; ?>>Manuelle</option>
                    <option value="Automatique" <?php if (($voiture['transmission'] ?? '') === 'Automatique') echo 'selected'; ?>>Automatique</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="carburant" class="form-label">Carburant</label>
                <select class="form-select" id="carburant" name="carburant">
                    <option value="">-- Choisir --</option>
                    <option value="Essence" <?php if (($voiture['carburant'] ?? '') === 'Essence') echo 'selected'; ?>>Essence</option>
                    <option value="Gasoil" <?php if (($voiture['carburant'] ?? '') === 'Gasoil') echo 'selected'; ?>>Gasoil</option>
                    <option value="Hybride" <?php if (($voiture['carburant'] ?? '') === 'Hybride') echo 'selected'; ?>>Hybride</option>
                    <option value="Électrique" <?php if (($voiture['carburant'] ?? '') === 'Électrique') echo 'selected'; ?>>Électrique</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom du propriétaire</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($voiture['prenom'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du propriétaire</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($voiture['nom'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone du propriétaire</label>
                <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($voiture['telephone'] ?? ''); ?>">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="vedette" name="vedette" <?php if ($voiture['vedette']) echo 'checked'; ?>>
                <label class="form-check-label" for="vedette">Mettre en vedette</label>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="voitures.php" class="btn btn-secondary ms-2">Annuler</a>
        </form>
    </div>
</body>
</html>
