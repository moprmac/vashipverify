<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/PartInfo.class.php');
include_once('../classes/BarcodeInfo.class.php');

$response = array();
if(array_key_exists('id', $_POST)){
	$partid = $_POST['id'];
	$barcodeInfo = new BarcodeInfo($db);

	$response['id'] = $partid;
	try{
		if($barcodeInfo->DeletedBarcodeById($partid)){
			$response['success'] = true;
			$response['message'] = '';
		}
		else{
			$response['success'] = false;
			$response['message'] = 'Could not delete barcode template.';
		}
	}
	catch (Exception $e){
		$response['success'] = false;
		$response['message'] = $e->getMessage();
	}
	echo json_encode($response);
} 