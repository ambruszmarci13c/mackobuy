/*
// Bejelentkezés ellenőrzése
function validateForm() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    if (username === "felhasznalo" && password === "jelszo") {
        alert("Sikeres bejelentkezés!");
        return true;
    } else {
        alert("Hibás felhasználónév vagy jelszó!");
        return false;
    }
}

// Regisztráció ellenőrzése
function validateRegistrationForm() {
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirm-password").value;

    if (username === "" || email === "" || password === "" || confirmPassword === "") {
        alert("Minden mezőt ki kell tölteni!");
        return false;
    }

    if (password !== confirmPassword) {
        alert("A jelszavak nem egyeznek!");
        return false;
    }

    alert("Sikeres regisztráció!");
    return true;
}
*/