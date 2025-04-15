<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mackobuy_bejelentkezes.css">
    <title>Bejelentkezés</title>
</head>
<body>
    <img src="kepek/logo.png" alt="Webshop Logó" class="logo">

    <div class="container">
        <h1>Bejelentkezés</h1>
        
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" action="mackobuy_bejelentkezes.php" id="bejelentkezes-form">
            <label for="felhasznalonev">Felhasználónév:</label>
            <input type="text" id="felhasznalonev" name="felhasznalonev" placeholder="Felhasznalonev1" required><br>
            
            <label for="jelszo">Jelszó:</label>
            <input type="password" id="jelszo" name="jelszo" placeholder="Jelszo123" required><br>
            
            <button type="submit" name="bejelentkezes">Bejelentkezés</button>
        </form>

        

        <p>Nincs még fiókja? <a href="mackobuy_regisztracio.php">Regisztráció</a></p>
    </div>
        
    <div>
    <?php
session_start();
include './sql_fuggvenyek.php';

$hiba_uzenet = ""; // Hibaüzenet változó

if (isset($_POST['bejelentkezes'])) {
    if (!empty($_POST['felhasznalonev']) && !empty($_POST['jelszo'])) {
        $fnev = $_POST['felhasznalonev'];
        $jelszo = $_POST['jelszo'];

        $bejelentkezes_sql = "SELECT id, fnev FROM felhasznalok WHERE fnev = '{$fnev}' AND jelszo = '{$jelszo}';";
        $bejelentkezes = adatokLekeres($bejelentkezes_sql);

        if (is_array($bejelentkezes) && count($bejelentkezes) > 0) {
            $_SESSION['user_id'] = $bejelentkezes[0]['id'];
            $_SESSION['username'] = $bejelentkezes[0]['fnev'];

            header("Location: mackobuy.php");
            exit;
        } else {
            $hiba_uzenet = "Hibás felhasználónév vagy jelszó.";
        }
    } else {
        $hiba_uzenet = "Adjon meg minden adatot!";
    }
}
?>
    <h2 style="color: red;"><?= htmlspecialchars($hiba_uzenet) ?></h2>
    </div>

    <script src="mackobuy_bejelentkezes.js"></script>
</body>
</html>
