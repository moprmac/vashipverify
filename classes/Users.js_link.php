<?php
include("../inc/db.php");
// Don't allow access to unless authorized
include("../inc/auth.php");
include("Users.class.php");

$userObj = new Users($db);

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch($action) {
        case 'GetUsers':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($userObj->GetUsers($_GET[login]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetUserByID':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($userObj->GetUserByID($_GET[userID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetUserByUsername':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($userObj->GetUserByUsername($_GET[login]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetUserAccess':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($userObj->GetUserAccess($_GET[userID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'ChangeUserPassword':
			// ToDO  add some verification here.
			try
			{
				echo json_encode(array('not implemented'));
				//echo json_encode($userObj->GetUserAccess($_GET[userID], $_GET[newPassword]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'SetUserPrivelages':
			// ToDO  add some verification here.
			try
			{
				echo json_encode(array('not implemented'));
				//echo json_encode($userObj->SetUserPrivelages($_GET[userID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'ListAccessLevels':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($userObj->ListAccessLevels());
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'UpdateUser':
			try
			{
				echo json_encode($userObj->UpdateUser($_GET[userID], $_GET[firstName], $_GET[lastName], $_GET[email], $_GET[newPassword], $_GET[active], $_GET[selectAccess]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
	}
}


?>