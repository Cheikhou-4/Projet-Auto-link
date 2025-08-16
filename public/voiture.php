<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Vérifier la présence de l'ID dans l'URL
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Identifiant de voiture invalide.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

$id = (int)$_GET['id'];

// Récupérer les infos de la voiture
$stmt = $pdo->prepare('SELECT * FROM voitures WHERE id = ?');
$stmt->execute([$id]);
$voiture = $stmt->fetch();

if (!$voiture) {
    echo '<div class="container mt-4"><div class="alert alert-warning">Voiture non trouvée.</div></div>';
    require_once '../includes/footer.php';
    exit();
}

// Récupérer les images associées à la voiture
$stmtImgs = $pdo->prepare('SELECT image FROM images_voiture WHERE voiture_id = ?');
$stmtImgs->execute([$id]);
$images = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
if (empty($images) && !empty($voiture['image'])) {
    $images = [$voiture['image']];
}
?>
<?php
$og_title = htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele'] . ' à vendre à ' . $voiture['ville'] . ' | AutoLink Sénégal');
$og_desc = !empty($voiture['description']) ? htmlspecialchars(substr($voiture['description'],0,150)) : 'Découvrez ce véhicule sur AutoLink Sénégal.';
$og_img = !empty($images[0]) ? 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . htmlspecialchars($images[0]) : 'https://via.placeholder.com/600x320?text=Pas+de+photo';
$og_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!-- Balises Open Graph pour partage réseaux sociaux -->
<meta property="og:title" content="<?php echo $og_title; ?>" />
<meta property="og:description" content="<?php echo $og_desc; ?>" />
<meta property="og:image" content="<?php echo $og_img; ?>" />
<meta property="og:url" content="<?php echo $og_url; ?>" />
<meta property="og:type" content="article" />
<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?php echo $og_title; ?>" />
<meta name="twitter:description" content="<?php echo $og_desc; ?>" />
<meta name="twitter:image" content="<?php echo $og_img; ?>" />
<div class="container mt-4">
    <a href="#" onclick="history.back(); return false;" class="btn btn-outline-primary mb-3"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    <div class="mx-auto" style="max-width:700px;">
        <!-- Carrousel d'images avancé avec Swiper.js -->
        <?php if (count($images) > 1): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <style>
