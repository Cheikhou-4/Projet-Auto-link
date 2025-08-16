<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="mb-4">Tableau de bord Admin</h2>
                        <a href="voitures.php" class="btn btn-primary w-100 mb-3">Gérer les voitures</a>
                        <a href="admins.php" class="btn btn-info w-100 mb-3">Gérer les administrateurs</a>
                        <a href="messages.php" class="btn btn-secondary w-100 mb-3">Voir les messages</a>
                        <a href="ajouter_pub.php" class="btn btn-warning w-100 mb-3">Ajouter pub</a>
                        <a href="pubs.php" class="btn btn-dark w-100 mb-3">Gérer les pubs</a>
                        <a href="logout.php" class="btn btn-danger w-100">Déconnexion</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
