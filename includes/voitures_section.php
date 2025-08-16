<?php
// Ce fichier affiche la sidebar des filtres et la grille des voitures
// Il doit être inclus dans index.php et voitures.php

// Les variables nécessaires doivent être définies avant l'inclusion :
// $voitures, $marques, $villes, $marque, $type, $ville, $search, $tri, $year_min, $year_max, $price_min, $price_max
?>
<div class="container-fluid mt-4">
  <h1 class="mb-4 text-center">Toutes les voitures</h1>
  <form id="advancedFiltersForm" method="get" class="row flex-nowrap g-1 align-items-end mb-4" style="overflow-x:auto;white-space:nowrap;background:#f8f9fa;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:12px 8px;gap:8px;">
    <div class="col-auto" style="min-width:110px;max-width:110px;">
      <style>
        #advancedFiltersForm select,
        #advancedFiltersForm input[type=number] {
          border-radius: 8px;
          border: 1px solid #ced4da;
          background: #fff;
          font-size: 0.95rem;
          box-shadow: none;
          transition: border-color 0.2s;
        }
        #advancedFiltersForm select:focus,
        #advancedFiltersForm input[type=number]:focus {
          border-color: #007bff;
          outline: none;
        }
        #advancedFiltersForm button {
          border-radius: 8px;
          font-size: 0.95rem;
          box-shadow: none;
        }
        #advancedFiltersForm .form-select-sm,
        #advancedFiltersForm .form-control-sm {
          padding: 0.25rem 0.5rem;
        }
      </style>
  <select name="ville" class="form-select form-select-sm" title="Ville" style="margin-bottom:0;">
        <option value="">Ville</option>
        <?php foreach ($villes as $v): ?>
          <option value="<?php echo htmlspecialchars($v); ?>" <?php if ($ville === $v) echo 'selected'; ?>><?php echo htmlspecialchars($v); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto" style="min-width:110px;max-width:110px;">
  <select name="marque" class="form-select form-select-sm" title="Marque" style="margin-bottom:0;">
        <option value="">Marque</option>
        <?php foreach ($marques as $m): ?>
          <option value="<?php echo htmlspecialchars($m); ?>" <?php if ($marque === $m) echo 'selected'; ?>><?php echo htmlspecialchars($m); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto" style="min-width:90px;max-width:90px;">
  <select name="type" class="form-select form-select-sm" title="Type" style="margin-bottom:0;">
        <option value="">Type</option>
        <option value="vente" <?php if ($type === 'vente') echo 'selected'; ?>>Vente</option>
        <option value="location" <?php if ($type === 'location') echo 'selected'; ?>>Location</option>
      </select>
    </div>
    <div class="col-auto" style="min-width:110px;max-width:110px;">
      <select name="annee" class="form-select form-select-sm" title="Année" style="margin-bottom:0;">
        <option value="">Année</option>
        <?php 
        $currentYear = date('Y');
        for ($y = $currentYear; $y >= 1990; $y--) {
          echo '<option value="' . $y . '"' . (($annee ?? '') == $y ? ' selected' : '') . '>' . $y . '</option>';
        }
        ?>
      </select>
    </div>
    <div class="col-auto" style="min-width:90px;max-width:90px;">
  <input type="number" name="price_min" class="form-control form-control-sm" value="<?php echo htmlspecialchars($price_min); ?>" placeholder="Prix min" title="Prix min" style="margin-bottom:0;">
    </div>
    <div class="col-auto" style="min-width:90px;max-width:90px;">
  <input type="number" name="price_max" class="form-control form-control-sm" value="<?php echo htmlspecialchars($price_max); ?>" placeholder="Prix max" title="Prix max" style="margin-bottom:0;">
    </div>
    <div class="col-auto" style="min-width:90px;max-width:90px;">
  <select name="tri" class="form-select form-select-sm" title="Trier par" style="margin-bottom:0;">
        <option value="recent" <?php if ($tri === 'recent') echo 'selected'; ?>>Récent</option>
        <option value="prix_asc" <?php if ($tri === 'prix_asc') echo 'selected'; ?>>Prix +</option>
        <option value="prix_desc" <?php if ($tri === 'prix_desc') echo 'selected'; ?>>Prix -</option>
      </select>
    </div>
    <div class="col-auto d-flex gap-1" style="min-width:120px;max-width:120px;">
  <button type="submit" class="btn btn-primary btn-sm" style="margin-bottom:0;"><i class="fas fa-filter"></i></button>
  <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm" style="margin-bottom:0;"><i class="fas fa-undo"></i></button>
    </div>
  </form>
  <div class="row" style="justify-content:center;">
        <?php
        if (isset($voitures) && is_array($voitures) && count($voitures) > 0) {
          foreach ($voitures as $voiture) {
            $stmtImgs = $pdo->prepare('SELECT image FROM images_voiture WHERE voiture_id = ?');
            $stmtImgs->execute([$voiture['id']]);
            $images = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
            if (empty($images) && !empty($voiture['image'])) {
                $images = [$voiture['image']];
            }
            $isNew = (strtotime($voiture['date_ajout']) > strtotime('-7 days'));
            ?>
      <div class="col-auto mb-4" style="width:220px;min-width:220px;max-width:220px;">
        <div class="card h-100 position-relative car-compact-size" style="min-height:200px;width:220px;max-width:220px;min-width:220px;">
                <?php if ($voiture['vedette']): ?>
                  <span class="badge bg-warning text-dark position-absolute" style="top:8px;left:8px;font-size:0.7rem;z-index:2;padding:2px 8px;"><i class="fas fa-star"></i> Vedette</span>
                <?php endif; ?>
                <span class="badge position-absolute" style="top:8px;right:8px;font-size:0.7rem;z-index:2;padding:2px 8px;<?php echo $voiture['type']==='vente' ? 'background:#1976d2;' : 'background:#43a047;'; ?>">
                    <i class="fas fa-tag"></i> <?php echo ucfirst($voiture['type']); ?>
                </span>
        <div class="image-container position-relative" style="height:110px;">
          <a href="voiture.php?id=<?php echo $voiture['id']; ?>">
            <div style="width:100%;height:110px;overflow:hidden;background:#eaeaea;border-radius:14px 14px 0 0;position:relative;">
                        <?php if (count($images) > 1): ?>
                            <div id="carouselCard<?php echo $voiture['id']; ?>" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="2500" style="position:relative;">
                              <div class="carousel-inner h-100">
                                <?php foreach ($images as $k => $img): ?>
                                  <div class="carousel-item<?php if ($k === 0) echo ' active'; ?> h-100" style="position:relative;">
                                    <img src="<?php echo '../uploads/' . rawurlencode($img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>" style="width:100%;height:110px;object-fit:cover;border-radius:14px 14px 0 0;">
                                    <?php if ($isNew): ?>
                                      <span class="badge bg-info text-dark position-absolute" style="bottom:8px;left:8px;font-size:0.7rem;z-index:3;padding:2px 8px;"> <i class="fas fa-bolt"></i> Nouveau</span>
                                    <?php endif; ?>
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
                            <img src="<?php echo !empty($images[0]) ? '../uploads/' . rawurlencode($images[0]) : '../public/assets/images/logo.png'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>" style="width:100%;height:110px;object-fit:cover;border-radius:14px 14px 0 0;">
                            <?php if ($isNew): ?>
                              <span class="badge bg-info text-dark position-absolute" style="bottom:8px;left:8px;font-size:0.7rem;z-index:3;padding:2px 8px;"> <i class="fas fa-bolt"></i> Nouveau</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        </div>
                    </a>
                </div>
                <div class="card-body p-4">
                  <h5 class="card-title mb-2" style="font-size:1.2rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>
                  </h5>
                  <p class="card-text mb-1" style="font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo isset($voiture['ville']) ? htmlspecialchars($voiture['ville']) : 'Non renseignée'; ?>
                  </p>
                  <p class="card-text mb-1" style="font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i class="fas fa-calendar-alt me-1"></i> <strong>Année :</strong> <?php echo htmlspecialchars($voiture['annee']); ?>
                  </p>
                  <p class="card-text mb-1" style="font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <i class="fas fa-money-bill-wave me-1 text-warning"></i> <strong>Prix :</strong> <?php echo number_format($voiture['prix'], 0, ',', ' '); ?> FCFA
                  </p>
                  <div class="d-flex gap-2 mt-2">
                    <a href="voiture.php?id=<?php echo $voiture['id']; ?>" class="btn btn-primary w-50" style="font-size:0.8rem;padding:4px 8px;line-height:1.1;"><i class="fas fa-eye me-1"></i>Voir la fiche</a>
                    <a href="https://wa.me/221785016571?text=Bonjour, je suis intéressé par la voiture <?php echo urlencode($voiture['marque'] . ' ' . $voiture['modele']); ?> sur AutoLink Sénégal.%0A%0ALien vers la fiche : <?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . 'voiture.php?id=' . $voiture['id']); ?>" target="_blank" class="btn btn-success w-50" style="font-size:0.8rem;padding:4px 8px;line-height:1.1;"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
                  </div>
                </div>
              </div>
            </div>
          <?php }
        } else {
          echo '<div class="col-12"><div class="alert alert-info text-center">Aucune voiture disponible pour le moment.</div></div>';
        }
        ?>
      </div>
      <!-- Mini navigation pagination -->
      <nav aria-label="Mini navigation voitures" class="mt-2 mb-2 text-center">
        <ul class="pagination pagination-sm justify-content-center" style="margin-bottom:0;">
          <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">2</a></li>
          <li class="page-item"><a class="page-link" href="#">3</a></li>
          <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
        </ul>
      </nav>
    </div>
  </div>
</div>
