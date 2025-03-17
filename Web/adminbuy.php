<?php
session_start();
include 'sql_fuggvenyek.php';

// Csak adminisztrátorok érhetik el (opcionális)
/*
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}
*/

// Keresési feltétel
$kereses = isset($_GET['kereses']) ? trim($_GET['kereses']) : '';

// Termékek lekérdezése szűrés alapján
if ($kereses) {
    // A keresett értékekhez csak a lekérdezésben adjuk hozzá a `%` karaktereket
    $termekek = adatokLekeres("SELECT * FROM termekek WHERE tnev LIKE ? OR leiras LIKE ?", ["%{$kereses}%", "%{$kereses}%"]);
} else {
    $termekek = adatokLekeres("SELECT * FROM termekek");
}

// Új termék hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $tnev = $_POST['tnev'];
    $leiras = $_POST['leiras'];
    $ar = (int)$_POST['ar'];
    $kategoria = $_POST['kategoria'];
    $kep = $_POST['kep'];

    adatokValtoztatasa("INSERT INTO termekek (tnev, leiras, ar, kategoria, kep) VALUES (?, ?, ?, ?, ?)", [$tnev, $leiras, $ar, $kategoria, $kep]);
    header('Location: adminbuy.php');
    exit;
}

// Termék módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit'])) {
    $id = (int)$_POST['id'];
    $tnev = $_POST['tnev'];
    $leiras = $_POST['leiras'];
    $ar = (int)$_POST['ar'];
    $kategoria = $_POST['kategoria'];
    $kep = $_POST['kep'];

    adatokValtoztatasa("UPDATE termekek SET tnev = ?, leiras = ?, ar = ?, kategoria = ?, kep = ? WHERE ID = ?", [$tnev, $leiras, $ar, $kategoria, $kep, $id]);
    header('Location: adminbuy.php');
    exit;
}

// Termék törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    adatokValtoztatasa("DELETE FROM termekek WHERE ID = ?", [$id]);
    header('Location: adminbuy.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adminisztrációs Felület</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="adminbuy.css">
</head>
<body>

    <nav>
        <a href="profil.php">
            <i class="bi bi-arrow-left-circle"></i>
        </a>
    </nav>

    <div class="container my-5">
        <h1 class="text-center">Adminisztrációs Felület</h1>

        <!-- Keresőmező -->
        <form method="GET" class="my-4">
        <div class="input-group">
            <input type="text" name="kereses" class="form-control" placeholder="Keresés termékek között..." value="<?= htmlspecialchars($kereses) ?>">
            <button class="btn btn-primary" type="submit">Keresés</button>
        </div>
        </form>

        <!-- Új termék hozzáadása -->
        <div class="card my-4">
            <div class="card-body">
                <h2>Új termék hozzáadása</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="tnev" class="form-label">Termék neve</label>
                        <input type="text" class="form-control" id="tnev" name="tnev" required>
                    </div>
                    <div class="mb-3">
                        <label for="leiras" class="form-label">Leírás</label>
                        <textarea class="form-control" id="leiras" name="leiras" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="ar" class="form-label">Ár (Ft)</label>
                        <input type="number" class="form-control" id="ar" name="ar" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategoria" class="form-label">Kategória</label>
                        <input type="text" class="form-control" id="kategoria" name="kategoria" required>
                    </div>
                    <div class="mb-3">
                        <label for="kep" class="form-label">Kép fájlneve</label>
                        <input type="text" class="form-control" id="kep" name="kep" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-success">Hozzáadás</button>
                </form>
            </div>
        </div>

        <!-- Termékek listája -->
        <h2 class="my-4">Termékek kezelése</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Név</th>
                    <th>Leírás</th>
                    <th>Ár</th>
                    <th>Kategória</th>
                    <th>Kép</th>
                    <th>Műveletek</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($termekek as $termek): ?>
                    <tr>
                        <td><?= htmlspecialchars($termek['ID']) ?></td>
                        <td><?= htmlspecialchars($termek['tnev']) ?></td>
                        <td><?= htmlspecialchars($termek['leiras']) ?></td>
                        <td><?= htmlspecialchars($termek['ar']) ?> Ft</td>
                        <td><?= htmlspecialchars($termek['kategoria']) ?></td>
                        <td><img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" alt="Kép" style="height: 50px;"></td>
                        <td>
                            <!-- Módosítás gomb, amely a modalt nyitja meg -->
                            <button class="btn btn-warning edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal"
                                    data-id="<?= $termek['ID'] ?>"
                                    data-tnev="<?= htmlspecialchars($termek['tnev']) ?>"
                                    data-leiras="<?= htmlspecialchars($termek['leiras']) ?>"
                                    data-ar="<?= $termek['ar'] ?>"
                                    data-kategoria="<?= htmlspecialchars($termek['kategoria']) ?>"
                                    data-kep="<?= htmlspecialchars($termek['kep']) ?>">
                                Módosítás
                            </button>

                            <!-- Törlés gomb -->
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="id" value="<?= $termek['ID'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger">Törlés</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Módosítási modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Termék módosítása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label for="edit-tnev" class="form-label">Termék neve</label>
                            <input type="text" class="form-control" id="edit-tnev" name="tnev" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-leiras" class="form-label">Leírás</label>
                            <textarea class="form-control" id="edit-leiras" name="leiras" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit-ar" class="form-label">Ár (Ft)</label>
                            <input type="number" class="form-control" id="edit-ar" name="ar" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-kategoria" class="form-label">Kategória</label>
                            <input type="text" class="form-control" id="edit-kategoria" name="kategoria" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-kep" class="form-label">Kép fájlneve</label>
                            <input type="text" class="form-control" id="edit-kep" name="kep" required>
                        </div>
                        <button type="submit" name="save_edit" class="btn btn-primary">Mentés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap és saját JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modalba tölti az adatokat, ha egy módosítás gombra kattintanak
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit-id').value = this.dataset.id;
                document.getElementById('edit-tnev').value = this.dataset.tnev;
                document.getElementById('edit-leiras').value = this.dataset.leiras;
                document.getElementById('edit-ar').value = this.dataset.ar;
                document.getElementById('edit-kategoria').value = this.dataset.kategoria;
                document.getElementById('edit-kep').value = this.dataset.kep;
            });
        });
    </script>
</body>
</html>
