<?php
    require_once("mysql.php");
    require_once("functions.php");

    verifySelf();

    if (!isset($_SESSION["uuid"]) || getRoleByUUID($_SESSION["uuid"]) < Admin) {
        exit;
    }

    initSite("MRG-Tests | Adminpanel");

    drawHeader();
?>

<div class="admin-content-form">
    
    <!-- Drop Down oben auf der Website -->
    <div>
        <center>
            <select id="adminSelection">
                <option value="lehrer_ranking"> Lehrer ernennen </option>
                <option value="password_change"> Passwort vergessen </option>
                <option value="delete_account"> Benutzer löschen </option>
                <option value="token_bruteforce"> Tokens Bruteforcen </option>
                <option value="goto_pref"> Gehe zu Preferences </option>
            </select>
        </center>

        <script>
            document.querySelector('#adminSelection').addEventListener("change", function() {
                changeWebsiteBasedOnSelection(this.value);
            });
        </script>
    </div>


    <!-- Div mit der Passwort Änderungs Möglichkeit wird hier angezeigt -->
    <div class="admin-page-div" id="password_change_div" style="display: none;">
        <div class="admin-content-form">
            <form method="post">
                <center>
                    <ul class="admin-content-form-ul">
                        <li class="mini-header"> Benutzername </li>
                        <li> <input type="text" name="username"> </li>
                        <li class="mini-header"> Sicherheits Pin </li>
                        <li> <input type="number" min="1000" max="9999" name="pin"> </li>
                        <li class="mini-header"> Neues Passwort </li>
                        <li> <input type="password" name="new_password"> </li>
                        <li> <input type="submit" name="password-change" value="Passwort ändern"> </li>
                    </ul>
                </center>
            </form>
        </div>
    </div>

    <!-- Div mit der Lehrer Ernennungs Möglichkeit wird hier angezeigt -->
    <div class="admin-page-div" id="lehrer_ranking_div" style="display: none;">
        <div class="admin-content-form">
            <form method="post">
                <center>
                    <ul class="admin-content-form-ul">
                        <li class="mini-header"> Benutzername </li>
                        <li> <input type="text" name="username"> </li>
                        <li class="mini-header"> Neuer Rang </li>
                        <li>
                            <select name="lehrer_ranking" id="actionSelection">
                                <option value="rank_lehrer"> Rang: Lehrer </option>
                                <option value="rank_schueler"> Rang: Schüler </option>
                            </select>
                        </li>
                        <li> <input type="submit" name="set-lehrer" value="Bestätigen"> </li>
                    </ul>
                </center>
            </form>
        </div>
    </div>

    <!-- Div mit der Redirect zur Preferences.php Möglichkeit wird hier angezeigt -->
    <div class="admin-page-div" id="goto_pref_div" style="display: none;">
            <center> <a href="preferences.php" style="color: lightblue"> (Klicke hier für den Redirect) </a> </center>
    </div>

    <!-- Div mit der Benutzerkonto Löschungs Möglichkeit wird hier angezeigt -->
    <div class="admin-page-div" id="delete_account_div" style="display: none;">
        <div class="admin-content-form">
            <form method="post">
                <center>
                    <ul class="admin-content-form-ul">
                        <li class="mini-header"> Benutzername </li>
                        <li> <input type="text" name="username"> </li>
                        <li class="mini-header"> Bestätigung </li>
                        <li> <input type="text" placeholder="Schreibe 'Ich bestätige'" name="confirmation"> </li>
                        <li> <input type="submit" name="delete-account" value="Benutzer Löschen"> </li>
                    </ul>
                </center>
            </form>
        </div>
    </div>

    <!-- Div mit der Token Bruteforce (jeden Montag) Möglichkeit wird hier angezeigt -->
    <div class="admin-page-div" id="token_bruteforce_div" style="display: none;">
        <div class="admin-content-form">
            <form method="post">
                <center>
                    <ul class="admin-content-form-ul">
                        <li class="mini-header"> Bestätigung </li>
                        <li> <input type="text" placeholder="Schreibe 'Ich bestätige'" name="confirmation"> </li>
                        <li> <input type="submit" name="token-bruteforce" value="Alle Tokens Bruteforcen"> </li>
                    </ul>
                </center>
            </form>
        </div>
    </div>

</div>

