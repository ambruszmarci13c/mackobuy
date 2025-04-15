<?php
session_start();
include 'sql_fuggvenyek.php'; // Győződj meg róla, hogy ez kezeli a DECIMAL típust is helyesen!

// Csak adminisztrátorok érhetik el (opcionális)
/*
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}
*/

// --- TERMÉK KEZELÉS - PHP ---

// Kategóriák lekérdezése
$kategoriak = adatokLekeres("SELECT id, nev FROM kategoria ORDER BY id");

// Keresési feltétel (Termékekhez)
$kereses_termek = isset($_GET['kereses_termek']) ? trim($_GET['kereses_termek']) : '';

// Termékek lekérdezése szűrés alapján JOIN-nal
$sql_select_termekek = "
    SELECT
        t.ID, t.tnev, t.leiras, t.ar, t.kategoria AS kategoria_id, t.kep,
        k.nev AS kategoria_nev
    FROM termekek t
    LEFT JOIN kategoria k ON t.kategoria = k.id
";
if ($kereses_termek) {
    $sql_where = " WHERE t.tnev LIKE ? OR t.leiras LIKE ?";
    $params = ["%{$kereses_termek}%", "%{$kereses_termek}%"];
    $termekek = adatokLekeres($sql_select_termekek . $sql_where, $params);
} else {
    $termekek = adatokLekeres($sql_select_termekek . " ORDER BY t.ID");
}

// Új termék hozzáadása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $tnev = $_POST['tnev']; $leiras = $_POST['leiras']; $ar = (int)$_POST['ar']; $kategoria_id = (int)$_POST['kategoria']; $kep = $_POST['kep'];
    adatokValtoztatasa("INSERT INTO termekek (tnev, leiras, ar, kategoria, kep) VALUES (?, ?, ?, ?, ?)", [$tnev, $leiras, $ar, $kategoria_id, $kep]);
    header('Location: adminbuy.php?success=add#termekek'); exit;
}

// Termék módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_edit_product'])) {
    $id = (int)$_POST['id']; $tnev = $_POST['tnev']; $leiras = $_POST['leiras']; $ar = (int)$_POST['ar']; $kategoria_id = (int)$_POST['kategoria']; $kep = $_POST['kep'];
    adatokValtoztatasa("UPDATE termekek SET tnev = ?, leiras = ?, ar = ?, kategoria = ?, kep = ? WHERE ID = ?", [$tnev, $leiras, $ar, $kategoria_id, $kep, $id]);
    header('Location: adminbuy.php?success=edit#termekek'); exit;
}

// Termék törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['id'];
    // Ideális esetben ellenőrizni kellene a rendeles_tetelek táblát is, mielőtt törölsz
    adatokValtoztatasa("DELETE FROM termekek WHERE ID = ?", [$id]);
    header('Location: adminbuy.php?success=delete#termekek'); exit;
}

// --- RENDELÉSEK KEZELÉSE - PHP ---

// Lehetséges állapotok
$rendeles_statuszok = ['feldolgozás alatt', 'szállítás alatt', 'kiszállítva', 'kézbesítve', 'lemondva'];

// Rendelés állapotának frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $rendeles_id = (int)$_POST['rendeles_id'];
    $uj_statusz = $_POST['uj_statusz'];
    if (in_array($uj_statusz, $rendeles_statuszok)) {
        adatokValtoztatasa("UPDATE rendelesek SET statusz = ? WHERE ID = ?", [$uj_statusz, $rendeles_id]);
        header('Location: adminbuy.php?success=status_updated#rendeles-' . $rendeles_id); exit;
    } else {
        header('Location: adminbuy.php?error=invalid_status#rendelesek'); exit;
    }
}

// Rendelések lekérdezése a listához (fő adatok)
$rendelesek = adatokLekeres(
    "SELECT
        r.ID, r.FID, r.vegosszeg, r.rendeles_datum, r.szallitasi_dij, r.statusz,
        f.fnev AS felhasznalo_nev, f.email AS felhasznalo_email
    FROM rendelesek r
    LEFT JOIN felhasznalok f ON r.FID = f.ID
    ORDER BY r.rendeles_datum DESC, r.ID DESC"
);

