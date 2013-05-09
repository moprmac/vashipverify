<?php
include("inc/auth.php");
include("inc/db.php");


$result = mysql_query("SELECT * FROM 'UserDB' WHERE email IS NOT NULL AND TRIM(email) <>");

while($row = mysql_fetch_array($result))
{
    $addresses[] = $row['address'];
}
$to = implode(", ", $addresses);

$subject = "Test mail";
$message = "Hello! This is a simple email message.";
$from = "qa@usuiusa.com";
$headers = "From:" . $from;
mail($to,$subject,$message,$headers);
?>