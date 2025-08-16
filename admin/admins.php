<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['is_superadmin']) || $_SESSION['is_superadmin'] != 1) {
    header('Location: dashboard.php');
    exit();
}

// Ajout d'un admin
$add_success = '';
$add_error = '';
if (isset($_POST['add_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (empty($username) || empty($email) || empty($password) || empty($password2)) {
        $add_error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $add_error = "Email invalide.";
    } elseif ($password !== $password2) {
        $add_error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier unicité
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $add_error = "Cet email ou identifiant existe déjà.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admins (username, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            $add_success = "Nouvel administrateur ajouté !";
        }
    }
}

// Suppression d'un admin (sauf soi-même)
$del_success = '';
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // On ne peut pas se supprimer soi-même
    $stmt = $pdo->prepare('SELECT username FROM admins WHERE id = ?');
    $stmt->execute([$id]);
    $toDelete = $stmt->fetchColumn();
    if ($toDelete && $toDelete !== $_SESSION['admin_username']) {
        $pdo->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
        $del_success = "Administrateur supprimé.";
    }
}

// Liste des admins
$admins = $pdo->query('SELECT * FROM admins ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des administrateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
    <h2 class="mb-4">Gestion des administrateurs</h2>
    <?php if ($add_success): ?>
        <div class="alert alert-success text-center"><?php echo $add_success; ?></div>
    <?php elseif ($add_error): ?>
        <div class="alert alert-danger text-center"><?php echo $add_error; ?></div>
    <?php endif; ?>
    <?php if ($del_success): ?>
        <div class="alert alert-success text-center"><?php echo $del_success; ?></div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Ajouter un administrateur</h5>
            <form method="post" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="username" class="form-label">Identifiant *</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="col-md-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="col-md-2">
                    <label for="password" class="form-label">Mot de passe *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-md-2">
                    <label for="password2" class="form-label">Confirmation *</label>
                    <input type="password" class="form-control" id="password2" name="password2" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="add_admin" class="btn btn-success w-100"><i class="fas fa-user-plus me-2"></i>Ajouter</button>
                </div>
            </form>
        </div>
    </div>
    <div class="mb-3">
        <input type="text" id="searchAdminInput" class="form-control" placeholder="Rechercher un administrateur (identifiant, email...)">
    </div>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Identifiant</th>
                <th>Email</th>
                <th>Date création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo $admin['id']; ?></td>
                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
                    <td>
                        <?php if ($admin['username'] !== $_SESSION['admin_username']): ?>
                            <a href="modifier_admin.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
                            <a href="admins.php?delete=<?php echo $admin['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet administrateur ?');"><i class="fas fa-trash-alt"></i></a>
                        <?php else: ?>
                            <span class="badge bg-secondary">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
</div>
<script>
    // Barre de recherche dynamique pour les admins
    document.getElementById('searchAdminInput').addEventListener('keyup', function() {
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