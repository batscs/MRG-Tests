<?php
    require_once("functions.php");

    verifySelf();

    initSite("MRG-Tests | Gruppen");

    if (!isset($_GET["id"])) {
        exit;
    }

    // bruteforceAllTokens(); TODO: Periodisch das jeden Montag morgen ausführen

?>


<?php
    if (isset($_SESSION["uuid"])) {

        drawHeader();

        $msg = true ? true ? "abc" : "def" : "ghi";
        // echo $msg;

        ?>

            <div class="groups-list">
                <ul>

                    <?php

                        // Hier wird oben das Div erstellt welches die momentane Gruppe anzeigt damit der Benutzer weiß, wo er sich befindet.
                        ?>
                        <li style="background-color: #202020;"> <a style="text-decoration: none;"> 
                            <table> 
                                <tr>
                                    <td>
                                        <?php echo getParticipantCountInGroup($_GET["id"]); ?>
                                    </td>

                                    <td style="padding-left: 5px;">
                                        <img width="22px" src="<?php echo ROOT_PATH;?>img/human-icon.png"> </img>
                                    </td>

                                    <td class="boldfont" style="width: 150px">
                                        <?php echo getGroupnameByID($_GET["id"]); ?>
                                    </td>

                                    <td style="padding-left: 5px;">
                                        <?php if (isUUIDOwnerOfGroupID($_SESSION["uuid"], $_GET["id"])) { ?> <img width="22px" src="<?php echo ROOT_PATH;?>img/crown-icon.png"> <?php } ?></img>
                                    </td>
                                </tr>
                            </table> </a> 
                        </li>

                        <li>
                            <table style="width: 100%; border-spacing: 0;"> 

                                <?php
                                
                                $gid = $_GET["id"];

                                if (isUUIDOwnerOfGroupID($_SESSION["uuid"], $gid)) {
                                    $participants_list = getParticipantsInGroup($gid);

                                    for ($i = 0; $i < count($participants_list); $i++) {

                                        $participant = $participants_list[$i]["participant"];
                                        $confirmation = $participants_list[$i]["confirmation"];
                                        $confirmation_img_src = ROOT_PATH . "img/questionmark-icon.png";

                                        if ($confirmation == "confirmed") {
                                            $valid = isTokenValid(getTokenByUUID($participant));
                                            $confirmation_img_src = $valid ? ROOT_PATH . "img/checkmark-icon.png" : ROOT_PATH . "img/failed-icon.png";
                                        }

                                        $rowColor = $i % 2 == 0 ? "#0659B9" : "#1267C8";

                                        ?>                                            
                                                <tr style="background-color: <?php echo $rowColor; ?>">

                                                    <td style="padding-left: 25px; width: 15px">
                                                        <img width="22px" src="<?php echo $confirmation_img_src; ?>"> </img>
                                                    </td>

                                                    <td class="boldfont" style="width: 150px">
                                                        <?php echo getUsernameByUUID($participant); ?>
                                                    </td>

                                                </tr>
                                        <?php
                                    }
                                } else {

                                    if (isUUIDPendingForGroupID($_SESSION["uuid"], $gid)) {
                                        $i = 0;

                                        // Darstellung des Lehrers in der Gruppe
                                        $i++;
                                        $rowColor = "#008DCE";

                                        $groupOwnerUUID = getOwnerOfGroupID($gid);
                                        
                                        ?>                                            
                                            <tr style="cursor: default; background-color: <?php echo $rowColor; ?>">

                                                <td class="boldfont" style="width: 50px; color: black">
                                                    Lehrer:
                                                </td>

                                                <td class="boldfont" style="width: 150px">
                                                    <?php echo getUsernameByUUID($groupOwnerUUID); ?>
                                                </td>

                                            </tr>
                                        <?php

                                        // Spacer zwischen zwei <tr> elementen
                                        ?> <tr style="background-color: #065fc5"> <td colspan="5" style="font-size: 2px"> <br> </td> </tr> <?php

                                        // Darstellung des Annehmen Buttons
                                        $i++;
                                        $rowColor = "#6FBA83";

                                        $origin = "groups/show/$gid";
                                        $action = "acceptGroup";
                                        $params = $gid;

                                        $href = "action-manager/$origin/$action/$params";
                                        
                                        ?>                                            
                                            <tr onclick="window.open('<?php echo(ROOT_PATH . $href); ?>', '_self')" style="background-color: <?php echo $rowColor; ?>">

                                                <td class="boldfont" colspan="2" style="color: black">
                                                    Einladung annehmen
                                                </td>

                                            </tr>
                                        <?php

                                        // Spacer zwischen zwei <tr> elementen
                                        ?> <tr style="background-color: #065fc5"> <td colspan="5" style="font-size: 2px"> <br> </td> </tr> <?php

                                        // Darstellung des Ablehnen Buttons
                                        $i++;
                                        $rowColor = "#C0392B";

                                        $origin = "groups.php";
                                        $action = "leaveGroup";
                                        $params = $gid;

                                        $href = "action-manager/$origin/$action/$params";
                                        
                                        ?>                                            
                                            <tr onclick="window.open('<?php echo(ROOT_PATH . $href); ?>', '_self')" style="background-color: <?php echo $rowColor; ?>">

                                                <td class="boldfont" colspan="2" style="color: black">
                                                    Einladung ablehnen
                                                </td>

                                            </tr>
                                        <?php
                                    } else if ( isUUIDaParticipantInGroup($_SESSION["uuid"], $gid) ) {

                                        $i = 0;

                                        // Darstellung des Lehrers in der Gruppe
                                        $i++;
                                        $rowColor = "#008DCE";

                                        $groupOwnerUUID = getOwnerOfGroupID($gid);
                                        
                                        ?>                                            
                                            <tr style="cursor: default; background-color: <?php echo $rowColor; ?>">

                                                <td class="boldfont" style="width: 50px; color: black">
                                                    Lehrer:
                                                </td>

                                                <td class="boldfont" style="width: 150px">
                                                    <?php echo getUsernameByUUID($groupOwnerUUID); ?>
                                                </td>

                                            </tr>
                                        <?php

                                        // Spacer zwischen zwei <tr> elementen
                                        ?> <tr style="background-color: #065fc5"> <td colspan="5" style="font-size: 2px"> <br> </td> </tr> <?php

                                        // Darstellung des Verlassens Button
                                        $i++;
                                        $rowColor = "#C0392B";

                                        $origin = "groups.php";
                                        $action = "leaveGroup";
                                        $params = $gid;

                                        $href = "action-manager/$origin/$action/$params";
                                        
                                        ?>                                            
                                            <tr style="background-color: <?php echo $rowColor; ?>">

                                                <td onclick="window.open('<?php echo(ROOT_PATH . $href); ?>', '_self')" class="boldfont" colspan="2" style="color: black">
                                                    Gruppe verlassen
                                                </td>

                                            </tr>
                                        <?php

                                    }

                                }

                                ?>

                            </table>
                        </li>

                </ul>
            </div>

            <div class="site-content-list">
                <ul>
                    <?php if (isUUIDOwnerOfGroupID($_SESSION["uuid"], $_GET["id"])) { // Nur Owner von der Gruppe kann sie bearbeiten
                        ?> <li> <p> <a style="background-color: #202020" href="<?php echo ROOT_PATH;?>groups/configure/<?php echo $_GET["id"]; ?>"> Gruppe konfigurieren </a> </p> </li> <?php
                    } ?>

                    <li> <p> <a href="<?php echo ROOT_PATH;?>groups"> Gruppenübersicht </a> </p> </li>

                    <li> <p> <a href="<?php echo ROOT_PATH;?>"> Homepage </a> </p> </li>
                </ul>
            </div>
        
        <?php
    }
?>