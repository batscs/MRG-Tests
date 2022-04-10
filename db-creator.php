<?php
	require_once("mysql.php");
	require_once("functions.php");
	
	// Es wird überprüft ob die Datenbank bereits aufgesetzt wurde
	$stmt = $mysql->prepare("SHOW TABLES LIKE 'users';");
	$stmt->execute();
	$row = $stmt->rowCount();
	echo $row;
	
	if ($row == 0) {
		// vordefiniertes password (sha512 hash) = ezlife123
		$admin_pw = "30c1a6ae229d27d7454c1f3d9862c75e5648664c7a3b3c21f6f1939d8c6d1e7aef8f780e88bf1eb4e0de4d18b3084466268466ae4796de9350c1cae7c48a9b36";
		$admin_uuid = generateUUID();

		// users tabelle wird erstellt in mrg datenbank
		$stmt = $mysql->prepare("CREATE TABLE `users` ( `uuid` VARCHAR(255) NOT NULL , `username` VARCHAR(255) NOT NULL , `password` VARCHAR(255) NOT NULL , `pin` VARCHAR(255) NOT NULL , `role` INT NOT NULL DEFAULT '0' ) ENGINE = InnoDB;");
		$stmt->execute();

		// tokens tabelle wird erstellt in mrg datenbank
		$stmt= $mysql->prepare("CREATE TABLE `tokens` ( `uuid` VARCHAR(255) NOT NULL , `token` VARCHAR(255) NOT NULL , `expiry` TIMESTAMP NULL DEFAULT NULL ) ENGINE = InnoDB; ");
		$stmt->execute();

		// groups tabelle wird erstellt in mrg datenbank (dient der erstellung & management einer gruppe)
		$stmt = $mysql->prepare("CREATE TABLE `groups_info` ( `group_id` VARCHAR(255) NOT NULL , `group_name` VARCHAR(255) NOT NULL , `owner` VARCHAR(255) NOT NULL ) ENGINE = InnoDB; ");
		$stmt->execute();
		
		// groups_structure wird erstellt in mrg datenbank (dient der zuordnung von usern zu einer gruppe)
		$stmt = $mysql->prepare("CREATE TABLE `groups_structure` ( `group_id` VARCHAR(255) NOT NULL , `participant` VARCHAR(255) NOT NULL , `confirmation` VARCHAR(255) NOT NULL ) ENGINE = InnoDB; ");
		$stmt->execute();

		// administrator account wird in users tabelle erstellt mit einem vordefiniertem password
		// desweiteren benötigt der administrator keinen token, deshalb wird keiner erstellt
		$stmt = $mysql->prepare("INSERT INTO users (uuid, username, password, role) VALUES (':admin_uuid', 'Administrator', :adminpw, 2)");
		$stmt->bindParam("adminpw", $admin_pw);
		$stmt->bindParam("admin_uuid", $admin_uuid);
		$stmt->execute();

		// null account dient der vermeidung von fehlern
		$stmt = $mysql->prepare("INSERT INTO users (uuid, username, password, role) VALUES (':nullname', 'null', :nullpw, -1)");
		$nullname = "null";
		$nullpw = "null";
		$stmt->bindParam("nullname", $nullname);
		$stmt->bindParam("nullpw", $nullpw);
		$stmt->execute();

		// primary key für users und tokens damit man einfacher editen kann
		$stmt = $mysql->prepare("ALTER TABLE users ADD id INT PRIMARY KEY AUTO_INCREMENT;");
		$stmt->execute();

		$stmt = $mysql->prepare("ALTER TABLE groups_info ADD id INT PRIMARY KEY AUTO_INCREMENT;");
		$stmt->execute();

		$stmt = $mysql->prepare("ALTER TABLE groups_structure ADD id INT PRIMARY KEY AUTO_INCREMENT;");
		$stmt->execute();

		$stmt = $mysql->prepare("ALTER TABLE tokens ADD id INT PRIMARY KEY AUTO_INCREMENT;");
		$stmt->execute();
		
	}
?>