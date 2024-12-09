<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT DISTINCT kategoria FROM termekek;");

// Szűrés kiválasztott kategória alapján
$kategoria_szures = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;

// Szűrt termékek
if ($kategoria_szures) {
    $szurt_termekek = adatokLekeres("SELECT * FROM termekek WHERE kategoria = '{$kategoria_szures}';");
} else {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">MackoBuy Webshop</h1>
        
        <!-- Kategóriaválasztó -->
        <form method="GET" class="mb-4">
            <label for="kategoria" class="form-label"><strong>Kategória kiválasztása:</strong></label>
            <select name="kategoria" id="kategoria" class="form-select" onchange="this.form.submit()">
                <option value="">Összes kategória</option>
                <?php foreach ($kategoriak as $kategoria): ?>
                    <option value="<?= htmlspecialchars($kategoria['kategoria']) ?>" 
                        <?= $kategoria_szures === $kategoria['kategoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategoria['kategoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Szűrt termékek -->
        <?php if ($kategoria_szures): ?>
            <h2 class="my-4">Kiválasztott kategória: <?= htmlspecialchars($kategoria_szures) ?></h2>
            <div class="row">
                <?php if (!empty($szurt_termekek)): ?>
                    <?php foreach ($szurt_termekek as $termek): ?>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <!-- Kép URL közvetlen beillesztése -->
                                <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($termek['leiras']) ?></p>
                                    <p class="card-text"><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                                    <a href="product.php?id=<?= $termek['ID'] ?>" class="btn btn-primary">Részletek</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nincsenek termékek ebben a kategóriában.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Összes termék -->
        <h2 class="my-4">Összes termék</h2>
        <div class="row">
            <?php foreach ($osszes_termek as $termek): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <!-- Kép URL közvetlen beillesztése -->
                        <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($termek['leiras']) ?></p>
                            <p class="card-text"><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                            <a href="product.php?id=<?= $termek['ID'] ?>" class="btn btn-primary">Részletek</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    
    <?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT DISTINCT kategoria FROM termekek;");

// Szűrés kiválasztott kategória alapján
$kategoria_szures = isset($_GET['kategoria']) ? $_GET['kategoria'] : null;

// Szűrt termékek
if ($kategoria_szures) {
    $szurt_termekek = adatokLekeres("SELECT * FROM termekek WHERE kategoria = '{$kategoria_szures}';");
} else {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">MackoBuy Webshop</h1>
        
        <!-- Kategóriaválasztó -->
        <form method="GET" class="mb-4">
            <label for="kategoria" class="form-label"><strong>Kategória kiválasztása:</strong></label>
            <select name="kategoria" id="kategoria" class="form-select" onchange="this.form.submit()">
                <option value="">Összes kategória</option>
                <?php foreach ($kategoriak as $kategoria): ?>
                    <option value="<?= htmlspecialchars($kategoria['kategoria']) ?>" 
                        <?= $kategoria_szures === $kategoria['kategoria'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kategoria['kategoria']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Szűrt termékek -->
        <?php if ($kategoria_szures): ?>
            <h2 class="my-4">Kiválasztott kategória: <?= htmlspecialchars($kategoria_szures) ?></h2>
            <div class="row">
                <?php if (!empty($szurt_termekek)): ?>
                    <?php foreach ($szurt_termekek as $termek): ?>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <!-- Kép URL közvetlen beillesztése -->
                                <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($termek['leiras']) ?></p>
                                    <p class="card-text"><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                                    <a href="product.php?id=<?= $termek['ID'] ?>" class="btn btn-primary">Részletek</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nincsenek termékek ebben a kategóriában.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Összes termék -->
        <h2 class="my-4">Összes termék</h2>
        <div class="row">
            <?php foreach ($osszes_termek as $termek): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <!-- Kép URL közvetlen beillesztése -->
                        <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($termek['leiras']) ?></p>
                            <p class="card-text"><strong>Ár:</strong> <?= htmlspecialchars($termek['ar']) ?> Ft</p>
                            <a href="product.php?id=<?= $termek['ID'] ?>" class="btn btn-primary">Részletek</a>
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
