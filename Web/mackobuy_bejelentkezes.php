<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mackobuy_bejelentkezes.css">
    <title>Bejelentkezés</title>
</head>
<body>
    <img src="./images/logo.png" alt="Webshop Logó" class="logo">

    <div class="container">
        <h1>Bejelentkezés</h1>
        
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="mackobuy_bejelentkezes.php" id="bejelentkezes-form">
            <label for="felhasznalonev">Felhasználónév:</label>
            <input type="text" id="felhasznalonev" name="felhasznalonev" required><br>
            
            <label for="jelszo">Jelszó:</label>
            <input type="password" id="jelszo" name="jelszo" required><br>
            
            <button type="submit" name="bejelentkezes">Bejelentkezés</button>
        </form>

        <p>Nincs még fiókja? <a href="mackobuy_regisztracio.php">Regisztráció</a></p>
    </div>

    <div>
        <?php
            include './sql_fuggvenyek.php';
            if (isset($_POST['bejelentkezes'])) {
                if (!empty($_POST['felhasznalonev']) && !empty($_POST['jelszo'])) {
                    $fnev = $_POST['felhasznalonev'];
                    $jelszo = $_POST['jelszo'];
                    
                    $bejelentkezes_sql = "SELECT felhasznalok.fnev, felhasznalok.jelszo FROM felhasznalok WHERE felhasznalok.fnev = '{$fnev}' AND felhasznalok.jelszo = '{$jelszo}';";
                    $bejelentkezes = adatokLekeres($bejelentkezes_sql);
                    
                    if (is_array($bejelentkezes) && count($bejelentkezes) > 0) {
                        header("Location: mackobuy.php");
                        exit;
                    } else {
                        echo '<script>alert("Nincsen ilyen felhasználó.")</script>';
                    }
                } else {
                    echo '<script>alert("Adjon meg minden adatot!")</script>';
                }
            }
        ?>
    </div>

    <script src="mackobuy_bejelentkezes.js"></script>
</body>
</html>
