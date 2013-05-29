<?php
include_once('../inc/db.php');
include_once('../inc/auth.php');
include_once('../classes/PartInfo.class.php');
include_once('../classes/BarcodeInfo.class.php');

$barcodeInfo = new BarcodeInfo($db);
$availableBarcodes = $barcodeInfo->GetAllAvailableBarcodes();


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
  <form action="generateLabelsPDF.php" method="post" id="editForm" onsubmit="return validateandconfirm();" target="_blank">
  <div id="container" style="min-height:100%;">
    <h2>Generate a barcode.</h2>
    <div class="side-by-side clearfix">
      <div>
		<h3> Part Number: </h3>
	  </div>
      <div>
        <select data-placeholder="Choose Part Number" class="chzn-select w250" tabindex="2" id="partNum" name="partNum">
          <option value=""></option> 
          <?php
		  foreach($availableBarcodes as $part) {
			  $partId = $part['PartId'];
			  $partNum = $part['PartNum']; 
			  $barcodeText = $part['BarcodeText'];
			  $barcodeSize = $part['Size'];
			  $hriText = str_replace("~|~", "\n", $part['HriText']);
	          echo "<option value=\"$partId\" data-bctext=\"$barcodeText\" data-size=\"$barcodeSize\" data-hritext=\"$hriText\">$partNum</option>";
		  }
		  ?>
        </select>
      </div>
    </div>
    <!-- 
	<div class="side-by-side clearfix">
		<div><h3>Serial Number:</h3></div>
		<div>
			<input type="text" name="serialNum" id="serialNum" class="w250" />
		</div>
	</div>
	-->
	<div class="side-by-side clearfix">
		<div><h3>Number of copies:</h3></div>
		<div>
			<select  data-placeholder="Choose quantity" class="chzn-select w250" tabindex="2" id="qty" name="qty">
				<option value=""></option>
			<?php 
			for($i=1; $i <= 100; $i++) {
			echo "<option value='$i'>$i</option>";
			}
			?>
			</select>
		</div>
	</div>
	<div class="previewBox">
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
    <div >
    	<div align="center"><input class="w250" type="submit" value="Generate Barcode"/></div>
    </div>
  </div>
  </form>
  <script src="../js/chosen.jquery.js" type="text/javascript"></script>
  <script src="../js/jquery.textareamaxrows.js" type="text/javascript"></script>
  <script type="text/javascript">
  	function validateandconfirm() {
  	  	if($('#partNum').val() == ''){
  	  	  	alert('Please choose a Part Number..');
  	  	  	return false;
  	  	}
  	  	if($('#qty').val() == '') {
  	  	  	alert('Please choose a quantitiy..');
  	  		return false;
  	  	}
  	  	if(!confirm("Are you sure you want to print?")){
  	  	  	return false;
  	  	}
  	  	return true;
  	  	
  	}
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

	/* Generate preview */
	function preview(){
		var selectedOpt = $('#partNum option:selected');
		var bcText = makeReplacements(selectedOpt.data('bctext'));
		$('#prevImg').attr('src', "barcode.php?code="+bcText+"&size="+(selectedOpt.data('size')+1));
		$('#prevText').html(selectedOpt.data('hritext'));
	}
	$("#partNum").change(preview);
	$('#editForm').submit(function(){
		$('#partNum').removeAttr('disabled');
	});

	function makeReplacements(txt){
		//$patterns = array("/{PartNumber}/", "/{Date1}/", "/{Date2}/", "/{Date3}/", "/{Serial}/");
		//$replacements = array($partDetails['PartNum'], date('ymd'), date('ydm'), date('Ymd'), str_pad((string)$ser, 4, "0", STR_PAD_LEFT));
		var date = new Date();
		var curr_date = ("0" + date.getDate()).slice(-2);
	    var curr_month = ("0" + (date.getMonth() + 1)).slice(-2); //Months are zero based
	    var curr_fullyear = date.getFullYear();
	    var curr_year = curr_fullyear.toString().slice(-2);
	    console.log(curr_year+curr_month+curr_date);
		txt = txt.replace(/{PartNumber}/, $('#partNum option:selected').text());
		txt = txt.replace(/{Serial}/, "0001");
		txt = txt.replace(/{Date1}/, curr_year+curr_month+curr_date);
		txt = txt.replace(/{Date2}/, curr_year+curr_date+curr_month);
		txt = txt.replace(/{Date3}/, curr_fullyear+curr_month+curr_date);
		return txt;
	} 
  </script>
</body>
<?php include("../inc/footer.php")?>
</html>
