<?php
include("inc/auth.php");

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


    <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript">
		// DO this when the document Loads
		$(document).ready(function()
		{
			$("input#plexSN").focus();
			
			//$("input#plexSN").blur(function(){
			//	$("input#plexSN").val( $("input#plexSN").val().substring(1) );
			//});
			
			// Add a filter to the qty textbox so it only accepts numeric
			$("input#partQty").keydown(function(event) {
				// Allow: backspace, delete, tab, escape, and enter
				if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || 
					 // Allow: Ctrl+A
					(event.keyCode == 65 && event.ctrlKey === true) || 
					 // Allow: home, end, left, right
					(event.keyCode >= 35 && event.keyCode <= 39)) {
						 // let it happen, don't do anything
						 return;
				}
				else {
					// Ensure that it is a number and stop the keypress
					if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
						event.preventDefault(); 
					}   
				}
			});
			
			// Add on blur event to plexSN to verify pattern is [sS][0-9]*
			$("input#plexSN").blur(function() {
				if ($("input#plexSN").val().match(/^[sS][0-9]*\b/))
					$("#plexError").hide();
				else
					$("#plexError").show();
			});
			
			
			// Add on blur event to QTY to verify quantity is between 1 & 99
			$("input#partQty").blur(function() {
				if ( $("input#partQty").val() < 1 || $("input#partQty").val() > 99 )
						$("#qtyError").show();
				else
					$("#qtyError").hide();
			});

			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#add_plex_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addRecord();
				}
			});
		});
		
		function addRecord()
		{
			// do validate
			var plexSN = $("input#plexSN").val();
			var partNum =  $("input#partNum").val();
			var partQty = $("input#partQty").val();
			var doPost = true; 
			
			if (plexSN === null || plexSN <= 0 || plexSN == "")
			{
				doPost = false;
			}
			
			if (!plexSN.match(/^[sS][0-9]*\b/))
			{
				doPost = false;
				$("input#plexSN").focus();
				alert("Error:  Plex Serial Number Invalid.");
				return false;
			}
			
			if (partNum === null || partNum <= 0 || partNum == "")
			{
				doPost = false;
			}
			
			if (partQty === null || partQty <= 0 || partQty == "")
			{
				// ToDo:  Check if is numeric
				doPost = false;
			}
			
			if (partQty < 1 || partQty > 99)
			{
				doPost = false;
				$("input#partQty").focus();
				alert("Error:  Part Quantity is invalid.");
				return false;
			}
			
			if (doPost)
			{
				$.ajax({ url: 'classes/PlexInfo.js_link.php',
					data: {action: 'AddRecord', plexSN:plexSN,
							partNum:partNum,
							partQty:partQty},
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
						
						if (window.console) { console.log("Redirect to add_part.php?plexID=" + data.PlexRecordID);}
						window.location.href = "add_part.php?plexID=" + data.PlexRecordID;
					}
				});
			}
			else
			{
				alert("Error:  You didn't fill in all the required fields.");
				//ToDO:  Add a <label> field or something with a * in it for required fields
				// Have javascript set them to Visible
			}
				
			return false; //true;
		}
	
		function clearForm()
		{
			$("input#plexSN").val("");
			$("input#partNum").val("");
			$("input#partQty").val("");
			//$("input#addPart).attr("disabled", "disabled");
			
		}
	</script>
    </head>
	<body>

		<?php include("inc/header.php")?>

	<div id="divContainer" class="divContainer">
		
        <form id="add_plex_form"  action ="" method="POST">
			<p id="auto">
			<label>Internal Label Plex Serial Number: </label>
			<input id="plexSN" name="plexSN" type="text" />
			<label id="plexError" style="color: red; display:none;">*</label>
			<br/>
			<label>Internal Label Part Number: </label>
			<input id="partNum" name="partNum" type="text" />
			<br/>
			<label>Internal Label Quantity: </label>
			<input id="partQty" name="partQty" type="text" />
			<label id="qtyError" style="color: red; display:none;">*</label>
			</p>
			
			<p>
			<input type="button" value="Add Record" onclick="addRecord();" />
			<input type="button" value="Cancel" onclick="clearForm();"/>
			</p>
        
		</form>	

<!--ed-->
	<?php include("inc/footer.php")?>
<!--/ed-->
	
    </body>
</html>