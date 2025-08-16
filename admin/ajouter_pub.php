<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['is_superadmin']) || $_SESSION['is_superadmin'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// Initialisation
$error = '';
$success = '';
$is_edit = false;
$pub = null;
$medias_pub = [];

// Si modification
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $is_edit = true;
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM publicites WHERE id = ?');
    $stmt->execute([$id]);
    $pub = $stmt->fetch();
    if ($pub) {
        $stmt2 = $pdo->prepare('SELECT * FROM medias_pub WHERE pub_id = ?');
        $stmt2->execute([$id]);
        $medias_pub = $stmt2->fetchAll();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $lien = trim($_POST['lien'] ?? '');
    $date_debut = !empty($_POST['date_debut']) ? $_POST['date_debut'] : null;
    $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;

    if ($is_edit && $pub) {
        // Modification
        $stmt = $pdo->prepare('UPDATE publicites SET titre=?, description=?, date_debut=?, date_fin=?, lien=? WHERE id=?');
        $stmt->execute([$titre, $description, $date_debut, $date_fin, $lien, $id]);
        // Ajout nouveaux fichiers
        if (!empty($_FILES['fichiers']['name'][0])) {
            $allowed_img = ['jpg','jpeg','png','webp'];
            $allowed_vid = ['mp4','webm','ogg'];
            $target_dir = '../public/uploads/pubs/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            foreach ($_FILES['fichiers']['name'] as $i => $name) {
                if (empty($name)) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $tmp_name = $_FILES['fichiers']['tmp_name'][$i];
                $filename = uniqid() . '_' . basename($name);
                $target_file = $target_dir . $filename;
                if (in_array($ext, $allowed_img)) {
                    $type = 'image';
                } elseif (in_array($ext, $allowed_vid)) {
                    $type = 'video';
                } else {
                    $error = "Format de fichier non autorisé : $name";
                    break;
                }
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $stmt2 = $pdo->prepare('INSERT INTO medias_pub (pub_id, type, fichier) VALUES (?, ?, ?)');
                    $stmt2->execute([$id, $type, $filename]);
                } else {
                    $error = "Erreur lors de l'upload du fichier : $name";
                    break;
                }
            }
        }
        $success = "Publicité modifiée avec succès !";
        // Recharger les infos
        $stmt = $pdo->prepare('SELECT * FROM publicites WHERE id = ?');
        $stmt->execute([$id]);
        $pub = $stmt->fetch();
        $stmt2 = $pdo->prepare('SELECT * FROM medias_pub WHERE pub_id = ?');
        $stmt2->execute([$id]);
        $medias_pub = $stmt2->fetchAll();
    } else {
        // Création
        if (empty($_FILES['fichiers']['name'][0])) {
            $error = "Au moins un fichier (image ou vidéo) est obligatoire.";
        } else {
            $allowed_img = ['jpg','jpeg','png','webp'];
            $allowed_vid = ['mp4','webm','ogg'];
            $target_dir = '../public/uploads/pubs/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $medias = [];
            foreach ($_FILES['fichiers']['name'] as $i => $name) {
                if (empty($name)) continue;
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $tmp_name = $_FILES['fichiers']['tmp_name'][$i];
                $filename = uniqid() . '_' . basename($name);
                $target_file = $target_dir . $filename;
                if (in_array($ext, $allowed_img)) {
                    $type = 'image';
                } elseif (in_array($ext, $allowed_vid)) {
                    $type = 'video';
                } else {
                    $error = "Format de fichier non autorisé : $name";
                    break;
                }
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $medias[] = ['type' => $type, 'fichier' => $filename];
                } else {
                    $error = "Erreur lors de l'upload du fichier : $name";
                    break;
                }
            }
            if (empty($error)) {
                $stmt = $pdo->prepare('INSERT INTO publicites (titre, description, date_debut, date_fin, lien) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$titre, $description, $date_debut, $date_fin, $lien]);
                $pub_id = $pdo->lastInsertId();
                foreach ($medias as $media) {
                    $stmt2 = $pdo->prepare('INSERT INTO medias_pub (pub_id, type, fichier) VALUES (?, ?, ?)');
                    $stmt2->execute([$pub_id, $media['type'], $media['fichier']]);
                }
                $success = "Publicité ajoutée avec succès !";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une publicité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:500px;">
    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
    <div class="card">
        <div class="card-body">
            <h3 class="mb-3">Ajouter une publicité</h3>
            <?php if ($success): ?>
                <div class="alert alert-success text-center"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fichiers" class="form-label">Fichiers (images ou vidéos)<?php if (!$is_edit): ?> *<?php endif; ?></label>
                    <input type="file" class="form-control" id="fichiers" name="fichiers[]" accept="image/*,video/*" multiple <?php if (!$is_edit): ?>required<?php endif; ?>>
                </div>
                <?php if ($is_edit && $medias_pub): ?>
                <div class="mb-3">
                    <label class="form-label">Médias existants :</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($medias_pub as $media): ?>
                            <?php if ($media['type'] === 'image'): ?>
                                <img src="../public/uploads/pubs/<?php echo htmlspecialchars($media['fichier']); ?>" style="max-width:80px;max-height:60px;object-fit:cover;">
                            <?php elseif ($media['type'] === 'video'): ?>
                                <video style="max-width:80px;max-height:60px;" muted>
                                    <source src="../public/uploads/pubs/<?php echo htmlspecialchars($media['fichier']); ?>">
                                </video>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" class="form-control" id="titre" name="titre" value="<?php echo $is_edit && $pub ? htmlspecialchars($pub['titre']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?php echo $is_edit && $pub ? htmlspecialchars($pub['description']) : ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="lien" class="form-label">Lien (URL de redirection si on clique sur la pub)</label>
                    <input type="url" class="form-control" id="lien" name="lien" placeholder="https://..." value="<?php echo $is_edit && $pub ? htmlspecialchars($pub['lien']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="date_debut" class="form-label">Date de début d'affichage</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="<?php echo $is_edit && $pub ? htmlspecialchars($pub['date_debut']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label for="date_fin" class="form-label">Date de fin d'affichage</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin" value="<?php echo $is_edit && $pub ? htmlspecialchars($pub['date_fin']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-warning"><?php echo $is_edit ? 'Modifier la pub' : 'Ajouter la pub'; ?></button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
</div>
</body>
</html> 