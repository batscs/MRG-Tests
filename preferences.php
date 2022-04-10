

<?php
require("functions.php");
initSite("Admin Preferences");
$global_preferences = file_get_contents('global_preferences.json');
$json_global_preferences = json_decode($global_preferences, true);
?>
<center>
    <form action="preferences.php" method="POST" style="padding : 20px">
        Stunden die ein QR-Code Valid bleibt: <br>
        <input style="padding : 10px" autocomplete="off" type="text" id="dwell_time" name="dwell_time" value="<?php echo $json_global_preferences["dwell_time"]; ?>">
        <br>
        <br>
        <input style="padding : 10px" type="submit" name="submit_dwelltime" value="Speichern">
        <div class="site-content-list">  <ul> <li> <p> <a href="index.php"> Homepage </a> </p> </li> </ul> </div>
    </form> 
</center>
<?php
if (isset($_POST['submit_dwelltime']))
{
    $modified_dwell_time = htmlentities($_POST['dwell_time']);
    if (preg_match('/\D/', $modified_dwell_time))
    {
        echo "Es sind nur Zahlen erlaubt";
    }
    else
    {
        $json_global_preferences["dwell_time"] = $modified_dwell_time;
        $modified_global_preferences = json_encode($json_global_preferences);
        file_put_contents('global_preferences.json', $modified_global_preferences);
        header("refresh: 0");
    }
}

?>
    <script>

        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }

    </script>
