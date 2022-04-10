<?php

    // Diese Seite dient dazu dass man nicht forms für alle aktionen benötigt, sondern hierhin redirecten kann und die Seite
    // dann den request verarbeitet.

    require_once("mysql.php");
    require_once("functions.php");

    $instantRedirect = true;

    $origin = $_GET["origin"];
    $action = $_GET["action"];
    $params = $_GET["params"];

    if ($action == "acceptGroup") {
        $pending_groups = getGroupsWhereUUIDisPending($_SESSION["uuid"]);

        // Es wird erstmals geguckt ob der User überhaupt eine Einladung zu der Gruppe hat, sonst könnte man sich selbst durch die Manipulation
        // der URL in jede Gruppe einladen
        for ($i = 0; $i < count($pending_groups); $i++) {
            if ($pending_groups[$i]["group_id"] == $params) {
                confirmGroupStatusForUUID("id123", $_SESSION["uuid"], true);
            }
        }
    } else if ($action == "leaveGroup") {

        // Hier wird nicht überprüft ob der Nutzer bereits in der Gruppe ist, da es nicht nötig ist.
        // Falls er es nicht ist, passiert nunmal nichts.
        confirmGroupStatusForUUID("id123", $_SESSION["uuid"], false);
    } else if ($action == "deleteGroup") {
        if (isUUIDOwnerOfGroupID($_SESSION["uuid"], $params)) {
            deleteGroup($_SESSION["uuid"], $params);
        }
    }

    if ($instantRedirect) { header("Location: " . ROOT_PATH . $_GET["origin"]); }
?>