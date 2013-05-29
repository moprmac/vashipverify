<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/BarcodeInfo.class.php');

$barcodeInfo = new BarcodeInfo($db);

$partNum = $_REQUEST['partNum'];
$numberOfCopies = $_REQUEST['qty'];

$barcodeDetails = $barcodeInfo->GetBarcodeByPartId($partNum);

$barcodeText = $barcodeDetails['BarcodeText'];
$hriText = $barcodeDetails['HRIText'];
$hriTextArr = explode("~|~", trim($hriText));
$size = $barcodeDetails['Size'];
$barcodeUrl = "barcode.php?code=".$barcodeText."&size=".($size+1);
?>
<html>
	<head>
		<style type="text/css">
		@media all {
			.page-break	{ display: block; page-break-before: always; }
			ul{
				list-style-type: none;
				padding: 0px;
				margin: 0px;
				font-family: Verdana;
				font-size: <?php echo ($size==2)?9:13;?>px;
			}
		}

		@media print {
			.hideInPrint { display: none;}
			.page-break	{ display: block; page-break-before: always; }
		}
		</style>
	</head>
	<body >
		<input type="button" class="hideInPrint" onclick="window.history.back()" value="&laquo Cancel">
		<input type="button" class="hideInPrint" onclick="window.print()" value="Print Barcodes">
	<?php
	for($i=0; $i< $numberOfCopies; $i++) {
		echo "
		<table>
			<tr>
				<td>
					<img border='0' src='$barcodeUrl' /> 
				</td>
				<td>
					<ul>";
		foreach ($hriTextArr as $txt){
			echo "<li>$txt</li>";
		}
		echo "
					</ul>
				</td>
			</tr>
		</table>";
		if($i != $numberOfCopies-1) echo "<div class='page-break'></div>";
	}
		?>
	</body>
</html>