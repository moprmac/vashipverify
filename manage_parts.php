<?php
include("inc/auth.php");
include("inc/db.php");
include("classes/PlexInfo.class.php");


if(isset($_GET['plexRecNum']) && !empty($_GET['plexRecNum']) && preg_match('/^\d+$/',$_GET['plexRecNum']))
{
	if ( $_SESSION['SupervisorAccess'] == true || $_SESSION['AdminAccess'] == true )
		$grantAccess = true;
	else
		$grantAccess = false;
	if ( !$grantAccess )
	{
		header("Location: add_part.php?plexID=" . $_GET['plexRecNum']); 
		die("Redirecting to user add parts");
	}
	$plexStatus = "";
	$plexObj = new PlexInfo($db);
	
	try
	{
		$plexInfo = $plexObj->GetStatusByID($_GET['plexRecNum']);
		$plexShippingInfo = $plexObj->GetShippingInfo($_GET['plexRecNum']);
		
		if ( $plexInfo["Locked"] == 1)
		{
			$plexStatus = "Locked";
		}
		else
		{
			$plexStatus = "Not Locked";
		}
		
		/*
		print_r($plexInfo);
		foreach ($plexInfo as $key=>$value)
		{
			echo($key . ' == ' . $value . '<br/>');
		}
		*/
	}
	catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}

	?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">

