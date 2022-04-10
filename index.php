<?php
    require_once("functions.php");

    verifySelf();

    initSite("MRG-Tests");

    // bruteforceAllTokens(); TODO: Periodisch das jeden Montag morgen ausführen

?>


<?php
    if (isset($_SESSION["uuid"])) {

        drawHeader();

        ?>

            <div class="qr-code"> 
                <img class="qr-image" src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=<?php echo getTokenByUUID($_SESSION["uuid"]); ?>"> <br>
                <!-- Auf Wunsch kann man Lokal den QR-Code erstellen, man muss aber seine eigene API oder Funktion dafür haben -->
            </div>

            <div class="site-content-list">

                <ul>
                    <?php if (getRoleByUUID($_SESSION["uuid"]) >= Lehrer) {
                        ?> <li> <p> <a href="scan.php"> Schüler Check </a> </p> </li> <?php
                    } ?>

                    <?php if (getRoleByUUID($_SESSION["uuid"]) == Lehrer || getRoleByUUID($_SESSION["uuid"]) == Schueler) { // Admins können das nicht sehen weil es nicht deren Aufgabenbereich ist
                        ?> <li> <p> <a href="groups.php"> Gruppenübersicht </a> </p> </li> <?php
                    } ?>

                    <?php if (getRoleByUUID($_SESSION["uuid"]) >= Admin) {
                        ?> <li> <p> <a href="admin.php"> Adminpanel </a> </p> </li> <?php
                    } ?>

                    <li class="logout"> <p> <a href="logout.php"> Logout </a> </p> </li>
                </ul>
            </div>
        
        <?php
    } else {
        ?>
            <div class="site-header">
                MRG-Tests
            </div>

            <div class="site-content-list">
                <ul>
                    <li> <a href="login.php"> Einloggen </a> </li>
                    <li> <a href="register.php"> Registrieren </a> </li>
                </ul>
            </div>

            
            
        <?php
    }
?>