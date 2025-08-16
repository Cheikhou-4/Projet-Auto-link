<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

// Suppression d'une voiture
$success = '';
if (isset($_GET['supprimer']) && ctype_digit($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $stmt = $pdo->prepare('DELETE FROM voitures WHERE id = ?');
    $stmt->execute([$id]);
    $success = 'La voiture a bien été supprimée.';
    // Redirection pour éviter la resoumission
    header('Location: voitures.php?success=1');
    exit();
}
if (isset($_GET['success'])) {
    $success = 'La voiture a bien été supprimée.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
    function confirmDelete(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette voiture ? Cette action est irréversible.')) {
            window.location = 'voitures.php?supprimer=' + id;
        }
        return false;
    }
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des voitures</h2>
            <a href="ajouter_voiture.php" class="btn btn-success"><i class="fas fa-plus me-2"></i>Ajouter une voiture</a>
        </div>
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher (propriétaire, marque, modèle, téléphone...)">
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success text-center"><?php echo $success; ?></div>
        <?php endif; ?>
        <table class="table table-bordered table-striped table-sm align-middle" style="font-size:0.93rem;">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Marque</th>
                    <th>Modèle</th>
                    <th>Année</th>
                    <th>Kilométrage</th>
                    <th>Transmission</th>
                    <th>Carburant</th>
                    <th>Prix (FCFA)</th>
                    <th>Type</th>
                    <th>Vedette</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query('SELECT * FROM voitures ORDER BY date_ajout DESC');
                $voitures = $stmt->fetchAll();
                if (count($voitures) > 0):
                    foreach ($voitures as $voiture): ?>
                        <tr>
                            <td><img src="<?php echo !empty($voiture['image']) ? '../uploads/' . htmlspecialchars($voiture['image']) : 'https://via.placeholder.com/80x40?text=Pas+de+photo'; ?>" width="56" height="32" style="object-fit:cover;border-radius:4px;"></td>
                            <td><?php echo htmlspecialchars($voiture['prenom'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($voiture['nom'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($voiture['telephone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($voiture['marque']); ?></td>
                            <td><?php echo htmlspecialchars($voiture['modele']); ?></td>
                            <td><?php echo htmlspecialchars($voiture['annee']); ?></td>
                            <td><?php echo isset($voiture['kilometrage']) ? htmlspecialchars($voiture['kilometrage']) . ' km' : '—'; ?></td>
                            <td><?php echo isset($voiture['transmission']) ? htmlspecialchars($voiture['transmission']) : '—'; ?></td>
                            <td><?php echo isset($voiture['carburant']) ? htmlspecialchars($voiture['carburant']) : '—'; ?></td>
                            <td><?php echo number_format($voiture['prix'], 0, ',', ' '); ?></td>
                            <td><?php echo htmlspecialchars($voiture['type']); ?></td>
                            <td><?php echo $voiture['vedette'] ? '<i class=\'fas fa-star text-warning\'></i>' : 'Non'; ?></td>
                            <td>
                                <a href="modifier_voiture.php?id=<?php echo $voiture['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-danger" onclick="return confirmDelete(<?php echo $voiture['id']; ?>)"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr><td colspan="9" class="text-center">Aucune voiture trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>
        // Barre de recherche dynamique
        document.getElementById('searchInput').addEventListener('keyup', function() {
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
