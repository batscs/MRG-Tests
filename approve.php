<?php
    require_once("functions.php");
    require_once("mysql.php");
    require __DIR__ . "/vendor/autoload.php";

    $folderDir = "temp";

    // refreshTestByUUID("ccc59fdd-78e1-4ea4-bdd9-a94ef2e37ada");
    // echo generateUUID();
    // echo getTokenByUUID($_SESSION["uuid"]);

    if (getRoleByUUID($_SESSION["uuid"]) < Lehrer) {
        // exit; // für debugging wird das erstmal ausgelassen :D
    }

?>
<?php

if (isset($_POST['submit'])) {
    
    $uniquePictureID = generateRandomString();
    //Die Encodeten Bild daten vom canvas welches wie über POST in php empfangen wird erstmal decoded
    //Der erste teil säubert den base64 encoding da davor noch text hinzugefügt wurde den wir nicht wollen
    $canvasdata = $_POST['canvasdata'];
    $canvasdata = str_replace('data:image/png;base64,', '', $canvasdata);
    $canvasdata = str_replace(' ', '+', $canvasdata);
    //hier wird es decoded
    $decodedcanvasdata = base64_decode($canvasdata);

    // und dann irgendwo vorerst gespeichert. Dies hier ist nur ein platzhalter zum testen der funktion aber nicht genug um qr codes einfach so zu scannen.
    // !!!!!!!!!!!!  newfolder zu einer directory in der htdocs ändern !!!!!!!!!!!!!!!
    $file = $folderDir . '/'. $uniquePictureID .'.jpg';
    file_put_contents($file, $decodedcanvasdata);
    
    $qrcode = new Zxing\QrReader($file);
    $scannedToken = $qrcode->text();

    approveToken($scannedToken);
    echo $scannedToken . " wurde approved";

}

?>