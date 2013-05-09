<?php
include("../inc/auth.php");
include("../inc/db.php");
include("../classes/Users.class.php");


if ( $_SESSION['AdminAccess'] == true )
	$grantAccess = true;
else
	$grantAccess = false;

if ( !$grantAccess )
{
	header("Location: ../index.php"); 
	die("Redirecting to index");
}
	
$userObj = new Users($db);
try
{
	$userInfo = $userObj->GetUserByID($_GET['userID']);
}
catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>
	<link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
	<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
	<link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
    <script type="text/javascript" src="../js/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui-1.10.2.min.js"></script>
    <script type="text/javascript">
	
		$(document).ready(function()
		{
			$('div#pop-up').hide();
			
			// populate the user access list
			popuplateAccessList();
			
			
			// Key to trap the enter action and prevent form input, rather trigger button.
			/*
			$('#add_part_form input').keydown(function(e) {
				if (e.keyCode == 13) {
					e.preventDefault();
					addPart();
				}
			});*/
			
		});
		
		function editRegex()
		{
			$("div#pop-up").dialog({dialogClass: "no-close", modal:true});
			updateExpressionList();
		}
		
		function popuplateAccessList()
		{
		
			$.ajax({url: '../classes/Users.js_link.php',
				data: {action: 'ListAccessLevels'},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					for (var i=0; i < data.length; i++)
					{
						//Creates the item
						$("#selectAccess").append('<option value="' + data[i]['StatusID'] + '">' + data[i]['Status'] + '</option>');
					}
					
					updateAccessList();
				}
			});
		}
	
		function updateAccessList()
		{
			$.ajax({ url: '../classes/Users.js_link.php',
				data: {action: 'GetUserAccess', userID:<?=$_GET['userID']?>},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					for (var i=0; i < data.length; i++)
					{
						//$('#selectAccess option:contains(value="' + data[i]['StatusID'] + '")').prop('selected', true);
						$("#selectAccess option").each(function(){
							if (data[i]['StatusID'] == $(this).val())
							{
								$(this).prop('selected', true);
							}
						});
						
					}
				}
			});
		}
			
		function updateUser()
		{
			//console.log($('#active').is(':checked') ? 1 : 0);
			
			$.ajax({ url: '../classes/Users.js_link.php',
				data: {action: 'UpdateUser', userID:<?=$_GET['userID']?>,
						firstName:$('#firstName').val(),
						lastName:$('#lastName').val(),
						email:$('#email').val(),
						newPassword:$('#newPassword').val(),
						active:$('#active').is(':checked') ? 1 : 0,
						selectAccess:serealizeSelects($('#selectAccess'))},
				dataType:'json',
				type: 'get',
				success: function(data)
				{
					if (data.error)
					{
						// handle the error
						alert("Error:" + data.error.msg);
						throw data.error.msg;
					}
					
					$('#newPassword').val('');
					alert("User Updated.");
					//console.log(data);
				}
			});
			
		}
		
		function serealizeSelects (select)
		{
			var array = [];
			select.each(function(){ array.push($(this).val()) });
			return array;
		}
		
	</script>
    </head>
	<body>
		<?php include("../inc/header.php")?>
		<div id="divModifyUser">
			<form name="User Info">
				<label>Login:&nbsp;&nbsp;<?=$userInfo["login"]?></label><br/>
				<label>First Name:</label><input id="firstName" name="firstName" type="text" value="<?=$userInfo["first_name"]?>"/><br/>
				<label>Last Name:</label><input id="lastName" name="lastName" type="text" value="<?=$userInfo["last_name"]?>"/><br/>
				<label>Email:</label><input id="email" name="email" type="text" value="<?=$userInfo["email"]?>"/><br/>
				<label>New Password:</label><input id="newPassword" name="newPassword" type="text" value=""/><br/>
				<label>User Active:</label><input type="checkbox" id="active" value="active" <?=($userInfo['Active'] == 1) ? 'checked="checked"' : "";?>"/>
			</form>
		</div>
		
		<div id="divUserAccess">
			<label>User Access: </label><br/>
			<select name="selectAccess[]" size="10" multiple="multiple" id="selectAccess">
			</select>
		</div>
		
		<input type="button" value="Update User" onclick="updateUser();"/>
		<?php include("../inc/footer.php")?>
	</body>
</html>