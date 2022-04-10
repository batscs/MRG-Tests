<?php
    require_once("functions.php");

    initSite("MRG-Tests | Login");

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
                <li> <input type="text" name="username"> </li>
                <li class="mini-header"> Passwort </li>
                <li> <input type="password" name="password"> </li>
                <li> <input type="submit" name="login" value="Einloggen"> </li>
                <div class="site-content-list">  <ul> <li> <p> <a href="index.php"> Homepage </a> </p> </li> </ul> </div>
            </ul>
        </center>
    </form>
</div>

<div class="site-php-output">
    <?php
        if (isset($_POST["login"])) {
            $username = $_POST["username"];
            $password = $_POST["password"];
            $password = hash("sha512", $password); // Password wird gehashed

            if (loginAs($username, $password)) {
                $_SESSION["uuid"] = getUUIDbyUsername($username);
                header("Location: index.php");
                echo "Login erfolgreich! <a href='index.php'> Klicke hier um deinen QR Code anzuzeigen. </a>";
            } else {
                echo "Die Login Daten sind ungültig.";
            }
            
        }
    ?>
</div>

