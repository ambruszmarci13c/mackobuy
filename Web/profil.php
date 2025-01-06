<?php
session_start();
include 'sql_fuggvenyek.php'; // Adatbázis kapcsolat betöltése

// Ellenőrizzük, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Felhasználói adatok lekérése
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ha az űrlapot elküldték, frissítjük az adatokat
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = htmlspecialchars(trim($_POST['username']));
    $new_email = htmlspecialchars(trim($_POST['email']));

    if (!empty($new_username) && !empty($new_email)) {
        $update_query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $new_username, $new_email, $user_id);

        if ($update_stmt->execute()) {
            $message = "Profil frissítve!";
            $user['username'] = $new_username;
            $user['email'] = $new_email;
        } else {
            $message = "Hiba történt a profil frissítésekor.";
        }
    } else {
        $message = "Minden mezőt ki kell tölteni!";
    }
}

// Eddigi rendelések lekérése
$orders_query = "SELECT id, order_date, total_price FROM orders WHERE user_id = ? ORDER BY order_date DESC";
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
    <title>Profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Profil</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" action="profil.php">
            <label for="username">Felhasználónév:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="email">Email cím:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit">Frissítés</button>
        </form>

        <h2>Korábbi rendeléseid</h2>
        <?php if ($orders_result->num_rows > 0): ?>
            <table>
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
        <?php else: ?>
            <p>Nincsenek korábbi rendeléseid.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