<div class="site-php-output" id="admin-php-output">
    <?php

        // Hier findet die Verarbeitung des Requests auf der Admin.php bzw. der Adminpanel Seite

        // Verarbeitung falls die Operation eine Rangveränderung eines Users ist.
        if (isset($_POST["set-lehrer"])) {
            $select = $_POST["lehrer_ranking"]; // $select ist die Bezeichnung des Rangs welcher der User nun bekommt
            $username = $_POST["username"];
            $uuid = getUUIDbyUsername($username);

            // Falls $select weder rank_lehrer oder rank_schueler ist vielleicht durch js injection wird hier einfach ein default wert definiert.
            $role = 0;
            $role_name = "Schüler";

            if ($select == "rank_lehrer") {
                $role = 1;
                $role_name = "Lehrer";
            } else if ($select == "rank_schueler") {
                $role = 0;
                $role_name = "Schüler";
            }

            updateRoleForUUID($uuid, $role);
            echo "Benutzer $username ist nun ein $role_name";

        // Verarbeitung falls die Operation eine Löschung eines Users ist.
        } else if (isset($_POST["delete-account"])) {
            $username = $_POST["username"];
            $uuid = getUUIDbyUsername($username);
            $confirmation = $_POST["confirmation"];

            if (strcasecmp($confirmation, "Ich bestätige") == 0) { // == 0 => beide strings stimmen überein (ohne groß und kleinschreibung zu beachten)
                deleteUserByUUID($uuid);
                echo "Der Benutzer $username wurde gelöscht!";
            } else {
                echo "Bestätigung ist fehlgeschlagen.";
            }

        // Verarbeitung falls die Operation eine Passwortänderung ist
        } else if (isset($_POST["password-change"])) {
            $username = $_POST["username"];
            $uuid = getUUIDbyUsername($username);
            $pin = hash("md5", $_POST["pin"]);
            $new_password = hash("sha512", $_POST["new_password"]);

            // $correctPin ist die Validierung der Authenzität des Schülers, weil ansonsten sich jeder als ein bestimmter User ausgeben kann
            // da alle User anonym sein können. Der Sicherheitspin der hier gefordert wird ist der, welcher bei der Registrierung eingegeben wird.
            $correctPin = verifySecurityPinForUUID($uuid, $pin);

            if ($correctPin) {
                changePasswordForUUID($uuid, $new_password);
                echo "Der Benutzer $username hat nun sein neues Passwort.";
            } else {
                echo "Der Sicherheits Pin ist falsch.";
            }

        // Verarbeitung falls die Operation der Token Bruteforce ist, welcher jeden Montag vom Admin durchgeführt werden muss
        // (Kann auch theoretisch mit cronjob automatisiert werden bei interesse)
        } else if (isset($_POST["token-bruteforce"])) {
            $confirmation = $_POST["confirmation"];

            if (strcasecmp($confirmation, "Ich bestätige") == 0) { // == 0 => beide strings stimmen überein (ohne groß und kleinschreibung zu beachten)
                bruteforceAllTokens();
                echo "Alle Tokens wurden gebruteforced.";
            } else {
                echo "Bestätigung ist fehlgeschlagen.";
            }
        }
    ?>
</div>

<div class="site-content-list">
    <ul>
        <li> <a href="login.php"> Homepage </a> </li>
    </ul>
</div>

<script>

    // Website generation basiert auf dem Ausgangswert von dem <select>
    // Alle divs werden am Anfang versteckt
    hideElementFromWebsite("lehrer_ranking");
    hideElementFromWebsite("password_change");
    hideElementFromWebsite("delete_account");
    hideElementFromWebsite("goto_pref");

    // Standart Div welche Angezeigt wird wird hier als die Lehrer Ernennungs Div definiert
    var _current = "lehrer_ranking";
    showElementOnWebsite(_current);
    var lastSelection = _current;

    // Funktionen werden hier definiert, namen sind ziemlich selbsterklärend
    function changeWebsiteBasedOnSelection(value) {
        hideElementFromWebsite(lastSelection);
        showElementOnWebsite(value);
        lastSelection = value;

        document.getElementById("admin-php-output").innerHTML = "";

    }

    function hideElementFromWebsite(id) {
        id = id + "_div";
        element = document.getElementById(id);
        if (element != null) {
            element.style.display = "none";
        }
    }

    function showElementOnWebsite(id) {
        id = id + "_div";
        element = document.getElementById(id);
        if (element != null) {
            element.style.display = "block";
        }
    }
</script>