.thumbsSwiper .swiper-slide-thumb-active img {
  border: 3px solid #ff9800;
  box-shadow: 0 2px 8px rgba(255,152,0,0.15);
  opacity: 1;
}
.thumbsSwiper .swiper-slide img {
  opacity: 0.7;
  transition: border 0.2s, box-shadow 0.2s, opacity 0.2s;
}
</style>
        <div class="mb-3">
          <div class="swiper mainSwiper" style="border-radius:12px;overflow:hidden;">
            <div class="swiper-wrapper">
              <?php foreach ($images as $img): ?>
                <div class="swiper-slide">
                  <img src="<?php echo '../uploads/' . htmlspecialchars($img); ?>" style="width:100%;height:320px;object-fit:cover;display:block;background:#eaeaea;" alt="Image véhicule">
                </div>
              <?php endforeach; ?>
            </div>
            <!-- Flèches -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
          </div>
          <!-- Miniatures -->
          <div class="swiper thumbsSwiper mt-2">
            <div class="swiper-wrapper">
              <?php foreach ($images as $img): ?>
                <div class="swiper-slide" style="height:70px;width:90px;">
                  <img src="<?php echo '../uploads/' . htmlspecialchars($img); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script>
        const thumbsSwiper = new Swiper('.thumbsSwiper', {
          spaceBetween: 8,
          slidesPerView: Math.min(<?php echo count($images); ?>, 7),
          freeMode: true,
          watchSlidesProgress: true,
        });
        const mainSwiper = new Swiper('.mainSwiper', {
          spaceBetween: 10,
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          thumbs: {
            swiper: thumbsSwiper,
          },
          autoplay: {
            delay: 2500,
            disableOnInteraction: false,
          },
        });
        mainSwiper.el.addEventListener('mouseenter', function() {
          mainSwiper.autoplay.stop();
        });
        mainSwiper.el.addEventListener('mouseleave', function() {
          mainSwiper.autoplay.start();
        });
        document.querySelector('.thumbsSwiper').addEventListener('mouseenter', function() {
          mainSwiper.autoplay.stop();
        });
        document.querySelector('.thumbsSwiper').addEventListener('mouseleave', function() {
          mainSwiper.autoplay.start();
        });
        </script>
        <?php else: ?>
        <!-- Image unique -->
        <div class="mb-3">
          <img src="<?php echo !empty($images[0]) ? '../uploads/' . htmlspecialchars($images[0]) : 'https://via.placeholder.com/600x320?text=Pas+de+photo'; ?>" style="width:100%;height:320px;object-fit:cover;display:block;background:#eaeaea;border-radius:12px;" alt="Image véhicule">
        </div>
        <?php endif; ?>
        <!-- Titre, prix, ville/date -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-2">
            <div>
                <h1 class="fw-bold mb-1" style="font-size:2.1rem;line-height:1.1;color:#222;">
                    <?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>
                </h1>
                <div class="d-flex align-items-center gap-3 mb-2">
                </div>
                <div class="text-muted d-flex align-items-center mb-2" style="font-size:1.05rem;">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($voiture['ville']); ?>
                </div>
            </div>
            <div class="text-md-end mt-2 mt-md-0">
                <div class="fw-bold" style="font-size:2.2rem;color:#ff9800;line-height:1.1;">
                    <?php echo number_format($voiture['prix'], 0, ',', ' '); ?> FCFA
                </div>
            </div>
        </div>
        <!-- Caractéristiques principales façon CoinAfrique -->
        <div class="row mb-3 g-2 text-center" style="color:#3a4a5e;">
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;min-height:28px;display:flex;align-items:center;justify-content:center;">
                    <?php
                    $logoPath = '../public/assets/images/logos/' . strtolower($voiture['marque']) . '.png';
                    if (file_exists($logoPath)) {
                        echo '<img src="' . $logoPath . '" alt="Logo ' . htmlspecialchars($voiture['marque']) . '" style="height:28px;width:auto;max-width:36px;object-fit:contain;">';
                    } else {
                        echo '<i class="fas fa-shield-alt"></i>';
                    }
                    ?>
                </div>
                <div style="font-size:0.85rem;">Constructeur</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo htmlspecialchars($voiture['marque']); ?> </div>
            </div>
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;"><i class="fas fa-key"></i></div>
                <div style="font-size:0.85rem;">Modèle</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo htmlspecialchars($voiture['modele']); ?> </div>
            </div>
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;"><i class='fas fa-calendar-alt'></i></div>
                <div style="font-size:0.85rem;">Année</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo isset($voiture['annee']) ? htmlspecialchars($voiture['annee']) : '—'; ?> </div>
            </div>
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;"><i class="fas fa-tachometer-alt"></i></div>
                <div style="font-size:0.85rem;">Kilométrage</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo isset($voiture['kilometrage']) ? htmlspecialchars($voiture['kilometrage']) . ' km' : '—'; ?> </div>
            </div>
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;"><i class="fas fa-cogs"></i></div>
                <div style="font-size:0.85rem;">Transmission</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo isset($voiture['transmission']) ? htmlspecialchars($voiture['transmission']) : '—'; ?> </div>
            </div>
            <div class="col-6 col-md">
                <div style="font-size:1.4rem;"><i class="fas fa-gas-pump"></i></div>
                <div style="font-size:0.85rem;">Carburant</div>
                <div class="fw-bold" style="font-size:0.95rem;"> <?php echo isset($voiture['carburant']) ? htmlspecialchars($voiture['carburant']) : '—'; ?> </div>
            </div>
        </div>
        <!-- Description -->
        <div class="mb-3">
            <h5 class="mb-2">Description</h5>
            <div class="bg-light p-3 rounded" style="font-size:1.05rem;">
                <?php echo nl2br(htmlspecialchars($voiture['description'])); ?>
            </div>
        </div>
        <!-- Boutons d'action -->
        <div class="mb-4 d-flex flex-column flex-md-row gap-2">
            <a href="https://wa.me/221785016571?text=Bonjour, je suis intéressé par la voiture <?php echo urlencode($voiture['marque'] . ' ' . $voiture['modele']); ?> sur AutoLink Sénégal.%0A%0ALien vers la fiche : <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-success btn-lg flex-fill"><i class="fab fa-whatsapp me-2"></i>Contacter sur WhatsApp</a>
            <button type="button" class="btn btn-outline-primary btn-lg flex-fill" data-bs-toggle="modal" data-bs-target="#shareModal"><i class="fas fa-share me-2"></i>Partager</button>
        </div>

        <!-- Modal de partage -->
        <div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Partager cette annonce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body text-center">
                <div class="d-flex flex-wrap justify-content-center gap-3 mb-3">
                  <a href="https://wa.me/?text=<?php echo urlencode('Regarde cette voiture : ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-success" title="WhatsApp"><i class="fab fa-whatsapp fa-lg"></i></a>
                  <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-primary" style="background:#1877f2;border:none;" title="Facebook"><i class="fab fa-facebook-f fa-lg"></i></a>
                  <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-info" style="background:#1da1f2;border:none;" title="Twitter/X"><i class="fab fa-x-twitter fa-lg"></i></a>
                  <a href="https://www.facebook.com/dialog/send?link=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&app_id=YOUR_FB_APP_ID&redirect_uri=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-primary" style="background:#0084ff;border:none;" title="Messenger"><i class="fab fa-facebook-messenger fa-lg"></i></a>
                  <a href="mailto:?subject=Annonce AutoLink Sénégal&body=Regarde cette voiture : <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="btn btn-secondary" title="Email"><i class="fas fa-envelope fa-lg"></i></a>
                  <button class="btn btn-dark" title="Copier le lien" onclick="navigator.clipboard.writeText('<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>');this.innerHTML='<i class=\'fas fa-check\'></i>';"><i class="fas fa-link fa-lg"></i></button>
                </div>
                <div class="text-muted" style="font-size:0.95rem;">Choisissez un réseau social pour partager l'annonce.</div>
              </div>
            </div>
          </div>
        </div>
    </div>
