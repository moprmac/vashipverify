<?php

date_default_timezone_set('America/New_York');
echo date('Y-m-d H:i:s');

echo "<br/><br/>";


$dbname = 'usuiusa0_shipverify';
$dbuser = 'usuiusa0_shipver';
$dbpass = 'kdDQvh9*pcda';

/*
// Connects to Our Database 
$mysqli = new mysqli("localhost", "$dbuser", "$dbpass", "$db"); 
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
*/
$db = NULL;
$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

try
{
    $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $dbuser, $dbpass, $options);
	$offset = 'America/New_York';
	$db->exec("SET time_zone='$offset';");
}
catch(PDOException $ex)
{
    die("Failed to connect to the database: " . $ex->getMessage());
}

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

//$mstime = SELECT NOW();
$stmt = $db->prepare("SELECT NOW() as CurrentTime");
$stmt->execute();
$row = $stmt->fetch();
echo($row["CurrentTime"] . "<br/>");

$stmt = $db->prepare("SELECT @@global.time_zone as TimeZone1, @@session.time_zone as TimeZone2;");
$stmt->execute();
$row = $stmt->fetch();
echo($row["TimeZone1"] . ", " . $row["TimeZone2"] . "<br/>");


echo ini_get('date.timezone');

 ?>