<?php
    require_once("functions.php");

    verifySelf();

    initSite("MRG-Tests | Gruppen");

    // bruteforceAllTokens(); TODO: Periodisch das jeden Montag morgen ausführen

?>


<?php
    if (isset($_SESSION["uuid"])) {

        drawHeader();

        $my_groups = getGroupsWhereUUIDisOwner($_SESSION["uuid"]);
        $participating_groups = getGroupsWhereUUIDisParticipant($_SESSION["uuid"]);

        $groups = array_merge($my_groups, $participating_groups);

        $pending_groups = getGroupsWhereUUIDisPending($_SESSION["uuid"]);


        ?>

            <div class="groups-list">
                <ul>

                    <?php

                        // Gruppen wo bereits teilgenommen wird darstellen

                        if (count($groups) > 0) {
                            ?> <a class="groups-mini-title-list"> Meine Gruppen </a> <?php
                        }

                        for ($i = 0; $i < count($groups); $i++) {
                            $gid = $groups[$i]["group_id"];

                            $href = "href='groups/show/$gid'";

                            ?>
                                <li> <a style="text-decoration: none;" <?php echo $href; ?>> <table> 
                                    
                                    <tr>
                                        
                                        <td>
                                            <?php echo getParticipantCountInGroup($gid); ?>
                                        </td>

                                        <td style="padding-left: 5px;">
                                            <img width="22px" src="img/human-icon.png"> </img>
                                        </td>

                                        <td class="boldfont" style="width: 150px">
                                            <?php echo getGroupnameByID($gid); ?>
                                        </td>

                                        <td style="padding-left: 5px;">
                                            <?php if (isUUIDOwnerOfGroupID($_SESSION["uuid"], $gid)) { ?> <a style="vertical-align: middle; padding: 0; margin: 0; height: 29px" href="groups/configure/<?php echo $gid; ?>"> <img width="29px" src="img/crown-icon.png"> <?php } ?></img> </a>
                                        </td>
                                    </tr>
                                </table> </a> </li>
                            <?php
                        }

                        // Gruppen die noch pending sind darstellen
                        
                        if (count($pending_groups) > 0) {
                            ?> <a class="groups-mini-title-list"> Ausstehende Gruppen </a> <?php
                        }

                        for ($i = 0; $i < count($pending_groups); $i++) {
                            $gid = $pending_groups[$i]["group_id"];

                            $href = "href='groups/show/$gid'";

                            ?>
                                <li style="background-color: #353535"> <a style="text-decoration: none;" <?php echo $href; ?>> <table> 
                                    
                                    <tr>

                                        <td class="boldfont" style="width: 150px">
                                            <?php echo getGroupnameByID($gid); ?>
                                        </td>

                                        <td style="padding-left: 15px;">
                                            <a style="font-size: 20px"> ... </a>
                                        </td>

                                    </tr>
                                </table> </a> </li>
                            <?php
                        }

                    ?>

                </ul>
            </div>

            <div class="site-content-list">
                <ul>
                    <?php if (getRoleByUUID($_SESSION["uuid"]) == Lehrer) { // Admins können das nicht sehen weil es nicht deren Aufgabenbereich ist
                        ?> <li> <p> <a href="groups-create.php"> Gruppe erstellen </a> </p> </li> <?php
                    } ?>

                    <li> <p> <a href="index.php"> Homepage </a> </p> </li>
                </ul>
            </div>
        
        <?php
    }
?>