</div>
<?php
// Section Annonces similaires
// Priorité : même catégorie uniquement, hors véhicule courant
$similaires = [];
$stmtSim = $pdo->prepare('SELECT * FROM voitures WHERE id != ? AND categorie = ? ORDER BY date_ajout DESC LIMIT 4');
$stmtSim->execute([$id, $voiture['categorie']]);
$similaires = $stmtSim->fetchAll();
if (count($similaires) > 0): ?>
<div class="container mt-5">
    <h4 class="mb-3">Annonces similaires</h4>
    <div class="row">
        <?php foreach ($similaires as $sim): ?>
            <?php
            $stmtImgs = $pdo->prepare('SELECT image FROM images_voiture WHERE voiture_id = ?');
            $stmtImgs->execute([$sim['id']]);
            $images = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
            if (empty($images) && !empty($sim['image'])) {
                $images = [$sim['image']];
            }
            ?>
            <div class="col-6 col-md-3 mb-3">
                <a href="voiture.php?id=<?php echo $sim['id']; ?>" class="text-decoration-none">
                    <div class="card h-100" style="border-radius:18px;box-shadow:0 4px 16px rgba(30,42,120,0.08);">
                        <div style="width:100%;height:110px;overflow:hidden;background:#eaeaea;border-radius:18px 18px 0 0;">
                            <img src="<?php echo !empty($images[0]) ? '../uploads/' . htmlspecialchars($images[0]) : 'https://via.placeholder.com/400x200?text=Pas+de+photo'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($sim['marque'] . ' ' . $sim['modele']); ?>" style="width:100%;height:110px;object-fit:cover;border-radius:18px 18px 0 0;">
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1" style="color:#ff9800;font-size:0.95rem;font-weight:600;line-height:1.1;">
                                <?php echo htmlspecialchars($sim['marque'] . ' ' . $sim['modele']); ?>
                            </h6>
                            <div class="d-flex align-items-center mb-1" style="font-size:0.85rem;">
                                <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo htmlspecialchars($sim['ville']); ?>
                            </div>
                            <div style="font-size:0.85rem;"><i class="fas fa-money-bill-wave me-1 text-warning"></i> <?php echo number_format($sim['prix'], 0, ',', ' '); ?> FCFA</div>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php require_once '../includes/footer.php'; ?>
<script>
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>
