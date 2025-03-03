<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mackobuy_bejelentkezes.css">
    <title>Regisztráció</title>
</head>
<body>
    <img src="./kepek/logo.png" alt="Webshop Logó" class="logo">

    <div class="container">
        <h1>Regisztráció</h1>
        
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="mackobuy_regisztracio.php" id="regisztracio-form">
            <label for="felhasznalonev">Felhasználónév:</label>
            <input type="text" id="felhasznalonev" name="felhasznalonev" required>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
    
            <label for="jelszo">Jelszó:</label>
            <input type="password" id="jelszo" name="jelszo" required>

            <label for="jelszo2">Jelszó megismétlése:</label>
            <input type="password" id="jelszo2" name="jelszo2" required>
    
            <button type="submit" name="regisztracio">Regisztráció</button>

            <p>Van már fiókja? <a href="mackobuy_bejelentkezes.php">Bejelentkezés</a></p>
            <a href=""></a>
        </form>

        <p id="hiba-uzenet" style="color: red; display: none;"></p>

    </div>

    <?php
        include './sql_fuggvenyek.php';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regisztracio'])) {
            $fnev = trim($_POST['felhasznalonev']);
            $email = trim($_POST['email']);
            $jelszo = $_POST['jelszo'];
            $jelszo2 = $_POST['jelszo2'];

            // Alapvető ellenőrzések
            if (empty($fnev) || empty($email) || empty($jelszo) || empty($jelszo2)) {
                echo '<script>alert("Minden mező kitöltése kötelező!")</script>';
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo '<script>alert("Érvénytelen email formátum!")</script>';
                exit;
            }

            if ($jelszo !== $jelszo2) {
                echo '<script>alert("A jelszavak nem egyeznek!")</script>';
                exit;
            }

            if (strlen($jelszo) < 6) {
                echo '<script>alert("A jelszónak legalább 6 karakter hosszúnak kell lennie!")</script>';
                exit;
            }

            // Adatbázis kapcsolat inicializálása
            $kapcsolat = new mysqli('localhost', 'root', '', 'mackobuy');

            // Ellenőrizzük a kapcsolódási hibát
            if ($kapcsolat->connect_error) {
                echo '<script>alert("Adatbázis kapcsolat hiba!")</script>';
                exit;
            }

            // Ellenőrizzük, hogy létezik-e már a felhasználónév vagy e-mail
            $stmt = $kapcsolat->prepare("SELECT COUNT(*) FROM felhasznalok WHERE fnev = ? OR email = ?");
            $stmt->bind_param("ss", $fnev, $email);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                echo '<script>alert("A felhasználónév vagy email már létezik!")</script>';
                $kapcsolat->close();
                exit;
            }

            // Felhasználó mentése az adatbázisba
            $stmt = $kapcsolat->prepare("INSERT INTO felhasznalok (fnev, email, jelszo) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $fnev, $email, $jelszo);

            if ($stmt->execute()) {
                $stmt->close();
                $kapcsolat->close();
                header("Location: mackobuy_bejelentkezes.php");
                exit;
            } else {
                echo '<script>alert("Hiba történt a regisztráció során!")</script>';
            }

            $stmt->close();
            $kapcsolat->close();
        }
        ?>

        


</body>
</html>
