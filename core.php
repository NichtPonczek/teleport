<?php
date_default_timezone_set('Europe/Berlin');	
ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'UTF-8');
echo "..:: Teleport ::..".PHP_EOL;
echo "..:: Loading Config ::..".PHP_EOL;

require_once "config/config.php";
require_once "include/ts3admin.class.php";
require_once "include/commander.php";

echo "..:: Conecting to teamspeak: ::..".PHP_EOL;

$ts = new ts3admin($config["teamSpeak"]["serverIP"], $config["teamSpeak"]["serverQueryPort"]);		
if($ts->getElement('success', $ts->connect())){ 
	if($ts->getElement('success', $ts->login($config["teamSpeak"]["serverQueryLogin"], $config["teamSpeak"]["serverQueryPassw"]))){				
		if($ts->getElement('success', $ts->selectServer($config["teamSpeak"]["serverPort"]))){
			$ts->setName($config["teamSpeak"]["botName"]);
			
			$tsAdminSocket = $ts->runtime['socket'];

			sendCommand("servernotifyregister event=textprivate");
			sendCommand("servernotifyregister event=server");

			while(true){
				$socketdata = getData();

				if(isset($socketdata["notifycliententerview"]) && !empty($socketdata["client_database_id"])){
					echo "ktos wszedl na serwer".PHP_EOL;
					$ts->sendMessage(1, $socketdata["clid"], "Hello [b]".$socketdata["client_nickname"]."[/b]. thanks to me you can teleport arround the server.");
					$ts->sendMessage(1, $socketdata["clid"], "if you want to teleport just type [b]!tp <TAG/ID>[/b].");
					$ts->sendMessage(1, $socketdata["clid"], "[b]List of available teleports:[/b]");
					$ts->sendMessage(1, $socketdata["clid"], " ");
					foreach($config["channels"] as $channel){				
						$ts->sendMessage(1, $socketdata["clid"], "● [b]".$channel["tag"]."[/b](".$channel["channel"].")");
					}
				}

				if(isset($socketdata["notifytextmessage"]) && !empty($socketdata["invokerid"])){
					$explode = explode(" ", $socketdata["msg"]);
					if($explode[0] == "!tp"){
						if(isset($explode[1])){
							$found = false;
							foreach($config["channels"] as $channel){
								if(strtolower($channel["tag"]) == strtolower($explode[1]) OR $channel["channel"] == $explode[1]){
									$found = true;
									$ts->clientMove($socketdata["invokerid"], $channel["channel"]);
									$ts->sendMessage(1, $socketdata["invokerid"], "You got teleported to [b]".$channel["tag"]."[/b].");
								}
							}
							if(!$found){
								$ts->sendMessage(1, $socketdata["invokerid"], "No such[b]tag[/b] or [b]channel[/b] exists!");
							}
						}else{
							$ts->sendMessage(1, $socketdata["invokerid"], "Please enter [b]tag[/b] or [b]id [/b].");
						}
					}
				}
			}
		}else{
			echo "..:: Server selecion failed ::..".PHP_EOL;
			echo "---------------------- Info: ----------------------".PHP_EOL;
			showErrors($ts->getElement('errors', $ts->selectServer($config["teamSpeak"]["serverPort"])));
			echo "---------------------------------------------------------".PHP_EOL;
			die();
		}
	}else{
		echo "..:: Failed to log on to the teamspeak server ::..".PHP_EOL;
		echo "---------------------- Informacje: ----------------------".PHP_EOL;
		showErrors($ts->getElement('errors', $ts->login($config["teamSpeak"]["serverQueryLogin"], $config["teamSpeak"]["serverQueryPassw"])));
		echo "---------------------------------------------------------".PHP_EOL;
		die();
	}
}else{
	echo "..:: Connection failed ::..".PHP_EOL;
	echo "---------------------- Informacje: ----------------------".PHP_EOL;
	showErrors($ts->getElement('errors', $ts->connect()));
	echo "---------------------------------------------------------".PHP_EOL;
	die();
}

function showErrors($errors){
	foreach($errors as $error){
		echo $error.PHP_EOL;
	}
}
