<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['is_superadmin']) || $_SESSION['is_superadmin'] != 1) {
    header('Location: dashboard.php');
    exit();
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: admins.php');
    exit();
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$id]);
$admin = $stmt->fetch();
if (!$admin) {
    header('Location: admins.php');
    exit();
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (empty($username) || empty($email)) {
        $error = "Identifiant et email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (!empty($password) && $password !== $password2) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier unicité (hors lui-même)
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE (email = ? OR username = ?) AND id != ?');
        $stmt->execute([$email, $username, $id]);
        if ($stmt->fetch()) {
            $error = "Cet email ou identifiant existe déjà.";
        } else {
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE admins SET username=?, email=?, password=? WHERE id=?');
                $stmt->execute([$username, $email, $hash, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE admins SET username=?, email=? WHERE id=?');
                $stmt->execute([$username, $email, $id]);
            }
            $success = "Administrateur modifié avec succès.";
            // Rafraîchir les infos si c'est soi-même
            if ($admin['id'] == $_SESSION['admin_id']) {
                $_SESSION['admin_username'] = $username;
            }
            // Recharger les infos
            $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
            $stmt->execute([$id]);
            $admin = $stmt->fetch();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:500px;">
    <a href="admins.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour à la gestion des administrateurs</a>
    <div class="card">
        <div class="card-body">
            <h3 class="mb-3">Modifier l'administrateur</h3>
            <?php if ($success): ?>
                <div class="alert alert-success text-center"><?php echo $success; ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger text-center"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Identifiant *</label>
                    <input type="text" class="form-control" id="username" name="username" required value="<?php echo htmlspecialchars($admin['username']); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Nouveau mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Laisser vide pour ne pas changer">
                </div>
                <div class="mb-3">
                    <label for="password2" class="form-label">Confirmation du mot de passe</label>
                    <input type="password" class="form-control" id="password2" name="password2" placeholder="Laisser vide pour ne pas changer">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="admins.php" class="btn btn-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
</div>
</body>
</html> 