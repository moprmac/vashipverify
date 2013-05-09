<?php
include("../inc/db.php");
// Don't allow access to unless authorized
include("../inc/auth.php");
include("PlexInfo.class.php");


$plexObj = new PlexInfo($db);

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch($action) {
        case 'GetStatusByID':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($plexObj->GetStatusByID($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetStatusBySN':
			try
			{
				echo json_encode($plexObj->GetStatusBySN($_GET[plexSN]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'GetParts':
			try
			{
				echo json_encode($plexObj->GetParts($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'GetShippingInfo':
			try
			{
				echo json_encode($plexObj->GetShippingInfo($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'AddPart':
			try
			{
				echo json_encode($plexObj->AddPart($_GET[plexID], $_GET[partInfo]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'RemovePart':
			try
			{
				echo json_encode($plexObj->RemovePart($_GET[plexID], $_GET[partRecNum]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'ValidateParts':
			//echo ("This function is private.");
			
			try
			{
				echo json_encode($plexObj->ValidateParts($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			
			break;
		case 'SearchParts':
			try
			{
				echo json_encode($plexObj->SearchParts($_GET[plexID], $_GET[partInfo]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'ReplacePart':
			try
			{
				echo json_encode($plexObj->ReplacePart($_GET[plexID], $_GET[oldPartID], $_GET[newPartInfo], $_GET[replaceReason]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'AddRecord':
			try
			{
				echo json_encode($plexObj->AddRecord($_GET[plexSN], $_GET[partNum], $_GET[partQty]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'GetPartQty':
			try
			{
				echo json_encode($plexObj->GetPartQty($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'DeleteRecord':
			try
			{
				echo json_encode($plexObj->DeleteRecord($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'LockRecord':
			try
			{
				echo json_encode($plexObj->LockRecord($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'UnlockRecord':
			try
			{
				echo json_encode($plexObj->UnlockRecord($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
		case 'GetInsertErrors':
			try
			{
				echo json_encode($plexObj->GetInsertErrors($_GET[plexID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
				//echo json_encode(array('Caught exception: '.  $e->getMessage()));
			}
			break;
        case 'Foo' : 
			echo json_encode($plexObj->Foo($_GET['bar']));
			break;
        // ...etc...
    }
}

?>