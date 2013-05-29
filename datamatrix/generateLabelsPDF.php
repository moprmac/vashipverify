<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/PartInfo.class.php');
include_once('../classes/BarcodeInfo.class.php');
include('php-barcode.php');
require('fpdf.php');
// -------------------------------------------------- //
//                      USEFUL
// -------------------------------------------------- //

class eFPDF extends FPDF{
	function TextWithRotation($x, $y, $txt, $txt_angle, $font_angle=0)
	{
		$font_angle+=90+$txt_angle;
		$txt_angle*=M_PI/180;
		$font_angle*=M_PI/180;

		$txt_dx=cos($txt_angle);
		$txt_dy=sin($txt_angle);
		$font_dx=cos($font_angle);
		$font_dy=sin($font_angle);
	  
		$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		if ($this->ColorFlag)
			$s='q '.$this->TextColor.' '.$s.' Q';
		$this->_out($s);
	}
}
// ACTUAL BARCODE PROCESS STARTS HERE //


$partNum = $_REQUEST['partNum'];
$numberOfCopies = (int)$_REQUEST['qty'];

$barcodeDao = new BarcodeInfo($db);
$partDao = new PartInfo($db);
try{
	
	$barcodeDetails = $barcodeDao->GetBarcodeByPartId($partNum);
	$partDetails = $partDao->GetPartByID($partNum);
	
	$ser = $barcodeDao->GetNextSerialByBarcodeId($partNum);
	if(!$ser){
		$ser = 1;
	}
	$barcodeText = $barcodeDetails['BarcodeText'];
	$hriText = $barcodeDetails['HRIText'];
	$hriTextArr = explode("~|~", trim($hriText));
	
	
	$size = $barcodeDetails['Size'];
	$properties = $barcodeDao->GetPropertiesByBarcodeSizeId($size);
	
	// Init Properties //
	$labelWidth=$properties['Width'];
	$labelHeight = $properties['Height'];
	$fontSize = $properties['FontSize'];
	$marge    = $properties['TextMargin'];   // between barcode and hri in pixel
	$yMarge   = $properties['TextYMargin'];
	$x        = $properties['X'];  // barcode center
	$y        = $properties['Y'];  // barcode center
	$module_height   = 5;   // barcode height in 1D ; module size in 2D
	$module_width    = $properties['ModuleWidth'];    // barcode height in 1D ; not use in 2D
	$angle    = 0;   // rotation in degrees
	$type     = 'datamatrix';
	$black    = '000000'; // color in hexa
	
	//$code     = $barcodeText; // barcode, of course ;)


	// -------------------------------------------------- //
	//            ALLOCATE FPDF RESSOURCE
	// -------------------------------------------------- //
	
	$pdf = new eFPDF('L', 'in', array($labelHeight, $labelWidth));
	$pdf->SetFont('Arial','B',$fontSize);
	$pdf->SetTextColor(0, 0, 0);
	
	for($loop=0; $loop<$numberOfCopies; $loop++, $ser++) {
		$pdf->AddPage();
		$xt = 0; $yt = 0; $hriArr = array();
		
		$patterns = array("/{PartNumber}/", "/{Date1}/", "/{Date2}/", "/{Date3}/", "/{Serial}/");
		$replacements = array($partDetails['PartNum'], date('ymd'), date('ydm'), date('Ymd'), str_pad((string)$ser, 4, "0", STR_PAD_LEFT));
		$code = preg_replace($patterns, $replacements, $barcodeText);
		foreach($hriTextArr as $idx => $txt){
			$hriArr[$idx] = preg_replace($patterns, $replacements, $txt);
		}
		
		// BARCODE
		$data = Barcode::fpdf($pdf, $black, $x, $y, $angle, $type, array('code'=>$code), $module_width, $module_height);
	
		// HRI
		//$len = $pdf->GetStringWidth($data['hri']);
		Barcode::rotate($data['width']/2, $fontSize + $marge, $angle, $xt, $yt);
		foreach($hriArr as $idx => $text){
			$pdf->TextWithRotation($x + $xt, ($yt+$idx*($fontSize)+$marge+$yMarge)/72, $text, $angle);
		}
	}
	$barcodeDao->UpdateNextSerialByBarcodeId($partNum, $ser);
	$pdf->Output();
}
catch (Exception $e){
	print($e->getMessage());
}
 
?>