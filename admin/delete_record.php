<?php
include("../inc/auth.php");
include("../inc/db.php");
include("../classes/PlexInfo.class.php");


if(isset($_GET['plexID']) && !empty($_GET['plexID']) && preg_match('/^\d+$/',$_GET['plexID']))
{
	if ( $_SESSION['AdminAccess'] == true )
		$grantAccess = true;
	else
		$grantAccess = false;
	if ( !$grantAccess )
	{
		header("Location: ../index.php"); 
		die("Redirecting to user index");
	}
	
	$plexStatus = "";
	$plexObj = new PlexInfo($db);
	try
	{
		$plexInfo = $plexObj->GetStatusByID($_GET['plexID']);
		$plexShippingInfo = $plexObj->GetShippingInfo($_GET['plexID']);
		
		if ( $plexInfo["Locked"] == 1)
		{
			$plexStatus = "Locked";
		}
		else
		{
			$plexStatus = "Not Locked";
		}
	}
	catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}
}
else
{
	throw new Exception("Error:  Invalid ID specified.");
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
			$('div#deleteConfirm').hide();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#delete_plex_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					deleteRecord();
				}
			});
			
		});
		
		function deleteRecord()
		{
			$("div#deleteConfirm").dialog({
			   modal: true,
			   dialogClass: "no-close",
			   buttons : {
					"Confirm" : function() {
						$.ajax({ url: '../classes/PlexInfo.js_link.php',
							data: {action: 'DeleteRecord', plexID:<?=$_GET[plexID]?>},
							dataType:'json',
							type: 'get',
							success: function(data)
							{
								if (data.error)
								{
									// handle the error
									if (window.console) { console.log(data.error);}
									alert("Error:" + data.error.msg);
									$( "div#deleteConfirm" ).dialog( "close" );
									throw data.error.msg;
								}
								// If error is thrown, script stops, otherwise, continue:
								$( "div#deleteConfirm" ).dialog( "close" );
								window.location.href = "index.php";
							}
						});           
					},
					"Cancel" : function() {
					  $(this).dialog("close");
					}
				  }
				});
		}
</script>
    </head>
	<body>
		<?php include("../inc/header.php")?>
		<div id="divInternalLabel" class="divInternalLabel">
			<h3>Internal Label Info</h3>
			<p>
				<label>Plex Serial Number: </label>
				<input id="plexSN" name="plexSN" type="text" readonly="true" value="<?=($plexInfo["PlexSerialNum"])?>"/>
				<br/>
				<label>Part Number: </label>
				<input id="partNum" name="partNum" type="text" readonly="true" value="<?=($plexInfo["PartNum"])?>"/>
				<br/>
				<label>Part Quantity: </label>
				<input id="partQty" name="partQty" type="text" readonly="true" value="<?=($plexInfo["PartQty"])?>"/>
			</p>
		</div>
		
		<div id="divShippingLabel" class="divShippingLabel">
			<h3>Shipping Info</h3>
			<p>
				<label>Shipping Part Number: </label>
				<input id="shipPartNum" name="partNum" type="text" readonly="true" value="<?=($plexShippingInfo["PartNum"])?>"/>
				<br/>
				<label>Shipping Part Quantity: </label>
				<input id="shipPartQty" name="partQty" type="text" readonly="true" value="<?=($plexShippingInfo["PartQty"])?>"/>
			</p>
		</div>
		
		<div id="divPartStatus" class="divPartStatus">
		<h3>Status of this Record:</h3>
			<label id="plexStatus" name="plexStatus"><?=$plexStatus?></label>
		</div>
		
		<div id="divPartStatus" class="divPartStatus">
		<h3>Status of this Shipping Record:</h3>
			<label id="shippingStatus" name="shippingStatus"><?=($plexShippingInfo[Locked])==1 ? "Locked" : "Unlocked"?></label>
		</div>
		
		<div id="divDeleteRecord">
			<form id="delete_plex_form">
				<p>
					<input type="button" class="modalInput" id="btnDeleteRecord" value="Delete this entire Plex Record" onclick="deleteRecord();"/>
				</p>
			</form>
		</div>
		
		<div id="deleteConfirm" class="modal">
		  <h3>Confirm</h3>
		  <p>
		  Are you absolutely sure you want to delete this record?
		  </p>
		</div>

		</body>

<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->

</html>