<?php
session_start();
include 'sql_fuggvenyek.php';

// Árfolyam beállítása
$arfolyam = 380;

// Pénznem módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['penznem'])) {
    $_SESSION['penznem'] = $_POST['penznem'];
}
$penznem = $_SESSION['penznem'] ?? 'HUF';

// Kosár elem eltávolítása
if (isset($_GET['remove'])) {
    $termek_id = intval($_GET['remove']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        unset($_SESSION['kosar'][$termek_id]);
    }
    header("Location: kosar.php");
    exit();
}

// Kosár elem eltávolítása
if (isset($_GET['remove'])) {
    $termek_id = intval($_GET['remove']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        unset($_SESSION['kosar'][$termek_id]);
    }
    header("Location: kosar.php");
    exit();
}

// Termék mennyiségének módosítása a kosárban
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['termek_id']) && isset($_POST['mennyiseg'])) {
    $termek_id = intval($_POST['termek_id']);
    $mennyiseg_valtozas = intval($_POST['mennyiseg']);

    if (isset($_SESSION['kosar'][$termek_id])) {
        $_SESSION['kosar'][$termek_id]['mennyiseg'] += $mennyiseg_valtozas;

        // Ha a mennyiség 0 vagy kisebb lesz, eltávolítjuk a kosárból
        if ($_SESSION['kosar'][$termek_id]['mennyiseg'] <= 0) {
            unset($_SESSION['kosar'][$termek_id]);
        }
    }
    header("Location: kosar.php");
    exit();
}


// Szállítási díjak
$szallitas_dijak = [
    "mpl" => ($penznem === 'EUR') ? 1190 / $arfolyam : 1190,
    "gls" => ($penznem === 'EUR') ? 1790 / $arfolyam : 1790
];

// Utánvételi díj
$utanvet_dij = ($penznem === 'EUR') ? 229 / $arfolyam : 229;

