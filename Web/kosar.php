<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

// Kosár elem mennyiségének növelése
if (isset($_GET['increase'])) {
    $termek_id = intval($_GET['increase']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        $_SESSION['kosar'][$termek_id]['mennyiseg']++;
    }
    header("Location: kosar.php");
    exit();
}

// Kosár elem mennyiségének csökkentése
if (isset($_GET['decrease'])) {
    $termek_id = intval($_GET['decrease']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        $_SESSION['kosar'][$termek_id]['mennyiseg']--;
        // Ha a mennyiség 0, eltávolítjuk az elemet
        if ($_SESSION['kosar'][$termek_id]['mennyiseg'] <= 0) {
            unset($_SESSION['kosar'][$termek_id]);
        }
    }
    header("Location: kosar.php");
    exit();
}

// Kosár elem eltávolítása
if (isset($_GET['remove'])) {
    $termek_id = intval($_GET['remove']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        unset($_SESSION['kosar'][$termek_id]);
    }
    header("Location: kosar.php");
    exit();
}

// Kosár kiürítése rendelés után
if (isset($_POST['megrendelem'])) {
    $_SESSION['kosar'] = []; // Kiürítjük a kosarat
    echo "<script>alert('Sikeres rendelés!'); window.location.href = 'kosar.php';</script>";
    exit();
}

// Termék hozzáadása a kosárhoz (ha POST kérés érkezett)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termek_id'])) {
    $termek_id = intval($_POST['termek_id']);

    // Ellenőrizzük, hogy a termék létezik-e az adatbázisban
    $termek = adatokLekeres("SELECT * FROM termekek WHERE ID = {$termek_id};");

    if ($termek) {
        $termek = $termek[0]; // Első találatot vesszük
        if (!isset($_SESSION['kosar'][$termek_id]) || !is_array($_SESSION['kosar'][$termek_id])) {
            // Ha nincs még a kosárban a termék, hozzáadjuk
            $_SESSION['kosar'][$termek_id] = [
                'tnev' => $termek['tnev'],
                'ar' => $termek['ar'],
                'kep' => $termek['kep'],
                'mennyiseg' => 1
            ];
        } else {
            // Ha már van a kosárban, növeljük a mennyiséget
            $_SESSION['kosar'][$termek_id]['mennyiseg']++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="./mackobuy.php">
                <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="kosar.php">Kosár</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Profil</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <br><br><br>
        <h1 class="text-center mb-4">Kosár</h1>

        <?php if (!empty($_SESSION['kosar'])): ?>
            <form method="POST" action="kosar.php">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Termék</th>
                            <th>Kép</th>
                            <th>Mennyiség</th>
                            <th>Ár (Ft)</th>
                            <th>Összesen (Ft)</th>
                            <th>Művelet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $vegosszeg = 0; 
                        foreach ($_SESSION['kosar'] as $id => $termek): 
                            if (!is_array($termek)) continue; // Ellenőrzés: csak tömb típusú elemeket dolgozunk fel
                            $osszesen = $termek['ar'] * $termek['mennyiseg'];
                            $vegosszeg += $osszesen;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($termek['tnev']) ?></td>
                            <td>
                                <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" alt="Kép" style="height: 50px;">
                            </td>
                            <td>
                                <a href="kosar.php?decrease=<?= $id ?>" class="btn btn-outline-secondary btn-sm">-</a>
                                <span class="mx-2"><?= intval($termek['mennyiseg']) ?></span>
                                <a href="kosar.php?increase=<?= $id ?>" class="btn btn-outline-secondary btn-sm">+</a>
                            </td>
                            <td><?= number_format($termek['ar'], 0, ',', ' ') ?></td>
                            <td><?= number_format($osszesen, 0, ',', ' ') ?></td>
                            <td>
                                <a href="kosar.php?remove=<?= $id ?>" class="btn btn-danger btn-sm">Törlés</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Végösszeg:</strong></td>
                            <td colspan="2"><?= number_format($vegosszeg, 0, ',', ' ') ?> Ft</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="text-center mt-4">
                    <button type="submit" name="megrendelem" class="btn btn-success">Megrendelem</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center">
                A kosár üres.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="mackobuy.php" class="btn btn-primary">Vissza a főoldalra</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
