<?php
error_reporting(0);

require_once("functions.php");
session_write_close();

if (getRoleByUUID($_SESSION["uuid"]) < Lehrer) {
    // exit; // für debugging wird das erstmal ausgelassen :D
}

?>

<?php
if (isset($_POST['submit'])) {

    //Der erste teil säubert den base64 encoding da davor noch text hinzugefügt wurde den wir nicht wollen
    //mit der neuen Methode brauchen wir das eigentlich nicht zu säubern aber ich habe angst es zu entfernen
    $canvasdata = $_POST['canvasdata'];



    $scannedToken = $canvasdata;

    if ($scannedToken == "") {
    } else {

        $username = getUsernameByUUID(getUUIDbyToken($scannedToken));
        $tested = isTokenValid($scannedToken);
        $output = $scannedToken;
    }

    //   echo $output;
}


if (isset($output)) {

    if ($scannedToken == "") {

        echo '{"isvalid":false,"tokenvalue":"error+decoding+QR+Code"}';
    } else {
        if ($_POST['approved'] == 1) {

            if (tokenExists($scannedToken)) {
                // todo: geht iwie nicht hi haron
                // mahlzeit 
                approveToken($scannedToken);
                $arr = array(
                    "isvalid" => $tested,
                    "tokenvalue" => $scannedToken,
                    "approve" => 1,
                    "username" => $username
                );

                echo json_encode($arr);
            }else{
               // approveToken($scannedToken);
                $arr = array(
                    "isvalid" => $tested,
                    "tokenvalue" => $scannedToken,
                    "approve" => 0,
                    "username" => $username
                );

                echo json_encode($arr);
            }
                } else {
            $arr = array(
                "isvalid" => $tested,
                "tokenvalue" => $scannedToken,
                "approve" => 2,
                "username" => $username
            );

            echo json_encode($arr);
        }
    }
}

?>