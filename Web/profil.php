<?php
session_start();
include 'sql_fuggvenyek.php';

$user_id = $_SESSION['user_id'];
$message = "";
$conn = new mysqli("localhost", "root", "", "mackobuy");
if ($conn->connect_error) {
    die("Adatbázis kapcsolat hiba: " . $conn->connect_error);
}

// POST feldolgozása: jelszó módosítása vagy profil adatainak frissítése
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Jelszó módosításához a rejtett "action" mező segítségével különválasztjuk a két űrlapot
    if (isset($_POST['action']) && $_POST['action'] == 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $message = "Minden mezőt ki kell tölteni a jelszó módosításához!";
        } elseif ($new_password !== $confirm_new_password) {
            $message = "Az új jelszavak nem egyeznek!";
        } else {
            // Lekérdezzük a jelenlegi jelszót a DB-ből (feltételezve, hogy a 'jelszo' oszlop tárolja a hash-t)
            $query = "SELECT jelszo FROM felhasznalok WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_password_data = $result->fetch_assoc();

            if ($user_password_data && password_verify($current_password, $user_password_data['jelszo'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE felhasznalok SET jelszo = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("si", $hashed_new_password, $user_id);
                if ($update_stmt->execute()) {
                    $message = "Jelszó sikeresen módosítva!";
                } else {
                    $message = "Hiba történt a jelszó módosítása során.";
                }
            } else {
                $message = "A jelenlegi jelszó hibás!";
            }
        }
    } else {
        // Profil adatok frissítése
        $new_username = htmlspecialchars(trim($_POST['username']));
        $new_email = htmlspecialchars(trim($_POST['email']));
        if (!empty($new_username) && !empty($new_email)) {
            $update_query = "UPDATE felhasznalok SET fnev = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);
            if ($update_stmt->execute()) {
                $message = "Profil frissítve!";
            } else {
                $message = "Hiba történt a profil frissítésekor.";
            }
        } else {
            $message = "Minden mezőt ki kell tölteni!";
        }
    }
}

// Felhasználói adatok lekérése
$query = "SELECT fnev AS username, email, jogkor FROM felhasznalok WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    die("A felhasználói adatok nem sikerült lekérni.");
}
$is_admin = ($user['jogkor'] === 'admin');

// Rendelések lekérése
$orders_query = "SELECT id, rendeles_datum AS order_date, vegosszeg AS total_price 
                 FROM rendelesek 
                 WHERE FID = ? 
                 ORDER BY rendeles_datum DESC";
$orders_stmt = $conn->prepare($orders_query);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="profil.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 70px;
        }
        .profile-container {
            max-width: 1200px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            flex: 1;
        }
        .navbar-custom {
            background-color: lightgrey;
            opacity: 80%;
        }
        .orders-table th {
            background-color: #6ccccc;
            color: #ffffff;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        footer {
            background-color: #6ccccc;
            color: #ffffff;
            padding: 10px 0;
            text-align: center;
            margin-top: auto;
            width: 100%;
        }
        .profile-actions a,
        .profile-actions button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="mackobuy.php">
                <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;" title="Főoldal">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
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
            <?php
             if (isset($_SESSION['user_id'])): ?>
                <li><a class="dropdown-item" href="profil.php">Profilom</a></li>
                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Kijelentkezés</a></li>
            <?php else: ?>
                <li><a class="dropdown-item" href="mackobuy_bejelentkezes.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </li>
                    <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="adminbuy.php">
                            <img src="kepek/admin.png" alt="Admin" style="height: 40px;" title="Admin">
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="mackobuy_bejelentkezes.php">
                            <img src="kepek/logout.png" alt="Kijelentkezés" style="height: 40px;" title="Kijelentkezés">
                        </a>
                    </li>
                </ul>
            </div>
            <div class="nav-item">
                        <button id="darkModeToggle" class="btn btn-outline-dark">🌙</button>
                    </div>
        </div>
    </nav>

    <!-- Fő tartalom -->
    <div class="container profile-container">
        <h2 class="text-center mb-4">Profil</h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'sikeresen') !== false || strpos($message, 'frissítve') !== false ? "success" : "error"); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Felhasználói adatok szerkesztése -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Felhasználói adatok</h4>
                <form method="POST" action="profil.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Felhasználónév</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Profil frissítése</button>
                </form>
            </div>
            <div class="col-md-6">
                <h4>Extra funkciók</h4>
                <!-- A jelszó módosítás modal-t indító gomb -->
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    Jelszó módosítása
                </button>
                <!-- Kedvenc termékek gomb -->
                <a href="favorites.php" class="btn btn-info">Kedvenc termékek</a>
            </div>
        </div>

        <!-- Korábbi rendelések megjelenítése -->
        <h4 class="text-center">Korábbi rendeléseid</h4>
        <?php if ($orders_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered orders-table">
                    <thead>
                        <tr>
                            <th>Rendelés ID</th>
                            <th>Dátum</th>
                            <th>Végösszeg</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo $order['order_date']; ?></td>
                                <td><?php echo number_format($order['total_price'], 2, ',', ' ') . " Ft"; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center">Nincsenek korábbi rendeléseid.</p>
        <?php endif; ?>
    </div>

    <!-- Jelszó módosítás Modal -->
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
                 <input type="password" class="form-control" id="current_password" name="current_password" required>
               </div>
               <div class="mb-3">
                 <label for="new_password" class="form-label">Új jelszó</label>
                 <input type="password" class="form-control" id="new_password" name="new_password" required>
               </div>
               <div class="mb-3">
                 <label for="confirm_new_password" class="form-label">Új jelszó megerősítése</label>
                 <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
               </div>
             </div>
             <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
               <button type="submit" class="btn btn-primary">Jelszó módosítása</button>
             </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> MackoBuy. Minden jog fenntartva.</p>
        </div>
    </footer>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".kedvenc-gomb").forEach(button => {
        button.addEventListener("click", function () {
            let termekId = this.getAttribute("data-termek-id");
            let action = this.classList.contains("kedvenc") ? "remove" : "add";

            fetch("kedvenc_muvelet.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `termek_id=${termekId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    this.classList.toggle("kedvenc");
                    this.innerHTML = this.classList.contains("kedvenc") ? "❤️ Kedvenc" : "🤍 Kedvencekhez";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error("Hiba:", error));
        });
    });
});

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
