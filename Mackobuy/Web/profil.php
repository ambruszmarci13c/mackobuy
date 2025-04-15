<?php
session_start();
include 'sql_fuggvenyek.php'; // Feltételezzük, hogy ez nem hoz létre saját $conn-t, vagy ha igen, akkor konzisztensen kellene használni

// Ellenőrizzük, hogy a user_id be van-e állítva a session-ben
if (!isset($_SESSION['user_id'])) {
    // Ha nincs bejelentkezve, irányítsuk át a bejelentkezési oldalra
    header('Location: mackobuy_bejelentkezes.php');
    exit; // Fontos a script futásának megállítása átirányítás után
}
$user_id = $_SESSION['user_id'];
$message = ""; // Üzenet inicializálása

// Adatbázis kapcsolat létrehozása a script elején
$conn = new mysqli("localhost", "root", "", "mackobuy");

// Kapcsolat ellenőrzése KÖZVETLENÜL a létrehozás után
if ($conn->connect_error) {
    // Naplózzuk a hibát ahelyett, hogy kiírnánk érzékeny infót
    error_log("Adatbázis kapcsolat hiba: " . $conn->connect_error);
    // Felhasználóbarát hibaüzenet
    die("Hiba történt a szolgáltatás elérésekor. Kérjük, próbálja meg később.");
}
// Karakterkészlet beállítása (ajánlott)
$conn->set_charset("utf8mb4");


