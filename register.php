<?php
    require_once("functions.php");

    initSite("MRG-Tests | Register");

    drawHeader();

    // Redirect zur index.php wenn man bereits eingeloggt ist
    if (verifySelf()) {
        header("Location: index.php");
    }
?>

<div class="site-content-form">
    <form method="post">
        <center>
            <ul class="site-content-form-ul">
                <li class="mini-header"> Benutzername </li>
                <li> <input required type="text" name="username"> </li>
                <li class="mini-header"> Passwort </li>
                <li> <input required type="password" name="password"> </li>
                <li class="mini-header"> Passwort (Wiederholen) </li>
                <li> <input required type="password" name="password_repeat"> </li>
                <li class="mini-header"> Sicherheits Pin </li>
                <li> <input required type="number" min="1000" max="9999" name="pin"> </li>
                <li> <input required type="submit" name="register" value="Registrieren"> </li>
                <div class="site-content-list">  <ul> <li> <p> <a href="index.php"> Homepage </a> </p> </li> </ul> </div>
            </ul>
        </center>
    </form>
</div>

<?php
    if (isset($_POST["register"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $password_repeat = $_POST["password_repeat"];
        $pin = $_POST["pin"];

        $valid_username = preg_match_all("/^[a-zA-Z0-9]+([._]?[a-zA-Z0-9]+)*$/", $username);
        
        if (!$valid_username) {
            echo "Benutzername ist ungültig, bitte nur A-Z, a-z, 0-9, oder . und _ benutzen.";
            return;
        }

        if ($password != $password_repeat) {
            echo "Die Passwörter stimmen nicht überein!";
            return;
        }

        if (strlen($password) < 6) {
            echo "Das Passwort muss länger als 6 Zeichen sein.";
            return;
        }

        // Benutzername muss länger als 3 und kürzer als 17 Buchstaben sein
        if (strlen($username) < 17 && strlen($username) > 3) {

        }

        
        $password = hash("sha512", $password); // Password wird gehashed damit es bei einem Datenbank Leak nicht in Klartext einsehbar wäre
        $pin = hash("md5", $pin); // Pin wird aus dem gleichen Grund auch gehashed

        $register = registerUser($username, $password, $pin);
        
        if (!$register) {
            echo "Registrierung fehlgeschlagen, der Benutzername ist bereits vergeben.";
        } else {
            $_SESSION["uuid"] = $register;
            echo "Registration erfolgreich! <a href='index.php'> Klicke hier um deinen QR Code anzuzeigen. </a>";
            header("Location: index.php");
        }
        
    }
?>