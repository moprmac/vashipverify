<?php
include("inc/auth.php");
include("inc/db.php");
include("classes/PlexInfo.class.php");

if(isset($_GET['plexID']) && !empty($_GET['plexID']) && preg_match('/^\d+$/',$_GET['plexID']))
{
	$plexObj = new PlexInfo($db);
	$imageSrc = "images/hollow.png";
	
	try
	{
		$plexInfo = $plexObj->GetStatusByID($_GET['plexID']);
		
		if ( $plexInfo["Locked"] == 1)
		{
			$imageSrc = "images/error.png";
			//header("Location: locked.php?plexID=" . $_GET['plexID']); 
			//die("Record locked.  Redirecting to locked page.");
		}
		elseif ( $plexInfo["Validated"] == 1)
		{
			header("Location: print_validation.php?plexID=" . $_GET['plexID']); 
			die("Redirecting to print validation page."); 
		}
		
			
	}
	catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}
		//header("Location: add_part.php?plexID=" . $_GET['plexID']); 
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
		// DO this when the document Loads
		$(document).ready(function()
		{
			$('#partInfo').focus();
			updatePartList();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#add_part_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					$('#partInfo').focus();
					e.preventDefault();
					searchPart();
				}
			});
			
			$("#btnSearchPart").focus(function () {
				  $('#partInfo').focus();
				  searchPart();
			});
			
			$('div#addConfirm').hide();
		});
		
		function searchPart()
		{
			$("#plexStatus").attr("src","images/hollow.png");
			
			var partInfo = $("input#partInfo").val();
			
			var doPost = true; 
			
			if (partInfo === null || partInfo <= 0 || partInfo == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexInfo.js_link.php',
					data: {action: 'SearchParts', plexID:<?=$_GET[plexID]?>,
							partInfo:partInfo},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned."); }
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error); }
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						if (data["Found"] == true)
						{
							$("#plexStatus").attr("src","images/valid.png");
							$('#partInfo').val('');
							$('#partInfo').focus();
						}
						else
						{
							$("div#addConfirm").dialog({
							   modal: true,
							   dialogClass: "no-close",
							   buttons : {
									"Confirm" : function() {
										$(this).dialog("close")
										addPart();
									},
									"Cancel" : function() {
									  $(this).dialog("close");
									  $("#plexStatus").attr("src","images/hollow.png");
									  $('#partInfo').val('');
									  $('#partInfo').focus();
									}
								  }
								});
						}
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
					data: {action: 'AddPart', plexID:<?=$_GET[plexID]?>,
							partInfo:partInfo},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (!data)
						{
							if (window.console) { console.log("No data returned."); }
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							throw "Error:  No data returned.";
						}
						
						if (data.error)
						{
							// handle the error
							if (window.console) { console.log(data.error); }
							$("#plexStatus").attr("src","images/warning.png");
							$('#partInfo').val('');
							alert(data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						if (window.console) { console.log("Response From Add Parts"); }
						// Now to check to see if the record got locked for some reason:
						checkStatus();
						
						
						$('#partInfo').val('');
						$('#partInfo').focus();
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
				data: {action: 'GetStatusByID', plexID:<?=$_GET[plexID]?>},
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
					
					if (data.Locked == 0)
					{
						$("#plexStatus").attr("src","images/valid.png");
					}
					else
					{
						$("#plexStatus").attr("src","images/error.png");
					}
					updatePartList();
				}
			});
		}
		
		function finalize()
		{
			if (window.console) { console.log("redirect to validation form."); }
			window.location.href = "print_validation.php?plexID=<?=$_GET[plexID]?>";
		}
		
		function updatePartList()
		{
			// Populate part table.
			$.ajax({ url: 'classes/PlexInfo.js_link.php',
				data: {action: 'GetParts', plexID:<?=$_GET[plexID]?>},
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
			var partQty = <?=($plexInfo["PartQty"])?>;
			if (data !== null)
			{
				for (i=0; i < data.length; i++)
				{
					var redLine = ((data[i]["Match_"] == 0 || data[i]["Duplicate"] == 1 || (i+1) > partQty) ? 1 : 0);
					content += "<tr id=\"" + data[i]["PlexPartRecordID"] + "\" class=\"error_" + redLine + "\">"
								+ "<td>" + (i+1)
								+ "</td><td>" + data[i]["PartNum"] 
								+ "</td><td>" + data[i]["PartSerialNum"] 
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
		
		function returnManage()
		{
			window.location.href = "manage_parts.php?plexRecNum=<?=$_GET[plexID]?>";
		}
		
	</script>
    </head>
	<body>
		<?php include("inc/header.php")?>
		
		<h3>Search Parts</h3>
	
		<div id="divAddPart" class="divAddPart" style="float:left;">
			<form id="add_part_form">
				<p>
				<label>Working with Plex Record: <?=($plexInfo["PlexSerialNum"])?></label>
				<br/>
				</p>
				<p id="auto">
				<label>Part: </label>
				<input id="partInfo" name="partInfo" type="text" />
				<br/>
				<input type="button" id="btnSearchPart" value="Search Part"/>
				<input type="button" value="Return to Manage Parts" onclick="returnManage();"/>
				</p>
			</form>
		</div>

		<div style="float:left; margin:2px;">
			<img id="plexStatus" name="plexStatus" src="<?=$imageSrc?>"/>
		</div>
		
		<div id="divPartInventory" class="divPartInventory" style="clear:both">
		<h3>100% Scan Part List</h3>
		<table id="partList" class="partList" border="1">
		<thead>
		<tr class="head">
		<td><b>PartCount</b></td>
		<td><b>PartNum</b></td>
		<td><b>PartSerialNum</b></td>
		</tr>
		</thead>
		<tbody />  
		</table>
		</div>
		
		<div id="addConfirm" class="modal">
		  <h3>Confirm</h3>
		  <p>
		  This part was not found.  Would you like to add it?
		  </p>
		</div>

<!--ed-->
</body>
	<?php include("inc/footer.php"); ?>
<!--/ed-->

	
</html>