// POST feldolgozása: jelszó módosítása vagy profil adatainak frissítése
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Jelszó módosításához a rejtett "action" mező segítségével különválasztjuk a két űrlapot
    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $message = "Minden mezőt ki kell tölteni a jelszó módosításához!";
            // Hozzáadva: Használj Bootstrap class-t a hiba jelzéséhez
            $message_class = "alert alert-danger";
        } elseif ($new_password !== $confirm_new_password) {
            $message = "Az új jelszavak nem egyeznek!";
            $message_class = "alert alert-danger";
        } else {
            // Lekérdezzük a jelenlegi jelszó hash-t a DB-ből
            $query = "SELECT jelszo FROM felhasznalok WHERE id = ?";
            $stmt = $conn->prepare($query);

             // Hibaellenőrzés prepare-re
             if ($stmt === false) {
                error_log("Prepare failed (SELECT jelszo): (" . $conn->errno . ") " . $conn->error);
                $message = "Adatbázis hiba (lekérdezés előkészítése sikertelen).";
                $message_class = "alert alert-danger";
            } else {
                $stmt->bind_param("i", $user_id);
                if (!$stmt->execute()) {
                     error_log("Execute failed (SELECT jelszo): (" . $stmt->errno . ") " . $stmt->error);
                     $message = "Adatbázis hiba (lekérdezés futtatása sikertelen).";
                     $message_class = "alert alert-danger";
                } else {
                    $result = $stmt->get_result();
                    $user_password_data = $result->fetch_assoc();

                    // Ellenőrzés: password_verify használata a hash összehasonlítására
                    if ($user_password_data && password_verify($current_password, $user_password_data['jelszo'])) {

                        // Új jelszó hash-elése
                        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                        $update_query = "UPDATE felhasznalok SET jelszo = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);

                        if (!$update_stmt) {
                             error_log("Prepare failed (UPDATE jelszo): (" . $conn->errno . ") " . $conn->error);
                             $message = "Adatbázis hiba (frissítés előkészítése sikertelen).";
                             $message_class = "alert alert-danger";
                        } else {
                            // Az ÚJ HASH-elt jelszót kötjük be
                            $update_stmt->bind_param("si", $new_hashed_password, $user_id);

                            if ($update_stmt->execute()) {
                                $message = "Jelszó sikeresen módosítva!";
                                $message_class = "alert alert-success"; // Siker üzenet stílusa
                            } else {
                                error_log("Execute failed (UPDATE jelszo): (" . $update_stmt->errno . ") " . $update_stmt->error);
                                $message = "Hiba történt a jelszó módosítása során.";
                                $message_class = "alert alert-danger";
                            }
                            $update_stmt->close();
                        }
                    } else {
                        $message = "A jelenlegi jelszó hibás!";
                        $message_class = "alert alert-danger";
                    }
                }
                $stmt->close(); // Mindig zárjuk be a statement-et
             }
        }
    } else { // Nincs 'action' vagy nem 'change_password' -> Profil adatok frissítése
        // Profil adatok frissítése
        $new_username = trim($_POST['username']); // Trim whitespace
        $new_email = trim($_POST['email']);

        // Validáció: üres mezők és email formátum
        if (empty($new_username) || empty($new_email)) {
            $message = "A felhasználónév és az e-mail cím megadása kötelező!";
            $message_class = "alert alert-danger";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
             $message = "Érvénytelen e-mail cím formátum!";
             $message_class = "alert alert-danger";
        } else {
             // Validáció: Egyediség ellenőrzése (opcionális, de ajánlott)
             // Ellenőrizzük, hogy az új felhasználónév foglalt-e MÁS felhasználó által
            $check_fnev_query = "SELECT id FROM felhasznalok WHERE fnev = ? AND id != ?";
            $check_fnev_stmt = $conn->prepare($check_fnev_query);
            $check_fnev_stmt->bind_param("si", $new_username, $user_id);
            $check_fnev_stmt->execute();
            $check_fnev_result = $check_fnev_stmt->get_result();

             // Ellenőrizzük, hogy az új e-mail foglalt-e MÁS felhasználó által
             $check_email_query = "SELECT id FROM felhasznalok WHERE email = ? AND id != ?";
             $check_email_stmt = $conn->prepare($check_email_query);
             $check_email_stmt->bind_param("si", $new_email, $user_id);
             $check_email_stmt->execute();
             $check_email_result = $check_email_stmt->get_result();

             if ($check_fnev_result->num_rows > 0) {
                 $message = "Ez a felhasználónév már foglalt!";
                 $message_class = "alert alert-danger";
             } elseif ($check_email_result->num_rows > 0) {
                 $message = "Ez az e-mail cím már foglalt!";
                 $message_class = "alert alert-danger";
             } else {
                // Ha minden rendben, frissítjük az adatokat
                $update_query = "UPDATE felhasznalok SET fnev = ?, email = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                if (!$update_stmt) {
                    error_log("Prepare failed (UPDATE profil): (" . $conn->errno . ") " . $conn->error);
                    $message = "Adatbázis hiba (profil frissítés előkészítése sikertelen).";
                    $message_class = "alert alert-danger";
                } else {
                    // htmlspecialchars NEM kell az adatbázisba mentéskor, csak kiíráskor!
                    // A prepare és bind_param gondoskodik az SQL injection elleni védelemről.
                    $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
                    if ($update_stmt->execute()) {
                        $message = "Profil sikeresen frissítve!";
                        $message_class = "alert alert-success";
                        // Frissítsük a $user tömböt is, hogy az oldal újratöltés nélkül is a friss adatokat mutassa
                        $user['username'] = $new_username;
                        $user['email'] = $new_email;
                    } else {
                         error_log("Execute failed (UPDATE profil): (" . $update_stmt->errno . ") " . $update_stmt->error);
                        $message = "Hiba történt a profil frissítésekor.";
                        $message_class = "alert alert-danger";
                    }
                    $update_stmt->close();
                }
             }
             $check_fnev_stmt->close();
             $check_email_stmt->close();
        }
    }
} // POST feldolgozás vége

