<?php
//show errors for debugging purpose
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//set $temperature and $humidity -1 to check later if there was data given
$temperature = -1;
$humidity = -1;

$time = new DateTime('now');
$strTime = $time->format("Y-m-d H:i:s");

//if there was an ip given, save it; ip usually represents the local ip of the raspberry; can be used for ssh-connection
if(isset($_GET["ip"])){
	$ip = $_GET["ip"];
	file_put_contents("ip.txt", $ip);
	exit();
}

//get the given values for temperature and humidity
if(isset($_GET["temperature"])){
	$temperature = $_GET["temperature"];
}

if(isset($_GET["humidity"])){
	$humidity = $_GET["humidity"];
}

//abort if no data was given
if(($humidity == -1) || ($temperature == -1)){
	die("Keine Messdaten übergeben!");
} 

//credentials for the sql-db
$host = "localhost";
$user = "";
$pass = "";
$db = "";

//save given values to db
$mysqli = new mysqli($host, $user, $pass, $db) or die("Das Speichern der Daten war nicht erfolgreich, da keine Verbindung zur Datenbank hergestellt werden konnte.");

$humidity = $mysqli->real_escape_string($humidity);
$temperature = $mysqli->real_escape_string($temperature);

$query = "INSERT INTO measurements (temperature, humidity, time) VALUES ('$temperature', '$humidity','$strTime');";

$mysqli->query($query);

if($mysqli->errno){
	echo("Es trat ein Fehler auf: ".$mysqli->errno." <br />");
}

$mysqli->close();

?>
