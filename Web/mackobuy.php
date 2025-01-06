<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT DISTINCT kategoria FROM termekek;");

// Kategória szűrés beállítása
$kategoria_szures = isset($_POST['kategoria']) ? $_POST['kategoria'] : ''; // Ha nincs beállítva, üres string

// Ár szűrés beállítása
$min_ar = isset($_POST['min_ar']) ? (int)$_POST['min_ar'] : 0;
$max_ar = isset($_POST['max_ar']) ? (int)$_POST['max_ar'] : 0;

// Adatbázisból a legkisebb és legnagyobb ár lekérése
$ar_tartomany = adatokLekeres("SELECT MIN(ar) AS min_ar, MAX(ar) AS max_ar FROM termekek;");
$alap_min_ar = $ar_tartomany[0]['min_ar'];
$alap_max_ar = $ar_tartomany[0]['max_ar'];

// Ha nincs beállítva szűrés, az alapértelmezett értékeket használjuk
if ($min_ar === 0) $min_ar = $alap_min_ar;
if ($max_ar === 0) $max_ar = $alap_max_ar;

// Szűrt termékek lekérése
$feltetel = "WHERE ar BETWEEN {$min_ar} AND {$max_ar}";
if ($kategoria_szures) {
    $feltetel .= " AND kategoria = '{$kategoria_szures}'";
}
$szurt_termekek = adatokLekeres("SELECT * FROM termekek {$feltetel};");

// Ha a változó nem lett meghatározva, akkor alapértelmezett üres tömböt adunk neki
if (!isset($szurt_termekek)) {
    $szurt_termekek = [];
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webshop Főoldal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- noUiSlider CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css" rel="stylesheet">
    <link rel="stylesheet" href="fooldal.css">
</head>
<body>
    <!-- Navbar kezdete -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="./mackobuy.php">
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
                        <a class="nav-link" href="./kosar.php">Kosár</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./profil.php">Profil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Tartalom -->
    <div class="container my-5">
        <br><br><br>
        <h1 class="text-center mb-4">Főoldal</h1>
        
        <!-- Kategóriaválasztó és ár szűrés -->
        <form method="POST" id="szuresForm">
            <div class="mb-4">
                <label for="kategoria" class="form-label"><strong>Kategória kiválasztása:</strong></label>
                <select name="kategoria" id="kategoria" class="form-select">
                    <option value="">Összes termék</option>
                    <?php foreach ($kategoriak as $kategoria): ?>
                        <option value="<?= htmlspecialchars($kategoria['kategoria']) ?>" 
                            <?= $kategoria_szures === $kategoria['kategoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kategoria['kategoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Ár szűrési csúszka -->
            <div class="mb-4">
                <label for="arSlider" class="form-label"><strong>Ár szűrés:</strong></label>
                <div id="arSlider"></div>
                <div class="d-flex justify-content-between">
                    <span id="minArKijelzo"><?= $min_ar ?> Ft</span>
                    <span id="maxArKijelzo"><?= $max_ar ?> Ft</span>
                </div>
                <input type="hidden" name="min_ar" id="minAr" value="<?= $min_ar ?>">
                <input type="hidden" name="max_ar" id="maxAr" value="<?= $max_ar ?>">
            </div>

            <!-- Szűrés gomb -->
            <button type="button" id="szuresGomb" class="btn custom-btn">Szűrés</button>
        </form>

        <!-- Szűrt termékek -->
        <div id="termekekListaja">
    <h2 class="my-4"><?= $kategoria_szures ? 'Kiválasztott kategória: ' . htmlspecialchars($kategoria_szures) : 'Összes termék' ?></h2>
    <div class="row">
        <?php if (is_array($szurt_termekek) && !empty($szurt_termekek)): ?>
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
        <?php else: ?>
            <div class="custom-alert text-center" role="alert">
                Ebben az ártartományban egy termék sem található!
            </div>
        <?php endif; ?>
    </div>
</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- noUiSlider JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>
    <script>
        var slider = document.getElementById('arSlider');

        // Csúszka inicializálása a min és max árak alapján
        noUiSlider.create(slider, {
            start: [<?= $min_ar ?>, <?= $max_ar ?>],
            connect: true,
            step: 1000,
            range: {
                'min': <?= $alap_min_ar ?>,
                'max': <?= $alap_max_ar ?>
            },
            format: {
                to: function(value) {
                    return Math.round(value);
                },
                from: function(value) {
                    return Number(value);
                }
            }
        });

        // Csúszka értékeinek frissítése
        var minArInput = document.getElementById('minAr');
        var maxArInput = document.getElementById('maxAr');
        var minArKijelzo = document.getElementById('minArKijelzo');
        var maxArKijelzo = document.getElementById('maxArKijelzo');

        slider.noUiSlider.on('update', function(values) {
            minArKijelzo.innerText = Math.round(values[0]) + ' Ft';
            maxArKijelzo.innerText = Math.round(values[1]) + ' Ft';
            minArInput.value = Math.round(values[0]);
            maxArInput.value = Math.round(values[1]);
        });

        // Szűrés gomb AJAX működés
        document.getElementById('szuresGomb').addEventListener('click', function() {
            document.getElementById('szuresForm').submit();
        });
    </script>
</body>
</html>