// AJAX KÉRÉS KEZELÉSE A RENDELÉS RÉSZLETEIHEZ
if (isset($_GET['action']) && $_GET['action'] == 'get_order_details' && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $response = ['orderInfo' => null];

    $orderInfoQuery = "SELECT
                        r.ID, r.FID, r.vegosszeg, r.rendeles_datum, r.szallitasi_dij, r.statusz,
                        f.fnev AS felhasznalo_nev, f.email AS felhasznalo_email
                       FROM rendelesek r
                       LEFT JOIN felhasznalok f ON r.FID = f.ID
                       WHERE r.ID = ?";
    $orderInfoResult = adatokLekeres($orderInfoQuery, [$order_id]);

    if (!empty($orderInfoResult)) {
        $response['orderInfo'] = $orderInfoResult[0];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
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
    <style>
        /* Leírás cella rövidítése (ha használni akarod) */
        .leiras-cella {
            max-width: 250px; /* Limitálja a szélességet */
            white-space: nowrap; /* Ne törjön sorokat */
            overflow: hidden; /* Rejtse a túlnyúlást */
            text-overflow: ellipsis; /* ... jelölés a végén */
            vertical-align: middle;
        }
        /* Aktív fül jelölése */
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link { background-color: #0d6efd; color: white; }
        /* Görgetési célpontokhoz offset */
        section { scroll-margin-top: 70px; /* Nav magassága */ }
    </style>
</head>
<body>
    <nav class="sticky-top bg-light py-2 mb-4 border-bottom shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Vissza gomb (opcionális) -->
            <a href="profil.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left-circle"></i> Profil
            </a>
            <!-- Navigációs linkek a sections-höz -->
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link" href="#termekek">Termékek Kezelése</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#rendelesek">Rendelések Kezelése</a>
                </li>
            </ul>
            <span style="width: 80px;"></span> <!-- Placeholder az egyensúlyhoz -->
        </div>
    </nav>

    <div class="container my-4">
        <h1 class="text-center mb-5">Adminisztrációs Felület</h1>

        <!-- Sikeres/Hiba üzenetek -->
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                    switch ($_GET['success']) {
                        case 'add': echo 'Termék sikeresen hozzáadva.'; break;
                        case 'edit': echo 'Termék sikeresen módosítva.'; break;
                        case 'delete': echo 'Termék sikeresen törölve.'; break;
                        case 'status_updated': echo 'Rendelés állapota sikeresen frissítve.'; break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                    switch ($_GET['error']) {
                        case 'invalid_status': echo 'Hiba: Érvénytelen rendelési állapot lett megadva.'; break;
                    }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ======================== -->
        <!-- TERMÉK KEZELÉS SZEKCIÓ   -->
        <!-- ======================== -->
        <section id="termekek" class="mb-5 pt-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Termék Kezelés</h2>
                <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#addProductForm" aria-expanded="false" aria-controls="addProductForm">
                    <i class="bi bi-plus-circle"></i> Új termék
                </button>
            </div>

            <!-- Új termék hozzáadása (Összecsukható) -->
            <div class="collapse" id="addProductForm">
                <div class="card card-body mb-4 shadow-sm">
                    <h5><i class="bi bi-box-seam"></i> Új termék rögzítése</h5>
                    <hr class="mt-1 mb-3">
                    <form method="POST" action="adminbuy.php#termekek">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tnev" class="form-label">Termék neve</label>
                                <input type="text" class="form-control form-control-sm" id="tnev" name="tnev" required>
                            </div>
                            <div class="col-md-6">
                                <label for="ar" class="form-label">Ár (Ft)</label>
                                <input type="number" class="form-control form-control-sm" id="ar" name="ar" required min="0">
                            </div>
                            <div class="col-12">
                                <label for="leiras" class="form-label">Leírás</label>
                                <textarea class="form-control form-control-sm" id="leiras" name="leiras" rows="3" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="kategoria" class="form-label">Kategória</label>
                                <select class="form-select form-select-sm" id="kategoria" name="kategoria" required>
                                    <option value="" selected disabled>Válasszon...</option>
                                    <?php foreach ($kategoriak as $kat): ?>
                                        <option value="<?= htmlspecialchars($kat['id']) ?>">
                                            <?= htmlspecialchars($kat['id']) ?> - <?= htmlspecialchars($kat['nev']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="kep" class="form-label">Kép fájlneve</label>
                                <input type="text" class="form-control form-control-sm" id="kep" name="kep" placeholder="pl: kep.jpg" required>
                            </div>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-primary btn-sm mt-3">Termék hozzáadása</button>
                    </form>
                </div>
            </div>

            <!-- Termék Keresőmező -->
            <form method="GET" action="adminbuy.php#termekek" class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="kereses_termek" class="form-control" placeholder="Keresés termékek között név vagy leírás alapján..." value="<?= htmlspecialchars($kereses_termek) ?>">
                    <button class="btn btn-outline-primary" type="submit">Keresés</button>
                    <?php if ($kereses_termek): ?>
                        <a href="adminbuy.php#termekek" class="btn btn-outline-secondary">Törlés</a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Termékek listája -->
            <?php if (empty($termekek)): ?>
                <div class="alert alert-info">
                    <?php echo $kereses_termek ? 'Nincs a keresésnek megfelelő termék.' : 'Nincsenek termékek az adatbázisban.'; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Kép</th>
                                <th>Név</th>
                                <th>Kategória</th>
                                <th>Leírás</th>
                                <th class="text-end">Ár</th>
                                <th class="text-center">Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($termekek as $termek): ?>
                                <tr>
                                    <td><?= htmlspecialchars($termek['ID']) ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($termek['kep']) && file_exists('kepek/' . $termek['kep'])): ?>
                                            <img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" alt="" style="height: 35px; width: auto; max-width: 50px; object-fit: contain;">
                                        <?php else: ?>
                                            <i class="bi bi-image text-muted fs-5" title="Nincs kép"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($termek['tnev']) ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($termek['kategoria_id']) ?> -
                                        <?= htmlspecialchars($termek['kategoria_nev'] ?? '?') ?></small>
                                    </td>
                                    <td class="leiras-cella" title="<?= htmlspecialchars($termek['leiras']) ?>">
                                        <?= nl2br(htmlspecialchars($termek['leiras'])) ?>
                                    </td>
                                    <td class="text-end text-nowrap"><?= htmlspecialchars(number_format($termek['ar'], 0, ',', ' ')) ?> Ft</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-outline-warning btn-sm edit-product-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editProductModal"
                                                data-id="<?= $termek['ID'] ?>"
                                                data-tnev="<?= htmlspecialchars($termek['tnev']) ?>"
                                                data-leiras="<?= htmlspecialchars($termek['leiras']) ?>"
                                                data-ar="<?= $termek['ar'] ?>"
                                                data-kategoria="<?= $termek['kategoria_id'] ?>"
                                                data-kep="<?= htmlspecialchars($termek['kep']) ?>"
                                                title="Módosítás">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <form method="POST" action="adminbuy.php#termekek" class="d-inline" onsubmit="return confirm('Biztosan törölni szeretné ezt a terméket?');">
                                            <input type="hidden" name="id" value="<?= $termek['ID'] ?>">
                                            <button type="submit" name="delete_product" class="btn btn-outline-danger btn-sm" title="Törlés">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section> <!-- /#termekek -->

        <hr class="my-5">

        <!-- ========================= -->
        <!-- RENDELÉS KEZELÉS SZEKCIÓ -->
        <!-- ========================= -->
        <section id="rendelesek" class="mb-5 pt-3">
            <h2 class="mb-4">Rendelések Kezelése</h2>

            <?php if (empty($rendelesek)): ?>
                <div class="alert alert-info">Nincsenek rendelések az adatbázisban.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Rend. ID</th>
                                <th>Dátum</th>
                                <th>Felhasználó</th>
                                <th class="text-end">Száll. díj</th>
                                <th class="text-end">Végösszeg</th>
                                <th class="text-center">Státusz</th>
                                <th class="text-center">Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rendelesek as $rendeles): ?>
                                <tr id="rendeles-<?= $rendeles['ID'] ?>">
                                    <td><?= htmlspecialchars($rendeles['ID']) ?></td>
                                    <td class="text-nowrap"><small><?= htmlspecialchars(date('Y-m-d H:i', strtotime($rendeles['rendeles_datum'] ?? time()))) ?></small></td>
                                    <td>
                                        <?= htmlspecialchars($rendeles['felhasznalo_nev'] ?? 'N/A') ?>
                                        <?php if (!empty($rendeles['felhasznalo_email'])): ?>
                                            <br><small class="text-muted"><i class="bi bi-envelope"></i> <?= htmlspecialchars($rendeles['felhasznalo_email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-nowrap"><?= htmlspecialchars(number_format($rendeles['szallitasi_dij'] ?? 0, 0, ',', ' ')) ?> Ft</td>
                                    <td class="text-end text-nowrap"><?= htmlspecialchars(number_format($rendeles['vegosszeg'], 0, ',', ' ')) ?> Ft</td>
                                    <td class="text-center">
                                        <span class="badge
                                            <?php
                                                $statusz = $rendeles['statusz'] ?? 'ismeretlen';
                                                switch (strtolower($statusz)) {
                                                    case 'kiszállítva': case 'kézbesítve': echo 'bg-success'; break;
                                                    case 'lemondva': echo 'bg-danger'; break;
                                                    case 'szállítás alatt': echo 'bg-warning text-dark'; break;
                                                    case 'feldolgozás alatt': echo 'bg-info text-dark'; break;
                                                    default: echo 'bg-secondary'; break;
                                                }
                                            ?>">
                                            <?= htmlspecialchars(ucfirst($statusz)) ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-outline-primary btn-sm view-order-details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#orderDetailsModal"
                                                data-order-id="<?= $rendeles['ID'] ?>"
                                                title="Rendelés Részletei">
                                            <i class="bi bi-eye-fill"></i> Részletek
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section> <!-- /#rendelesek -->
    </div> <!-- /.container -->

    <!-- ================= -->
    <!--      MODALOK      -->
    <!-- ================= -->

    <!-- Termék Módosítás Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="adminbuy.php#termekek">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProductModalLabel">Termék módosítása</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-product-id">
                        <div class="mb-3">
                            <label for="edit-product-tnev" class="form-label">Termék neve</label>
                            <input type="text" class="form-control" id="edit-product-tnev" name="tnev" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-product-leiras" class="form-label">Leírás</label>
                            <textarea class="form-control" id="edit-product-leiras" name="leiras" rows="4" required></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit-product-ar" class="form-label">Ár (Ft)</label>
                                <input type="number" class="form-control" id="edit-product-ar" name="ar" required min="0">
                            </div>
                            <div class="col-md-6">
                                <label for="edit-product-kep" class="form-label">Kép fájlneve</label>
                                <input type="text" class="form-control" id="edit-product-kep" name="kep" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="edit-product-kategoria" class="form-label">Kategória</label>
                            <select class="form-select" id="edit-product-kategoria" name="kategoria" required>
                                <option value="" disabled>Válasszon...</option>
                                <?php foreach ($kategoriak as $kat): ?>
                                    <option value="<?= htmlspecialchars($kat['id']) ?>">
                                        <?= htmlspecialchars($kat['id']) ?> - <?= htmlspecialchars($kat['nev']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                        <button type="submit" name="save_edit_product" class="btn btn-primary">Mentés</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Rendelés Részletek és Státusz Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md"> <!-- Módosítva modal-xl-ről modal-lg-re -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Rendelés Részletei (ID: <span id="modal-order-id"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-order-loading" class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Töltés...</span></div></div>
                <div id="modal-order-error" class="alert alert-danger" style="display: none;"></div>
                <div id="modal-order-content" style="display: none;">
                    <h6><i class="bi bi-person-circle"></i> Felhasználó adatai</h6>
                    <p class="small">
                        <strong>Név:</strong> <span id="modal-user-name">-</span><br>
                        <strong>Email:</strong> <span id="modal-user-email">-</span><br>
                        <span class="text-muted">(Felhasználó ID: <span id="modal-user-fid">-</span>)</span>
                    </p>
                    <hr>
                    <h6><i class="bi bi-receipt"></i> Rendelés adatai</h6>
                    <p class="small">
                        <strong>Dátum:</strong> <span id="modal-order-date">-</span><br>
                        <strong>Szállítási díj:</strong> <span id="modal-order-shipping">0</span> Ft<br>
                        <strong>Végösszeg:</strong> <strong class="text-primary"><span id="modal-order-total">0</span> Ft</strong>
                    </p>
                    <hr>
                    <h6><i class="bi bi-truck"></i> Státusz Módosítása</h6>
                    <form method="POST" action="adminbuy.php#rendelesek" id="statusUpdateForm">
                        <input type="hidden" name="rendeles_id" id="modal-status-order-id">
                        <div class="input-group input-group-sm">
                            <select class="form-select" name="uj_statusz" id="modal-order-status-select" required>
                                <option value="" disabled selected>Válasszon...</option>
                                <?php foreach ($rendeles_statuszok as $stat): ?>
                                    <option value="<?= htmlspecialchars($stat) ?>"><?= htmlspecialchars(ucfirst($stat)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_order_status" class="btn btn-primary">Frissítés</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bezárás</button>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap és saját JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Termék modal feltöltése
        document.querySelectorAll('.edit-product-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit-product-id').value = this.dataset.id;
                document.getElementById('edit-product-tnev').value = this.dataset.tnev;
                document.getElementById('edit-product-leiras').value = this.dataset.leiras;
                document.getElementById('edit-product-ar').value = this.dataset.ar;
                document.getElementById('edit-product-kategoria').value = this.dataset.kategoria;
                document.getElementById('edit-product-kep').value = this.dataset.kep;
            });
        });

        // Bootstrap Tooltip inicializálása
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Alert automatikus eltüntetése
        const alertElements = document.querySelectorAll('.alert-dismissible');
        alertElements.forEach(alertElement => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alertElement);
                if (bsAlert) { bsAlert.close(); }
            }, 5000);
        });

        // Aktív fül beállítása és kezelése
        const setActiveTab = () => {
            const currentHash = window.location.hash || '#termekek';
            document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentHash) {
                    link.classList.add('active');
                }
            });
            if (!document.querySelector('.nav-pills .nav-link.active') && currentHash.startsWith('#rendeles')) {
                const orderTab = document.querySelector('.nav-pills .nav-link[href="#rendelesek"]');
                if (orderTab) orderTab.classList.add('active');
            }
            if (!document.querySelector('.nav-pills .nav-link.active')) {
                const defaultTab = document.querySelector('.nav-pills .nav-link[href="#termekek"]');
                if (defaultTab) defaultTab.classList.add('active');
            }
        };
        window.addEventListener('hashchange', setActiveTab);
        document.addEventListener('DOMContentLoaded', setActiveTab);
        document.querySelectorAll('.nav-pills .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.nav-pills .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // --- RENDELÉS RÉSZLETEK MODAL KEZELÉSE ---
        const orderDetailsModal = document.getElementById('orderDetailsModal');
        const modalLoading = document.getElementById('modal-order-loading');
        const modalContent = document.getElementById('modal-order-content');
        const modalError = document.getElementById('modal-order-error');

        function escapeHTML(str) {
            if (typeof str !== 'string' || str === null || str === undefined) {
                return str;
            }
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": "'"
            };
            return str.replace(/[&<>"']/g, m => map[m]);
        }

        orderDetailsModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');

            // Reset modal state
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            modalError.style.display = 'none';
            modalError.textContent = 'Hiba történt az adatok betöltése közben.';
            document.getElementById('modal-order-id').textContent = orderId;
            document.getElementById('modal-status-order-id').value = orderId;

            // Fetch order details
            fetch(`adminbuy.php?action=get_order_details&order_id=${orderId}`)
                .then(response => {
                    if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
                    return response.json();
                })
                .then(data => {
                    modalLoading.style.display = 'none';

                    if (data && data.orderInfo) {
                        const info = data.orderInfo;
                        document.getElementById('modal-user-name').textContent = escapeHTML(info.felhasznalo_nev) || '-';
                        document.getElementById('modal-user-email').textContent = escapeHTML(info.felhasznalo_email) || '-';
                        document.getElementById('modal-user-fid').textContent = escapeHTML(info.FID) || '-';
                        document.getElementById('modal-order-date').textContent = info.rendeles_datum ? new Date(info.rendeles_datum).toLocaleString('hu-HU', { dateStyle: 'short', timeStyle: 'short'}) : '-';
                        document.getElementById('modal-order-shipping').textContent = Number(info.szallitasi_dij || 0).toLocaleString('hu-HU');
                        document.getElementById('modal-order-total').textContent = Number(info.vegosszeg || 0).toLocaleString('hu-HU');
                        document.getElementById('modal-order-status-select').value = info.statusz || "";
                        modalContent.style.display = 'block';
                    } else {
                        modalError.textContent = 'Nem található rendelés ezzel az ID-vel, vagy hiba történt az adatok feldolgozása során.';
                        modalError.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error fetching order details:', error);
                    modalLoading.style.display = 'none';
                    modalError.textContent = `Hiba: ${error.message}`;
                    modalError.style.display = 'block';
                });
        });
    </script>
</body>
</html>