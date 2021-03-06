<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/PartInfo.class.php');
include_once('../classes/BarcodeInfo.class.php');

$partInfoObj = new PartInfo($db);
$partsList = $partInfoObj->GetAllParts();

$barcodeInfo = new BarcodeInfo($db);
$barcodes = $barcodeInfo->GetAllAvailableBarcodes();
?>

<!doctype html> 
<html lang="en"> 
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="../css/app.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
  <link rel="stylesheet" href="../css/chosen.css" />
  <style type="text/css" media="all">
	.box-table-a{font-family:"Lucida Sans Unicode", "Lucida Grande", Sans-Serif;font-size:12px;width:98%;text-align:left;border-collapse:collapse;margin:auto;}
	.box-table-a th{font-size:16px;font-weight:bold;background:#1971ab;border-top:4px solid #014876;border-bottom:1px solid #fff;color:#eee;padding:8px;}
	.box-table-a td{background:#d9d9d9;border-bottom:1px solid #fff;color:#1971ab;border-top:1px solid transparent;padding:8px;}
	.box-table-a tr:hover td{background:#e77a10;color:#d9d9d9;}
	.free-link{margin-left:1em; padding-left:1em;}
  </style>
</head>
<body>
<?php include("../inc/header.php")?>
  <div id="container">
    <div style="text-align: right; margin: 0.25em 1.25em;">
    	<a class="free-link" href="edit-barcode.php" id="newBC" >New Template</a>
    	<a class="free-link" href="user-barcode.php" id="printBC" >Generate Barcode</a>
    </div>
    <h2>List of Available Barcode Templates</h2>
    <table class="box-table-a">
    	<thead>
    		<tr>
    			<th>Part Number </th>
    			<th>Size</th>
    			<th>Barcode Text</th>
    			<th>IsActive</th>
    			<th>Edit </th>
    		</tr>
    		<?php
    		foreach ($barcodes as $code){
				$statImg = ($code['IsActive'])?'../images/ok-icon.png':'../images/danger-icon.png';
				$statAlt = ($code['IsActive'])?'Active':'Inactive';
			?>
    		<tr>
    			<td><?= $code['PartNum'] ?> </td>
    			<td><?= $code['Size']-1 ?> </td>
    			<td><?= $code['BarcodeText'] ?></td>
    			<td><img src="<?= $statImg ?>" alt="<?= $statAlt ?>" title="<?= $statAlt ?>"/></td>
    			<td><a href="edit-barcode.php?id=<?=$code['PartId']?>"><img src="../images/edit-icon.png" title="Edit Template" alt="Edit Template"/></a></td>
    		</tr>
			<?php
			} 
			?>
    	</thead>
    </table>
  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
  
</body>
<?php include("../inc/footer.php")?>
</html>
