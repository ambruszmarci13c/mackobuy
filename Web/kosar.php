<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolódás és lekérdezés funkciók

if (isset($_POST['termek_id'])) {
    $termek_id = $_POST['termek_id'];

    // A termék hozzáadása a kosárhoz
    if (!isset($_SESSION['kosar'])) {
        $_SESSION['kosar'] = [];
    }

    // Ellenőrizzük, hogy a termék már benne van-e a kosárban
    if (isset($_SESSION['kosar'][$termek_id])) {
        $_SESSION['kosar'][$termek_id]++; // Ha már benne van, növeljük a mennyiséget
    } else {
        $_SESSION['kosar'][$termek_id] = 1; // Ha nincs benne, hozzáadjuk
    }

    // Válasz küldése AJAX hívásra
    echo json_encode(['success' => true, 'message' => 'Sikeresen hozzáadva a kosárhoz!']);
}
?>
