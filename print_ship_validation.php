<?php
include("inc/auth.php");
include("inc/db.php");
include("classes/PlexShippingInfo.class.php");
//include("classes/SendMail.class.php");

if(isset($_GET['shippingID']) && !empty($_GET['shippingID']) && preg_match('/^\d+$/',$_GET['shippingID']) && $_GET['shippingID'] != -1)
{
	$shipObj = new PlexShipInfo($db);
	$shipInfo = $shipObj->GetShippingStatusByID($_GET['shippingID']);
	try
	{
		if (is_null($shipInfo))
		{
			throw new Exception("Error:  No data returned for id " . $_GET['shippingID']);
		}
		
		/*
		if ( $shipInfo["Locked"] == 1)
		{
			throw new Exception("Error:  This shipping record is locked due to an error.  Please see your supervisor.");
		}
		*/
		
		$shipInfo = $shipObj->GetShippingStatusByID($_GET['shippingID']);
		
		// Run verification tool
		$scannedPartCount = $shipObj->GetPartQty($_GET['shippingID']);

		if ( $scannedPartCount['NumParts'] >= 5 and  $shipObj->ValidateParts($_GET['shippingID']) and $shipInfo["Locked"] != 1) 
		{
			// Update record, set validated = true
			$stmt = $db->prepare("UPDATE ShippingRecords SET Verified = 1 WHERE ShippingRecordID = ?");
			if (!$stmt->execute(array($_GET['shippingID'])))
			{
				throw new Exception("Error:  Unable to update validation code for this record.");
			}
			// add validation timestamp to database
			$stmt = $db->prepare("INSERT INTO ShippingVerification (ShippingRecordID, VerificationUser, PrintTime) values (?, ?, NOW())");
			if (!$stmt->execute(array($_GET['shippingID'], $_SESSION['user']['UserID'])))
			{
				throw new Exception("Error:  Unable to update validation timestamp this record.");
			}
			?>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
 			<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
			<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
			<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
			</head>
			<body>
			<?php include("inc/header.php")?>
			
			<center>

				<div class="barcode">
				<p style="margin:0;font-size:35pt;font-weight:bold;color:#000000;letter-spacing:1pt;font-family:impact, sans-serif">Ship Ready</p>
				<p style="margin:0"><?=($shipInfo["PlexSerialNum"])?></p>
				<img class"bar"alt="<?=$shipInfo["PlexSerialNum"]?>"src="inc/barcode.php?codetype=Code39&size=30&text=<?=$shipInfo["PlexSerialNum"]?>"/>
				</div>



<!--			<div class="barcode">
			<img src="images/shipready.png" alt="ShipReady"/><br/>
			<?=$shipInfo["PlexSerialNum"]?><br/>
			<img alt="<?=$shipInfo["PlexSerialNum"]?>" src="inc/barcode.php?codetype=Code39&size=80&text=<?=$shipInfo["PlexSerialNum"]?>" /><br/>
			</div>
-->

			<div class="noprint">
			<button onClick="window.print()">Print</button><br/>
			</div>
			</center>

			</body>
			<?php include("inc/footer.php")?>
			</html>

			<?
		}
		else
		{
			?>
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
 			<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
			<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
			<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
			</head>
			<body>
			<?php include("inc/header.php")?>
			<center>
		
				<div class="barcode">
				<p style="margin:0;font-size:35pt;font-weight:bold;color:#000000;letter-spacing:1pt;font-family:impact, sans-serif">Do Not Ship</p>
				<p style="margin:0"><?=($shipInfo["PlexSerialNum"])?></p>
				<img class"bar"alt="<?=$shipInfo["PlexSerialNum"]?>"src="inc/barcode.php?codetype=Code39&size=30&text=<?=$shipInfo["PlexSerialNum"]?>"/>
				</div>

<!--			<div class="barcode">
			<img src="images/donotship.png" alt="DoNotShip"/><br/>
			<?=$shipInfo["PlexSerialNum"]?><br/>
			<img alt="<?=$shipInfo["PlexSerialNum"]?>" src="inc/barcode.php?codetype=Code39&size=80&text=<?=$shipInfo["PlexSerialNum"]?>" /><br/>
			</div>
-->
			<div class="noprint">
			<button onClick="window.print()">Print</button><br/>
			</div>
			</center>

			</body>
			<?php include("inc/footer.php")?>
			</html>

			<?
			
			//This class includes plexshippinginfo.class.php
			// That class includes plexinfo.php, which includes SendMail.php.  So send mail is already avlalible here.  Probably should 
			// change to RequireOnce rather than include, just for simplicty sake.
			$mailObj = new SendMail($db);
			$mailObj->NotifySupervisor("Error with Shipping Verification", "Record " . $shipInfo["PlexSerialNum"] . " failed to pass shipping verification.  Do Not Ship.");
		}
	}
	catch (Exception $e)
	{
	?>
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
				<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
				<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
				<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
			</head>
			<body>
			<?php include("inc/header.php")?>
			
			<center>
			<div class="barcode">
				<?='Caught exception: '.  $e->getMessage(). "\n"?>
			</div>
			</center>

			</body>
			<?php include("inc/footer.php")?>
			</html>
	<?
		die();
	}
}
else
{
	//throw new Exception("Error:  Invalid shipping ID specified.");
	?>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
		<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
		<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
		<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
	</head>
	<body>
	<?php include("inc/header.php")?>
	
	<center>
	<div class="barcode">
		Unable to print a shipping record for the specified id.  Please go back and check the data.
	</div>
	</center>

	</body>
	<?php include("inc/footer.php")?>
	</html>
	<?
}
?>
