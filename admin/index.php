<?php
include("../inc/auth.php");

if ( $_SESSION['AdminAccess'] == true )
	$grantAccess = true;
else
	$grantAccess = false;

if ( !$grantAccess )
{
	header("Location: ../index.php"); 
	die("Redirecting to index");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>

<!--ed-->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
<!--/ed-->

	<link rel="stylesheet" href="../css/style.css" type="text/css" media="screen">
    <script type="text/javascript" src="../js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui-1.10.2.min.js"></script>
    <script type="text/javascript">
	</script>
	<?php include("../inc/header.php")?>
    </head>
	<body>
		<br/><br/>
		<a href="add_user.php">Add User</a><br/>
		<a href="users.php">Manage User</a><br/>
		<a href="parts.php">Manage Parts</a><br/>
		<a href="find_record.php">Find and Delete a Plex Record</a><br/>
		<br/><br/>
	</body>
<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->
</html>
