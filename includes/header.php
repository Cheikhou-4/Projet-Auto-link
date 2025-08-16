<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vente/Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/Projet%20Automobile/public/assets/css/style.css" rel="stylesheet">
    <link href="/Projet%20Automobile/public/assets/css/coinafrique-style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Projet%20Automobile/public/assets/js/filters.js"></script>
    <script src="/Projet%20Automobile/public/assets/js/theme.js"></script>
    <!-- Scripts désactivés pour simplifier l'interface -->
    <!-- <script src="/Projet%20Automobile/public/assets/js/compare.js"></script> -->
    <!-- <script src="/Projet%20Automobile/public/assets/js/gallery.js"></script> -->
    <!-- <script src="/Projet%20Automobile/public/assets/js/share.js"></script> -->
    <script src="/Projet%20Automobile/public/assets/js/animations.js"></script>
    <script src="/Projet%20Automobile/public/assets/js/categories.js"></script>
    <script src="/Projet%20Automobile/public/assets/js/coinafrique.js"></script>
</head>
<body>
<!-- Header principal style CoinAfrique -->
<header class="main-header">
    <div class="container">
        <div class="row align-items-center py-3">
            <div class="col-lg-3">
                <a href="/Projet%20Automobile/public/index.php" class="logo">
                    <img src="/Projet%20Automobile/public/assets/images/logo.png" alt="Logo AutoLink Sénégal" style="height:44px;width:auto;max-width:140px;display:inline-block;vertical-align:middle;">
                </a>
            </div>
            <div class="col-lg-6">
                <div class="search-container">
                    <form class="search-box" action="/Projet%20Automobile/public/index.php" method="get" style="display:flex;align-items:center;">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="form-control search-input" placeholder="Rechercher un véhicule, une marque, une ville..." id="mainSearch" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-warning search-btn" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-lg-3 text-end">
                <div class="header-nav d-flex gap-2 justify-content-end">
                    <a href="/Projet%20Automobile/public/voitures.php?type=vente" class="btn btn-outline-light btn-sm">Vente</a>
                    <a href="/Projet%20Automobile/public/voitures.php?type=location" class="btn btn-outline-light btn-sm">Location</a>
                    <a href="/Projet%20Automobile/public/contact.php" class="nav-link">Contact</a>
                </div>
            </div>
        </div>
    </div>
</header>
