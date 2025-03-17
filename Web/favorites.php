<?php
session_start();
include 'sql_fuggvenyek.php';

// Adatbázis kapcsolat
$conn = new mysqli("localhost", "root", "", "mackobuy");
if ($conn->connect_error) {
    die("Adatbázis kapcsolat hiba: " . $conn->connect_error);
}

// 🔴 Valuta kezelése
if (isset($_POST['penznem'])) {
    $_SESSION['penznem'] = $_POST['penznem'];

    // Ha be van jelentkezve a felhasználó, frissítjük az adatbázisban is
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE felhasznalok SET penznem = ? WHERE id = ?");
        $stmt->bind_param("si", $_POST['penznem'], $user_id);
        $stmt->execute();
    }

    // Az oldal újratöltése a pénznem frissítése után
    header("Location: favorites.php");
    exit;
}

// Alapértelmezett valuta
$aktualis_penznem = $_SESSION['penznem'] ?? 'HUF';

// 🔴 Árfolyam lekérése (csak ha EUR-ra váltunk)
$arfolyam = 1;
if ($aktualis_penznem === 'EUR') {
    $arfolyamAdat = json_decode(file_get_contents("https://api.exchangerate-api.com/v4/latest/HUF"), true);
    $arfolyam = $arfolyamAdat['rates']['EUR'] ?? 1; // Ha az API nem elérhető, marad 1
}

// Kedvencek lekérdezése
$user_id = $_SESSION['user_id'];
$query = "SELECT t.ID, t.tnev, t.ar, t.kep 
          FROM termekek t
          JOIN kedvencek k ON t.ID = k.termek_id
          WHERE k.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

//  Kedvenc termék eltávolítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termek_id'])) {
    $termek_id = $_POST['termek_id'];
    $user_id = $_SESSION['user_id'];

    $delete_stmt = $conn->prepare("DELETE FROM kedvencek WHERE user_id = ? AND termek_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $termek_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Az oldal újratöltése a törlés után
    header("Location: favorites.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kedvenc Termékeid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="favorites.css">
</head>
<body>

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
                        <li class="nav-item"><a class="nav-link" href="kosar.php"><img src="kepek/kosar.png" alt="Kosár" style="height: 38px;" alt="Kosár" title="Kosár"></a></li>
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

<!-- Kedvencek listája -->
<div class="container" id="termekek">
    <h2 class="kedvterm">Kedvenc Termékeid</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="termek-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="termek-kartya">
                    <img src="kepek/<?= htmlspecialchars($row['kep']) ?>" alt="<?= htmlspecialchars($row['tnev']) ?>">
                    <h3><?= htmlspecialchars($row['tnev']) ?></h3>
                    <?php
                        $atvaltott_ar = round($row['ar'] * $arfolyam, 2);
                        $valuta_jel = ($aktualis_penznem === 'EUR') ? '€' : 'Ft';
                        $formazott_ar = number_format($atvaltott_ar, 0, ',', ' ') . ' ' . $valuta_jel;
                    ?>
                    <p class="ar"><?= $formazott_ar ?></p>

                    <form method="POST">
                        <input type="hidden" name="termek_id" value="<?= $row['ID'] ?>">
                        <button type="submit" class="eltavolitas-gomb">Eltávolítás</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="nincstermek">Nincsenek kedvenc termékeid. 😢</p>
    <?php endif; ?>
</div>

<footer>
    <div class="container" id="foot">
        <p>&copy; 2025 MackoBuy. Minden jog fenntartva.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleButton = document.getElementById("darkModeToggle");
    const body = document.body;

    // Ellenőrizzük, hogy a felhasználó előzőleg bekapcsolta-e a dark mode-ot
    if (localStorage.getItem("dark-mode") === "enabled") {
        body.classList.add("dark-mode");
        toggleButton.innerHTML = "☀️";
    }

    toggleButton.addEventListener("click", function() {
        body.classList.toggle("dark-mode");

        // Ha dark mode aktív, tároljuk a localStorage-ban
        if (body.classList.contains("dark-mode")) {
            localStorage.setItem("dark-mode", "enabled");
            toggleButton.innerHTML = "☀️";
        } else {
            localStorage.setItem("dark-mode", "disabled");
            toggleButton.innerHTML = "🌙";
        }
    });
});

</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