// Felhasználói adatok lekérése (újra lekérdezzük a POST utáni állapotot, ha nem frissítettük a $user tömböt)
// De ha a POST végén frissítettük, ez nem feltétlenül szükséges, csak ha máshol is változhatott.
// Most kihagyom az újra lekérdezést, feltételezve, hogy a $user tömb frissítése elég a POST-ban.
$query = "SELECT fnev AS username, email, jogkor FROM felhasznalok WHERE id = ?";
$stmt_user = $conn->prepare($query); // Használjunk más nevet, mint a korábbi $stmt
if(!$stmt_user) { /* Hiba */ die("Hiba: " . $conn->error); }
$stmt_user->bind_param("i", $user_id);
if(!$stmt_user->execute()) { /* Hiba */ die("Hiba: " . $stmt_user->error); }
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc(); // Felülírjuk a $user tömböt a legfrissebb DB adatokkal
$stmt_user->close(); // Bezárjuk ezt a statementet is

if (!$user) {
    // Ha a felhasználó valahogy eltűnt a DB-ből (vagy user_id hibás)
    session_destroy(); // Munkamenet törlése
    header('Location: mackobuy_bejelentkezes.php?error=user_not_found');
    exit;
}
$is_admin = ($user['jogkor'] === 'admin');


// Rendelések lekérése (ez a rész változatlan a korábbi javítás óta)
// ... (a rendelések lekérdezésének kódja, $orders tömb feltöltése) ...
$orders_query = "SELECT id, rendeles_datum, vegosszeg, statusz
                 FROM rendelesek
                 WHERE FID = ?
                 ORDER BY rendeles_datum DESC";
$orders = [];
$db_error_orders = null;
// ... (a teljes rendelés lekérdezési blokk hibakezeléssel, ahogy korábban mutattam) ...
// Egyszerűsített változat most:
if ($orders_stmt = $conn->prepare($orders_query)) {
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    if ($orders_result) {
        while ($row = $orders_result->fetch_assoc()) { $orders[] = $row; }
    } else { $db_error_orders = "Hiba: " . $conn->error; }
    $orders_stmt->close();
} else { $db_error_orders = "Hiba: " . $conn->error; }


