<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT DISTINCT kategoria FROM termekek;");

// Kategória szűrés beállítása
$kategoria_szures = isset($_GET['kategoria']) ? $_GET['kategoria'] : ''; // Ha nincs beállítva, üres string

// Szűrt termékek
if ($kategoria_szures) {
    $szurt_termekek = adatokLekeres("SELECT * FROM termekek WHERE kategoria = '{$kategoria_szures}';");
} else {
    $szurt_termekek = adatokLekeres("SELECT * FROM termekek;"); // Ha nincs szűrő, betöltjük az összes terméket
}

// Ha a változó nem lett meghatározva, akkor alapértelmezett üres tömböt adunk neki
if (!isset($szurt_termekek)) {
    $szurt_termekek = [];
}

// Összes termék betöltése
$osszes_termek = adatokLekeres("SELECT * FROM termekek;");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop Főoldal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fooldal.css">
</head>
<body>
    <!-- Navbar kezdete -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="#">
                <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;">
            </a>
            <!-- Toggle button for mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Navbar items -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Kosár</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Profil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Tartalom -->
    <div class="container my-5">
        <br><br><br>
        <h1 class="text-center mb-4">Főoldal</h1>
        
        <!-- Kategóriaválasztó -->
        <form method="GET" class="mb-4">
            <label for="kategoria" class="form-label"><strong>Kategória kiválasztása:</strong></label>
            <select name="kategoria" id="kategoria" class="form-select" onchange="this.form.submit()">
                <option value="">Összes termék</option>
                <?php foreach ($kategoriak as $kategoria): ?>
                    <option value="<?= htmlspecialchars($kategoria['kategoria']) ?>" 
                        <?= $kategoria_szures === $kategoria['kategoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategoria['kategoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Szűrt termékek -->
        <h2 class="my-4"><?= $kategoria_szures ? 'Kiválasztott kategória: ' . htmlspecialchars($kategoria_szures) : 'Összes termék' ?></h2>
        <div class="row">
            <?php foreach ($szurt_termekek as $termek): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($termek['leiras']) ?></p>
                            <p class="card-text"><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                            <!-- Részletek gomb -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termekModal<?= $termek['ID'] ?>">
                                Részletek
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="termekModal<?= $termek['ID'] ?>" tabindex="-1" aria-labelledby="termekModalLabel<?= $termek['ID'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="termekModalLabel<?= $termek['ID'] ?>"><?= htmlspecialchars($termek['tnev']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                            </div>
                            <div class="modal-body">
                                <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                                <p><?= nl2br(htmlspecialchars($termek['leiras'])) ?></p>
                                <p><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                            </div>
                            <div class="modal-footer">
                                <form method="POST" action="kosar.php">
                                    <input type="hidden" name="termek_id" value="<?= $termek['ID'] ?>">
                                    <button type="submit" class="btn btn-success">Kosárba</button>
                                </form>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
