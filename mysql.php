 <?php
$host = "localhost";
$user = "root";
$password = "";

$db_name = "mrg";

// Wird benutzt damit man einfach redirecten kann immer relativ zum pfad, ansonsten gibt es manchmal probleme mit htaccess da die url geändert wird, und nichts mehr ist wie es scheint
define('ROOT_PATH', "https://127.0.0.1/mrg/");

try {
  $mysql = new PDO("mysql:host=$host;dbname=$db_name", $user, $password);
  
} catch (PDOException $e) {
  echo "SQL Error: " .$e->getMessage();
}

?> 