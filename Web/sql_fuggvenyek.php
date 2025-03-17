<?php
function dbKapcsolat() {
    $db = new mysqli('localhost', 'root', '', 'mackobuy');
    if ($db->connect_errno) {
        die("Adatbázis kapcsolódási hiba: " . $db->connect_error);
    }
    $db->set_charset("utf8");
    return $db;
}

// Adatok lekérdezése paraméterekkel
function adatokLekeres($muvelet, $parameterek = []) {
    $db = dbKapcsolat();
    $stmt = $db->prepare($muvelet);
    
    if (!$stmt) {
        die("SQL hiba: " . $db->error);
    }
    
    if (!empty($parameterek)) {
        $tipusok = str_repeat('s', count($parameterek)); // Minden paraméter stringként kerül kezelve
        $stmt->bind_param($tipusok, ...$parameterek);
    }
    
    $stmt->execute();
    $eredmeny = $stmt->get_result();
    $adatok = $eredmeny->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $db->close();
    
    return $adatok;
}

// Adatok beszúrása / módosítása / törlése paraméterekkel
function adatokValtoztatasa($muvelet, $parameterek = []) {
    $db = dbKapcsolat();
    $stmt = $db->prepare($muvelet);
    
    if (!$stmt) {
        die("SQL hiba: " . $db->error);
    }
    
    if (!empty($parameterek)) {
        $tipusok = str_repeat('s', count($parameterek)); // Minden paraméter stringként kerül kezelve
        $stmt->bind_param($tipusok, ...$parameterek);
    }
    
    $stmt->execute();
    $siker = $stmt->affected_rows > 0;
    
    $stmt->close();
    $db->close();
    
    return $siker ? "Sikeres művelet!" : "Sikertelen művelet!";
}
?>
