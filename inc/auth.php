<?php
session_start();
if(empty($_SESSION['user'])) 
{ 
	// If they are not, we redirect them to the login page. 
	header("Location: http://usuiusa.com/va/shipverify/login.php"); 
	 
	// Remember that this die statement is absolutely critical.  Without it, 
	// people can view your members-only content without logging in. 
	die("Redirecting to login.php"); 
}
?>