<?php
include("inc/auth.php");
include("inc/db.php");
include("classes/PlexInfo.class.php");

if(isset($_GET['plexID']) && !empty($_GET['plexID']) && preg_match('/^\d+$/',$_GET['plexID']))
{
	$plexObj = new PlexInfo($db);
	
	try
	{
		$plexInfo = $plexObj->GetStatusByID($_GET['plexID']);
		if ( $plexInfo["Verified"] == 1)
		{
			?>
				<!--ed-->
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
 				<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
				<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
				<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
				<?php include("inc/header.php")?>
				</head>
				<body>
				<!/ed-->

				<center>
				<div class="barcode">
				<p style="margin:0;font-size:50pt;font-weight:bold;color:#000000;letter-spacing:1pt;font-family:impact, sans-serif">Go</p>
				<p style="margin:0"><?=($plexInfo["PlexSerialNum"])?></p>
				<img class"bar"alt="<?=$plexInfo["PlexSerialNum"]?>"src="inc/barcode.php?codetype=Code39&size=30&text=<?=$plexInfo["PlexSerialNum"]?>"/>
				</div>				

				<div class="noprint">
				<br/>
				<button onClick="window.print()">Print</button>
				<br/>
				</div>
				</center>

				<!--ed-->
				</body>
				<?php include("inc/footer.php")?>			
				</html>
				<!--/ed-->

			<?
		}
		else
		{
			// Run verification tool
			$scannedPartCount = $plexObj->GetPartQty($_GET['plexID']);
			//echo("Part Qty: " .  $plexInfo["PartQty"] . " VS " . $scannedPartCount["NumParts"]);
			if ( $plexObj->ValidateParts($_GET['plexID']) and $plexInfo["PartQty"] == $scannedPartCount["NumParts"]) 
			{
				// Update record, set validated = true
				$stmt = $db->prepare("UPDATE PlexRecords SET Verified = 1 WHERE PlexRecordID = ?");
				if (!$stmt->execute(array($_GET['plexID'])))
				{
					throw new Exception("Error:  Unable to update validation code for this record.");
				}
				// add validation timestamp to database
				$stmt = $db->prepare("INSERT INTO PlexVerification (PlexRecord, VerificationUser, PrintTime) values (?, ?, NOW())");
				if (!$stmt->execute(array($_GET['plexID'], $_SESSION['user']['UserID'])))
				{
					throw new Exception("Error:  Unable to update validation timestamp this record.");
				}
				?>
							
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
 				<link rel="stylesheet" type="text/css" href="css/html.css" media="screen, projection, tv " />
				<link rel="stylesheet" type="text/css" href="css/style.css" media="screen, projection, tv" />
				<link rel="stylesheet" type="text/css" href="css/print.css" media="print" />
				<?php include("inc/header.php")?>
				</head>
				<body>
				
				<center>
				<div class="barcode">
				<p style="margin:0;font-size:50pt;font-weight:bold;color:#000000;letter-spacing:1pt;font-family:impact, sans-serif">Go</p>
				<p style="margin:0"><?=($plexInfo["PlexSerialNum"])?></p>
				<img class"bar"alt="<?=$plexInfo["PlexSerialNum"]?>"src="inc/barcode.php?codetype=Code39&size=30&text=<?=$plexInfo["PlexSerialNum"]?>"/>
				</div>

				<div class="noprint">
				<br/>
				<button onClick="window.print()">Print</button>
				<br/>
				</div>
				</center>

				</body>
				<?php include("inc/footer.php")?>
				</html>
				
				<?
			}
			else
			{
				echo("There is an error validating " . $plexInfo["PlexSerialNum"] . ".  Please contact your supervisor to correct.");
			}
		}
	}
	catch (Exception $e)
	{
		die('Caught exception: '.  $e->getMessage(). "\n");
	}
}
?>
