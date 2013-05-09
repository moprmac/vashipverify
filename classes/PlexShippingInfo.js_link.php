<?php
include("../inc/db.php");
// Don't allow access to unless authorized
include("../inc/auth.php");
include("PlexShippingInfo.class.php");


$plexShipObj = new PlexShipInfo($db);

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    switch($action) {
        case 'AddRecord':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($plexShipObj->AddRecord($_GET[plexSN], $_GET[partNum], $_GET[partQty]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetShippingStatusBySN':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($plexShipObj->GetShippingStatusBySN($_GET[plexSN]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetShippingStatusByID':
			// ToDO  add some verification here.
			try
			{
				echo json_encode($plexShipObj->GetShippingStatusByID($_GET[shippingID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'AddPart':
			try
			{
				echo json_encode($plexShipObj->AddPart($_GET[shippingID], $_GET[partInfo]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetParts':
			try
			{
				echo json_encode($plexShipObj->GetParts($_GET[shippingID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'ValidateParts':
			try
			{
				echo json_encode($plexShipObj->ValidateParts($_GET[shippingID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'RemovePart':
			try
			{
				echo json_encode($plexShipObj->RemovePart($_GET[shippingID], $_GET[shippingRecordPartID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'GetPartQty':
			try
			{
				echo json_encode($plexShipObj->GetPartQty($_GET[shippingID]));
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
		case 'Foo':
			try
			{
				echo json_encode("bar");
			}
			catch (Exception $e)
			{
				echo json_encode(array('error' => array('msg' => $e->getMessage(), 'code' => $e->getCode())));
			}
			break;
	}
}
?>