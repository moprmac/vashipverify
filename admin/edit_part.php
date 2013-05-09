<?php
include("../inc/auth.php");
include("../inc/db.php");
include("../classes/PartInfo.class.php");


if ( $_SESSION['AdminAccess'] == true )
	$grantAccess = true;
else
	$grantAccess = false;

if ( !$grantAccess )
{
	header("Location: ../index.php"); 
	die("Redirecting to index");
}
	
$partObj = new PartInfo($db);
try
{
	$partInfo = $partObj->GetPartByID($_GET['partID']);
}
catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
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
	
		$(document).ready(function()
		{
			$('div#pop-up').hide();
			updatePartList();
			updateAlternateList();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			/*
			$('#add_part_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addPart();
				}
			});*/
			
			$("input#partFilter").keyup(function(e) {
				updatePartList();
			});
			
		});
		
		function editRegex()
		{
			$("div#pop-up").dialog({dialogClass: "no-close", modal:true});
			updateExpressionList();
		}
	
		function updateExpressionList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/PartInfo.js_link.php',
				data: {action: 'GetExpressions', regexFilter:$('#regexFilter').val()},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						//console.log(data.error);
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					// If error is thrown, script stops, otherwise, continue:
					//console.log("Got part list");
					drawRegexTable(data);
				}
			});
		}
		
		function drawRegexTable(data)
		{
			var content = "";
			if (data !== null)
			{
				for (i=0; i < data.length; i++)
				{
					content += "<tr>"
								+ "</td><td>" + data[i]["RegularExpression"] 
								+ "</td><td><img src=\"../images/valid.png\" width=\"20\" height=\"20\" onclick=\"updateExpression(" + data[i]["RegexID"] +");\""
								+ "</td></tr>";
				}
				$('#expressionList tbody').html(content);
			}
			else
			{
				// no data
				$('#expressionList tbody').html(content);
			}
		}
		
		function updateExpression(regexID)
		{
			var doPost = true;
			
			var partInfo = $('input#partNum').val();
			
			if (regexID === null || regexID <= 0 || regexID == "")
			{
				doPost = false;
			}
			//console.log("update regex.");
			
			if (doPost)
			{
				// Populate part table.
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'UpdateRegex', partID:<?=$_GET[partID]?>, regexID:regexID},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						//console.log("update successful");
						
						$('#partRegex').val(data['NewRegex']);
						$( "div#pop-up" ).dialog( "close" );
						$('input#regexFilter').val('');
						
					}
				});
			}
		}
		
		function cancelRegexUpdate()
		{
			$( "div#pop-up" ).dialog( "close" );
			$('input#regexFilter').val('');
		}
		
		function toggleActive()
		{
			if ($("#active").is(':checked'))
			{
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'ActivatePart', partID:<?=$_GET[partID]?>},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						//console.log("update successful");
					}
				});
			}
			else
			{
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'RetirePart', partID:<?=$_GET[partID]?>},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						//console.log("update successful");
					}
				});
			}
		}
		
		function updatePartList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/PartInfo.js_link.php',
				data: {action: 'GetParts', partNum:$('input#partFilter').val()},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						//console.log(data.error);
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					$('#allParts').find('option').remove();
					// If error is thrown, script stops, otherwise, continue:
					//console.log("Got part list");
					for (var i=0; i < data.length; i++)
					{
						//Creates the item
						$("#allParts").append('<option value="' + data[i]['PartID'] + '">' + data[i]['PartNum'] + '</option>');
					}
				}
			});
		}
		
		function updateAlternateList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/PartInfo.js_link.php',
				data: {action: 'GetAlternateParts', partID:<?=$_GET[partID]?>},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						//console.log(data.error);
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					// If error is thrown, script stops, otherwise, continue:
					//console.log("Got part list");
					$('#selectedParts').find('option').remove();
					for (var i=0; i < data.length; i++)
					{
						
						//Creates the item
						$("#selectedParts").append('<option value="' + data[i]['PartID'] + '">' + data[i]['PartNum'] + '</option>');
					}
				}
			});
		}
		
		function addSelected()
		{
			var newAlternate = $('#allParts').find(":selected").val();
			if (typeof newAlternate !== 'undefined')
			{
				//console.log(newAlternate);
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'AddAlternatePart', partID:<?=$_GET[partID]?>, alternatePartID:newAlternate},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}

						$("#allParts").children().removeProp('selected');
						$("#selectedParts").children().removeProp('selected');
						updateAlternateList();
					}
				});
			}
			else
			{
				//console.log("nothing selected.");
				alert("Error:  No part selected.");
			}
		}
			
		function removeSelected()
		{
			var removeAlternate = $('#selectedParts').find(":selected").val();
			if (typeof removeAlternate !== 'undefined')
			{
				//console.log(removeAlternate);
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'RemoveAlternatePart', partID:<?=$_GET[partID]?>, alternatePartID:removeAlternate},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}

						$("#allParts").children().removeProp('selected');
						$("#selectedParts").children().removeProp('selected');
						updateAlternateList();
					}
				});
			}
			else
			{
				//console.log("nothing selected.");
				alert("Error:  No part selected.");
			}
		}

		function toggleIntPartNum()
		{
			if ($("#IntPartNum").is(':checked'))
			{
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'AddInternalPart', partID:<?=$_GET[partID]?>},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						//console.log("update successful");
					}
				});
			}
			else
			{
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'RemoveInternalPart', partID:<?=$_GET[partID]?>},
					dataType:'json',
					type: 'get',
					success: function(data)
					{
						if (data.error)
						{
							// handle the error
							//console.log(data.error);
							alert("Error:" + data.error.msg);
							throw data.error.msg;
						}
						
						// If error is thrown, script stops, otherwise, continue:
						//console.log("update successful");
					}
				});
			}
		}

		
	</script>
	<?php include("../inc/header.php")?>
    </head>
	<body>

		<div id="divRetirePart">
			<label>Part Number:&nbsp;&nbsp;<?=$partInfo["PartNum"]?></label><br/>
			<label>Date Added:&nbsp;&nbsp;<?=$partInfo["DateAdded"]?></label><br/>
			<form name="IntPartNum">
				<label>Internal Part Number:</label><input type="checkbox" id="IntPartNum" value="" onchange="toggleIntPartNum();" <?=($partInfo['IntPartNum'] == 1) ? 'checked="checked"' : "";?>"/>
			</form>
			<form name="retirePart">
				<label>Part Active:</label><input type="checkbox" id="active" value="active" onchange="toggleActive();" <?=($partInfo['Active'] == 1) ? 'checked="checked"' : "";?>"/>
			</form>
		</div>
		<div id="divRegex">
			<p>
			<label>Part Regular Expression:</label>
			<input id="partRegex" name="partRegex" type="text" readonly="true" value="<?=($partInfo["RegularExpression"])?>"/>
			<img src="../images/edit.png" onclick="editRegex();">
			</p>
		</div>

		<div id="divManageAlternates" style="width: 500px;">
			<label>Part Filter:</label><input id="partFilter" value=""/><br/>
			<!---->
			<div id="divAllParts" style="float: left; width: 200px;">
				<label>All Parts</label><br/>
				<select name="allParts" size="10" id="allParts"></select>
			</div>
			<div id="buttons" style="float: left; width: 100px;">
				<input type="button" id="addAlternate" value=">>" onclick="addSelected();"/>
				<input type="button" id="removeAlternate" value="<<" onclick="removeSelected();"/>
			</div>
			<div id="divAlternateParts" style="float: left; width: 200px;">
				<label>Valid Alternate Parts</label><br/>
				<select name="selectedParts" size="10" id="selectedParts"></select>
			</div>
			<br style="clear: left;" />
			
		</div>
		
		
		<div id="pop-up" class="pop-up">
		  <h3>Attribute Update Pop-up</h3>
		  <p>
			Select Regular Expression:
		  </p>
		  <p>
		  <label>Expression Filter:</label><input type="text" name="regexFilter" id="regexFilter"/><br/>
		  </p>
		  <table id="expressionList" class="expressionList" border="1">
				<thead>
				<tr class="head">
					<th>Expression</th>
					<th>Select</th> 
				</tr> 
				</thead> 
				<tbody />  
			</table>
		  <input type="button" id="closePopup" value="Cancel" onclick="cancelRegexUpdate();"/>
		</div>
	</body>
<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->
</html>