<!--ed-->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
<!--/ed-->


    <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.10.2.min.js"></script>
    <script type="text/javascript">
		var valToUpdate = "";
		
		$(document).ready(function()
		{
			$('div#pop-up').hide();
			$('div#overrideConfirm').hide();
			updatePartList();
			updateShippingPartList();
			updateErrorList();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#add_part_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addPart();
				}
			});
			
			$('#replace_part_form').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					replacePart();
				}
			});
			
		});
	
		function drawTable(data)
		{
			var content = "";
			var partQty = <?=($plexInfo["PartQty"])?>;
			if (data !== null)
			{
				for (i=0; i < data.length; i++)
				{
					var redLine = ((data[i]["Match_"] == 0 || data[i]["Duplicate"] == 1 || (i+1) > partQty) ? 1 : 0);
					content += "<tr id=\"" + data[i]["PlexPartRecordID"] + "\" class=\"error_" + redLine + "\">"
								+ "<td>" + (i+1)
								+ "</td><td>" + data[i]["PlexPartRecordID"] 
								+ "</td><td>" + data[i]["PartNum"] 
								+ "</td><td>" + data[i]["PartSerialNum"] 
								+ "</td><td>" + data[i]["PartID"] 
								+ "</td><td>" + data[i]["Match_"] 
								+ "</td><td>" + data[i]["Duplicate"] 
								+ "</td><td>" + data[i]["UserName"]
								<?php
								if ( $plexInfo['Verified'] != 1 )
								{
								?>
								+ "</td><td><img src=\"images/remove.png\" onclick=\"removePart(" + data[i]["PlexPartRecordID"] +");\""
								<?php
								}
								?>
								+ "</td><td><img src=\"images/refresh.png\" onclick=\"replacePart(" + data[i]["PlexPartRecordID"] +", '" + data[i]["PartSerialNum"] + "');\""
								+ "</td></tr>";
				}
				$('#partList tbody').html(content);
			}
			else
			{
				// no data
				$('#partList tbody').html(content);
			}
		}
		
		function drawShippingTable(data)
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
								+ "</td><td>" + data[i]["UserName"]
								+ "</td><td><img src=\"images/remove.png\" onclick=\"removeShippingPart(" + data[i]["ShippingRecordPartID"] +");\""
								+ "</td></tr>";
				}
				$('#shipPartList tbody').html(content);
			}
			else
			{
				// no data
				$('#shipPartList tbody').html(content);
			}
		}
		
		function drawErrorTable(data)
		{
			var content = "";
			if (data !== null)
			{
				for (i=0; i < data.length; i++)
				{
					content += "<tr>"
								+ "<td>" + data[i]["PartInfo"]
								+ "</td><td>" + data[i]["ErrorReason"] 
								+ "</td><td>" + data[i]["UserName"] 
								+ "</td><td>" + data[i]["ScanTime"]
								+ "</td></tr>";
				}
				$('#errorList tbody').html(content);
			}
			else
			{
				// no data
				$('#errorList tbody').html(content);
			}
		}
		
		function editAttr(attributeField)
		{
			/*
			$(attributeField).removeAttr("readonly");
			valToUpdate = attributeField;
			$("div#pop-up").dialog({dialogClass: "no-close", modal:true});
			*/
			if (window.console) { console.log("This feature is disabled.  If the record is invalid, user should delete and rescan."); }
		}
		
		function cancelUpdate()
		{
			$(valToUpdate).attr("readonly","true");
			valToUpdate = '';
			$( "div#pop-up" ).dialog( "close" );
			$('input#attrUpdate').val('');
		}
		
		function confirmUpdate()
		{
			if (window.console) { console.log("kickoff the ajax to update the proper attibute."); }
			
			// migrate this part to the OnSuccess
			$(valToUpdate).attr("readonly","true");
			$(valToUpdate).val($('input#attrUpdate').val());
			$( "div#pop-up" ).dialog( "close" );
			$('input#attrUpdate').val('');
			valToUpdate = '';
		}
		
		function removePart(partRecordID)
		{
			if (window.console) { console.log("Attempt to remove part:" + partRecordID); }
			var doPost = true;
			
			if (partRecordID === null || partRecordID <= 0 || partRecordID == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexInfo.js_link.php',
					data: {action: 'RemovePart', plexID:<?=$_GET[plexRecNum]?>,
							partRecNum:partRecordID},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned."); }
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error); }
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						if (window.console) { console.log("Response From Remove Part"); }
						updatePartList();
						checkStatus();
						
					}
				});
			}
			else
			{
				if (window.console) { console.log("Data invalid, do not post"); }
			}
		}
		
		function removeShippingPart(shippingPartRecordID)
		{
			if (window.console) { console.log("Attempt to remove part:" + shippingPartRecordID); }
			var doPost = true;
			
			if (shippingPartRecordID === null || shippingPartRecordID <= 0 || shippingPartRecordID == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
					data: {action: 'RemovePart', shippingID:<?=(is_null($plexShippingInfo['ShippingRecordID']) ? -1 : $plexShippingInfo['ShippingRecordID'])?>,
							shippingRecordPartID:shippingPartRecordID},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned."); }
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error); }
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						if (window.console) { console.log("Response From Shipping Remove Part"); }
						updateShippingPartList();
						checkStatus();
						
					}
				});
			}
			else
			{
				if (window.console) { console.log("Data invalid, do not post"); }
			}
		}
		
		function addPart()
		{
			if (window.console) { console.log("Attempt to add a part."); }
			var partInfo = $("input#partInfo").val();
			
			var doPost = true; 
			
			if (partInfo === null || partInfo <= 0 || partInfo == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexInfo.js_link.php',
					data: {action: 'AddPart', plexID:<?=$_GET[plexRecNum]?>,
							partInfo:partInfo},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned."); }
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error); }
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						
						// If error is thrown, script stops, otherwise, continue:
						if (window.console) { console.log("Response From Add Parts"); }
						updatePartList();
						checkStatus();
						
						$('#partInfo').val('');
						
					}
				});
			}
			else
			{
				if (window.console) { console.log("Data invalid, do not post"); }
			}
		}
		
		function checkStatus()
		{
			$.ajax({ url: 'classes/PlexInfo.js_link.php',
				data: {action: 'GetStatusByID', plexID:<?=$_GET[plexRecNum]?>},
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
						$("#plexStatus").html("Not Locked");
					}
					else
					{
						$("#plexStatus").html("Locked")
					}
				}
			});
			
			$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
				data: {action: 'GetShippingStatusByID', shippingID:<?=(is_null($plexShippingInfo['ShippingRecordID']) ? -1 : $plexShippingInfo['ShippingRecordID'])?>},
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
						$("#shippingStatus").html("Not Locked");
					}
					else
					{
						$("#shippingStatus").html("Locked")
					}
				}
			});
			
			updateErrorList();
		}
		
		function updatePartList()
		{
			// Populate part table.
			$.ajax({ url: 'classes/PlexInfo.js_link.php',
				data: {action: 'GetParts', plexID:<?=$_GET[plexRecNum]?>},
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
		
		function updateShippingPartList()
		{
			// Populate part table.
			$.ajax({ url: 'classes/PlexShippingInfo.js_link.php',
				data: {action: 'GetParts', shippingID:<?=(is_null($plexShippingInfo['ShippingRecordID']) ? -1 : $plexShippingInfo['ShippingRecordID'])?>},
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
					drawShippingTable(data);
				}
			});
		}
		
		function updateErrorList()
		{
			// Populate part table.
			$.ajax({ url: 'classes/PlexInfo.js_link.php',
				data: {action: 'GetInsertErrors', plexID:<?=$_GET[plexRecNum]?>},
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
					drawErrorTable(data);
				}
			});
		}
		
		function golabel(labelType)
		{
			switch (labelType)
			{
				case "shipping":
					window.location.href = "print_ship_validation.php?shippingID=<?=(is_null($plexShippingInfo['ShippingRecordID']) ? -1 : $plexShippingInfo['ShippingRecordID'])?>";
					break;
				case "100Scan":
					window.location.href = "print_validation.php?plexID=<?=$_GET[plexRecNum]?>";
					break;
			}
		}
		
		function unlockRecord()
		{
			$("div#overrideConfirm").dialog({
			   modal: true,
			   dialogClass: "no-close",
			   buttons : {
					"Confirm" : function() {
						$.ajax({ url: 'classes/PlexInfo.js_link.php',
							data: {action: 'UnlockRecord', plexID:<?=$_GET[plexRecNum]?>},
							dataType:'json',
							type: 'get',
							success: function(data)
							{
								if (data.error)
								{
									// handle the error
									if (window.console) { console.log(data.error);}
									alert("Error:" + data.error.msg);
									$( "div#overrideConfirm" ).dialog( "close" );
									throw data.error.msg;
								}
								// If error is thrown, script stops, otherwise, continue:
								$( "div#overrideConfirm" ).dialog( "close" );
								checkStatus();
							}
						});           
					},
					"Cancel" : function() {
					  $(this).dialog("close");
					}
				  }
				});
		}
		
		function GoSearchPart()
		{
			window.location.href = "search_part.php?plexID=<?=$_GET[plexRecNum]?>";
		}
		
		function replacePart(partRecordID, partSerialNum)
		{
			$("#oldPartInfo").html(partSerialNum);
			
			$("div#replacePart").dialog({
			   modal: true,
			   dialogClass: "no-close",
			   buttons : {
					"Confirm" : function() {
						
						var partInfo = $("input#replacePartInfo").val();
						var replaceReason = $("input#replaceReason").val();
						
						var doPost = true; 
						
						if (partInfo === null || partInfo <= 0 || partInfo == "")
						{
							doPost = false;
						}
						
						if (doPost)
						{
							$.ajax({ url: 'classes/PlexInfo.js_link.php',
								data: {action: 'ReplacePart', plexID:<?=$_GET[plexRecNum]?>, oldPartID:partRecordID, newPartInfo:partInfo, replaceReason:replaceReason},
								dataType:'json',
								type: 'get',
								success: function(data)
								{
								
									if (!data)
									{
										if (window.console) { console.log("No data returned.");}
										alert("Error:  No data was returned.  Check with your administrator.");
										$( "div#replacePart" ).dialog( "close" );
										throw "Error:  No data returned.";
									}
									
									if (data.error)
									{
										// handle the error
										if (window.console) { console.log(data.error);}
										alert("Error:" + data.error.msg);
										$( "div#replacePart" ).dialog( "close" );
										throw data.error.msg;
									}
									
									if (data["ReplaceStatus"])
									{
										alert("Part successfully replaced.");
										// If error is thrown, script stops, otherwise, continue:
										$( "div#replacePart" ).dialog( "close" );
										$("input#replacePartInfo").val("");
										$("input#replaceReason").val("");
										updatePartList();
										checkStatus();
									}
									else
									{
										alert("Error:  Part was not replaced.");
										// If error is thrown, script stops, otherwise, continue:
										$( "div#replacePart" ).dialog( "close" );
										$("input#replacePartInfo").val("");
										$("input#replaceReason").val("");
										updatePartList();
										checkStatus();
									}
								}
							});
						}
						else
						{
							alert("Error:  You didn't fill in all the required fields.");
						}
					},
					"Cancel" : function() {
					  $(this).dialog("close");
					  $("input#replacePartInfo").val("");
					  $("input#replaceReason").val("");
					}
				  }
				});
		}
		
	</script>
    </head>
	<body>
		<?php include("inc/header.php")?>
		<div id="divInternalLabel" class="divInternalLabel">
			<h3>Internal Label Info</h3>
			<p>
				<label>Plex Serial Number: </label>
				<input id="plexSN" name="plexSN" type="text" readonly="true" value="<?=($plexInfo["PlexSerialNum"])?>"/>
				<br/>
				<label>Part Number: </label>
				<input id="partNum" name="partNum" type="text" readonly="true" value="<?=($plexInfo["PartNum"])?>"/>
				<!--<img src="images/edit.png" onclick="editAttr('input#partNum');">-->
				<br/>
				<label>Part Quantity: </label>
				<input id="partQty" name="partQty" type="text" readonly="true" value="<?=($plexInfo["PartQty"])?>"/>
				<!--<img src="images/edit.png" onclick="editAttr('input#partQty');">-->
			</p>
			<p>
				<input type="button" value="Print 100% Scan Label" onclick="golabel('100Scan');"/>
			</p>
		</div>
		
		<div id="divShippingLabel" class="divShippingLabel">
			<h3>Shipping Info</h3>
			<p>
				<label>Shipping Part Number: </label>
				<input id="shipPartNum" name="partNum" type="text" readonly="true" value="<?=($plexShippingInfo["PartNum"])?>"/>
				<!--<img src="images/edit.png" onclick="editAttr('input#shipPartNum');">-->
				<br/>
				<label>Shipping Part Quantity: </label>
				<input id="shipPartQty" name="partQty" type="text" readonly="true" value="<?=($plexShippingInfo["PartQty"])?>"/>
				<!--<img src="images/edit.png" onclick="editAttr('input#shipPartQty');">-->
			</p>
			<p>
				<input type="button" value="Print Shipping Label" onclick="golabel('shipping');"/>
			</p>
		</div>
		
		<div id="divPartStatus" class="divPartStatus">
		<h3>Status of this Record:</h3>
			<label id="plexStatus" name="plexStatus"><?=$plexStatus?></label>
		</div>
		
		<div id="divPartInventory" class="divPartInventory">
		<h3>100% Scan Part List</h3>
		<p><input type="button" value="Search for a Part" onclick="GoSearchPart();"/></p>
		<table id="partList" class="partList" border="1">
		<thead>
		<tr class="head">
		<td><b>PartCount</b></td>
		<td><b>PlexPartRecordID</b></td>
		<!--<td><b>PlexRecord</b></td>
		<td><b>ScanUser</b></td>
		<td><b>ScanTime</b></td>-->
		<td><b>PartNum</b></td>
		<td><b>PartSerialNum</b></td>
		<td><b>PartID</b></td>
		<td><b>Match</b></td>
		<td><b>Duplicate</b></td>
		<td><b>Scan User</b></td>
		<?php
		if ( $plexInfo['Verified'] != 1 )
		{
		?>
		<td><b>Remove</b></td>
		<?php
		}
		?>
		<td><b>Replace</b></td>
		</tr>
		</thead>
		<tbody />  
		</table>
		</div>
		
		<div id="divAddPart" class="divAddPart">
		<h3>Add Additional Parts</h3>
			<form id="add_part_form">
				<p>
				<label>Working with Plex Record: <?=($plexInfo["PlexSerialNum"])?></label>
				<br/>
				</p>
				<p id="auto">
				<label>Part: </label>
				<input id="partInfo" name="partInfo" type="text" />
				<!-- don't think I need this, as it's set in the javascript-->
				<input type="hidden" id="plexRecNum" name="plexRecNum" value="<?=$_GET['plexRecNum']?>"/>
				<br/>
				<input type="button" value="Add Part" onclick="addPart();"/>
				</p>
			</form>
		</div>
		
		<div id="divPartStatus" class="divPartStatus">
		<h3>Status of this Shipping Record:</h3>
			<label id="shippingStatus" name="shippingStatus"><?=($plexShippingInfo[Locked])==1 ? "Locked" : "Unlocked"?></label>
		</div>
		
		<div id="divScanRecord" class="divPartInventory">
		<h3>Verification Scan Part List</h3>
		<table id="shipPartList" class="shipPartList" border="1">
		<thead>
		<tr class="head">
		<td><b>PartCount</b></td>
		<td><b>ShippingRecordPartID</b></td>
		<td><b>PartNum</b></td>
		<td><b>PartSerialNum</b></td>
		<td><b>PartID</b></td>
		<td><b>Match</b></td>
		<td><b>Duplicate</b></td>
		<td><b>Valid</b></td>
		<td><b>Scan User</b></td>
		<td><b>Remove</b></td>
		</tr>
		</thead>
		<tbody />  
		</table>
		</div>
		
		<div id="divErrorRecord" class="divErrorRecord">
		<h3>Bad Part List</h3>
		<table id="errorList" class="errorList" border="1">
		<thead>
		<tr class="head">
		<td><b>Part Info Attempted</b></td>
		<td><b>Error Reason</b></td>
		<td><b>Scan User</b></td>
		<td><b>Scan Time</b></td>
		</tr>
		</thead>
		<tbody />  
		</table>
		</div>
		
		<div id="divManualUnlock">
			<input type="button" class="modalInput" id="btnUnlockRecord" value="Unlock Record" onclick="unlockRecord();"/>
		</div>
		
		<div id="overrideConfirm" class="modal">
		  <h3>Confirm</h3>
		  <p>
		  Are you absolutely sure you want to manually override the locks on this record?
		  </p>
		</div>

		
		<div id="pop-up" class="modal">
		  <h3>Attribute Update Pop-up</h3>
		  <p>
			Update Attribute:
		  </p>
		  <p>
		  <label>New Value:</label><input type="text" name="attrUpdate" id="attrUpdate"/><br/>
		  </p>
		  <input type="button" id="closePopup" value="Cancel" onclick="cancelUpdate();"/>
		  <input type="button" id="closePopup" value="Update" onclick="confirmUpdate();"/>
		</div>
		
		<div id="replacePart" class="modal">
			<h3>Replace Part</h3>
			<p>
			Replacing Part: <label id="oldPartInfo"></label>
			</p>
			<p>
				<label>New Part: </label><input id="replacePartInfo" name="replacePartInfo" type="text" /><br/>
				<label>Reason:</label><input id="replaceReason" name="replaceReason" type="text" />
			</p>
		</div>

	<?
}
else
{
	header("Location: index.php"); 
	die("Redirecting to initial scan");
}
?>	
</body>	
	<?php include("inc/footer.php")?>
</html>

		


