<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['is_superadmin']) || $_SESSION['is_superadmin'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// Suppression d'une pub
$success = '';
if (isset($_GET['supprimer']) && ctype_digit($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare('SELECT fichier FROM publicites WHERE id = ?');
    $stmt->execute([$id]);
    $fichier = $stmt->fetchColumn();
    if ($fichier && file_exists('../public/uploads/pubs/' . $fichier)) {
        unlink('../public/uploads/pubs/' . $fichier);
    }
    $pdo->prepare('DELETE FROM publicites WHERE id = ?')->execute([$id]);
    $success = 'La publicité a bien été supprimée.';
    header('Location: pubs.php?success=1');
    exit();
}
if (isset($_GET['success'])) {
    $success = 'La publicité a bien été supprimée.';
}
$pubs = $pdo->query('SELECT * FROM publicites ORDER BY date_ajout DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des publicités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des publicités</h2>
        <a href="ajouter_pub.php" class="btn btn-warning"><i class="fas fa-plus me-2"></i>Ajouter une pub</a>
    </div>
    <div class="mb-3">
        <input type="text" id="searchPubInput" class="form-control" placeholder="Rechercher une pub (titre, description, dates, lien...)">
    </div>
    <?php if ($success): ?>
        <div class="alert alert-success text-center"><?php echo $success; ?></div>
    <?php endif; ?>
    <table class="table table-bordered table-striped table-sm align-middle" style="font-size:0.95rem;">
        <thead class="table-dark">
            <tr>
                <th>Type</th>
                <th>Média</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Lien</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pubs as $pub): ?>
            <tr>
                <td><?php echo htmlspecialchars($pub['type']); ?></td>
                <td>
                    <?php if ($pub['type'] === 'image'): ?>
                        <img src="<?php echo '../public/uploads/pubs/' . htmlspecialchars($pub['fichier']); ?>" alt="Pub" style="max-width:90px;max-height:60px;object-fit:cover;">
                    <?php elseif ($pub['type'] === 'video'): ?>
                        <video style="max-width:90px;max-height:60px;" muted>
                            <source src="<?php echo '../public/uploads/pubs/' . htmlspecialchars($pub['fichier']); ?>">
                        </video>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($pub['titre']); ?></td>
                <td><?php echo htmlspecialchars($pub['description']); ?></td>
                <td><?php echo htmlspecialchars($pub['date_debut']); ?></td>
                <td><?php echo htmlspecialchars($pub['date_fin']); ?></td>
                <td><?php if (!empty($pub['lien'])): ?><a href="<?php echo htmlspecialchars($pub['lien']); ?>" target="_blank">Lien</a><?php endif; ?></td>
                <td>
                    <a href="ajouter_pub.php?id=<?php echo $pub['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="pubs.php?supprimer=<?php echo $pub['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette pub ?');"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
// Barre de recherche dynamique
    document.getElementById('searchPubInput').addEventListener('keyup', function() {
        var value = this.value.toLowerCase();
        var rows = document.querySelectorAll('table tbody tr');
        rows.forEach(function(row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(value) > -1 ? '' : 'none';
        });
    });
</script>
</body>
</html> 