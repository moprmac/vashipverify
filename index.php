<?php
include("inc/auth.php");

if ( $_SESSION['SupervisorAccess'] == true || $_SESSION['AdminAccess'] == true )
		$grantAccess = true;
	else
		$grantAccess = false;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>

<!--ed-->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
<!--/ed-->

    <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.2.min.js"></script>
	
    <script type="text/javascript">

	
		function goAddParts()
		{
			if (window.console)
			{
				console.log("redirect to scan record form.");
			}
			window.location.href = "scan_record.php";
		}
		
		function goVerify()
		{
			if (window.console)
			{
				console.log("redirect to scan record form.");
			}
			window.location.href = "shipping_record.php";
		}


<?php
	if ( $grantAccess)
	{
	?>
		$(document).ready(function()
		{
			$('#plexSN').focus();
		});
		
		// Javascript to process the supervisor record search:
		function managePlexRecord()
		{
			var doPost = true;
			var plexSN = $("input#plexSN").val();
			if (plexSN === null || plexSN <= 0 || plexSN == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				// need to verify this particular plexSN exists.  If so, redirect to manage page w/ plexID
				$.ajax({ url: 'classes/PlexInfo.js_link.php',
							data: {action: 'GetStatusBySN', plexSN:plexSN},
							dataType:'json',
							type: 'get',
							success: function(data)
							{
								// check for undefined or null
								if (!data)
								{
									if (window.console) { console.log("No data returned"); }
									throw "Error:  No Data Returned.";
								}
								
								if (data.error)
								{
									// handle the error
									if (window.console) { console.log(data.error); }
									Alert("Error:" + data.error.msg);
									throw data.error.msg;
								}
								
								// If error is thrown, script stops, otherwise, continue:
								if (window.console)
								{
									console.log("manage_parts.php?plexRecNum=" + data.PlexRecordID);
								}
								window.location.href = "manage_parts.php?plexRecNum=" + data.PlexRecordID;
							}
						});
			}
		}
	
	<?
	}
?>
	</script>
	<?php include("inc/header.php")?>
    </head>
	<body>
		
		<div id="divSelection" class="divSelection">
		<br/>
		<input type="button" value="Enter 100% Scan" onclick="goAddParts();"/>
		<input type="button" value="Enter Verification Scan" onclick="goVerify();"/>
		</div>
<?php
	
	
	if ( $grantAccess )
	{
	?>
		<br/>
		<label>Plex Serial Number: </label>
		<input id="plexSN" name="plexSN" type="text" />
		<br/>
		<input type="button" value="Manage Record" onclick="managePlexRecord();"/>
		<br/><br/>

	<?
	}
?>

	</body>

<!--ed-->
	<?php include("inc/footer.php")?>
<!--/ed-->

</html>