// Inicializáljuk a változókat
$megrendeles_sikeres = false;
$hiba_uzenet = null;

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['termek_id']) && !isset($_POST['vezeteknev'])) {
        // Termék hozzáadása a kosárhoz
        $termek_id = intval($_POST['termek_id']);
        $termek = adatokLekeres("SELECT * FROM termekek WHERE ID = {$termek_id};");
        if ($termek) {
            $termek = $termek[0];
            if (!isset($_SESSION['kosar'][$termek_id]) || !is_array($_SESSION['kosar'][$termek_id])) {
                $_SESSION['kosar'][$termek_id] = [
                    'tnev' => $termek['tnev'],
                    'ar' => $termek['ar'],
                    'kep' => $termek['kep'],
                    'mennyiseg' => 1
                ];
            } else {
                $_SESSION['kosar'][$termek_id]['mennyiseg']++;
            }
        }
        header("Location: kosar.php");
        exit();
    } elseif (isset($_POST['vezeteknev'])) {
        // Megrendelés feldolgozása
        $vezeteknev = trim($_POST['vezeteknev']);
        $keresztnev = trim($_POST['keresztnev']);
        $email = trim($_POST['email']);
        $telefonszam = trim($_POST['telefonszam']);
        $cim = trim($_POST['cim']);
        $szallitas = $_POST['szallitas'] ?? '';
        $fizetes = $_POST['fizetes'] ?? '';

        // Alapos validáció minden mezőre
        if (empty($vezeteknev) || empty($keresztnev) || empty($email) || empty($telefonszam) || empty($cim) || empty($szallitas) || empty($fizetes)) {
            $hiba_uzenet = "Kérjük, tölts ki minden mezőt!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $hiba_uzenet = "Érvénytelen e-mail cím!";
        } else {
            // Adatbázisba mentés és e-mail küldése élesben szükséges lenne
            unset($_SESSION['kosar']);
            $megrendeles_sikeres = true;
        }
    } elseif (isset($_POST['termek_id']) && isset($_POST['mennyiseg'])) {
        $termek_id = intval($_POST['termek_id']);
        $mennyiseg_valtozas = intval($_POST['mennyiseg']);
    
        if (isset($_SESSION['kosar'][$termek_id])) {
            // Explicit csökkentés vagy növelés a mennyiség változása alapján
            if ($mennyiseg_valtozas < 0) {
                $_SESSION['kosar'][$termek_id]['mennyiseg'] += $mennyiseg_valtozas; // Csökkentés (-1 esetén)
            } else {
                $_SESSION['kosar'][$termek_id]['mennyiseg'] += $mennyiseg_valtozas; // Növelés (+1 esetén)
            }
    
            // Ha a mennyiség 0 vagy kisebb, eltávolítjuk a kosárból
            if ($_SESSION['kosar'][$termek_id]['mennyiseg'] <= 0) {
                unset($_SESSION['kosar'][$termek_id]);
            }
        }

        
        header("Location: kosar.php");
        exit();
    }
    }
 elseif (isset($_GET['remove'])) {
    // Kosár elem eltávolítása
    $termek_id = intval($_GET['remove']);
    if (isset($_SESSION['kosar'][$termek_id])) {
        unset($_SESSION['kosar'][$termek_id]);
    }
    header("Location: kosar.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./kosar.css">
</head>

<body>

    <!-- Sikeres megrendelés üzenet (felugró ablak) -->
    <div id="sikeresMegrendelesAblak" class="overlay <?php echo $megrendeles_sikeres ? '' : 'hidden'; ?>">
        <div class="popup">
            <h2>Köszönjük a megrendelést!</h2>
            <button onclick="document.getElementById('sikeresMegrendelesAblak').classList.add('hidden')" class="btn btn-primary">OK</button>
        </div>
    </div>

    <!-- Hibaüzenet (ha van) -->
    <?php if (isset($hiba_uzenet)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($hiba_uzenet) ?></div>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-light fixed-top custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="mackobuy.php">
                <img src="kepek/navlogo.png" alt="Logo" style="height: 50px;" alt="Főoldal" title="Főoldal">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="kosar.php"><img src="kepek/kosar.png" alt="Kosár" style="height: 38px;" alt="Kosár" title="Kosár"></a></li>
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
                    <li class="nav-item">
                        <form method="POST" action="" class="d-flex align-items-center">
                            <select name="penznem" class="form-select penznem-select" onchange="this.form.submit()">
                                <option value="HUF" <?php echo (isset($_SESSION['penznem']) && $_SESSION['penznem'] === 'HUF') ? 'selected' : ''; ?>>HUF</option>
                                <option value="EUR" <?php echo (isset($_SESSION['penznem']) && $_SESSION['penznem'] === 'EUR') ? 'selected' : ''; ?>>EUR</option>
                            </select>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="nav-item">
                <button id="darkModeToggle" class="btn btn-outline-dark">🌙</button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <br><br><br>
        <h1 class="text-center mb-4">Kosár</h1>

        <?php if (!empty($_SESSION['kosar'])): ?>
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Termék</th>
                        <th>Kép</th>
                        <th>Mennyiség</th>
                        <th>Ár (<?= $penznem ?>)</th>
                        <th>Összesen (<?= $penznem ?>)</th>
                        <th>Művelet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $vegosszeg = 0;
                    foreach ($_SESSION['kosar'] as $id => $termek):
                        if (!is_array($termek)) continue;
                        $ar = ($penznem === 'EUR') ? $termek['ar'] / $arfolyam : $termek['ar'];
                        $osszesen = $ar * $termek['mennyiseg'];
                        $vegosszeg += $osszesen;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($termek['tnev']) ?></td>
                            <td><img src="kepek/<?= htmlspecialchars($termek['kep']) ?>" alt="Kép" style="height: 50px;"></td>
                            <td>
                                <form method="POST" action="kosar.php" class="d-inline">
                                    <input type="hidden" name="termek_id" value="<?= $id ?>">
                                    <input type="hidden" name="mennyiseg" value="-1">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" name="minus_button">−</button>
                                </form>
                                <?= intval($termek['mennyiseg']) ?>
                                <form method="POST" action="kosar.php" class="d-inline">
                                    <input type="hidden" name="termek_id" value="<?= $id ?>">
                                    <input type="hidden" name="mennyiseg" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-success" name="plus_button">+</button>
                                </form>
                            </td>
                            <td><?= number_format($ar, 2, ',', ' ') ?></td>
                            <td><?= number_format($osszesen, 2, ',', ' ') ?></td>
                            <td><a href="kosar.php?remove=<?= $id ?>" class="btn btn-danger btn-sm">Törlés</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-end mt-3">
                <div id="extraKoltsegek" class="text-muted"></div>
                <h3>Végösszeg: <strong id="vegosszeg"><?= number_format($vegosszeg, 2, ',', ' ') ?> <?= $penznem ?></strong></h3>
            </div>

            <h2 class="mt-5 text-center">Megrendelési adatok</h2>
            <form method="POST" action="kosar.php">

                <div class="mb-3">
                    <label class="form-label">Vezetéknév</label>
                    <input type="text" name="vezeteknev" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keresztnév</label>
                    <input type="text" name="keresztnev" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" placeholder="pelda@email.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefonszám</label>
                    <input type="text" name="telefonszam" class="form-control" placeholder="06301234567" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Szállítási cím</label>
                    <input type="text" name="cim" class="form-control" placeholder="Irányítószám, Település, Utca, Házszám" required>
                </div>

                <h2 class="mt-5">Szállítási mód</h2>
                <label class="d-block">
                    <div class="mb-3 p-3 border rounded">
                        <input type="radio" name="szallitas" value="mpl" id="mpl"> Magyar Posta (+1190 Ft)
                        <img src="./kepek/mpl.jpg" alt="Magyar Posta" class="ms-2" style="height: 26px;">
                </label>
            </div>
            <div class="mb-3 p-3 border rounded">
                <label class="d-block">
                    <input type="radio" name="szallitas" value="gls" required> Gls futár (+1790 Ft)
                    <img src="./kepek/gls.jpg" alt="GLS" class="ms-2" style="height: 30px;">
                </label>
            </div>

            <h2 class="mt-5">Fizetési mód</h2>
            <label class="d-block">
                <div class="mb-3 p-3 border rounded">
                    <input type="radio" name="fizetes" value="bankkartya" id="bankkartya"> Bankkártyával online
                    <img src="./kepek/fizetesimod.jpg" alt="Bankkártyás fizetési lehetőségek" class="ms-2" style="height: 24px;">
                    <button type="button" class="btn btn-sm btn-outline-primary ms-3 d-none" id="adatBevitelGomb">Adatok bevitele</button>
                </label>
            </div>
            <div class="mb-3 p-3 border rounded">
                <label class="d-block">
                    <input type="radio" name="fizetes" value="utanvet" required> Utánvét (+229 Ft)
                </label>
            </div>

            <input type="checkbox" id="kuponCheckbox">
            <label for="kuponCheckbox">Kuponkódot / Vásárlási utalványt szeretnék beváltani</label>

            <div id="kuponInputContainer" class="mt-3 d-none">
                <input type="text" id="kuponKod" class="form-control w-50 d-inline" placeholder="Írd be a kuponkódot">
                <button type="button" id="kuponBevitelGomb" class="btn btn-primary ms-2">Bevitel</button>
                <p id="kuponUzenet" class="mt-2 text-success d-none"></p>
            </div>

            <!-- Bankkártyás fizetés modal -->
            <div class="modal fade" id="bankkartyaModal" tabindex="-1" aria-labelledby="bankkartyaModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bankkartyaModalLabel">Bankkártya adatok</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bezárás"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label for="kartyaSzam" class="form-label">Kártyaszám</label>
                                    <input type="text" class="form-control" id="kartyaSzam" placeholder="0000 0000 0000 0000">
                                </div>
                                <div class="mb-3">
                                    <label for="lejarat" class="form-label">Lejárati dátum</label>
                                    <input type="text" class="form-control" id="lejarat" placeholder="MM/YY">
                                </div>
                                <div class="mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn custom-btn" id="fizetesGomb">Adatok mentése</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn custom-btn btn-lg" id="rendelesLeadas">Megrendelés leadása</button><br>
                <span class="text-muted fs-6"><img src="kepek/ketmunkanap.png" alt="asd" style="height: 35px;"> Várhatóan 2 munkanapon belül kiszállításra kerül.</span>
            </div>
            </form>

        <?php else: ?>
            <div class="alert alert-info text-center">A kosarad jelenleg üres. Nézz körül az ajánlataink között!</div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>© 2025 MackoBuy. Minden jog fenntartva.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButton = document.getElementById("darkModeToggle");
            const body = document.body;

            if (toggleButton) {
                if (localStorage.getItem("dark-mode") === "enabled") {
                    body.classList.add("dark-mode");
                    toggleButton.textContent = "☀️";
                }

                toggleButton.addEventListener("click", function() {
                    body.classList.toggle("dark-mode");
                    if (body.classList.contains("dark-mode")) {
                        localStorage.setItem("dark-mode", "enabled");
                        toggleButton.textContent = "☀️";
                    } else {
                        localStorage.setItem("dark-mode", "disabled");
                        toggleButton.textContent = "🌙";
                    }
                });
            }

            <?php if (!empty($_SESSION['kosar'])): ?>
                const bankkartyaRadio = document.getElementById("bankkartya");
                const adatBevitelGomb = document.getElementById("adatBevitelGomb");
                const modalElement = document.getElementById("bankkartyaModal");
                const vegosszegElem = document.getElementById("vegosszeg");
                const extraKoltsegekElem = document.getElementById("extraKoltsegek");
                const kuponCheckbox = document.getElementById("kuponCheckbox");
                const kuponInputContainer = document.getElementById("kuponInputContainer");
                const kuponUzenet = document.getElementById("kuponUzenet");
                const kuponBevitelGomb = document.getElementById("kuponBevitelGomb");
                const kuponKodElem = document.getElementById("kuponKod");
                const fizetesGomb = document.getElementById("fizetesGomb");

                const bankkartyaModal = modalElement ? new bootstrap.Modal(modalElement) : null;

                const eredetiVegosszeg = vegosszegElem?.dataset.original ? parseFloat(vegosszegElem.dataset.original) : parseFloat("<?= $vegosszeg ?>");
                const penznem = "<?= $penznem ?>";
                const arfolyam = parseFloat("<?= $arfolyam ?>");
                let kuponKedvezmeny = 0;

                const szallitasDijak = {
                    "mpl": penznem === "EUR" ? 1190 / arfolyam : 1190,
                    "gls": penznem === "EUR" ? 1790 / arfolyam : 1790
                };
                const utanvetDij = penznem === "EUR" ? 229 / arfolyam : 229;

                const kuponok = {
                    "NMTH1000": penznem === "EUR" ? 1000 / arfolyam : 1000,
                    "AMBRSZ10000": penznem === "EUR" ? 10000 / arfolyam : 10000,
                    "PARDON50000": penznem === "EUR" ? 50000 / arfolyam : 50000
                };

                function frissitVegosszeg() {
                    if (!vegosszegElem || !extraKoltsegekElem) return;

                    let extraKoltseg = 0;
                    extraKoltsegekElem.innerHTML = "";

                    const szallitasElem = document.querySelector("input[name='szallitas']:checked");
                    const fizetesElem = document.querySelector("input[name='fizetes']:checked");

                    if (szallitasElem) {
                        const szallitasDij = szallitasDijak[szallitasElem.value] || 0;
                        extraKoltseg += szallitasDij;
                        extraKoltsegekElem.innerHTML += `<p class='mb-1 small'>Szállítási díj: +${szallitasDij.toFixed(2)} ${penznem}</p>`;
                    }
                    if (fizetesElem && fizetesElem.value === "utanvet") {
                        extraKoltseg += utanvetDij;
                        extraKoltsegekElem.innerHTML += `<p class='mb-1 small'>Utánvételi díj: +${utanvetDij.toFixed(2)} ${penznem}</p>`;
                    }
                    if (kuponKedvezmeny > 0) {
                        extraKoltsegekElem.innerHTML += `<p class='mb-1 small text-success'>Kuponkedvezmény: -${kuponKedvezmeny.toFixed(2)} ${penznem}</p>`;
                    }

                    const ujVegosszeg = Math.max(0, eredetiVegosszeg + extraKoltseg - kuponKedvezmeny);
                    vegosszegElem.textContent = ujVegosszeg.toLocaleString("hu-HU", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + " " + penznem;
                }

                function toggleAdatBevitelGomb() {
                    if (adatBevitelGomb && bankkartyaRadio) {
                        adatBevitelGomb.classList.toggle("d-none", !bankkartyaRadio.checked);
                    }
                }

                function kuponVisszajelzes(uzenet, sikeres) {
                    if (kuponUzenet) {
                        kuponUzenet.textContent = uzenet;
                        kuponUzenet.classList.toggle("text-success", sikeres);
                        kuponUzenet.classList.toggle("text-danger", !sikeres);
                        kuponUzenet.classList.remove("d-none");
                    }
                }

                document.querySelectorAll("input[name='szallitas'], input[name='fizetes']").forEach(radio => {
                    radio.addEventListener("change", frissitVegosszeg);
                });

                document.querySelectorAll("input[name='fizetes']").forEach(radio => {
                    radio.addEventListener("change", toggleAdatBevitelGomb);
                });

                if (adatBevitelGomb && bankkartyaModal) {
                    adatBevitelGomb.addEventListener("click", () => bankkartyaModal.show());
                }

                if (fizetesGomb) {
                    fizetesGomb.addEventListener("click", function() {
                        const kartyaSzam = document.getElementById("kartyaSzam")?.value.trim();
                        const lejarat = document.getElementById("lejarat")?.value.trim();
                        const cvv = document.getElementById("cvv")?.value.trim();
                        if (!kartyaSzam || !lejarat || !cvv) {
                            alert("Kérlek, töltsd ki az összes mezőt!");
                            return;
                        }
                        if (bankkartyaModal) bankkartyaModal.hide();
                    });
                }

                if (kuponCheckbox) {
                    kuponCheckbox.addEventListener("change", function() {
                        if (kuponInputContainer) {
                            kuponInputContainer.classList.toggle("d-none", !this.checked);
                        }
                        if (!this.checked) {
                            if (kuponUzenet) kuponUzenet.classList.add("d-none");
                            kuponKedvezmeny = 0;
                            frissitVegosszeg();
                        }
                    });
                }

                if (kuponBevitelGomb) {
                    kuponBevitelGomb.addEventListener("click", function() {
                        const kuponKod = kuponKodElem?.value.trim();
                        if (!kuponKod) {
                            kuponVisszajelzes("Kérlek, adj meg egy érvényes kuponkódot!", false);
                            return;
                        }
                        if (!(kuponKod in kuponok)) {
                            kuponVisszajelzes("Érvénytelen kuponkód!", false);
                            return;
                        }
                        kuponKedvezmeny = kuponok[kuponKod];
                        kuponVisszajelzes(`Sikeres beváltás! ${kuponKedvezmeny.toFixed(2)} ${penznem} levonásra került.`, true);
                        frissitVegosszeg();
                    });
                }
                toggleAdatBevitelGomb();
                frissitVegosszeg();
            <?php endif; ?>
        });
    </script>
</body>
</html>