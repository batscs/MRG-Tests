<?php
    require_once("functions.php");

    initSite("MRG-Tests | Login");

    drawHeader();

    // Redirect zur index.php wenn man bereits eingeloggt ist
    if (getRoleByUUID($_SESSION["uuid"]) < Lehrer) {
        exit;
    }

    if (!isset($_GET["id"])) {
        exit;
    }

?>

<div class="site-php-output">
    <?php
        if (isset($_POST["save"])) {

            $groupData = array();
            $groupData["name"] = $_POST["groupname"];
            $groupData["GID"] = $_GET["id"];
            $groupData["userAddList"] = explode(", ", $_POST["targetUserList"]);
            updateGroup($groupData);
            
        }

        if (isset($_POST["delete"])) {

            $groupData = array();
            $groupData["name"] = $_POST["groupname"];
            $groupData["GID"] = $_GET["id"];
            $groupData["userAddList"] = explode(", ", $_POST["targetUserList"]);
            updateGroup($groupData);
            
        }
    ?>
</div>

<div class="groups-list">

    <form method="post">
        <ul>

        <?php

            // Hier wird oben das Div erstellt welches die momentane Gruppe anzeigt damit der Benutzer weiß, wo er sich befindet.
            ?>
            <li class="mini-header"> Gruppenname </li>
            <li style="background-color: #202020;"> <a style="text-decoration: none;"> 
                <table> 
                    <tr>

                        <td class="boldfont">
                            <input name="groupname" style="width: 90%" class="groups-configure-input" type="text" value="<?php echo getGroupnameByID($_GET["id"]); ?>"> </input>
                        </td>

                    </tr>
                </table> </a> 
            </li>

            <li class="mini-header"> Benutzer hinzufügen </li>
            <li style="background-color: #202020;"> <a style="text-decoration: none;"> 
                <table> 
                    <tr>

                        <td class="boldfont">
                            <input id="user_add" style="width: 90%" class="groups-configure-input" type="text"> </input>
                        </td>

                    </tr>
                </table> </a> 

                <script>
                    const targetUserList = [];
                    const targetUserListFrontEnd = [];

                    document.querySelector('#user_add').addEventListener("input", function() {
                        lastChar = this.value.substring(this.value.length - 1, this.value.length);

                        if (lastChar == "," || lastChar == " ") {
                            targetUser = this.value.substring(0, this.value.length - 1);
                            targetUserFrontend = '<div style="display: inline-block; margin-bottom: 5px; margin-top: 10px"> <a id="userTag">' + " <b style='color: gray'> x </b> " + targetUser + '</a> </div>';

                            targetUserList.push(targetUser);
                            targetUserListFrontEnd.push(targetUserFrontend);

                            document.getElementById("targetUserList").value = targetUserList.join(", ");
                            document.getElementById("frontendUserList").innerHTML = targetUserListFrontEnd.join(" ");
                            this.value = "";

                            
                        }
                    });
                </script>
            </li>

            <input name="targetUserList" id="targetUserList" hidden> </input>

            <div name="frontendUserList" id="frontendUserList" style=""> </div>

            <br>

            <li> <input class="groups-configure-submit" type="submit" name="save" value="Speichern"> </li>

            <?php 
                $gid = $_GET["id"];
                $href = "action-manager/groups.php/deleteGroup/$gid";
            ?>

            <li> <input onclick="window.open('<?php echo(ROOT_PATH . $href); ?>', '_self')" style="cursor: pointer; text-align: center" class="groups-configure-submit" name="delete" value="Gruppe Löschen"> </li>



            <?php 
                $gid = $_GET["id"];
                $href = "groups/show/$gid";
            ?>

            <li> <input onclick="window.open('<?php echo(ROOT_PATH . $href); ?>', '_self')" style="cursor: pointer; text-align: center" class="groups-configure-submit" name="goback" value="Gruppenübersicht"> </li>

        </ul>
    </form>
    
</div>


</script>

