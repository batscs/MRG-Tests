
<?php



    require_once("functions.php");
    

    $folderDir = "temp";

    // refreshTestByUUID("ccc59fdd-78e1-4ea4-bdd9-a94ef2e37ada");
    // echo generateUUID();
    // echo getTokenByUUID($_SESSION["uuid"]);

    if (!verifySelf()) {
        exit;
    }

    if (getRoleByUUID($_SESSION["uuid"]) < Lehrer) {
        exit;
    }

    initSite("MRG-Tests | Schüler Check");

    drawHeader();

?>
    

<body id="body"></body>
<div class="scan-video-input"> 


<!-- der wunderschöne output -->

<b><center> <div style="font-size: 20px;" id="outputdiv" ></div> </center></b>
<br>
<b><center> <div style="font-size: 20px;" id="errordiv" ></div> </center></b>

<center> <video id="webcam" muted="true" autoplay="true"></video> </center>
    
   
</div>


<canvas id="screen" width="900" height="900" style="visibility: hidden; position: fixed;"></canvas>
<!-- Das Form um die Canvas zu PHP per POST Hochzuladen -->


<div class="site-content-list">
    <ul>               
    <form method="POST" action="" onsubmit="sendcanvastophp();">
    <input id="canvasdata" name="canvasdata" type="hidden">     
    <input id="submit" type="submit" name="submit" value="Gültigkeit Prüfen" hidden>       
    </form>
    

    <form method="POST" action="" onsubmit="sendcanvastophp();">
    <input id="canvasdata" name="canvasdata" type="hidden">     
    <input id="submit" type="submit" name="submit" value="Test Bestätigen " hidden >   
    </form>
    <li> <p>  <a onclick="togglescan()" id="togglescan"> Scan Starten </a> </p> </li> 
    <li> <p> <a onclick="toggleapprove()" id="approvescan"> Gescannte QR-Codes Validieren </a> </p> </li>
    <li> <p> <a href="index.php"> Homepage </a> </p> </li>
    </ul>
    </div>
    <!-- Jquery libary für AJAX von google apis importieren -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>
        // Entfernt php post meldung beim refreshen, vermutlich nicht dauerthaft einsetzbar aber vorerst erspart es nerven
        if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }

        // Streamt Video von Kamera zu video tag mithilfe von javascript, Quelle : https://dev.to/dalalrohit/live-stream-your-webcam-to-html-page-3ehf
        var webcam = document.querySelector('#webcam');
        // Versucht eine Video-Feed von der Kamera zu empfangen, wenn vorhanden von der environment kamera, welche die Kamera
        // auf der Hinterseite des Handys ist, falls nicht vorhanden wird das benutzt was vorhanden ist.
        // Falls der Video-Feed nicht gestartet werden kann, dann gibt es den alert error
        window.navigator.mediaDevices.getUserMedia({ video: { facingMode : 'environment'}})
        .then(stream => {
            webcam.srcObject = stream;
            webcam.onloadedmetadata = (e) => {
            webcam.play();
                };
        })
        .catch( () => {
            alert('Keine Rechte auf die Kamera oder keine Kamera gefunden');
        });
        
        function screen() {
            var canvas = document.getElementById("screen");
            var video = document.getElementById("webcam");
            canvas.getContext('2d').drawImage(video, 0, 0);

            // Hilft beim Debuggen :D
            console.log(video);
            
        }

        function sendcanvastophp(){
            // Macht screenshot vom video
            screen();
            // Canvas Daten des Bildes werden zu base64 string encoded um über Forms schickbar zu sein, wird in php  decoded

            document.getElementById('canvasdata').value = document.getElementById('screen').toDataURL();
        }
      
        
        // Scan starten und Gescannte qr-codes validieren button kriegen ihre gebrauchten variablen und ein wenig css magie :sparkles:
        var scanning = false;
        var approve = 0;
        document.getElementById("togglescan").style.backgroundColor = "#3ED260";
        document.getElementById("approvescan").style.backgroundColor = "#FC575E";
        document.getElementById("togglescan").style.cursor = "pointer";
        document.getElementById("approvescan").style.cursor = "pointer"

      

        // Wenn der Gescannte QR-Code Validiert button geklickt wird dann das hier, ähnelt dem togglescan
        function toggleapprove() {
            if (approve == 0) {
                approve = 1;
                document.getElementById('approvescan').style.backgroundColor = "#3ED260";
            }else if (approve == 1){
                approve = 0;
                document.getElementById('approvescan').style.backgroundColor = "#FC575E";
                document.getElementById('errordiv').innerText = "";
            }

        }
          // Wenn der "Scan Starten" Button geklickt wird, wird dies ausgeführt :
        // Wenn der Scan schon an ist, wird er aus und anders herum auch, Beim ausmachen werden um Bugs zu vermeiden vieles zurückgesetzt auf Standard
        function togglescan() {
        
        if (scanning == false) {
            scanning = true;
            document.getElementById("togglescan").style.backgroundColor = "#FC575E";
            document.getElementById("togglescan").innerText = "Scan Stoppen";

            submitform();
        }else if (scanning == true){
            scanning = false;
            document.getElementById("togglescan").style.backgroundColor = "#3ED260";
            document.getElementById("togglescan").innerText = "Scan Starten";
            document.getElementById('outputdiv').style.color = "black";
            document.body.style.backgroundColor = "065fc5";
            document.getElementById('outputdiv').innerText = "";
            document.getElementById('errordiv').innerText = "";
            
            submitform();
            
        }
        
        }
            // Libary decoded das Empfangene Bild vom canvas mit der id canvasdata von base64 und Erkennt im Bild den QR-Code
        function decodeImageFromBase64(data, callback){
                qrcode.callback = callback;
                qrcode.decode(data);
            }
        var qrdata;
        function submitform() {
        
        qrcode.decode(document.getElementById('canvasdata').value)
            

            if(scanning == true) {
            sendcanvastophp();
            decodeImageFromBase64(document.getElementById('canvasdata').value, function (decodedInformation) {
                  qrdata = decodedInformation;
            });
        
            
           // AJAX call zu video.php um zu empfangen ob der Token valid ist oder nicht
           $.post("video.php",  {   

            canvasdata: qrdata,
            submit: 1,
            approved : approve,
            dataType: 'json',
            async : true, },  
            function(data){     
	        
            // Falls ein error kommt sollen standard werte festgelegt werden, ansonsten holt es sich die JSON die video.php echoed und holt sich die daten daraus
            try {
            let text = data;    
            const obj = JSON.parse(text);
            var token = obj.tokenvalue;
            var isvalid = obj.isvalid;
            var approvereturn = obj.approve;
            var user = obj.username;
           
            }
            catch(err) {
             isvalid = false;
             approvereturn = 2;
             token = "0";
             document.getElementById('outputdiv').innerText = 'Bitte warte...';
            }
    // Falls der Token valid ist wird der Hintergrund wird und es wird ein Bestätigender Text angegeben, Es wartet 1 Sekunde bevor es submitform(); aufruft
    // Falls der Token invalid und mit "token-" anfängt, Hintergrund wird Rot und ein Text wird ausgegeben, Es wartet 1 Sekunde bevor es submitform(); aufruft
    // Falls der Token invalid ist und ohne "token-" anfängt wird es Ignoriert und es gibt an es sucht nach einem QR-Code, Es ruft submitform(); sofort auf
    // Alle Token in der Datenbank starten mit "token-" daher ist dies vorteilhaft um nicht jedes Bild auch ohne QR-Code als Invalid anzusehen
    // Falls Gescanne QR-Codes Validiert werden sollten, dann versucht es dies und weist darauf hin falls fehler entstehen
            if(isvalid == true) { 
                if(approvereturn == 0){
                    document.getElementById('errordiv').innerText = 'Es hab ein Fehler beim Validieren des QR-Codes'
                }else if(approvereturn == 1){
                    document.getElementById('errordiv').innerText = 'QR-Code von ' + user + ' wurde Validiert'
                }

                document.getElementById('outputdiv').innerText = user + ' hat einen gültigen Test';
                document.getElementById('outputdiv').style.color = "black";
                document.body.style.backgroundColor = "green";

                window.setTimeout(submitform, 1000);      
            }else if(isvalid == false) {  
                             
                if(token.startsWith("token-")) {

                if(approvereturn == 0){
                    document.getElementById('errordiv').innerText = 'Es hab ein Fehler beim Validieren des QR-Codes'
                }else if(approvereturn == 1){
                    document.getElementById('errordiv').innerText = 'QR-Code von ' + user + ' wurde Validiert'
                }

                document.getElementById('outputdiv').style.color = "black";
                document.body.style.backgroundColor = "red";
                document.getElementById('outputdiv').innerText = user + ' hat keinen gültigen Test';

                window.setTimeout(submitform, 1000);      

                }else{

                document.getElementById('outputdiv').innerText = 'Suche QR-Code...';
                document.getElementById('errordiv').innerText = "";
                document.body.style.backgroundColor = "#065fc5";

                submitform();
                }
            }


            
            });

            }else if(scanning == false){

            document.getElementById('outputdiv').innerHTML = "";
            }
            }


        function approvetest() {

            sendcanvastophp();
          
           $.post("approve.php",  {   

            canvasdata: document.getElementById('canvasdata').value,
            submit: 1,
            async : true, },  
            function(data){     
          

            

            });

        }    
            
  

    </script>
    

<!-- Importiert die QR-Code Libary https://github.com/LazarSoft/jsqrcode -->
<script type="text/javascript" src="src/grid.js"></script>
<script type="text/javascript" src="src/version.js"></script>
<script type="text/javascript" src="src/detector.js"></script>
<script type="text/javascript" src="src/formatinf.js"></script>
<script type="text/javascript" src="src/errorlevel.js"></script>
<script type="text/javascript" src="src/bitmat.js"></script>
<script type="text/javascript" src="src/datablock.js"></script>
<script type="text/javascript" src="src/bmparser.js"></script>
<script type="text/javascript" src="src/datamask.js"></script>
<script type="text/javascript" src="src/rsdecoder.js"></script>
<script type="text/javascript" src="src/gf256poly.js"></script>
<script type="text/javascript" src="src/gf256.js"></script>
<script type="text/javascript" src="src/decoder.js"></script>
<script type="text/javascript" src="src/qrcode.js"></script>
<script type="text/javascript" src="src/findpat.js"></script>
<script type="text/javascript" src="src/alignpat.js"></script>
<script type="text/javascript" src="src/databr.js"></script>
<script src="src/qrcode-decoder.min.js"></script>




	