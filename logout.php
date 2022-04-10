<?php
    session_start();
    session_destroy();
    
    ?> <a href="index.php"> Homepage hier. </a> <br> <?php
    header("Location: index.php");
?>