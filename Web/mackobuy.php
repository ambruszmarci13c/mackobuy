<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Valuta beállítása, ha a felhasználó választott
if (isset($_POST['penznem'])) {
    $penznem = $_POST['penznem'];
    $_SESSION['penznem'] = $penznem;

    // Ha be van jelentkezve a felhasználó, frissítjük az adatbázisban is
    if (isset($_SESSION['felhasznalo_id'])) {
        $felhasznalo_id = $_SESSION['felhasznalo_id'];
        $stmt = $pdo->prepare("UPDATE felhasznalok SET penznem = ? WHERE id = ?");
        $stmt->execute([$penznem, $felhasznalo_id]);
    }
}

// Valuta beállítása bejelentkezés alapján
if (isset($_SESSION['felhasznalo_id']) && !isset($_SESSION['penznem'])) {
    $felhasznalo_id = $_SESSION['felhasznalo_id'];
    $eredmeny = adatokLekeres("SELECT penznem FROM felhasznalok WHERE id = {$felhasznalo_id}");
    $_SESSION['penznem'] = $eredmeny[0]['penznem'] ?? 'HUF'; 
} 
 
// Alapértelmezett valuta 
$aktualis_penznem = $_SESSION['penznem'] ?? 'HUF';

// Árfolyam lekérése (EUR esetén)
$arfolyam = 1;
if ($aktualis_penznem === 'EUR') {
    $arfolyamAdat = json_decode(file_get_contents("https://api.exchangerate-api.com/v4/latest/HUF"), true);
    $arfolyam = $arfolyamAdat['rates']['EUR'] ?? 1; // Ha valamiért nem elérhető, alapértelmezett érték 1
}

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT DISTINCT kategoria.nev FROM termekek INNER JOIN kategoria ON termekek.kategoria = kategoria.id;");

// Kategória szűrés beállítása
$kategoria_szures = isset($_POST['kategoria']) ? $_POST['kategoria'] : ''; // Ha nincs beállítva, üres string

// Ár szűrés beállítása
$min_ar = isset($_POST['min_ar']) ? (int)$_POST['min_ar'] : 0;
$max_ar = isset($_POST['max_ar']) ? (int)$_POST['max_ar'] : 0;

// Ha az aktuális pénznem EUR, akkor visszaváltjuk HUF-ra a kereséshez
if ($aktualis_penznem === 'EUR') {
    $min_ar = round($min_ar / $arfolyam);  // Corrected from multiplication to division
    $max_ar = round($max_ar / $arfolyam);
}

// Alapértelmezett szűrés (nincs szűrési feltétel)
$feltetel = "";
if ($min_ar > 0 || $max_ar > 0) {
    $feltetel = "WHERE ar BETWEEN {$min_ar} AND {$max_ar}";
}
if ($kategoria_szures) {
    // Kategória ID lekérése a név alapján
    $kategoria_id = adatokLekeres("SELECT id FROM kategoria WHERE nev = '{$kategoria_szures}'");
    if ($kategoria_id) {
        $kategoria_id = $kategoria_id[0]['id']; // Az ID értéke
        $feltetel .= " AND kategoria = '{$kategoria_id}'";
    }
}
$szurt_termekek = adatokLekeres("SELECT * FROM termekek {$feltetel};");



// Ár szűrési csúszka
$valuta_jel = ($aktualis_penznem === 'EUR') ? '€' : 'Ft';
$formazott_min_ar = number_format($min_ar, 0, ',', ' ') . ' ' . $valuta_jel;
$formazott_max_ar = number_format($max_ar, 0, ',', ' ') . ' ' . $valuta_jel;

// Adatbázisból a legkisebb és legnagyobb ár lekérése
$ar_tartomany = adatokLekeres("SELECT MIN(ar) AS min_ar, MAX(ar) AS max_ar FROM termekek;");
$alap_min_ar = $ar_tartomany[0]['min_ar'];
$alap_max_ar = $ar_tartomany[0]['max_ar'];

// Ha nincs beállítva szűrés, az alapértelmezett értékeket használjuk
if ($min_ar === 0) $min_ar = $alap_min_ar;
if ($max_ar === 0) $max_ar = $alap_max_ar;

