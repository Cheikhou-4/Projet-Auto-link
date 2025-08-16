TEST CONTACT.PHP
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../includes/db.php';
require_once '../includes/header.php';
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<div style="color:red;background:#fff;padding:8px;">POST reçu</div>';
    file_put_contents('debug_contact.log', "POST: " . print_r($_POST, true), FILE_APPEND);
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (empty($nom) || empty($email) || empty($message)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO messages (nom, email, message) VALUES (?, ?, ?)');
            $stmt->execute([$nom, $email, $message]);
            $success = true;
            file_put_contents('debug_contact.log', "Insertion OK\n", FILE_APPEND);
        } catch (PDOException $e) {
            $error = "Erreur lors de l'envoi du message : " . $e->getMessage();
            file_put_contents('debug_contact.log', "Erreur SQL: " . $e->getMessage() . "\n", FILE_APPEND);
        }
    }
}
?>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Contactez-nous</h1>
    <?php if ($success): ?>
        <div class="alert alert-success text-center">Votre message a bien été envoyé. Nous vous répondrons rapidement.</div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.querySelector('form');
                if(form) form.reset();
            });
        </script>
    <?php else: ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" class="mx-auto" style="max-width:500px;">
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Envoyer</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
<script>
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>