?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="profil.css">
</head>
<body class="<?php // Itt lehet beolvasni a cookie-t vagy localStorage-t a dark mode-hoz ?>">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
        <!-- Navbar tartalom... (változatlan) -->
         <div class="container">
            <a class="navbar-brand" href="mackobuy.php">
                <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;" title="Főoldal">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="kosar.php">
                            <img src="kepek/kosar.png" alt="Kosár" style="height: 38px;" title="Kosár">
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="kepek/profil.png" alt="Profil" style="height: 40px;" title="Profil">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profilDropdown">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <li><a class="dropdown-item active" href="profil.php">Profilom</a></li> 
                                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Kijelentkezés</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Bejelentkezés</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="adminbuy.php">
                            <img src="kepek/admin.png" alt="Admin" style="height: 40px;" title="Admin Felület">
                        </a>
                    </li>
                    <?php endif; ?>
                     <li class="nav-item ms-2">
                         <button id="darkModeToggle" class="btn btn-outline-dark btn-sm">🌙</button>
                     </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Fő tartalom -->
    <div class="container profile-container">
        <h2 class="text-center mb-4">Profil</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo htmlspecialchars($message_class); ?>" role="alert">
                 <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Felhasználói adatok és Extra funkciók (változatlan struktúra) -->
         <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <h4>Felhasználói adatok</h4>
                <form method="POST" action="profil.php">
                     <div class="mb-3">
                        <label for="username" class="form-label">Felhasználónév</label>
                        <input type="text" class="form-control form-control-sm" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control form-control-sm" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Profil frissítése</button>
                </form>
            </div>
            <div class="col-md-6">
                <h4>Extra funkciók</h4>
                <div class="profile-actions">
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                       <i class="bi bi-key-fill"></i> Jelszó módosítása
                    </button>
                    <a href="favorites.php" id="kedvenctermekek" class="btn btn-info btn-sm">
                       <i class="bi bi-heart-fill"></i> Kedvenc termékek
                    </a>
                </div>
            </div>
        </div>

        <!-- Korábbi rendelések (változatlan HTML struktúra, de a PHP rész frissült) -->
         <hr class="my-4">
        <h4 class="text-center mb-3">Korábbi rendeléseid</h4>
        <?php if ($db_error_orders): ?>
            <div class="alert alert-warning text-center" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Hiba történt a rendelések betöltésekor.
            </div>
        <?php elseif (!empty($orders)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover orders-table table-sm align-middle">
                   <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Dátum</th><th class="text-end">Végösszeg</th><th class="text-center">Státusz</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y.m.d', strtotime($order['rendeles_datum']))); ?></td>
                                <td class="text-end text-nowrap"><?php echo htmlspecialchars(number_format($order['vegosszeg'], 0, ',', ' ')) . " Ft"; ?></td>
                                <td class="text-center">
                                    <span class="badge <?php /* Státusz szín kód */
                                        $statusz_lower = strtolower($order['statusz'] ?? '');
                                        switch ($statusz_lower) {
                                            case 'kézbesítve': echo 'bg-success'; break; case 'kiszállítva': echo 'bg-primary'; break; case 'lemondva': echo 'bg-danger'; break; case 'szállítás alatt': echo 'bg-warning text-dark'; break; case 'feldolgozás alatt': echo 'bg-info text-dark'; break; default: echo 'bg-secondary'; break;
                                        } ?>">
                                        <?php echo htmlspecialchars(ucfirst($order['statusz'] ?? 'Ismeretlen')); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                <i class="bi bi-info-circle"></i> Még nincsenek korábbi rendeléseid.
            </div>
        <?php endif; ?>
    </div>

    <!-- Jelszó módosítás Modal (változatlan struktúra) -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="profil.php">
                 <input type="hidden" name="action" value="change_password">
                 <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">Jelszó módosítása</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezár"></button>
                 </div>
                 <div class="modal-body">
                   <div class="mb-3">
                     <label for="current_password" class="form-label">Jelenlegi jelszó</label>
                     <input type="password" class="form-control form-control-sm" id="current_password" name="current_password" required>
                   </div>
                   <div class="mb-3">
                     <label for="new_password" class="form-label">Új jelszó</label>
                     <input type="password" class="form-control form-control-sm" id="new_password" name="new_password" required>
                   </div>
                   <div class="mb-3">
                     <label for="confirm_new_password" class="form-label">Új jelszó megerősítése</label>
                     <input type="password" class="form-control form-control-sm" id="confirm_new_password" name="confirm_new_password" required>
                   </div>
                 </div>
                 <div class="modal-footer">
                   <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Mégse</button>
                   <button type="submit" class="btn btn-primary btn-sm">Jelszó módosítása</button>
                 </div>
              </form>
            </div>
        </div>
    </div>

    <!-- Footer (Változatlan) -->
    <footer>
        <div class="container">
            <p>© <?php echo date('Y'); ?> MackoBuy. Minden jog fenntartva.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Dark Mode JS (változatlan)
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButton = document.getElementById("darkModeToggle");
            const body = document.body;
            const currentTheme = localStorage.getItem("dark-mode");

             function setTheme(theme) {
                if (theme === "enabled") {
                    body.classList.add("dark-mode");
                    if (toggleButton) toggleButton.textContent = "☀️";
                } else {
                    body.classList.remove("dark-mode");
                     if (toggleButton) toggleButton.textContent = "🌙";
                }
            }
            setTheme(currentTheme);
            if (toggleButton) {
                toggleButton.addEventListener("click", function() {
                    const isDarkMode = body.classList.contains("dark-mode");
                    const newTheme = isDarkMode ? "disabled" : "enabled";
                    setTheme(newTheme);
                    localStorage.setItem("dark-mode", newTheme);
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Kapcsolat bezárása
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>