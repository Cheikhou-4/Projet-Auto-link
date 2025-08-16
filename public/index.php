
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../includes/db.php';
require_once '../includes/header.php';

// Préparation des variables pour l'include
$marque = $_GET['marque'] ?? '';
$type = $_GET['type'] ?? '';
$ville = $_GET['ville'] ?? '';
$search = $_GET['search'] ?? '';
$tri = $_GET['tri'] ?? 'recent';
$year_min = $_GET['year_min'] ?? '';
$year_max = $_GET['year_max'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

$today = date('Y-m-d');
$pubs = $pdo->query("SELECT * FROM publicites WHERE (date_debut IS NULL OR date_debut <= '$today') AND (date_fin IS NULL OR date_fin >= '$today') ORDER BY date_ajout DESC")->fetchAll();
foreach ($pubs as &$pub) {
    $stmt = $pdo->prepare('SELECT * FROM medias_pub WHERE pub_id = ?');
    $stmt->execute([$pub['id']]);
    $pub['medias'] = $stmt->fetchAll();
}
unset($pub);
$queryVedette = 'SELECT * FROM voitures WHERE vedette = 1 ORDER BY date_ajout DESC LIMIT 5';
$stmtVedette = $pdo->prepare($queryVedette);
$stmtVedette->execute();
$voituresVedette = $stmtVedette->fetchAll();

// Logique harmonisée avec voitures.php
$query = 'SELECT * FROM voitures WHERE 1';
$params = [];
if ($search !== '') {
    $query .= ' AND (
        LOWER(marque) LIKE LOWER(?) OR
        LOWER(modele) LIKE LOWER(?) OR
        LOWER(description) LIKE LOWER(?) OR
        LOWER(annee) LIKE LOWER(?) OR
        LOWER(prix) LIKE LOWER(?) OR
        LOWER(ville) LIKE LOWER(?) OR
        LOWER(type) LIKE LOWER(?)
    )';
    $searchParam = "%$search%";
    for ($i = 0; $i < 7; $i++) {
        $params[] = $searchParam;
    }
} else {
    if ($marque !== '') {
        $query .= ' AND marque = ?';
        $params[] = $marque;
    }
    if ($type !== '') {
        $query .= ' AND type = ?';
        $params[] = $type;
    }
    if ($ville !== '') {
        $query .= ' AND ville = ?';
        $params[] = $ville;
    }
    if (isset($_GET['annee']) && $_GET['annee'] !== '') {
        $query .= ' AND annee = ?';
        $params[] = $_GET['annee'];
    } else {
        if ($year_min !== '') {
            $query .= ' AND annee >= ?';
            $params[] = $year_min;
        }
        if ($year_max !== '') {
            $query .= ' AND annee <= ?';
            $params[] = $year_max;
        }
    }
    if ($price_min !== '') {
        $query .= ' AND prix >= ?';
        $params[] = $price_min;
    }
    if ($price_max !== '') {
        $query .= ' AND prix <= ?';
        $params[] = $price_max;
    }
}
if ($tri === 'prix_asc') {
    $query .= ' ORDER BY prix ASC';
} elseif ($tri === 'prix_desc') {
    $query .= ' ORDER BY prix DESC';
} else {
    $query .= ' ORDER BY date_ajout DESC';
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$voitures = $stmt->fetchAll();
$marques = $pdo->query('SELECT DISTINCT marque FROM voitures ORDER BY marque')->fetchAll(PDO::FETCH_COLUMN);
$villes = $pdo->query('SELECT DISTINCT ville FROM voitures WHERE ville IS NOT NULL AND ville != "" ORDER BY ville')->fetchAll(PDO::FETCH_COLUMN);

?>

<!-- SECTION VEDETTES EN HAUT -->
<?php if ($search === '' && count($voituresVedette) > 0): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <section class="vedette-section mb-4">
        <div class="container">
            <h2 class="mb-3 text-center" style="font-size:1.3rem;">Voitures vedettes</h2>
            <div class="swiper vedetteSwiper rounded shadow-sm bg-white p-3 mb-3" style="max-width:100%;margin:auto;">
                <div class="swiper-wrapper">
                    <?php foreach ($voituresVedette as $voiture): ?>
                        <?php $isNew = (strtotime($voiture['date_ajout']) >= strtotime('-7 days')); ?>
                        <div class="swiper-slide">
                            <div class="featured-card flex-shrink-0 me-3" data-category="<?php echo htmlspecialchars($voiture['categorie'] ?? 'voiture'); ?>">
                                <div class="featured-image-container">
                                    <img src="<?php echo !empty($voiture['image']) ? '../uploads/' . rawurlencode($voiture['image']) : '../public/assets/images/logo.png'; ?>" class="featured-image" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>">
                                    <span class="badge badge-vedette"><i class="fas fa-star"></i> Vedette</span>
                                    <?php if ($isNew): ?>
                                        <span class="badge badge-new"><i class="fas fa-bolt"></i> Nouveau</span>
                                    <?php endif; ?>
                                    <span class="badge badge-type badge-<?php echo $voiture['type']; ?>"><?php echo ucfirst($voiture['type']); ?></span>
                                </div>
                                <div class="featured-content p-2">
                                    <h3 class="featured-title mb-1" style="font-size:1rem;line-height:1.2;">
                                        <?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>
                                    </h3>
                                    <div class="featured-details mb-1" style="font-size:0.9rem;">
                                        <div class="detail-item"><i class="fas fa-map-marker-alt"></i> <span><?php echo isset($voiture['ville']) ? htmlspecialchars($voiture['ville']) : 'Non renseignée'; ?></span></div>
                                        <div class="detail-item"><i class="fas fa-calendar-alt"></i> <span><?php echo htmlspecialchars($voiture['annee']); ?></span></div>
                                        <div class="detail-item"><i class="fas fa-money-bill-wave"></i> <span><?php echo number_format($voiture['prix'], 0, ',', ' '); ?> FCFA</span></div>
                                    </div>
                                    <div class="featured-actions d-flex gap-1">
                                        <a href="voiture.php?id=<?php echo $voiture['id']; ?>" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-eye"></i></a>
                                        <a href="https://wa.me/221785016571?text=Bonjour, je suis intéressé par la voiture <?php echo urlencode($voiture['marque'] . ' ' . $voiture['modele']); ?> sur AutoLink Sénégal.%0A%0ALien vers la fiche : <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/Projet%20Automobile/voiture.php?id=' . $voiture['id']); ?>" target="_blank" class="btn btn-success btn-sm flex-fill"><i class="fab fa-whatsapp"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    new Swiper('.vedetteSwiper', {
        slidesPerView: 4,
        spaceBetween: 20,
        loop: true,
        pagination: { el: '.vedetteSwiper .swiper-pagination', clickable: true },
        navigation: { nextEl: '.vedetteSwiper .swiper-button-next', prevEl: '.vedetteSwiper .swiper-button-prev' },
        autoplay: { delay: 3500, disableOnInteraction: false },
        breakpoints: {
            0: { slidesPerView: 1 },
            576: { slidesPerView: 2 },
            992: { slidesPerView: 3 },
            1200: { slidesPerView: 4 }
        }
    });
    </script>
<?php endif; ?>

<!-- SECTION PUBS -->
<?php if (count($pubs) > 0): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <section class="pub-section mb-4">
        <div class="container">
            <div class="swiper pubSwiper rounded shadow-sm bg-white p-3 mb-3" style="max-width:700px;margin:auto;">
                <div class="swiper-wrapper">
                    <?php foreach ($pubs as $pub): ?>
                    <div class="swiper-slide text-center">
                        <?php if (!empty($pub['lien'])): ?><a href="<?php echo htmlspecialchars($pub['lien']); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;">
                        <?php endif; ?>
                        <?php if (!empty($pub['medias'])): ?>
                            <div class="swiper pubMediaSwiper" style="max-width:100%;max-height:220px;">
                                <div class="swiper-wrapper">
                                    <?php foreach ($pub['medias'] as $media): ?>
                                        <div class="swiper-slide">
                                            <?php if ($media['type'] === 'image'): ?>
                                                <img src="<?php echo '/Projet%20Automobile/public/uploads/pubs/' . rawurlencode($media['fichier']); ?>" alt="Publicité" style="width:100%;height:220px;object-fit:cover;border-radius:8px;display:block;background:#222;">
                                            <?php elseif ($media['type'] === 'video'): ?>
                                                <video controls autoplay muted loop style="width:100%;height:220px;object-fit:cover;background:#222;border-radius:8px;display:block;">
                                                    <source src="<?php echo '/Projet%20Automobile/public/uploads/pubs/' . rawurlencode($media['fichier']); ?>">
                                                    Votre navigateur ne supporte pas la vidéo.
                                                </video>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-pagination"></div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($pub['lien'])): ?></a><?php endif; ?>
                        <?php if (!empty($pub['titre'])): ?>
                            <h5 class="mt-2 mb-1" style="font-size:1.1rem;"><?php echo htmlspecialchars($pub['titre']); ?></h5>
                        <?php endif; ?>
                        <?php if (!empty($pub['description'])): ?>
                            <div class="text-muted" style="font-size:0.97rem;"> <?php echo htmlspecialchars($pub['description']); ?> </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    document.querySelectorAll('.pubMediaSwiper').forEach(function(swiperEl) {
        new Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 10,
            loop: swiperEl.querySelectorAll('.swiper-slide').length > 1,
            pagination: { el: swiperEl.querySelector('.swiper-pagination'), clickable: true },
            navigation: { nextEl: swiperEl.querySelector('.swiper-button-next'), prevEl: swiperEl.querySelector('.swiper-button-prev') },
            autoplay: { delay: 3500, disableOnInteraction: false },
            on: {
                slideChange: function() {
                    swiperEl.querySelectorAll('video').forEach(function(vid, idx) {
                        if (idx === this.realIndex) {
                            vid.play();
                        } else {
                            vid.pause();
                            vid.currentTime = 0;
                        }
                    }, this);
                }
            }
        });
    });
    </script>
<?php endif; ?>

<!-- SECTION TOUTES LES VOITURES -->
<?php include '../includes/voitures_section.php'; ?>

<?php require_once '../includes/footer.php'; ?>