// Ha a változó nem lett meghatározva, akkor alapértelmezett üres tömböt adunk neki
if (!isset($szurt_termekek)) {
    $szurt_termekek = [];
}

$conn = new mysqli("localhost", "root", "", "mackobuy");
if ($conn->connect_error) {
    die("Adatbázis hiba: " . $conn->connect_error);
}

// Felhasználó ID lekérése
$user_id = $_SESSION['user_id'] ?? 0;

// Kedvencek lekérdezése az adott felhasználónak
$query = "SELECT termek_id FROM kedvencek WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$kedvencek = [];
while ($row = $result->fetch_assoc()) {
    $kedvencek[] = $row['termek_id'];
}
$stmt->close();

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
    <!-- Navbar -->
     
    <nav class="navbar navbar-expand-lg navbar-light fixed-top custom-navbar">
            <div class="container">
                <a class="navbar-brand" href="mackobuy.php">
                    <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;" alt="Főoldal" title="Főoldal">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" id="kosarGomb" href="kosar.php"><img src="kepek/kosar.png" alt="Kosár" style="height: 38px;" alt="Kosár" title="Kosár"></a></li>
                        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="kepek/profil.png" alt="Profil" style="height: 40px;" title="Profil">
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profilDropdown">
            <?php
             if (isset($_SESSION['user_id'])): ?>
                <li><a class="dropdown-item" href="profil.php">Profilom</a></li>
                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </li>
                        <li class="nav-item">
                        <form method="POST" action="" class="d-flex align-items-center">
                        <select name="penznem" class="form-select penznem-select" onchange="this.form.submit()">
                            <option value="HUF" <?= (isset($_SESSION['penznem']) && $_SESSION['penznem'] === 'HUF') ? 'selected' : '' ?>>HUF</option>
                            <option value="EUR" <?= (isset($_SESSION['penznem']) && $_SESSION['penznem'] === 'EUR') ? 'selected' : '' ?>>EUR</option>
                        </select>
                    </form>
                        </form>
                    </li>
                    </ul>
                </div>
                <div class="nav-item">
                        <button id="darkModeToggle" class="btn btn-outline-dark">🌙</button>
                    </div>
            </div>
        </nav>

    <!-- Tartalom -->
    <div class="container my-5">
        <br><br>
        <div class="text-center">
    <img src="./kepek/logo.png" alt="mackobuy logo" class="img-fluid" style="max-width: 300px;">
