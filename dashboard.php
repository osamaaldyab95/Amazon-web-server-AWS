<?php
//show errors for debugging purpose
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//credentials for the sql-db
$user = "";
$host = "";
$database = "";
$password = "";

//open database
$mysqli = new mysqli($host, $user, $password, $database) or die("Keine Verbindung zur Datenbank!");

//get the measurements of the last seven days from the database
$timeBegin = new DateTime('now');
$timeBegin = $timeBegin->sub(new DateInterval('P7D'));
$strTimeBegin = $timeBegin->format('Y-m-d');

$query = "SELECT * FROM measurements WHERE time >= '$strTimeBegin' ORDER BY time DESC;";

$result = $mysqli->query($query);

if ($mysqli->errno) {
	echo("Es trat folgender SQL-Fehler auf: ".$mysqli->errno)."<br />";
}

//display measurements > code is quite messy but works ;) script selects all temperature-points of the last saved day and displays them
$temperature = 0;
$humidity = 0;
$counter = 0;

$record = $result->fetch_assoc();
$dateLast = new DateTime($record['time']);
$strDateLast = $dateLast->format("Y-m-d");
echo("<center><h1>$strDateLast</h1></center>");

while($record){

	$dateCurrent = new DateTime($record['time']);
    $strDateCurrent = $dateCurrent->format("Y-m-d");

	$dateCurrent = $dateCurrent->setTime(0,0,0);
	$dateLast = $dateLast->setTime(0,0,0);

	if($dateCurrent == $dateLast){
	    if($temperature < 100) {
            $exactTime = new DateTime($record['time']);
            $strExactTime = $exactTime->format('H:i');
		
		    $temperature = $record['temperature'];
            $humidity = $record['humidity'];
		    $counter++;

            if ($counter <= 10) {
                echo("$strExactTime => Temperatur: " . (round($temperature, 2)) . " Feuchtigkeit: " . (round($humidity, 2)) . " % <br />");
            }

            $dataPointsTemperature[] = array("label" => $strExactTime, "y" => round(($temperature), 2));

            $temperature = 0;
            $humidity = 0;

        }
	}else{
	    break;
    }


	$dateLast = new DateTime($record['time']);
	$strDateLast = $dateLast->format("Y-m-d");

	$record = $result->fetch_assoc();

}

$dataPointsTemperature = array_reverse($dataPointsTemperature);

$result->free();
$mysqli->close();
?>

<!DOCTYPE HTML>
<html>
<head>
	<script>
		window.onload = function () {

			var chartTemperature = new CanvasJS.Chart("chartContainer", {
				title: {
					text: <?php echo("\" Temperatur am $strDateLast \""); ?> 
				},
				axisX: {
					title: "Uhrzeit",
					suffix: ""
				},
				axisY: {
					title: "Temperatur",
					suffix: " in °C"
				},
				data: [{
					type: "line",
					yValueFormatString: "#,##0 °C",
					dataPoints: <?php echo json_encode($dataPointsTemperature, JSON_NUMERIC_CHECK); ?>
				}]
			});

			chartTemperature.render();

	}
	</script>
</head>
<body>
<div id="chartContainer" style="height: 400px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>
