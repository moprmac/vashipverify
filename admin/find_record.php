<?php
include("../inc/auth.php");
include("../inc/db.php");


if ( $_SESSION['AdminAccess'] == true )
	$grantAccess = true;
else
	$grantAccess = false;
if ( !$grantAccess )
{
	header("Location: ../index.php"); 
	die("Redirecting to user index");
}


	?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>
	<link rel="stylesheet" href="../css/style.css" type="text/css" media="screen"/>

<!--ed-->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
<!--/ed-->


    <script type="text/javascript" src="../js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui-1.10.2.min.js"></script>
    <script type="text/javascript">
		$(document).ready(function()
		{
			$('#plexSN').focus();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#select_plex_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					findPlexRecord();
				}
			});
		});

		// Javascript to process the supervisor record search:
		function findPlexRecord()
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
				$.ajax({ url: '../classes/PlexInfo.js_link.php',
							data: {action: 'GetStatusBySN', plexSN:plexSN},
							dataType:'json',
							type: 'get',
							success: function(data)
							{
								// check for undefined or null
								if (!data)
								{
									if (window.console) { console.log("No data returned"); }
									alert("Error:  No data returned.");
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
									console.log("delete_record.php?plexID=" + data.PlexRecordID);
								}
								window.location.href = "delete_record.php?plexID=" + data.PlexRecordID;
							}
						});
			}
		}
	</script>
    </head>
	<body>
		<?php include("../inc/header.php")?>
		<div id="selectPlexRecord">
			<form id="select_plex_form">
				<p>
				<label>Plex Serial Number: </label>
				<input id="plexSN" name="plexSN" type="text" />
				<br/>
				<input type="button" value="Find and Dete Record" onclick="findPlexRecord();"/>
				<br/>
				</p>
			</form>
		</div>
	</body>

<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->

</html>