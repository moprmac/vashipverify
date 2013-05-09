<?php
include("../inc/db.php");
// Don't allow access to unless authorized
include("../inc/auth.php");
include("RegexInfo.class.php");


$regexObj = new RegexInfo($db);

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch($action) {
		case 'GetExpressions':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($regexObj->GetExpressions($_GET[regexFilter]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'AddExpression':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($regexObj->AddExpression($_GET[newRegex]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
	}
}
?>