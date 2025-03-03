<?php
session_start(); // EZ LEGYEN AZ ELSŐ SORBAN!
include 'sql_fuggvenyek.php';


file_put_contents("debug.log", print_r($_POST, true));
file_put_contents("debug.log", "\nSESSION: " . print_r($_SESSION, true), FILE_APPEND);

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(["status" => "error", "message" => "Be kell jelentkezni!"]);
    exit;
}
$user_id = $_SESSION['user_id']; // Helyesen "user_id"


$conn = new mysqli("localhost", "root", "", "mackobuy");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Adatbázis hiba"]));
}

$user_id = $_SESSION['user_id'];
$termek_id = isset($_POST['termek_id']) ? intval($_POST['termek_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($termek_id > 0) {
    if ($action == "add") {
        $query = "INSERT IGNORE INTO kedvencek (user_id, termek_id) VALUES (?, ?)";
    } elseif ($action == "remove") {
        $query = "DELETE FROM kedvencek WHERE user_id = ? AND termek_id = ?";
    } else {
        echo json_encode(["status" => "error", "message" => "Érvénytelen művelet"]);
        exit;
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $termek_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Művelet sikeres"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Hiba történt"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Érvénytelen termék"]);
}

$conn->close();
?>

