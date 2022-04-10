<?php
    require_once("functions.php");

    initSite("MRG-Tests | Login");

    drawHeader();
    verifySelf();


    if (!isset($_SESSION["uuid"]) || getRoleByUUID($_SESSION["uuid"]) < Lehrer) {
        exit;
    }


  
?>

<div class="site-content-form">
    <form method="post">
        <center>
            <ul class="site-content-form-ul">
                <li class="mini-header"> Gruppenname </li>
                <li> <input type="text" name="groupname"> </li>
                <li> <input type="submit" name="creategroup" value="Gruppe erstellen"> </li>
            </ul>
        </center>
    </form>
</div>

<div class="site-php-output">
    <?php
        if (isset($_POST["creategroup"])) {
            $groupname = $_POST["groupname"];

            createGroup($groupname, $_SESSION["uuid"]);
            header("Location: groups.php");
        }
    ?>
</div>

