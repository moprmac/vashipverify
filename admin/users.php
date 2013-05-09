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
	<script type="text/javascript" src="../js/tablesorter/jquery.tablesorter.js"></script> 
    <script type="text/javascript">
		// DO this when the document Loads
		$(document).ready(function()
		{
			$('#partInfo').focus();
			
			updateuserList();
			//$("#userList").tablesorter( {sortList: [[0,0], [1,0]]} ); 
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			$('#user_list_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
				}
			});
			
			$("input#userLogin").keyup(function(e) {
				updateuserList();
			});
			
		});
		
		function updateuserList()
		{
			// Populate part table.
			$.ajax({ url: '../classes/Users.js_link.php',
				data: {action: 'GetUsers', login:$('input#userLogin').val()},
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
					content += "<tr>"
								+ "<td>" + data[i]["UserID"] 
								+ "</td><td>" + data[i]["login"] 
								+ "</td><td>" + data[i]["first_name"] 
								+ "</td><td>" + data[i]["last_name"] 
								+ "</td><td>" + data[i]["email"] 
								+ "</td><td><img src=\"../images/edit.png\" onclick=\"editUser(" + data[i]["UserID"] +");\""
								+ "</td></tr>";
				}
				$('#userList tbody').html(content);
			}
			else
			{
				// no data
				$('#userList tbody').html(content);
			}
		}
		
		function editUser(userID)
		{
			window.location.href = "edit_user.php?userID=" + userID;
		}
	
	</script>
	<?php include("../inc/header.php")?>
    </head>
	<body>
		
		<div id="divUserList" class="divUserList">
		<h3>Part List</h3>
			<form id="user_list_form">
				<p id="auto">
				<label>User: </label>
				<input id="userLogin" name="login" type="text" />
				</p>
			</form>
			<p>
			<table id="userList" class="userList">
				<thead>
				<tr class="head">
					<th>UserID</th> 
					<th>Login</th> 
					<th>First Name</th> 
					<th>Last Name</th>
					<th>Email</th>
					<th>Edit</th>
				</tr> 
				</thead> 
				<tbody />  
			</table>
		</div>
		
	</body>
<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->
</html>