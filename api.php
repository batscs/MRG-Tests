<?php
    require_once("functions.php");
    require_once("mysql.php");

    class API {

        function Select() {

            // API: domain/api/username/password ==> returned: login_valid: true/false
            // API: domain/api/username/password/token/check/token-ID ==> returned token_valid: true/false UND token_associated_username: schueler 

            $response = array();

            $login = false;

            if (isset($_GET["username"]) && isset($_GET["password"])) {
                $login = loginAs($_GET["username"], $_GET["password"]);
                $role = intval(getRoleByUUID(getUUIDbyUsername($_GET["username"])));
                $response["login_valid"] = $login;
                $response["login_role"] = $role;
            }


            $action = $_GET["action"] ?? "";
            if ($login) {

                if ($action == "token") {

                    if (isset($_GET["check"])) {

                        $token = $_GET["check"];

                        if ($role >= Lehrer) {

                            $response["token_valid"] = isTokenValid($token);
                            $response["token_associated_username"] = getUsernameByUUID(getUUIDbyToken($token));
                        } else {
                            $response["token_error"] = "Not enough Permissions.";
                        }
    
                    }
                }

                
            }

            

            

            //$string = "hallo";

            return json_encode($response);
        }
    }

    $API = new API;

    header('Content-Type: application/json');
    echo $API->Select();
?>