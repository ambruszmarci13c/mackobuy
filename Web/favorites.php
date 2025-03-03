<?php
session_start();
include 'sql_fuggvenyek.php';

if (!isset($_SESSION['user_id'])) {
    die("Hiba: Nem vagy bejelentkezve.");
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "mackobuy");

if ($conn->connect_error) {
    die("Adatbázis kapcsolat hiba: " . $conn->connect_error);
}

// 🔴 Eltávolítás kezelése, ha POST kérés érkezik
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termek_id'])) {
    $termek_id = $_POST['termek_id'];

    $query = "DELETE FROM kedvencek WHERE felhasznalo_id = ? AND termek_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $termek_id);

    if ($stmt->execute()) {
        echo "<script>alert('Sikeresen eltávolítva!'); window.location.href='favorites.php';</script>";
        exit;
    } else {
        echo "<script>alert('Hiba történt a törlésnél!');</script>";
    }

    $stmt->close();
}

// Kedvencek lekérdezése
$query = "SELECT t.ID, t.tnev, t.ar, t.kep 
          FROM termekek t
          JOIN kedvencek k ON t.ID = k.termek_id
          WHERE k.felhasznalo_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Kedvenc Termékeid</h2>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $termek_id = $row['ID'];
        $termek_nev = htmlspecialchars($row['tnev']);
        $termek_ar = number_format($row['ar'], 0, ',', ' ') . " Ft";
        $termek_kep = "kepek/" . htmlspecialchars($row['kep']);

        echo "<div>";
        echo "<h3>" . $termek_nev . "</h3>";
        echo "<p>Ár: " . $termek_ar . "</p>";
        echo "<img src='" . $termek_kep . "' alt='Termék kép' width='100'>";
        
        // 🔴 Eltávolítás gomb (mivel ez az egy fájl kezeli, nem kell külön remove_favorite.php)
        echo "<form method='POST' style='display:inline;'>";
        echo "<input type='hidden' name='termek_id' value='" . $termek_id . "'>";
        echo "<button type='submit' style='background-color: red; color: white; padding: 5px 10px; border: none; cursor: pointer;'>
                🗑 Eltávolítás a kedvencek közül
              </button>";
        echo "</form>";

        echo "</div>";
    }
} else {
    echo "<p>Nincsenek kedvenc termékeid.</p>";
}

$stmt->close();
$conn->close();
?>
