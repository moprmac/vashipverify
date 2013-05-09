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
			$('#regex').focus();
			updateRegexList();
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#regex_list_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addRegex();
				}
			});
			
			$("input#regex").keyup(function(e) {
				updateRegexList();
			});
			
		});
		
		function updateRegexList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/RegexInfo.js_link.php',
				data: {action: 'GetExpressions', regexFilter:$('input#regex').val()},
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
								+ "<td>" + data[i]["RegexID"] 
								+ "</td><td>" + data[i]["RegularExpression"] 
								+ "</td></tr>";
				}
				$('#regexList tbody').html(content);
			}
			else
			{
				// no data
				$('#regexList tbody').html(content);
			}
		}
		
		
		function addRegex()
		{
			var doPost = true;
			
			var regexInfo = $('input#regex').val();
			
			if (regexInfo === null || regexInfo <= 0 || regexInfo == "")
			{
				doPost = false;
			}
			
			if (doPost)
			{
				$.ajax({ url: '../classes/RegexInfo.js_link.php',
					data: {action: 'AddExpression', newRegex:regexInfo},
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
						updateRegexList();
						alert("Expression Successfully Added.  ExpressionID: " + data['RegexID']);
					}
				});
			}
		}
	
	</script>
    </head>
	<body>
		<?php include("../inc/header.php")?>
		<div id="divRegexList" class="divRegexList">
		<p>
			Be sure to test your expression against what you expect the data to look like so you can be sure it works.<br/>
			A good site to test this at is <a href="http://regexpal.com/" target="_new">http://regexpal.com/</a><br/>
		</p>
		<h3>Regular Expression List</h3>
			<form id="regex_list_form">
				<p id="auto">
				<label>Regular Expression: </label>
				<input id="regex" name="regex" type="text" />
				<input type="button" value="Add New Regex" onclick="addRegex();">
				<!-- don't think I need this, as it's set in the javascript-->
				</p>
			</form>
			<p>
			<table id="regexList" class="regexList" border="1">
				<thead>
				<tr class="head">
					<th>RegexID</th> 
					<th>Regular Expression</th> 
				</tr> 
				</thead> 
				<tbody />  
			</table>
		</div>

<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->
		
	</body>
</html>