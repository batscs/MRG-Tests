<?php
    require_once("mysql.php");

    session_start();

    // Rollen Konfiguration
    define("Schueler", 0);
    define("Lehrer", 1);
    define("Admin", 2);

    // verifySelf returned true oder false, anhand dessen ob der momentane User eingeloggt ist oder nicht
    function verifySelf() {
        if (isset($_SESSION["uuid"])) {
            $username = getUsernameByUUID($_SESSION["uuid"]);

            if ($username == "") {
                header("Location: logout.php");
                exit;
            }

            return true;
        } else {
            return false;
        }
        
    }

    // <head> wird hier für jede Seite erstellt
    function initSite($title) {
        ?>
            <head>
                <link rel="stylesheet" href="<?php echo ROOT_PATH;?>stylesheet.css">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title> <?php echo $title; ?> </title>
            </head>
        <?php
    }

    // Da auf jeder Seite oben MRG-Tests und der Username angezeigt wird, gibt es hierzu eine Funktion, um einfacheres modifizieren erlaubt.
    function drawHeader() {
        ?>
            <div class="site-header">
                <h1 class="mainfont"> MRG-Tests </h1>
                <?php
                    if (isset($_SESSION["uuid"])) {
                        ?>
                            „<?php echo getUsernameByUUID($_SESSION["uuid"]); ?>“
                        <?php
                    }
                ?>       
            </div>
        <?php
    }

    function bruteforceAllTokens() {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM tokens");
        $stmt->execute();

        $result = $stmt->fetchAll();
        $count = count($result);

        for ($i = 0; $i < $count; $i++) {
            $uuid = $result[$i]["uuid"];
            $new_token = getTokenByUUID($uuid);

            $stmt = $mysql->prepare("UPDATE tokens SET token = :token WHERE uuid = :uuid");
            $stmt->bindParam("token", $new_token);
            $stmt->bindParam("uuid", $uuid);
            $stmt->execute();
        }
    }

    // Returned true oder false basierend darauf ob die login daten richtig sind
    function loginAs($username, $password) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->bindParam("username", $username);
        $stmt->bindParam("password", $password);
        $stmt->execute();

        $row = $stmt->rowCount();

        // Falls es einen User gibt mit dem $username und dem gleichen $password wird true returned = valide login daten
        if ($row == 1) {
            return true;
        } else {
            return false;
        }
    }


    // Löscht alles gespeicherten Daten die zu einem User gehören
    function deleteUserByUUID($uuid) {
        global $mysql;

        $stmt = $mysql->prepare("DELETE FROM users WHERE uuid = :uuid; DELETE FROM tokens WHERE uuid = :uuid");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();

    }

    // Durch die Funktion kann man die Rolle (Berechtigungen) eines Users verändern (0 = Schüler, 1 = Lehrer, 2 = Admin)
    function updateRoleForUUID($uuid, $role) {
        global $mysql;
        $role = intval($role);

        $stmt = $mysql->prepare("UPDATE users SET role = :role WHERE uuid = :uuid");
        $stmt->bindParam("role", $role);
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();
    }

    function verifySecurityPinForUUID($uuid, $pin) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT pin FROM users WHERE uuid = :uuid");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result["pin"] == $pin ? true : false;
        
    }

    function changePasswordForUUID($uuid, $newpassword) {
        global $mysql;

        $stmt = $mysql->prepare("UPDATE users SET password = :password WHERE uuid = :uuid");
        $stmt->bindParam("password", $newpassword);
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();
    }

    function registerUser($username, $password, $pin) {
        // TODO: Check ob UUID bereits vorhanden ist, ist aber astronomisch unwahrscheinlich
        // TODO: Auch für Tokens, ebenfalls extrem unwahrscheinlich

        global $mysql;
        $stmt = $mysql->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam("username", $username);
        $stmt->execute();

        $row = $stmt->rowCount();

        // $username ist unvergeben, account darf erstellt werden
        if ($row == 0) {
            $uuid = generateUUID();
            $token = getTokenByUUID($uuid);

            $stmt = $mysql->prepare("INSERT INTO users (uuid, username, password, pin, role) VALUES (:uuid, :username, :password, :pin, 0); INSERT INTO tokens (uuid, token) VALUES (:uuid, :token)");
            
            $stmt->bindParam("uuid", $uuid);
            $stmt->bindParam("token", $token);

            $stmt->bindParam("username", $username);
            $stmt->bindParam("password", $password);
            $stmt->bindParam("pin", $pin);
            $stmt->execute();

            //--> wird nicht mehr benötigt, da tokens nun anonym sind
            //$stmt = $mysql->prepare("INSERT INTO tokens (uuid) VALUES (:uuid)");
            //$stmt->bindParam("uuid", $uuid);
            //$stmt->execute();

            return $uuid;
        } else {
            return false;
        }
    }

    function getUUIDbyUsername($username) {
        global $mysql;

        // Die Funktion gibt aus der MySQL Datenbank aus der Tabelle 'users' den Eintrag aus der Spalte 'uuid'(Unique User ID) zu dem zugehörigen Eintrag des 'username'
        $stmt = $mysql->prepare("SELECT uuid FROM users WHERE username = :username");
        $stmt->bindParam("username", $username);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result["uuid"];
    }

    function getUsernameByUUID($uuid) {
        global $mysql;

        // Die Funktion gibt aus der MySQL Datenbank aus der Tabelle 'users' den Eintrag aus der Spalte 'username' zu dem zugehörigen Eintrag der 'uuid' (Unique User ID)
        $stmt = $mysql->prepare("SELECT username FROM users WHERE uuid = :uuid");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result["username"];
    }

    function getRoleByUUID($uuid) {
        global $mysql;

        // Die Funktion gibt aus der MySQL Datenbank aus der Tabelle 'users' den Eintrag aus der Spalte 'role' zu dem zugehörigen Eintrag der 'uuid' (Unique User ID)
        $stmt = $mysql->prepare("SELECT role FROM users WHERE uuid = :uuid");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();
        $result = $stmt->fetch();

        // 0 = schüler, 1 = lehrer, 2 = administrator
        return $result["role"];
    }

    function getUUIDbyToken($token) {
        global $mysql;

        // Die Funktion gibt aus der MySQL Datenbank aus der Tabelle 'tokens' den Eintrag aus der Spalte 'uuid' zu dem zugehörigen Eintrag des 'token' (token sind einzigartig und verifizieren eine testung)
        $stmt = $mysql->prepare("SELECT uuid FROM tokens WHERE token = :token");
        $stmt->bindParam("token", $token);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result["uuid"];
    }

    // Die neue Token Lösung basiert auf einem deterministischen Prinzip, wobei der Random Number Generator auf der Unique User ID und dem heutigen Datum basiert, wodurch Tokens
    // nicht geklaut werden können, da sie am nächsten Tag ungültig sind, theoretisch könnte man die immer noch klauen wenn man die UUID von jemandem weiß, jedoch wird die
    // dem Benutzer niemals gesagt, wodurch der User sie nicht preis geben kann. Durch das deterministische Prinzip ist keine Internet Verbindung vorrausgesetzt, nur muss die
    // eigene UUID lokal gespeichert werden, ab dann kann man die aktuellen Tokens mit hilfe des aktuellen Datums rekonstruieren.
    function getTokenByUUID($uuid) {
        // $todayDateTime = date_format(new DateTime("today"), 'Y-m-d H:i:s');
        $week_start = date("Y-m-d", strtotime("-". date("w") ." days")); // DateTime von Wochenstart, jeden Montag wird ein neuer QR Code pro User benutzt.
        return "token-" . hash("md5", $uuid . $week_start);
    }


    // Überprüft ob der angegebene $token noch Gültig ist, sprich nicht nach dem Ablaufdatum (expiry) ist.
    function isTokenValid($token) {
        global $mysql;

        // Hier wird aus der MySQL Datenbank aus der Tabelle 'tokens' der Eintrag aus der Spalte 'expiry' zu dem zugehörigen Eintrag des 'token' gespeichert.
        $stmt = $mysql->prepare("SELECT * FROM tokens WHERE token = :token");
        $stmt->bindParam("token", $token);
        $stmt->execute();
        $result = $stmt->fetch();

        // $expiry wird auf den Datenbank Eintrag in der Spalte 'expiry' gesetzt
        $expiry = $result["expiry"];
        // Da der Datenbank Eintrag im DateTime Format ist, wird dieser zu einem unix timestamp verarbeitet
        $expiry_timestamp = strtotime($expiry);
        $now = time();

        // Es wird verglichen ob das Ablaufsdatum noch nicht abgelaufen ist, und anhand der Gültigkeit true oder false returned
        return $expiry_timestamp > $now ? true : false;
    }

    function tokenExists($token) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM tokens WHERE token = :token");
        $stmt->bindParam("token", $token);
        $stmt->execute();

        $rows = $stmt->rowCount();

        return $rows == 0 ? false : true;
    }
    

    // Ein negativer Test von einem Schüler (uuid) kann hier von einem Lehrer bestätigt werden.
    function approveToken($token) {
        global $mysql;
        $global_preferences = file_get_contents('global_preferences.json');
        $json_global_preferences = json_decode($global_preferences, true);
        $time = $json_global_preferences["dwell_time"];
        // Neues ablauf Datum wird erstellt
        $expiry = new DateTime("now +$time hours"); // expiry = morgen (ablaufdatum der testgültigkeit)
        $expiry = date_format($expiry, 'Y-m-d H:i:s'); // wird formattiert in >-m-d H:i:s für mysql datenbank

        // Es wird überprüft ob es bereits einen Eintrag zu dem Token gibt
        $stmt = $mysql->prepare("SELECT * FROM tokens WHERE token = :token");
        $stmt->bindParam("token", $token);
        $stmt->execute();
        $row = $stmt->rowCount();

        // Wenn es noch keinen gibt, wird ein neuer Eintrag in der Datenbank mit dem Token generiert und das Ablaufdatum eingetragen.
        if ($row == 0) {
            $stmt = $mysql->prepare("INSERT INTO tokens SET expiry = :exp, token = :token");
            $stmt->bindParam("exp", $expiry);
            $stmt->bindParam("token", $token);

            $stmt->execute();
            
        // Falls der Token bereits existiert, wird lediglich das Ablaufdatum "erfrischt".
        } else {
            $stmt = $mysql->prepare("UPDATE tokens SET expiry = :exp WHERE token = :token");
            $stmt->bindParam("exp", $expiry);
            $stmt->bindParam("token", $token);

            $stmt->execute();
        }
        
    }

    function generateUUID($data = null) {
		// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
		$data = $data ?? random_bytes(16);
		assert(strlen($data) == 16);

		// Set version to 0100
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		// Set bits 6-7 to 10
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		// Output the 36 character UUID.
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

    function generateToken($seed = null) {  
        return "token-" . generateRandomString(12, $seed); 
    }

    function generateRandomString($length = 10, $seed = null) {
        
        // TODO: Cross Language Compatible Same Random Number Generator implementation

        // Wenn $seed nicht null ist, wird der srand() seed auf den $seed gesetzt, sonst wird er auf das default (0) gesetzt.
        if ($seed != null) {
            srand(crc32($seed));
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $charactersLength = strlen($characters); 

        $randomString = ''; for ($i = 0; $i < $length; $i++) { 
            $randomString .= $characters[rand(0, $charactersLength - 1)]; 
        } 
        return $randomString; 
    }
    // Guckt ob ein String mit einem Bestimmten String anfängt
    function strStarsWith($haystack, $needle) {
        $length = strlen($needle);
        return substr($haystack, 0, $length) == $needle ? true : false;
    }
   // INSERT INTO users (uuid, username, password, pin, role) VALUES (:uuid, :username, :password, :pin, 0); INSERT INTO tokens (uuid, token) VALUES (:uuid, :token)");
    function createGroup($groupname, $groupowner) {
        global $mysql;

        $stmt = $mysql->prepare("INSERT INTO groups_info (group_id, group_name, owner) VALUES (:group_id, :group_name, :owner)");
        @$stmt->bindParam("group_id", generateRandomString(12));
        $stmt->bindParam("group_name", $groupname);
        $stmt->bindParam("owner", $groupowner);
        $stmt->execute();
        
    }
    // guckt ob eine UUID in einer Gruppe ist
    function isUUIDaParticipantInGroup($uuid, $groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_structure WHERE group_id = :group_id AND participant = :uuid");
        $stmt->bindParam("group_id", $groupid);
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();

        $count = $stmt->rowCount();

        // Wenn die UUID nicht in der GruppenID gefunden wurde ( == 0) wird false returned, andernfalls true.
        return $count == 0 ? false : true;
    }
    // Guckt wie viele in einer gruppe sind
    function getParticipantCountInGroup($groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_structure WHERE group_id = :group_id");
        $stmt->bindParam("group_id", $groupid);
        $stmt->execute();

        $count = $stmt->rowCount();

        return $count;
    }

    function getParticipantsInGroup($groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_structure WHERE group_id = :group_id ORDER BY confirmation ASC");
        $stmt->bindParam("group_id", $groupid);
        $stmt->execute();

        $count = $stmt->fetchAll();

        return $count;
    }

    function getGroupnameByID($groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_info WHERE group_id = :group_id");
        $stmt->bindParam("group_id", $groupid);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row["group_name"];
    }

    function getGroupsWhereUUIDisOwner($uuid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT group_id FROM groups_info WHERE owner = :uuid");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }

    function getGroupsWhereUUIDisParticipant($uuid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT group_id FROM groups_structure WHERE participant = :uuid AND confirmation = 'confirmed'");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }

    function getGroupOwnerUUIDByGroupID($groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT owner FROM groups_info WHERE group_id = :gid");
        $stmt->bindParam("gid", $groupid);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row["owner"];
    }

    function isUUIDOwnerOfGroupID($uuid, $groupid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT owner FROM groups_info WHERE group_id = :gid");
        $stmt->bindParam("gid", $groupid);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row["owner"] == $uuid ? true : false;
    }

    function getAllUsers() {
        global $mysql;

        $stmt = $mysql->prepare("SELECT username FROM users");
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $result;
    }

    function updateGroup($groupData) {
        global $mysql;

        $stmt = $mysql->prepare("UPDATE groups_info SET group_name = :groupName WHERE group_id = :groupID");
        $stmt->bindParam("groupID", $groupData["GID"]);
        $stmt->bindParam("groupName", $groupData["name"]);
        $stmt->execute();

        for ($i = 0; $i < count($groupData["userAddList"]); $i++) {
            $currentUser = $groupData["userAddList"][$i];
            $currentUUID = getUUIDbyUsername($currentUser);

            if (!isUUIDaParticipantInGroup($currentUUID, $groupData["GID"]) && $currentUUID != null) {
                $stmt = $mysql->prepare("INSERT INTO groups_structure (group_id, participant, confirmation) VALUES (:gid, :user, 'pending')");
                $stmt->bindParam("gid", $groupData["GID"]);
                $stmt->bindParam("user", $currentUUID);
                $stmt->execute();
                
            }

            
        }
    }

    function confirmGroupStatusForUUID($group_id, $uuid, $confirmation = true) {
        global $mysql;

        if ($confirmation == true) {
            $stmt = $mysql->prepare("UPDATE groups_structure SET confirmation = 'confirmed' WHERE participant = :uuid AND group_id = :gid");
            $stmt->bindParam("uuid", $uuid);
            $stmt->bindParam("gid", $group_id);
            $stmt->execute();
        } else {
            $stmt = $mysql->prepare("DELETE FROM groups_structure WHERE participant = :uuid AND group_id = :gid");
            $stmt->bindParam("uuid", $uuid);
            $stmt->bindParam("gid", $group_id);
            $stmt->execute();
        }
    }

    function getGroupsWhereUUIDisPending($uuid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_structure WHERE participant = :uuid AND confirmation = 'pending' ");
        $stmt->bindParam("uuid", $uuid);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    function isUUIDPendingForGroupID($uuid, $gid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_structure WHERE participant = :uuid AND group_id = :gid ");
        $stmt->bindParam("uuid", $_SESSION["uuid"]);
        $stmt->bindParam("gid", $gid);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row["confirmation"] == "pending" ? true : false;
    }

    function getOwnerOfGroupID($gid) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_info WHERE group_id = :gid");
        $stmt->bindParam("gid", $gid);
        $stmt->execute();
        
        $row = $stmt->fetch();
        return $row["owner"];
    }

    function deleteGroup($confirmation_uuid_owner, $group_id) {
        global $mysql;

        $stmt = $mysql->prepare("SELECT * FROM groups_info WHERE group_id = :gid");
        $stmt->bindParam("gid", $group_id);
        $stmt->execute();

        $row = $stmt->fetch();

        if ($row["owner"] == $confirmation_uuid_owner) {
            $stmt = $mysql->prepare("DELETE FROM groups_info WHERE group_id = :gid; DELETE FROM groups_structure WHERE group_id = :gid");
            $stmt->bindParam("gid", $group_id);
            $stmt->execute();
        }
    }
?>


