<?php
session_start();
function array_key_exists_not_empty($key, $search){
	if(array_key_exists($key, $search) && !empty($search[$key])) {
		return true;
	}
	return false;
}

function validateRequest($req){
	$required = array('partNum', 'size', 'barcodeText', 'hriText');
	foreach ($required as $item){
		if(!array_key_exists_not_empty($item, $req)) {
			return false;
		}
	}
	return true;
}

if(!validateRequest($_REQUEST)) {
	echo "Cannot complete barcode creation process as requested!";
	echo "<pre>";
	print_r($_REQUEST);
	exit();
}

/* Everything is good lets get the process started */
include_once '../inc/db.php';
include_once '../classes/BarcodeInfo.class.php';

$barcodeDAO = new BarcodeInfo($db);

$hriTextArr = explode("\n", trim($_REQUEST['hriText']));
if(count($hriTextArr) > 4) { array_splice($hriTextArr, 4); }
$hriText = implode('~|~', $hriTextArr);

try{
	$bc = $barcodeDAO->GetBarcodeByPartId($_REQUEST['partNum']);
	
	if(empty($bc)){
		$result = $barcodeDAO->SaveNewBarcode($_REQUEST['partNum'], $_REQUEST['size'], $_REQUEST['barcodeText'], $hriText);
	}
	else{
		$result = $barcodeDAO->UpdateBarcodeById($_REQUEST['partNum'], $_REQUEST['size'], $_REQUEST['barcodeText'], $hriText);
	}
	if($result){
		header('Location: edit-barcode.php?id='.$_REQUEST['partNum']);
	}
}
catch(Exception $e){
	echo $e->getMessage() ." in ". $e->getFile() .$e->getLine();
}