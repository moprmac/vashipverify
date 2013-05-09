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
		// DO this when the document Loads
		$(document).ready(function()
		{
			$('#partNum').focus();
			
			updatePartList();
			//$("#partList").tablesorter( {sortList: [[0,0], [1,0]]} ); 
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#part_list_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addPart();
				}
			});
			
			$("input#partNum").keyup(function(e) {
				updatePartList();
			});
			
		});
		
		function updatePartList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/PartInfo.js_link.php',
				data: {action: 'GetParts', partNum:$('input#partNum').val()},
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
					//id=\"" + data[i]["PartID"] +"
					content += "<tr>"
								+ "<td>" + data[i]["PartID"] 
								+ "</td><td>" + data[i]["PartNum"] 
								+ "</td><td>" + data[i]["DateAdded"] 
								+ "</td><td>" + data[i]["Active"] 
								+ "</td><td><img src=\"../images/edit.png\" onclick=\"editPart(" + data[i]["PartID"] +");\""
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
		
		function editPart(partID)
		{
			window.location.href = "edit_part.php?partID=" + partID;
		}
		
		function addPart()
		{
			var doPost = true;
			
			var partInfo = $('input#partNum').val();
			
			if (partInfo === null || partInfo <= 0 || partInfo == "")
			{
				doPost = false;
			}
			
			var regularExpression = /^[a-z0-9]+$/i;
			
			if (!regularExpression.test(partInfo))
			{
				alert("Error:  Invalid part input.  Part must be alphanumeric (a-Z 0-9)");
				//console.log("fail pattern match");
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: '../classes/PartInfo.js_link.php',
					data: {action: 'AddPart', partNum:partInfo},
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
						updatePartList();
						alert("Part Successfully Added.  PartID: " + data['PartID']);
					}
				});
			}
		}
	
	</script>
    </head>
	<body>
		<?php include("../inc/header.php")?>
		<div id="divPartList" class="divPartList">
		<h3>Part List</h3>
			<form id="part_list_form">
				<p id="auto">
				<label>Part: </label>
				<input id="partNum" name="partNum" type="text" />
				<input type="button" value="Add New Part" onclick="addPart();">
				<!-- don't think I need this, as it's set in the javascript-->
				</p>
			</form>
			
			<table id="partList" class="partList" border="1">
				<thead>
				<tr class="head">
					<th>PartID</th> 
					<th>Part Number</th> 
					<th>Part Added</th> 
					<th>Status</th>
					<th>Edit</th>
				</tr> 
				</thead> 
				<tbody />  
			</table>
		</div>

	</body>
	<?php include("../inc/footer.php")?>
</html>
		
