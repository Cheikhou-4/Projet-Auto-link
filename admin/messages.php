<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
require_once '../includes/db.php';

// Vérifier si la table messages existe
$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'messages'");
    $tableExists = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $tableExists = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages reçus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour au dashboard</a>
        <h2 class="mb-4">Messages reçus</h2>
        <?php if (!$tableExists): ?>
            <div class="alert alert-info">La table <b>messages</b> n'existe pas encore. Les messages du formulaire de contact ne sont pas enregistrés en base.</div>
        <?php else: ?>
            <?php
            $stmt = $pdo->query('SELECT * FROM messages ORDER BY date_envoi DESC');
            $messages = $stmt->fetchAll();
            ?>
            <div class="mb-2 text-muted">Nombre de messages trouvés : <b><?php echo count($messages); ?></b></div>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query('SELECT * FROM messages ORDER BY date_envoi DESC');
                    $messages = $stmt->fetchAll();
                    if (count($messages) > 0):
                        foreach ($messages as $msg): ?>
                            <tr>
                                <td><?php echo $msg['id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['nom']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                <td><?php echo htmlspecialchars($msg['date_envoi']); ?></td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr><td colspan="5" class="text-center">Aucun message reçu.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
