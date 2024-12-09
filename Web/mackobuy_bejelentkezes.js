/*document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("bejelentkezes-form");
    const felhasznalonev = document.getElementById("felhasznalonev");
    const jelszo = document.getElementById("jelszo");
    const hibaUzenet = document.getElementById("hiba-uzenet");

    showPasswordCheckbox.addEventListener("change", () => {
        jelszo.type = showPasswordCheckbox.checked ? "text" : "password";
    });

    form.addEventListener("submit", (e) => {
        let valid = true;
        let uzenet = "";

        if (felhasznalonev.value.trim() === "") {
            valid = false;
            uzenet = "A felhasználónév nem lehet üres.";
        }

        if (jelszo.value.trim() === "") {
            valid = false;
            uzenet = "A jelszó nem lehet üres.";
        } else if (jelszo.value.length < 6) {
            valid = false;
            uzenet = "A jelszónak legalább 6 karakter hosszúnak kell lennie.";
        }

        if (!valid) {
            e.preventDefault(); 
            alert(uzenet);
        }
    });
});
*/