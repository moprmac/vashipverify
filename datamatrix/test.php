<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  		<link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
  		<link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
  		<link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />

    	<script type="text/javascript" src="../js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="../js/jquery-ui-1.10.2.min.js"></script>
	</head>
<?php include("../inc/header.php")?>
	<form method="post" action="generateLabels.php">
		<label for="BarcodeText"> Barcode Text: <input type="text" name="BarcodeText" /> </label> <br/>
		<label for="HriText"> Human Readable Text: <textarea disabled="disabled" name="HriText" rows=4>35234523
23452345
USA</textarea></label> <br/>
		<label for="size"> Size :
			<select name="size">
				<option>1</option>
				<option>2</option>
			</select>
		</label><br/>
		<label for="NoOfCopies"> Number of Copies: <input type="number" name="NoOfCopies" /></label> <br/>
		<input type="submit" name="Genarate Labels" />
	</form>
	
<?php include("../inc/footer.php")?>
</html>