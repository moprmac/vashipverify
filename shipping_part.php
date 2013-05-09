<?php
include("inc/auth.php");
include("inc/db.php");
include("classes/PlexShippingInfo.class.php");

if(isset($_GET['shippingID']) && !empty($_GET['shippingID']) && preg_match('/^\d+$/',$_GET['shippingID']))
{
	$plexShipObj = new PlexShipInfo($db);
	$imageSrc = "";
	
	try
	{
		$plexShippingInfo = $plexShipObj->GetShippingStatusByID($_GET['shippingID']);
		
		if ( $plexShippingInfo["Locked"] == 1)
		{
			$imageSrc = "images/error.png";
			//header("Location: locked.php?shippingID=" . $_GET['shippingID']); 
			//die("Record locked.  Redirecting to locked page.");
		}
		elseif ( $plexShippingInfo["Validated"] == 1)
		{
			header("Location: print_ship_validation.php?shippingID=" . $_GET['shippingID']); 
			die("Redirecting to print validation page."); 
		}
		
			
	}
	catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}
		//header("Location: add_part.php?shippingID=" . $_GET['shippingID']); 
		//die("Redirecting to user add parts"); 
}
else
{
	// Bad data input.
	die("Error:  Bad input for Plex ID.");
}

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

	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
    <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.2.min.js"></script>
    <script type="text/javascript">
		$(document).ready(function()
		{
			updatePartList();
			
			$('#partInfo').focus();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#add_part_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addPart();
				}
			});
			
			$("#btnAddPart").focus(function () {
				  addPart();
				  $('#partInfo').focus();
			});
		});
		
		function addPart()
		{
			if (window.console) { console.log("Attempt to add a part.");}
			var partInfo = $("input#partInfo").val();
			
			var doPost = true; 
			
			if (partInfo === null || partInfo <= 0 || partInfo == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
					data: {action: 'AddPart', shippingID:<?=$_GET[shippingID]?>,
							partInfo:partInfo},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned.");}
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error);}
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						if (window.console) { console.log("Response From Add Parts");}
						// Now to check to see if the record got locked for some reason:
						$('#partInfo').val('');
						$('#partInfo').focus();
						if (data['ValidPart'] === true)
						{
							$("#plexStatus").attr("src","images/valid.png");
						}
						else
						{
							$("#plexStatus").attr("src","images/error.png");
						}
						//console.log(data);
						
							
						//checkStatus();
						updatePartList();
						if (window.console) { console.log(data);}
					}
				});
			}
			else
			{
				$('#partInfo').val('');
				if (window.console) { console.log("Data invalid, do not post");}
			}
		}
		
		function checkStatus()
		{
			$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
				data: {action: 'GetShippingStatusByID', shippingID:<?=$_GET[shippingID]?>},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (!data)
					{
						if (window.console) { console.log("No data returned.");}
						throw "Error:  No data returned.";
					}
					
					if (data.error)
					{
						// handle the error
						if (window.console) { console.log(data.error);}
						alert(data.error.msg);
						throw data.error.msg;
					}
					
					if (data.Locked == 0)
					{
						$("#plexStatus").attr("src","images/valid.png");
					}
					else
					{
						$("#plexStatus").attr("src","images/error.png");
					}
				}
			});
		}
		
		function finalize()
		{
			if (window.console) { console.log("redirect to validation form.");}
			//window.location.href = "print_ship_validation.php?shippingID=<?=$_GET[shippingID]?>";
		}
		
		function updatePartList()
		{
			// Populate part table.
			$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
				data: {action: 'GetParts', shippingID:<?=$_GET[shippingID]?>},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						if (window.console) { console.log(data.error);}
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					// If error is thrown, script stops, otherwise, continue:
					if (window.console) { console.log("Got part list");}
					drawTable(data);
				}
			});
		}
		
		function drawTable(data)
		{
			var content = "";
			if (data !== null)
			{
				for (i=0; i < data.length; i++)
				{
					var redLine = ((data[i]["Match_"] == 0 || data[i]["Duplicate"] == 1 || data[i]["Valid"] == 0) ? 1 : 0);
					content += "<tr id=\"" + data[i]["ShippingRecordPartID"] + "\" class=\"error_" + redLine + "\">"
								+ "<td>" + (i+1)
								+ "</td><td>" + data[i]["ShippingRecordPartID"] 
								+ "</td><td>" + data[i]["PartNum"] 
								+ "</td><td>" + data[i]["PartSerialNum"] 
								+ "</td><td>" + data[i]["PartID"] 
								+ "</td><td>" + data[i]["Match_"] 
								+ "</td><td>" + data[i]["Duplicate"] 
								+ "</td><td>" + data[i]["Valid"] 
								+ "</tr>";
				}
				$('#partList tbody').html(content);
			}
			else
			{
				// no data
				$('#operationList tbody').html(content);
				// since no data, disable delete button
				$("#delete").attr("disabled", "disabled");
			}
		}
		
		function doneVerification()
		{
			$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
				data: {action: 'GetPartQty', shippingID:<?=$_GET[shippingID]?>},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						if (window.console) { console.log(data.error);}
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					// If error is thrown, script stops, otherwise, continue:
					if (data['NumParts'] < 5)
					{
						alert("Error:  You have not scanned enough parts yet.");
					}
					else
					{
						window.location.href = "print_ship_validation.php?shippingID=<?=$_GET[shippingID]?>";
					}
				}
			});
		}
		
		
		
	</script>
    </head>
	<body>
		<?php include("inc/header.php")?>
		<h3>Verification Scan - Add Parts</h3>
		<div id="divAddPart" class="divAddPart" style="float:left;">

			<form id="add_part_form">
				<p>
				<label>Working with Shipping Record: <?=$plexShippingInfo["ShippingRecordID"]?></label>
				<br/>
				</p>
				<p id="auto">
				<label>Part: </label>
				<input id="partInfo" name="partInfo" type="text" />
				<br/>
				<input type="button" id="btnAddPart" value="Add Part" onclick="addPart();"/>
				<input type="button" value="Done Verification" onclick="doneVerification();"/>
				</p>
			</form>
		</div>

		<div style="float:left; margin:2px;">
			<img id="plexStatus" name="plexStatus" src="<?=$imageSrc?>"/>
		</div>
		
		<div id="divPartInventory" class="divPartInventory" style="clear:both">
		<h3>Part List</h3>
		<table id="partList" class="partList" border="1">
		<thead>
		<tr class="head">
		<td><b>PartCount</b></td>
		<td><b>ShippingRecordPartID</b></td>
		<!--<td><b>PlexRecord</b></td>
		<td><b>ScanUser</b></td>
		<td><b>ScanTime</b></td>-->
		<td><b>PartNum</b></td>
		<td><b>PartSerialNum</b></td>
		<td><b>PartID</b></td>
		<td><b>Match</b></td>
		<td><b>Duplicate</b></td>
		<td><b>Valid</b></td>
		</tr>
		</thead>
		<tbody />  
		</table>
		</div>

</body>

	<?php include("inc/footer.php")?>

</html>