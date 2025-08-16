<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

// Filtres et tri
$marque = $_GET['marque'] ?? '';
$type = $_GET['type'] ?? '';
$ville = $_GET['ville'] ?? '';
$search = $_GET['search'] ?? '';
$tri = $_GET['tri'] ?? 'recent';
$annee = $_GET['annee'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';


$query = 'SELECT * FROM voitures WHERE 1';
$params = [];
if ($search !== '') {
  // Recherche insensible à la casse (accents selon la collation SQL)
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
  if ($annee !== '') {
    $query .= ' AND annee = ?';
    $params[] = $annee;
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
<div class="container-fluid mt-4">
  <div class="row">
    <!-- Sidebar Filtres -->
    <div class="col-lg-3 mb-4">
      <div class="sidebar-filtres">
        <h5 class="mb-3"><i class="fas fa-filter me-2 text-warning"></i>Filtres Avancés</h5>
        <form id="advancedFiltersForm" method="get">
          <div class="mb-3">
            <label class="form-label">Ville</label>
            <select name="ville" class="form-select">
              <option value="">Toutes les villes</option>
              <?php foreach ($villes as $v): ?>
                <option value="<?php echo htmlspecialchars($v); ?>" <?php if ($ville === $v) echo 'selected'; ?>><?php echo htmlspecialchars($v); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Marque</label>
            <select name="marque" class="form-select">
              <option value="">Toutes les marques</option>
              <?php foreach ($marques as $m): ?>
                <option value="<?php echo htmlspecialchars($m); ?>" <?php if ($marque === $m) echo 'selected'; ?>><?php echo htmlspecialchars($m); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
              <option value="">Tous les types</option>
              <option value="vente" <?php if ($type === 'vente') echo 'selected'; ?>>Vente</option>
              <option value="location" <?php if ($type === 'location') echo 'selected'; ?>>Location</option>
            </select>
          </div>
          
          <!-- Filtre par année -->
          <div class="mb-3">
            <label class="form-label">Année</label>
            <div class="d-flex justify-content-between mb-2">
              <small class="text-muted">Min: <span id="yearMin"><?php echo $year_min ?: '2010'; ?></span></small>
              <small class="text-muted">Max: <span id="yearMax"><?php echo $year_max ?: '2024'; ?></span></small>
            </div>
            <div id="yearRange"></div>
          </div>
          
          <!-- Filtre par prix -->
          <div class="mb-3">
            <label class="form-label">Prix (FCFA)</label>
            <div class="d-flex justify-content-between mb-2">
              <small class="text-muted">Min: <span id="priceMin"><?php echo $price_min ? number_format($price_min, 0, ',', ' ') : '1 000 000'; ?> FCFA</span></small>
              <small class="text-muted">Max: <span id="priceMax"><?php echo $price_max ? number_format($price_max, 0, ',', ' ') : '50 000 000'; ?> FCFA</span></small>
            </div>
            <div id="priceRange"></div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Trier par</label>
            <select name="tri" class="form-select">
              <option value="recent" <?php if ($tri === 'recent') echo 'selected'; ?>>Plus récent</option>
              <option value="prix_asc" <?php if ($tri === 'prix_asc') echo 'selected'; ?>>Prix croissant</option>
              <option value="prix_desc" <?php if ($tri === 'prix_desc') echo 'selected'; ?>>Prix décroissant</option>
            </select>
          </div>
          
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Appliquer les filtres</button>
            <button type="button" id="resetFilters" class="btn btn-outline-secondary"><i class="fas fa-undo me-2"></i>Réinitialiser</button>
          </div>
          
          <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </div>
    </div>
    <!-- Grille des voitures -->
    <div class="col-lg-9">
      <h1 class="mb-4 text-center">Toutes les voitures</h1>
      <div class="row">
        <?php if (count($voitures) > 0): ?>
          <?php foreach ($voitures as $voiture): ?>
            <?php
            // Récupérer les images associées à la voiture
            $stmtImgs = $pdo->prepare('SELECT image FROM images_voiture WHERE voiture_id = ?');
            $stmtImgs->execute([$voiture['id']]);
            $images = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
            if (empty($images) && !empty($voiture['image'])) {
                $images = [$voiture['image']];
            }
            $isNew = (strtotime($voiture['date_ajout']) > strtotime('-7 days'));
            ?>
            <div class="col-md-4 mb-4">
              <div class="card h-100 position-relative">
                <?php if ($isNew): ?>
                  <span class="badge bg-info text-dark position-absolute" style="top:48px;left:12px;font-size:1rem;z-index:2;"><i class="fas fa-bolt"></i> Nouveau</span>
                <?php endif; ?>
                <?php if ($voiture['vedette']): ?>
                  <span class="badge bg-warning text-dark position-absolute" style="top:12px;left:12px;font-size:1rem;z-index:2;"><i class="fas fa-star"></i> Vedette</span>
                <?php endif; ?>
                <span class="badge position-absolute" style="top:12px;right:12px;font-size:1rem;z-index:2;<?php echo $voiture['type']==='vente' ? 'background:#1976d2;' : 'background:#43a047;'; ?>">
                    <i class="fas fa-tag"></i> <?php echo ucfirst($voiture['type']); ?>
                </span>
                <div class="image-container position-relative">
                    <a href="voiture.php?id=<?php echo $voiture['id']; ?>">
                        <div style="width:100%;height:180px;overflow:hidden;background:#eaeaea;border-radius:18px 18px 0 0;">
                        <?php if (count($images) > 1): ?>
                            <div id="carouselCard<?php echo $voiture['id']; ?>" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="2500">
                              <div class="carousel-inner h-100">
                                <?php foreach ($images as $k => $img): ?>
                                  <div class="carousel-item<?php if ($k === 0) echo ' active'; ?> h-100">
                                    <img src="<?php echo '../uploads/' . htmlspecialchars($img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>" style="width:100%;height:180px;object-fit:cover;border-radius:18px 18px 0 0;">
                                  </div>
                                <?php endforeach; ?>
                              </div>
                              <button class="carousel-control-prev" type="button" data-bs-target="#carouselCard<?php echo $voiture['id']; ?>" data-bs-slide="prev" style="width:24px;">
                                <span class="carousel-control-prev-icon" aria-hidden="true" style="background-size:18px 18px;"></span>
                                <span class="visually-hidden">Précédent</span>
                              </button>
                              <button class="carousel-control-next" type="button" data-bs-target="#carouselCard<?php echo $voiture['id']; ?>" data-bs-slide="next" style="width:24px;">
                                <span class="carousel-control-next-icon" aria-hidden="true" style="background-size:18px 18px;"></span>
                                <span class="visually-hidden">Suivant</span>
                              </button>
                            </div>
                        <?php else: ?>
                            <img src="<?php echo !empty($images[0]) ? '../uploads/' . htmlspecialchars($images[0]) : '../public/assets/images/logo.png'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>" style="width:100%;height:180px;object-fit:cover;border-radius:18px 18px 0 0;">
                        <?php endif; ?>
                        </div>
                    </a>
                </div>
                <div class="card-body">
                  <h5 class="card-title mb-2"><?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?></h5>
                  <p class="card-text mb-1"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo isset($voiture['ville']) ? htmlspecialchars($voiture['ville']) : 'Non renseignée'; ?></p>
                  <p class="card-text mb-1"><i class="fas fa-calendar-alt me-1"></i> <strong>Année :</strong> <?php echo htmlspecialchars($voiture['annee']); ?></p>
                  <p class="card-text mb-1"><i class="fas fa-money-bill-wave me-1 text-warning"></i> <strong>Prix :</strong> <?php echo number_format($voiture['prix'], 0, ',', ' '); ?> FCFA</p>
                  <div class="d-flex gap-2 mt-2">
                    <a href="voiture.php?id=<?php echo $voiture['id']; ?>" class="btn btn-primary w-50"><i class="fas fa-eye me-2"></i>Voir la fiche</a>
                    <a href="https://wa.me/221785016571?text=Bonjour, je suis intéressé par la voiture <?php echo urlencode($voiture['marque'] . ' ' . $voiture['modele']); ?> sur AutoLink Sénégal.%0A%0ALien vers la fiche : <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'voiture.php?id=' . $voiture['id']); ?>" target="_blank" class="btn btn-success w-50"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12">
            <div class="alert alert-info text-center">Aucune voiture disponible pour le moment.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>
<script>
window.addEventListener('pageshow', function(event) {
  if (event.persisted) {
    window.location.reload();
  }
});
</script>
