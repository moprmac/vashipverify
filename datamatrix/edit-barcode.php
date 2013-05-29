<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/PartInfo.class.php');
include_once('../classes/BarcodeInfo.class.php');

$partInfoObj = new PartInfo($db);
$partsList = $partInfoObj->GetAllInternalParts();

$barcodeInfo = new BarcodeInfo($db);
$sizes = $barcodeInfo->GetAllAvailableBarcodeSizes();
if($_GET['id']){
	$currentVals = $barcodeInfo->GetBarcodeByPartId($_GET['id']);
	if(empty($currentVals)) { header('Location: edit-barcode.php'); }
	$currentVals['HRIText'] = str_replace('~|~', "\n", $currentVals['HRIText']);
}


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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
  <style type="text/css" media="all">
    /* fix rtl for demo */
    .chzn-rtl .chzn-search { left: -9000px; }
    .chzn-rtl .chzn-drop { left: -9000px; }
    p { margin: 0em 0em }
    .previewBox {
    	border: 1px solid #666;
    	min-height: 60px;
    	text-align: left;
    	width: 40%;
    	margin: auto;
    	margin-top: 1.5em;
    	margin-bottom:1.5em;
    }
    .previewBox table{
    	margin: auto;
    }
  </style>
</head>
<body>
<?php include("../inc/header.php")?>
  <form action="upsertBarcode.php" method="post" id="editForm">
  <div id="container">
  	<div style="text-align: left; margin: 0.25em">
		<a href="index.php" id="newBC" > &laquo; Back to list</a>
	</div>
    <h2><?=!empty($currentVals)?'Update':'Create a ' ?> barcode template.</h2>
    <div class="side-by-side clearfix">
		<div><h3>Is Active:</h3></div>
		<div><input type="checkbox" name="isActive" id="isActive" <?= ((!empty($currentVals)) && $currentVals['IsActive']==0)?'null':'checked'?> /></div>
	</div>
    <div class="side-by-side clearfix">
      <div>
		<h3> Part Number: </h3>
	  </div>
      <div>
        <select data-placeholder="Choose Part Number" class="chzn-select w250" tabindex="2" id="partNum" name="partNum" <?= !empty($currentVals)?'disabled':null ?>>
          <option value=""></option> 
          <?php
		  foreach($partsList as $part) {
			  $partId = $part['PartID'];
			  $partNum = $part['PartNum'];
			  $selected = ((!empty($currentVals)) && $currentVals['PartId'] == $part['PartID'])?'selected':null; 
	          echo "<option value=\"$partId\" $selected>$partNum</option>";
		  }
		  ?>
        </select>
      </div>
    </div>
	<div class="side-by-side clearfix">
		<div><h3>Label Size:</h3></div>
		<div>
			<select  data-placeholder="Pick a label size" class="chzn-select w250" tabindex="2" id="size" name="size">
				<option></option>
				<?php 
				foreach ($sizes as $size) {
				?>
					<option value="<?=$size['Id']?>" <?=((!empty($currentVals)) && $currentVals['Size'] == $size['Id'])?'selected':null?>><?=$size['Name']?></option>
				<?php
				}
				?>
			</select>
		</div>
	</div>
	<div class="side-by-side clearfix">
		<div><h3>Barcode Text:</h3></div>
		<div><input type="text" name="barcodeText" id="barcodeText" class="w250" value="<?= (!empty($currentVals))?$currentVals['BarcodeText']:null ?>"/></div>
	</div>
	<div class="side-by-side clearfix">
		<div><h3>Human Readable Text:</h3></div>
		<div><textarea rows=4 name="hriText" id="hri" class="w250"><?= (!empty($currentVals))?$currentVals['HRIText']:null ?></textarea></div>
	</div>
		<h3>Available Keywords for Barcode Text and Human Readable Text:</h3>
	<div class="descriptionBox" style="border:1px solid black; width:80%">
		<table style="width:100%; color:#666">
			<thead>
				<tr>
					<td style="border-bottom:2px solid #999; color:#333">Key Word</td>
					<td style="border-bottom:2px solid #999; color:#333">Description</td>
				</tr>
			</thead>
			<tr>
				<td>{PartNumber}</td>
				<td>Part Number</td>
			</tr>
			<tr>
				<td>{Serial}</td>
				<td>Serial Number 0001 - 9999</td>
			</tr>
			<tr>
				<td>{Date1}</td>
				<td>Date Format = YYMMDD </td>
			</tr>
			<tr>
				<td>{Date2}</td>
				<td>Date Format = YYDDMM </td>
			</tr>
			<tr>
				<td>{Date3}</td>
				<td>Date Format = YYYYMMDD </td>
			</tr>
		</table>
	</div>
	<div class="previewBox hide">
		<table>
			<tr>
				<td>
					<img id="prevImg" src="" />
				</td>
				<td>
					<pre id="prevText">
					
					</pre>
				</td>
			</tr>
		</table>
	</div>
    <div class="side-by-side clearfix">
    	<input type="hidden" name="submitType" value="preview" />
    	<div align="center"><input class="w250" type="button" value="Generate Preview" id="preview" /></div>
    	<div align="left"><input class="w250" type="submit" value="Save Barcode" /></div>
    </div>
  </div>
  </form>
  <script src="../js/chosen.jquery.js" type="text/javascript"></script>
  <script src="../js/jquery.textareamaxrows.js" type="text/javascript"></script>
  <script type="text/javascript">
  	$( "input[type=submit], input[type=button]" ).button();
    var config = {
      '.chzn-select'           : {},
      '.chzn-select-deselect'  : {allow_single_deselect:true},
      '.chzn-select-no-single' : {disable_search_threshold:10},
      '.chzn-select-no-results': {no_results_text:'Oops, nothing found!'},
      '.chzn-select-width'     : {width:"95%"}
    }
    for (var selector in config) {
      $(selector).chosen(config[selector]);
    }
	$('#hri').textareamaxrows({maxcharsinrow : 20,  maxrows : 4});

	function validateForm(){
		if($('#size').val() == ''){
			return "Please pick a label size";
		}
		if($('#barcodeText').val() == ''){
			return "Please enter barcode text.";
		}
		return true;
	}
	
	/* Generate preview */
	function preview(){
		var isValid = validateForm();
		if(isValid === true){
			$('#prevImg').attr('src', "barcode.php?code="+document.getElementById('barcodeText').value+"&size="+document.getElementById("size").value);
			$('#prevText').html(document.getElementById('hri').value);
			$('.previewBox').show();
		} else {
			alert(isValid);
		}
	}
	if($('#barcodeText').val() != ''){
		preview();
	}
	$("#preview").click(preview);
	$('#editForm').submit(function(){
		$('#partNum').removeAttr('disabled');
	});
  </script>
</body>
<?php include("../inc/footer.php")?>
</html>