</div>        

        <div class="container mt-4">
            <!-- Szűrési feltételek blokk -->
            <div class="card p-3 shadow-sm">
                <h4 class="card-title mb-4 text-center">Szűrési feltételek</h4>
                <form method="POST" id="szuresForm">
                    <div class="row align-items-center">
                        <!-- Kategóriaválasztó -->
                        <div class="col-md-6">
                            <label for="kategoria" class="form-label"><strong>Kategória kiválasztása:</strong></label>
                            <select name="kategoria" id="kategoria" class="form-select form-control-lg">
                                <option value="">Összes termék</option>
                                <?php foreach ($kategoriak as $kategoria): ?>
                                    <option value="<?= htmlspecialchars($kategoria['nev']) ?>" 
                                        <?= $kategoria_szures === $kategoria['nev'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kategoria['nev']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>


                        <?php
                        $valuta_jel = ($aktualis_penznem === 'EUR') ? '€' : 'Ft';
                        $formazott_min_ar = number_format($min_ar * $arfolyam, 0, ',', ' ') . ' ' . $valuta_jel;
                        $formazott_max_ar = number_format($max_ar * $arfolyam, 0, ',', ' ') . ' ' . $valuta_jel;
                        ?>

                        <input type="hidden" id="aktualisPenznem" value="<?= $valuta_jel ?>">



                        <!-- Ár szűrési csúszka -->
                    <div class="col-md-6">
                        <label for="arSlider" class="form-label"><strong>Ár szűrés:</strong></label>
                        <div id="arSlider" class="custom-slider"></div>

                        <?php
                        // Új változók a formázott árakhoz
                        $valuta_jel = ($aktualis_penznem === 'EUR') ? '€' : 'Ft';
                        $formazott_min_ar = number_format($min_ar, 0, ',', ' ') . ' ' . $valuta_jel;
                        $formazott_max_ar = number_format($max_ar, 0, ',', ' ') . ' ' . $valuta_jel;
                        ?>

                        <div class="d-flex justify-content-between mt-2">
                            <span id="minArKijelzo"><?= $formazott_min_ar ?></span>
                            <span id="maxArKijelzo"><?= $formazott_max_ar ?></span>
                        </div>

                        <input type="hidden" name="min_ar" id="minAr" value="<?= $min_ar ?>">
                        <input type="hidden" name="max_ar" id="maxAr" value="<?= $max_ar ?>">
                    </div>

                    <!-- Szűrés gomb -->
                    <div class="text-center mt-4">
                        <button type="button" id="szuresGomb" class="btn custom-btn">Szűrés</button>
                    </div>
                </form>
            </div>
        </div>
        
<!-- Szűrt termékek -->
<div id="termekekListaja">
    <h2 class="my-4">
        <?= $kategoria_szures ? 'Kiválasztott kategória: ' . htmlspecialchars($kategoria_szures) : 'Termékek:' ?>
    </h2>
    <div class="row">
    <?php if (is_array($szurt_termekek) && !empty($szurt_termekek)): ?>
        <?php foreach ($szurt_termekek as $termek): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card mb-4">
                    <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="card-img-top" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($termek['tnev']) ?></h5>
                        <?php
                            $atvaltott_ar = round($termek['ar'] * $arfolyam, 2);
                            $valuta_jel = $aktualis_penznem === 'EUR' ? '€' : 'Ft';
                        ?>
                        <p class="card-text"><strong>Ár:</strong> <?= $atvaltott_ar . ' ' . $valuta_jel ?></p>

                        <!-- Részletek gomb -->
                        <button type="button" class="btn custom-btn" data-bs-toggle="modal" data-bs-target="#termekModal<?= $termek['ID'] ?>">
                            Részletek
                        </button>

                        <!-- Kedvenc gomb -->
                        <?php $hozzadva = in_array($termek['ID'], $kedvencek); ?>
                        <button class="kedvenc-gomb"  
                            data-termek-id="<?= $termek['ID'] ?>" 
                            data-hozzadva="<?= $hozzadva ? 'true' : 'false' ?>">
                            <?= $hozzadva ? '❤️' : '🤍' ?>
                        </button>

                    </div>
                </div>
            </div>

            <!-- Modal (a foreach cikluson belül!) -->
            <div class="modal fade" id="termekModal<?= $termek['ID'] ?>" tabindex="-1" aria-labelledby="termekModalLabel<?= $termek['ID'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="termekModalLabel<?= $termek['ID'] ?>"><?= htmlspecialchars($termek['tnev']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" class="img-fluid mb-3" alt="<?= htmlspecialchars($termek['tnev']) ?>">
                                <p><?= nl2br(htmlspecialchars($termek['leiras'])) ?></p>

                                <?php if ($termek['garancia']): ?>
                                    <p>Garancia: <?= htmlspecialchars($termek['garancia']) ?> hónap</p>
                                <?php endif; ?>

                                <p><strong>Ár:</strong> <?= $atvaltott_ar . ' ' . $valuta_jel ?></p>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-center">
                            <form method="POST" action="kosar.php">
                                <input type="hidden" name="termek_id" value="<?= $termek['ID'] ?>">
                                <button type="submit" class="btn custom-btn">Kosárba</button>
                            </form>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Bezárás</button>
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
    </div>
</div>



    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- noUiSlider JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js"></script>



    <script>
var slider = document.getElementById('arSlider');
var minArInput = document.getElementById('minAr');
var maxArInput = document.getElementById('maxAr');
var minArKijelzo = document.getElementById('minArKijelzo');
var maxArKijelzo = document.getElementById('maxArKijelzo');
var penznemSelect = document.querySelector("select[name='penznem']");
var arfolyam = <?= $arfolyam ?>; // PHP-ból az aktuális árfolyam

console.log(maxArInput.value);

// Kezdeti csúszka beállítása az árfolyamnak megfelelően
noUiSlider.create(slider, {
    start: [<?= $alap_min_ar ?>, <?= $alap_max_ar ?>],
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

// Frissíti a csúszka kijelző értékeit megfelelő pénznemben
function updateSliderValues(values) {
    var selectedCurrency = penznemSelect.value;
    var conversionRate = selectedCurrency === 'EUR' ? arfolyam : 1;

    minArKijelzo.innerText = Math.round(values[0] * conversionRate) + (selectedCurrency === 'EUR' ? ' €' : ' Ft');
    maxArKijelzo.innerText = Math.round(values[1] * conversionRate) + (selectedCurrency === 'EUR' ? ' €' : ' Ft');

    minArInput.value = Math.round(values[0] * conversionRate);
    maxArInput.value = Math.round(values[1] * conversionRate);
}

// Frissíti a csúszka kijelző értékeit
slider.noUiSlider.on('update', function(values) {
    updateSliderValues(values);
});

// Szűrés gomb AJAX működés

document.getElementById('szuresGomb').addEventListener('click', function() {
    document.getElementById('szuresForm').submit();
});

// Pénznem váltásakor frissítjük a csúszka tartományát és értékeit
penznemSelect.addEventListener('change', function() {
    var selectedCurrency = this.value;
    var conversionRate = selectedCurrency === 'EUR' ? arfolyam : 1;

    // Frissítjük a csúszka tartományát a megfelelő pénznemben
    slider.noUiSlider.updateOptions({
        range: {
            'min': Math.round(<?= $alap_min_ar ?> / conversionRate),
            'max': Math.round(<?= $alap_max_ar ?> / conversionRate)
        }
    });

    // Frissítjük az aktuális csúszka értékeit
    var minValue = slider.noUiSlider.get()[0];
    var maxValue = slider.noUiSlider.get()[1];
    minValue = Math.round(minValue / conversionRate);
    maxValue = Math.round(maxValue / conversionRate);
    slider.noUiSlider.set([minValue, maxValue]);

    // Kijelzők frissítése
    updateSliderValues([minValue, maxValue]);
});



document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".kedvenc-gomb").forEach(function(button) {
        button.addEventListener("click", function() {
            let termekId = this.getAttribute("data-termek-id");
            let action = this.classList.contains("kedvenc") ? "remove" : "add";  // Ha már kedvenc, akkor törlés
            
            fetch("kedvenc_muvelet.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `termek_id=${termekId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    if (action === "add") {
                        button.classList.add("kedvenc");  // CSS osztály a kijelöléshez
                        button.innerHTML = "❤️";  // Szív ikon beállítása
                    } else {
                        button.classList.remove("kedvenc");
                        button.innerHTML = "🤍";  // Visszaállítás
                    }
                } else {
                    alert("Hiba: " + data.message);
                }
            })
            .catch(error => console.error("Hiba:", error));
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("darkModeToggle");
    const body = document.body;

    // Ellenőrizzük, hogy a felhasználó előzőleg bekapcsolta-e a dark mode-ot
    if (localStorage.getItem("dark-mode") === "enabled") {
        body.classList.add("dark-mode");
        toggleButton.textContent = "☀️";
    }

    toggleButton.addEventListener("click", function () {
        body.classList.toggle("dark-mode");

        // Ha dark mode aktív, tároljuk a localStorage-ban
        if (body.classList.contains("dark-mode")) {
            localStorage.setItem("dark-mode", "enabled");
            toggleButton.textContent = "☀️";
        } else {
            localStorage.setItem("dark-mode", "disabled");
            toggleButton.textContent = "🌙";
        }
    });
});


console.log(maxArInput.value);
console.log(maxArKijelzo.value);


document.addEventListener("DOMContentLoaded", function() {
    const kosarGomb = document.getElementById("kosarGomb");
    const loadingScreen = document.getElementById("loadingScreen");

    if (kosarGomb && loadingScreen) {
        kosarGomb.addEventListener("click", function(event) {
            event.preventDefault(); // Megakadályozza az azonnali navigációt

            loadingScreen.classList.add("active"); // Betöltő képernyő megjelenítése

            setTimeout(() => {
                window.location.href = this.getAttribute("href"); // Átirányítás a kosár oldalra
            }, 1000); // 1,5 másodperces késleltetés
        });
    }
});
</script>

<div id="loadingScreen">
    <div class="spinner"></div>
</div>

<footer>
    <div class="container">
        <p>&copy; 2025 MackoBuy. Minden jog fenntartva.</p>
    </div>
</footer>



</body>
</html>
