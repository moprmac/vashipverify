<?php
include("../inc/db.php");
// Don't allow access to unless authorized
include("../inc/auth.php");
include("PartInfo.class.php");


$partObj = new PartInfo($db);

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch($action) {
		case 'GetPartByID':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->GetPartByID($_GET[partID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
        case 'GetParts':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->GetParts($_GET[partNum]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'AddPart':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->AddPart($_GET[partNum]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetExpressions':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->GetExpressions($_GET[regexFilter]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'UpdateRegex':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->UpdateRegex($_GET[partID], $_GET[regexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'ActivatePart':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->ActivatePart($_GET[partID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'RetirePart':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->RetirePart($_GET[partID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetAlternateParts':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->GetAlternateParts($_GET[partID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'AddAlternatePart':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->AddAlternatePart($_GET[partID], $_GET[alternatePartID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'RemoveAlternatePart':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($partObj->RemoveAlternatePart($_GET[partID], $_GET[alternatePartID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
	}
}
?>