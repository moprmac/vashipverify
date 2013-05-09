<?php
include("SendMail.class.php");

// Class used to interact with and get information back about PlexRecords
class PlexInfo
{
	protected $db;
 
        public function __construct($db)
        {
            $this->db = $db;
        }
		
		public function GetStatusByID($plexID)
		{
			if ( is_null($plexID) || $plexID == "" || !preg_match('/^\d+$/',$plexID))
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT T1.PlexSerialNum, T2.PartNum, T1.PartQty, T1.PartID, T1.Verified, T1.Locked FROM PlexRecords T1 INNER JOIN PlexParts T2 on (T1.PartID = T2.PartID) WHERE T1.PlexRecordID = ?");
			if ($stmt->execute(array($plexID)))
			{
				if ($stmt->rowCount() == 0)
				{
					throw new Exception("Error:  No records returned.");
				}
				
				$row = $stmt->fetch();
				return($row);
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		public function GetStatusBySN($plexSN)
		{
			if ( is_null($plexSN) || $plexSN == "")
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT T1.PlexRecordID, T2.PartNum, T1.PartQty, T1.PartID, T1.Verified, T1.Locked FROM PlexRecords T1 INNER JOIN PlexParts T2 on (T1.PartID = T2.PartID) WHERE T1.PlexSerialNum = ?");
			if ($stmt->execute(array($plexSN)))
			{
				while ($row = $stmt->fetch())
				{
					return($row);
				}
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		public function GetParts($plexID)
		{
			// Modify this section to indicate wheither or not the part matches the partID it's supposed to and the SN isn't a duplicate
			$partList = array();
			if ( is_null($plexID) and $plexID == "")
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT T1.PlexPartRecordID, T2.PartNum as PartNum, T1.PartSerialNum, T1.PartID, T1.Match_, T1.Duplicate, T1.ScanUser, concat(T3.first_name, ' ', T3.last_name) as UserName FROM PlexPartRecords T1 INNER JOIN PlexParts T2 on (T1.PartID = T2.PartID) INNER JOIN UserDB T3 on (T1.ScanUser = T3.UserID) WHERE T1.PlexRecord = ? ORDER BY T1.PlexPartRecordID, T1.PartID, T1.PartSerialNum");
			if ($stmt->execute(array($plexID)))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($partList, $row);
				}
				
				return($partList);
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		public function GetShippingInfo($plexID)
		{
			if ( is_null($plexID) and $plexID == "" and preg_match('/^\d+$/',$plexID))
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT T1.ShippingRecordID, T1.PlexRecord, T2.PartNum, T1.PartQty, T1.PartID, T1.Verified, T1.Locked FROM ShippingRecords T1 INNER JOIN PlexParts T2 on (T1.PartID = T2.PartID) Where T1.PlexRecord = ?");
			if ($stmt->execute(array($plexID)))
			{
				$row = $stmt->fetch();
				// this returns null if nothing happens here.
				return($row);
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		/*
		public function AddPart($plexID, $partInfo)
		{
			// Variables
			$match;
			$duplicate;
			$plexInfo;
			
			if ( is_null($plexID) and $plexID == "" and preg_match('/^\d+$/',$plexID) )
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			if ( is_null($partInfo) and $partInfo == "")
			{
				throw new Exception("Error:  Invalid input for part info value.");
			}
			
			$plexInfo = $this->GetStatusByID($plexID);
			
			if ($plexInfo["Locked"])
			{
				throw new Exception("Error:  This record is locked.  Cannot add new parts.");
			}
			
			if ($plexInfo["Verified"])
			{
				throw new Exception("Error:  This record has already completed 100% verification.  Cannot add new parts.");
			}
			
			$stmt = $this->db->prepare("SELECT RegularExpression FROM Regex INNER JOIN PartRegex ON ( Regex.RegexID = PartRegex.RegexID ) INNER JOIN PlexRecords ON ( PartRegex.PartID = PlexRecords.PartID ) WHERE PlexRecords.PlexRecordID = ?");
			if ($stmt->execute(array($plexID)))
			{
				if ($stmt->rowCount() == 0)
				{
					// no records found
					throw new Exception("Error: No regular expression known for this part type.");
				}
				$row = $stmt->fetch();
				
				preg_match_all("/".$row['RegularExpression']."/", $partInfo, $matches);
				if(is_array($matches) && count($matches) == 1)
				{
					if(is_array($matches{0}) && count($matches{0}) == 2)
					{
						// need to add section to regex respoistory that indicates which is the part and which is the serial#
						$partNum = $matches[0][0];
						$serialNum = $matches[0][1];
						
						// Verify Part# scanned matches Part# for this record
						$stmt = $this->db->prepare("SELECT PartID FROM PlexParts WHERE PartNum = ?");
						if ($stmt->execute(array($partNum)))
						{
							if ($stmt->rowCount() == 0)
							{
								// no records found
								throw new Exception("Error: No part matched known for this part type.");
							}
							$row = $stmt->fetch();
							$partID = $row['PartID'];
							
							// We insert the record regardless.  Just find out if this record is valid.
							if ($plexInfo['PartID'] == $partID)
								$match=1;
							else
								$match=0;
								//throw new Exception("Error: Submitted part number does not match PLEX part.");
							
							// Check for duplicate serial numbers
							$stmt = $this->db->prepare("SELECT PartID, PartSerialNum FROM PlexPartRecords WHERE PlexRecord = ? AND PartID = ? AND PartSerialNum = ?");
							if ($stmt->execute(array($plexID, $partID, $serialNum)))
							{
								if ($stmt->rowCount() == 0)
								{
									// No Duplicates Found
									$duplicate = 0;
								}
								else
								{
									$duplicate = 1;
								}
							}
							
							$stmt = $this->db->prepare("INSERT INTO PlexPartRecords (PlexRecord, ScanUser, ScanTime, PartSerialNum, PartID, Match_, Duplicate) values (?, ?, NOW(), ?, ?, ?, ?)");
							if ($stmt->execute(array($plexID, $_SESSION['user']['UserID'], $serialNum, $partID, $match, $duplicate)))
							{
								$lastInsertID = $this->db->lastInsertId();
								// update plexRecord if $match or $duplicate = 1
								if ($match == 0 || $duplicate == 1)
								{
									$this->LockRecord($plexID);
								}
								// Check Part Qty:
								$numParts = $this->GetPartQty($plexID);
								if ($numParts["NumParts"] > $plexInfo["PartQty"])
								{
									$this->LockRecord($plexID);
								}
								
								
								return($lastInsertID);
							}
							else
							{
								throw new Exception("Error:  Unable to insert record.");
							}
						}
					}
					else
						throw new Exception("Error:  Unable to match input with regular expression.");
				//return(htmlspecialchars($row['RegularExpression']));
				}
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
			//return array($plexID, $partInfo);
			// Get RegEX for PartID listed in PlexRecord where PlexRecordID = CurrentRecord
			// Attempt to decode scanned Input
			// Check if part is valid match against PartID from PlexRecords
			// Check if part serial number already exists in PlexPartRecords
				// All Good:
					// Add Part
				// Not Good:
					// Add Part, Set PlexRecord = Locked
		}
		*/
		
		public function RemovePart($plexID, $partRecNum)
		{
			$grantAccess = false;
			if ( $_SESSION['SupervisorAccess'] == true || $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
			
			if(!$grantAccess)
			{
				throw new Exception ("Error:  You do not have permission to remove records.");
			}
			
			if ( is_null($plexID) || $plexID == "" || !preg_match('/^\d+$/',$plexID) )
			{
				throw new Exception("Error:  Invalid input for part record ID value.");
			}
			
			if ( is_null($partRecNum) || $partRecNum == "" || !preg_match('/^\d+$/',$partRecNum) )
			{
				throw new Exception("Error:  Invalid input for part record ID value.");
			}
			
			$plexInfo = $this->GetStatusByID($plexID);
			
			/*
			if ($plexInfo["Verified"])
			{
				throw new Exception("Error:  This record has already completed 100% verification.  Cannot add remove parts.");
			}
			*/
			

			$stmt = $this->db->prepare("SELECT PlexRecord, ScanUser, ScanTime, PartID, PartSerialNum, Match_, Duplicate FROM PlexPartRecords WHERE PLexRecord = ? AND PlexPartRecordID = ?");
			if ($stmt->execute(array($plexID, $partRecNum)))
			{
				if ($stmt->rowCount() == 0)
				{
					// no records found
					throw new Exception("Error: No match known for this part record.");
				}
				$row = $stmt->fetch();
				
				// Copy into Error Table for history
				$stmt = $this->db->prepare("INSERT INTO PlexPartErrors (PlexRecord, ScanUser, ScanTime, FixUser, FixTime, ErrorPartSerialNum, ErrorPartID, Match_, Duplicate) values (?, ?, ?, ?, ?, ?, ?, ?, ?)");
				if($stmt->execute(array($row["PlexRecord"], $row["ScanUser"], $row["ScanTime"], $_SESSION["user"]["UserID"], date('Y-m-d H:i:s'), $row["PartSerialNum"], $row["PartID"], $row["Match_"], $row["Duplicate"])))
				{
					// Remove invalid record
					$stmt = $this->db->prepare("DELETE FROM PlexPartRecords WHERE PlexPartRecordID = ?");
					if($stmt->execute(array($partRecNum)))
					{
						if($this->ValidateParts($plexID))
						{
							$this->UnlockRecord($plexID);
						}
						return "Success";
					}
					else
					{
						throw new Exception("Error:  Unable to remove selected part record");
					}
				}
				else
				{
					throw new Exception("Error:  Unable to remove selected part record");
				}
				
				// ToDO:  Kickoff the Validate function to see if we can unlock the plex record now.
			}
			else
			{
				throw new Exception("Error:  Unable to query selected part record.");
			}
				
			
			// Insert the data from this PlexPartRecords into PlexPartErrors
			// Remove this record from PlexPartRecords
			// Verify everything everything is copesetic
				// All Copesetic:
					// Unlock PlexRecord where PlexRecordID = CurrentRecord
				// Not Copesetic:
					// Do not unlock
		
		}
		
		public function ValidateParts($plexID)
		{
			if ( is_null($plexID) and $plexID == "" and preg_match('/^\d+$/',$plexID) )
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			$plexInfo = $this->GetStatusByID($plexID);
			
			// Make sure everything matches up on the part ID's.
			// Easier enough to bulk update them.  If the user changes the PartID of the PlexRecord, this'll automagically make all the existing
			// Parts not mach, which we want.
			$stmt = $this->db->prepare("UPDATE PlexPartRecords SET Match_ = 1 WHERE PlexRecord = ? AND PartID = ?");
			if (!$stmt->execute(array($plexID, $plexInfo["PartID"])))
			{
				throw new Exception("Error:  Unable to update Match_ value for selected Plex record parts.");
			}
			
			// bulk update any alternate ID's that match
			$stmt = $this->db->prepare("SELECT CorrespondingPartID FROM PlexPartAlternatives WHERE MainPartID = ?");
			if ($stmt->execute(array($plexInfo["PartID"])))
			{
				while( $row = $stmt->fetch())
				{
					$stmt2 = $this->db->prepare("UPDATE PlexPartRecords SET Match_ = 1 WHERE PlexRecord = ? AND PartID = ?");
					if (!$stmt2->execute(array($plexID, $row["CorrespondingPartID"])))
					{
						throw new Exception("Error:  Unable to update Match_ value for selected Plex record parts.");
					}
				}
			}
			
			// Update the duplicate flag for all records.
			$stmt2 = $this->db->prepare("SELECT PartSerialNum, PartID, Count(*) as NumRemaining FROM PlexPartRecords WHERE PlexRecord = ? GROUP BY PartSerialNum, PartID");
			if ($stmt2->execute(array($plexID)))
			{
				while ($row2 = $stmt2->fetch())
				{
					
					if ($row2["NumRemaining"] == 1)
					{
						// Flagged as duplicate, but only 1 SN exists, update flag.
						$stmt3 = $this->db->prepare("UPDATE PlexPartRecords SET Duplicate = 0 WHERE PlexRecord = ? AND PartID = ? AND PartSerialNum = ?");
						$stmt3->execute(array($plexID, $plexInfo["PartID"], $row2["PartSerialNum"]));
					}
					elseif ($row2["NumRemaining"] > 1)
					{
						$stmt3 = $this->db->prepare("UPDATE PlexPartRecords SET Duplicate = 1 WHERE PlexRecord = ? AND PartID = ? AND PartSerialNum = ?");
						$stmt3->execute(array($plexID, $plexInfo["PartID"], $row2["PartSerialNum"]));
					}
				}
			}
			else
			{
				throw new Exception("Error:  Unable to execute query for part serial numbers.");
			}
			
			$allPartsMatch = false;
			// Make sure all parts are a match.
			// Part list is for our select parts IN PartList statment to check that all parts are valid
			$partList = array();
			// Logic:
			// Using part list lets me build the IN (<Parts>) table.  But I need to pass a single array as the PDO Parameter
			// SO Add the PlexRecordID as the first tiem, then in the Placeholders array, do one less item than the count.
			array_push($partList, $plexID);
			$stmt = $this->db->prepare("SELECT CorrespondingPartID FROM PlexPartAlternatives WHERE MainPartID = ?");
			if ($stmt->execute(array($plexInfo["PartID"])))
			{
				if ($stmt->rowCount() == 0)
				{
					array_push($partList, $plexInfo["PartID"]);
				}
				else
				{
					while($row = $stmt->fetch())
					{
						array_push($partList, $row["CorrespondingPartID"]);
					}
					array_push($partList, $plexInfo["PartID"]);
				}

				$placeholders = array_fill(0, count($partList)-1, '?');
				$stmt = $this->db->prepare("SELECT * FROM PlexPartRecords WHERE PlexRecord = ? AND PartID not in (" . implode(',', $placeholders) . ")");
				if ($stmt->execute($partList))
				{
					if ($stmt->rowCount() == 0)
					{
						$allPartsMatch=true;
					}
				}
				else
				{
					throw new Exception("Error:  Cannot verify part match.");
				}
				
			}
			
			// Lastly, get status
			$stmt = $this->db->prepare("SELECT * FROM PlexPartRecords WHERE (Duplicate = 1 or Match_ = 0) AND PlexRecord = ?");
			if ($stmt->execute(array($plexID)))
			{
				if ($stmt->rowCount() == 0)
				{
					// Looks good, no more invalid records
					// Now check to make sure QTY is correct
					$currentQty = $this->GetPartQty($plexID);
					
					if ($currentQty["NumParts"] <= $plexInfo["PartQty"] && $allPartsMatch)
					{
						return(true);
					}
					else
					{
						return(false);
					}
				}
				else
					return(false);
			}
			else
			{
				throw new Exception("Error:  Unable to query for part status.");
			}
		}
		
		public function UnlockRecord($plexID)
		{
			if ( is_null($plexID) or $plexID == "" or !preg_match('/^\d+$/',$plexID) )
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("UPDATE PlexRecords SET Locked = 0 WHERE PlexRecordID = ?");
			if (!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to update Locked value for selected Plex record.");
			}
			
			return true;
			
		}
		
		public function AddRecord($plexSN, $partNum, $partQty)
		{
			$defaultVal = 0;	// Added for default value for locked & verified as putting the value 0 into the query threw binding errors
			
			if ( is_null($plexSN) or $plexSN == ""  )
			{
				//echo("PlexSN: " . $plexSN . ", PartNum: " . $partNum . ", PartQty: " . $partQty);
				throw new Exception("Error:  Invalid input for Plex serial number.");
			}
			
			if ( !preg_match('/^[sS][0-9]*\b/',$plexSN) )
				throw new Exception("Error:  Invalid input for Plex serial number.");
			
			if ( is_null($partNum) or $partNum == ""  )
				throw new Exception("Error:  Invalid input for part number number.");
			
			if ( !preg_match('/^\d+$/',$partQty) or is_null($partQty) or $partQty == "" )
				throw new Exception("Error:  Invalid input for part quantity.");
			
			if ($partQty < 1 or $partQty > 99)
				throw new Exception("Error:  Invalid input for part quantity.");				
				
			$plexInfo = $this->GetStatusBySN($plexSN);
			
			if ( is_null($plexInfo) )
			{
				// Verify part number exists in records:
				$partID = $this->ValidatePart($partNum);
				if ( is_null($partID) || $partID === false )
				{
					throw new Exception("Error:  Part $partNum does not exist in the database as a known part.");
				}
				
				// Record doesn't exist, add it.
				$stmt = $this->db->prepare("INSERT INTO  PlexRecords(ScanUser, ScanTime, PlexSerialNum, PartQty, PartID, Verified, Locked) VALUES (?, ?, ?, ?, ?, ?, ?)");
				if ( $stmt->execute(array($_SESSION["user"]["UserID"], date('Y-m-d H:i:s'), $plexSN, $partQty, $partID["PartID"], $defaultVal, $defaultVal)) )
				{
					// return ID:
					return(array("PlexRecordID" => $this->db->lastInsertId()));
				}
				else
				{
					throw new Exception("Error:  Error when inserting record into database.");
				}
			}
			else
			{
				// Record already exists, make sure all the attributes match up and return ID
				if ( $plexInfo["PartNum"] == $partNum and $plexInfo["PartQty"] == $partQty)
				{
					return(array("PlexRecordID" => $plexInfo["PlexRecordID"]));
				}
				else
				{
					throw new Exception("Error:  Record already exists for Plex Serial $plexSN with different attributes.");
				}
			}
		}
		
		/*  This function check the part list to ensure the partSN given exists */
		private function ValidatePart($partSN)
		{
			if ( is_null($partSN) and $partSN == "" )
			{
				throw new Exception("Error:  Invalid input for Part Serial Number value.");
			}
			
			$stmt = $this->db->prepare("SELECT PartID FROM PlexParts WHERE PartNum = ? AND Active = 1");
			if ($stmt->execute(array($partSN)))
			{
				if ($stmt->rowCount() == 0)
				{
					// no matching record found
					return(false);
				}
				else
				{
					$row = $stmt->fetch();
					return($row);
				}
			}
			else
			{
				return(false);
			}
		}
		
		public function GetPartQty($plexID)
		{
			if ( !preg_match('/^\d+$/',$plexID) || is_null($plexID) || $plexID == "" )
			{
				throw new Exception("Error:  Invalid input for Part ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT count(*) as NumParts FROM PlexPartRecords WHERE PlexRecord = ?");
			if ($stmt->execute(array($plexID)))
			{
				if ($stmt->rowCount() == 0)
				{
					return(array("NumParts" => 0));
				}
				else
				{
					return($stmt->fetch());
				}
			}
			else
			{
				throw new Exception("Error:  Unable to query for part quantity.");
			}
		}
		
		public function LockRecord($plexID)
		{
			if ( !preg_match('/^\d+$/',$plexID) || is_null($plexID) || $plexID == "" )
			{
				throw new Exception("Error:  Invalid input for Part ID value.");
			}
		
			$stmt = $this->db->prepare("UPDATE PlexRecords SET Locked = 1 WHERE PlexRecordID = ?");
			$stmt->execute(array($plexID));
			
			// send email:
			$plexInfo = $this->GetStatusByID($plexID);
			$mailObj = new SendMail($this->db);
			$mailObj->NotifySupervisor("Locked Record", "Internal Record " . $plexInfo["PlexSerialNum"] . " has been locked due to an error.");
			
		}
		
		public function DeleteRecord($plexID)
		{
			$grantAccess = false;
			if ( $_SESSION['SupervisorAccess'] == true || $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
			
			if(!$grantAccess)
			{
				throw new Exception ("Error:  You do not have permission to remove records.");
			}
			
			if ( !preg_match('/^\d+$/',$plexID) || is_null($plexID) || $plexID == "" )
			{
				throw new Exception("Error:  Invalid input for Part ID value.");
			}
			
			// step 1:
			
			/*
			PlexRecords
			PlexVerification
			PlexRecordErrors (currently not in use)
			PlexPartRecords
			PlexPartErrors
			
			ShippingRecords (Get ShippingID from this)
			ShippingRecordParts
			ShippingRecordErrors (currently not in use)
			ShippingPartErrors
			ShippingVerification
			*/
			
			$shippingInfo = null;
			
			$stmt = $this->db->prepare("SELECT ShippingRecordID FROM ShippingRecords WHERE PlexRecord = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexPartErrors");
			}
			else
			{
				$shippingInfo = $stmt->fetch();
				//return($shippingInfo);
			}
			
			
			$stmt = $this->db->prepare("DELETE FROM PlexPartErrors WHERE PlexRecord = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexPartErrors");
			}
			
			$stmt = $this->db->prepare("DELETE FROM PlexRecordErrors WHERE PlexRecord = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexRecordErrors");
			}
			
			$stmt = $this->db->prepare("DELETE FROM PlexPartRecords WHERE PlexRecord = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexPartRecords");
			}
			
			$stmt = $this->db->prepare("DELETE FROM PlexVerification WHERE PlexRecord = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexVerification");
			}
			
			if (!is_null($shippingInfo))
			{
				$stmt = $this->db->prepare("DELETE FROM ShippingPartErrors WHERE ShippingRecordID = ?");
				if(!$stmt->execute(array($shippingInfo['ShippingRecordID'])))
				{
					throw new Exception("Error:  Unable to delete from ShippingPartErrors");
				}
				
				$stmt = $this->db->prepare("DELETE FROM ShippingRecordErrors WHERE ShippingRecordID = ?");
				if(!$stmt->execute(array($shippingInfo['ShippingRecordID'])))
				{
					throw new Exception("Error:  Unable to delete from ShippingRecordErrors");
				}
				
				$stmt = $this->db->prepare("DELETE FROM ShippingRecordParts WHERE ShippingRecordID = ?");
				if(!$stmt->execute(array($shippingInfo['ShippingRecordID'])))
				{
					throw new Exception("Error:  Unable to delete from ShippingRecordParts");
				}
				
				$stmt = $this->db->prepare("DELETE FROM ShippingVerification WHERE ShippingRecordID = ?");
				if(!$stmt->execute(array($shippingInfo['ShippingRecordID'])))
				{
					throw new Exception("Error:  Unable to delete from ShippingVerification");
				}
				
				$stmt = $this->db->prepare("DELETE FROM ShippingRecords WHERE ShippingRecordID = ?");
				if(!$stmt->execute(array($shippingInfo['ShippingRecordID'])))
				{
					throw new Exception("Error:  Unable to delete from ShippingRecords");
				}
			}
			
			$stmt = $this->db->prepare("DELETE FROM PlexRecords WHERE PlexRecordID = ?");
			if(!$stmt->execute(array($plexID)))
			{
				throw new Exception("Error:  Unable to delete from PlexRecords");
			}
			
			
			return(true);
		}
		
		/*  This ValidateAlternate checks to see if the alternate part ID's exist */
		/* Validate alternate works by the following:
			1:  The regex for the internal part is generic enough to match all alternates:
			2:	There is a table that has the correlation between the internal and alternate part ID's:
			3:  After decoding the part, we verify that the PartID exists in that list
			
			Please note, in the examples below, thse patterns I enter are just guesstimates, not verifed perfectly correct
			
			Example:
			Internal Part: 1234
			We know we'll have customer parts T1234, F1234, G1234 produced from it
			Our regex is then [tTfFgG]1[0-9]*  
				AKA:  Look for a pattern that has a single letter of  T, G, or F followed by any amount of numbers
			Our Parts table will have 4 entires.
				PartID	PartNumber		Pattern:
				1		1234			tTfFgG]1[0-9]*
				2		T1234			null
				3		F1234			null
				4		G1234			null
			Our Alternate Table will have a join between the two
				PartID	Alternate
				1		2
				1		3
				1		4
			
			While working with a plex record where the PartID = 1
			When we scan T1234;S0000001.  We decode it using the regex and get Part: T1234 Serial# S0000001
			We check the database to see what the PartID for T1234 is and get PartID: 2
			Normally we'd throw an error as PartID for the Plex Record is 1, and the scanned PartID is 2
			However, we check the alternate table and see if PartID: 2 is a valid match for PartID: 1
			If it is, we add it.
			
			The key to this method is knowing what every part type will be and all alternate parts, and ensuring
			the patterns closely match.  Also, ideally we want to never have a part that's S1234; because our 
			serial numbers also start with the letter S, so you could get too many matches.
			
			This breaks down if Our Alternate Parts don't closely match our inital Regex.
			Example:
			Internal Part:	1234
			Customer Parts:	T1234, F1234, AAFQ-1127-A
			There isn't a way to write a regex that will match both T1234 and AAFQ-1127-A.
			
			To make that method work, each part needs it's own expression.
			Our Parts table will have 4 entires.
				PartID	PartNumber		Pattern:
				1		1234			null
				2		T1234			[tT]1[0-9]*
				3		F1234			[fF]1[0-9]*
				4		AAFQ-1127-A		[aZ]4-[0-9]4-[aZ]
			Our Alternate Table will have a join between the two
				PartID	Alternate
				1		2
				1		3
				1		4
			
			Now when the user scans a part we kick off a loop that goes through each Alternate Part
			We attempt to decode the user input (EX: AAFQ-1127-A;S0000001)
			If the pattern matches, we get the ID of the match and add that to the part list.
			If no pattern matches, we return a fail code.
				IE:	[tT]1[0-9]* vs AAFQ-1127-A;S0000001 == no match
					[fF]1[0-9]* vs AAFQ-1127-A;S0000001 == no match
					[aZ]4-[0-9]4-[aZ] vs AAFQ-1127-A;S0000001 == match, PartID: 4, Serial#: S0000001
			
			The only risk here is if the patterns between the alternate parts are too simular, you could get the wrong
			partID returned.  It shoudl theroitcally be OK, as it's still a valid alternate, but the data would be bad.
				IE:	[aZ]1[0-9]* vs F1234;S0000001 == erronious match, returns PartID:1 Serial S0000001
					[fF]1[0-9]* vs F1234;S0000001 == Correct Match, but never get here
					[aZ]4-[0-9]4-[aZ] vs AAFQ-1127-A;S0000001 == no match
		*/
		
		private function ValidAlternate($plexID, $partID)
		{
			if ( !preg_match('/^\d+$/',$plexID) or is_null($plexID) or $plexID == "" )
			{
				throw new Exception("Error:  Unable to find valid part alternatives for $plexID");
			}
			
			if ( !preg_match('/^\d+$/',$partID) or is_null($partID) or $partID == "" )
			{
				throw new Exception("Error:  Unable to find valid part alternatives for $partID");
			}
			
			$stmt = $this->db->prepare("SELECT * FROM PlexRecords T1 INNER JOIN PlexPartAlternatives T2 on (T1.PartID = T2.MainPartID) WHERE T1.PlexRecordID = ? AND CorrespondingPartID = ?");
			if ( $stmt->execute(array($plexID, $partID)) )
			{
				if ($stmt->rowCount() == 0)
				{
					// not a valid match, or no valid matches
					return(false);
				}
				else
				{
					return(true);
				}
			}
			else
			{
				throw new Exception("Error:  Unable to search for alternate parts for record $plexID and part $partID");
			}
		}
		
		public function Foo($bar)
		{
			// send email:
			//$plexInfo = $this->GetStatusByID($plexID);
			$mailObj = new SendMail($this->db);
			//$mailObj->NotifySupervisor("Locked Record", "Internal Record 999999 has been locked due to an error.");
		}
		
		// optional variable is added here so we can allow it to add a part after 100% scan is complete.  Useful
		// for replace part.  Otherwise, disallow adding parts after 100% scan is complete.
		public function AddPart($plexID, $partInfo, $override=false)
		{
			// Variables
			$match = 1;
			$duplicate;
			$plexInfo;
			
			if ( is_null($plexID) and $plexID == "" and preg_match('/^\d+$/',$plexID) )
			{
				$this->InsertError($plexID, $partInfo, "Invalid input for Plex Record ID value in Add Part");
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			if ( is_null($partInfo) and $partInfo == "")
			{
				$this->InsertError($plexID, $partInfo, "Invalid input for part info value in Add Part");
				throw new Exception("Error:  Invalid input for part info value.");
			}
			
			$plexInfo = $this->GetStatusByID($plexID);
			
			if ($plexInfo["Locked"])
			{
				throw new Exception("Error:  This record is locked.  Cannot add new parts.");
			}
			
			if (!$override)
			{
				if ($plexInfo["Verified"])
				{
					throw new Exception("Error:  This record has already completed 100% verification.  Cannot add new parts.");
				}
			}
			
			
			// Are there alternate parts?
			$stmt = $this->db->prepare
				("SELECT
					T1.PartID, T2.CorrespondingPartID, T4.RegularExpression
				FROM
					PlexParts T1
					INNER JOIN PlexPartAlternatives T2 on (T1.PartID = T2.MainPartID)
					INNER JOIN PartRegex T3 on (T2.CorrespondingPartID = T3.PartID)
					INNER JOIN Regex T4 on (T3.RegexID = T4.RegexID)
				WHERE
					T1.PartID = ?
					AND T1.Active = 1
					AND T2.Active = 1
				ORDER BY
					T1.PartID, T2.CorrespondingPartID");
			
			$decodedPart = false;
			if ($stmt->execute(array($plexInfo["PartID"])))
			{
				// If there are no altenrates, primary Regex
				if ($stmt->rowCount() == 0)
				{
					// Get regex for part directly
					$stmt = $this->db->prepare("SELECT RegularExpression FROM Regex INNER JOIN PartRegex ON ( Regex.RegexID = PartRegex.RegexID ) INNER JOIN PlexRecords ON ( PartRegex.PartID = PlexRecords.PartID ) WHERE PlexRecords.PlexRecordID = ?");
					$row = $stmt->fetch();
					if ($stmt->rowCount() == 0)
					{
						// no records found
						$this->InsertError($plexID, $partInfo, "Unable to match to regular expression for part type");
						throw new Exception("Error: No regular expression known for this part type.");
					}
					$decodedPart = $this->DecodePartInfo($row['RegularExpression'], $partInfo);
				}
				else
				{
					// If there are alternates, use their Regex, disregard the primary
					// Get regex for alternate parts
					while ($row = $stmt->fetch())
					{
						$decodedPart = $this->DecodePartInfo($row['RegularExpression'], $partInfo);
						if ($decodedPart !== false)
						{
							// Have a match, exit out of loop
							break;
						}
					}
				}
				
				if ($decodedPart !== false)
				{
					// add part
					$partID = $decodedPart[0];
					$partNum = $decodedPart[1];
					$serialNum = $decodedPart[2];
					// Check for duplicate serial numbers
					$stmt = $this->db->prepare("SELECT PartID, PartSerialNum FROM PlexPartRecords WHERE PlexRecord = ? AND PartID = ? AND PartSerialNum = ?");
					if ($stmt->execute(array($plexID, $partID, $serialNum)))
					{
						if ($stmt->rowCount() == 0)
						{
							// No Duplicates Found
							$duplicate = 0;
						}
						else
						{
							$duplicate = 1;
						}
						
						// make sure it's the right partID
						$validpart = false;
						if ($partID == $plexInfo["PartID"] || $this->ValidAlternate($plexID, $partID))
						{
							$validpart = true;
						}
						
						if (!$validpart)
						{
							$this->InsertError($plexID, $partInfo, "Part $partNum with Part ID $partID is not a valid match.");
							throw new Exception("Error:  Part $partNum with Part ID $partID is not a valid match.");
						}
						
						$stmt = $this->db->prepare("INSERT INTO PlexPartRecords (PlexRecord, ScanUser, ScanTime, PartSerialNum, PartID, Match_, Duplicate) values (?, ?, ?, ?, ?, ?, ?)");
						if ($stmt->execute(array($plexID, $_SESSION['user']['UserID'], date('Y-m-d H:i:s'), $serialNum, $partID, $match, $duplicate)))
						{
							$lastInsertID = $this->db->lastInsertId();
							// update plexRecord if $match or $duplicate = 1
							if ($match == 0 || $duplicate == 1)
							{
								$this->LockRecord($plexID);
							}
							// Check Part Qty:
							$numParts = $this->GetPartQty($plexID);
							if ($numParts["NumParts"] > $plexInfo["PartQty"])
							{
								$this->LockRecord($plexID);
							}
							
							
							return($lastInsertID);
						}
						else
						{
							throw new Exception("Error:  Unable to insert record.");
						}
					}
				}
				else
				{
					$this->InsertError($plexID, $partInfo, "Unable to match this part info to the list of valid parts.");
					throw new Exception("Error:  Unable to match this part info to the list of valid parts.");
				}
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		private function DecodePartInfo($regularExpression, $partInfo)
		{
			$matches = "";
			
			preg_match_all("/".$regularExpression."/", $partInfo, $matches);
			if(is_array($matches) && count($matches) == 1)
			{
				if(is_array($matches{0}) && count($matches{0}) == 2)
				{
					// need to add section to regex respoistory that indicates which is the part and which is the serial#
					$partNum = $matches[0][0];
					$serialNum = $matches[0][1];
					
					// Verify Part# scanned matches Part# for this record
					$stmt = $this->db->prepare("SELECT PartID FROM PlexParts WHERE PartNum = ?");
					if ($stmt->execute(array($partNum)))
					{
						if ($stmt->rowCount() == 0)
						{
							// no records found
							//throw new Exception("Error: No part matched known for this part type.");
							return(false);
						}
						$row = $stmt->fetch();
						$partID = $row['PartID'];
						
						return(array($partID, $partNum, $serialNum));
					}
					else
					{
						throw new Exception("Error:  Unable to query for PartID where for PartNum $partNum");
					}
				}
				else { return(false); }
			}
			else { return(false); }
		}
		
		public function InsertError($plexID, $partInfo, $reason)
		{
			$stmt = $this->db->prepare("INSERT INTO PlexInvalidEntryLog (PlexRecord, PartInfo, ScanUser, ScanTime, ErrorReason) VALUES (?, ?, ?, ?, ?)");
			if ($stmt->execute(array($plexID, $partInfo, $_SESSION['user']['UserID'], date('Y-m-d H:i:s'), $reason)))
			{
				$this->LockRecord($plexID);
			}
			else
			{
				throw new Exception("Error:  Unable to log error.");
			}
		}
		
		public function GetInsertErrors($plexID)
		{
			$errorList = array();
			if ( is_null($plexID) and $plexID == "")
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			$stmt = $this->db->prepare("SELECT t1.PlexRecord, t1.PartInfo, t1.ScanUser, concat(t2.first_name, ' ', t2.last_name) as UserName, t1.ScanTime, t1.ErrorReason FROM PlexInvalidEntryLog t1 inner join UserDB t2 on (t1.ScanUser = t2.UserID)  WHERE PlexRecord = ?");
			if ($stmt->execute(array($plexID)))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($errorList, $row);
				}
				
				return($errorList);
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		
		}
		
		public function SearchParts($plexID, $partInfo)
		{
			if ( is_null($plexID) and $plexID == "" and preg_match('/^\d+$/',$plexID) )
			{
				$this->InsertError($plexID, $partInfo, "Invalid input for Plex Record ID value in Add Part");
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			if ( is_null($partInfo) and $partInfo == "")
			{
				$this->InsertError($plexID, $partInfo, "Invalid input for part info value in Add Part");
				throw new Exception("Error:  Invalid input for part info value.");
			}
			
			$plexInfo = $this->GetStatusByID($plexID);
			
			// Are there alternate parts?
			$stmt = $this->db->prepare
				("SELECT
					T1.PartID, T2.CorrespondingPartID, T4.RegularExpression
				FROM
					PlexParts T1
					INNER JOIN PlexPartAlternatives T2 on (T1.PartID = T2.MainPartID)
					INNER JOIN PartRegex T3 on (T2.CorrespondingPartID = T3.PartID)
					INNER JOIN Regex T4 on (T3.RegexID = T4.RegexID)
				WHERE
					T1.PartID = ?
					AND T1.Active = 1
					AND T2.Active = 1
				ORDER BY
					T1.PartID, T2.CorrespondingPartID");
			
			$decodedPart = false;
			if ($stmt->execute(array($plexInfo["PartID"])))
			{
				// If there are no altenrates, primary Regex
				if ($stmt->rowCount() == 0)
				{
					// Get regex for part directly
					$stmt = $this->db->prepare("SELECT RegularExpression FROM Regex INNER JOIN PartRegex ON ( Regex.RegexID = PartRegex.RegexID ) INNER JOIN PlexRecords ON ( PartRegex.PartID = PlexRecords.PartID ) WHERE PlexRecords.PlexRecordID = ?");
					$row = $stmt->fetch();
					if ($stmt->rowCount() == 0)
					{
						// no records found
						$this->InsertError($plexID, $partInfo, "Unable to match to regular expression for part type");
						throw new Exception("Error: No regular expression known for this part type.");
					}
					$decodedPart = $this->DecodePartInfo($row['RegularExpression'], $partInfo);
				}
				else
				{
					// If there are alternates, use their Regex, disregard the primary
					// Get regex for alternate parts
					while ($row = $stmt->fetch())
					{
						$decodedPart = $this->DecodePartInfo($row['RegularExpression'], $partInfo);
						if ($decodedPart !== false)
						{
							// Have a match, exit out of loop
							break;
						}
					}
				}
				
				if ($decodedPart !== false)
				{
					// add part
					$partID = $decodedPart[0];
					$partNum = $decodedPart[1];
					$serialNum = $decodedPart[2];
					// Check for duplicate serial numbers
					$stmt = $this->db->prepare("SELECT PartID, PartSerialNum FROM PlexPartRecords WHERE PlexRecord = ? AND PartID = ? AND PartSerialNum = ?");
					if ($stmt->execute(array($plexID, $partID, $serialNum)))
					{
						if ($stmt->rowCount() == 0)
						{
							// No Duplicates Found
							return array("Found" => false);
						}
						else
						{
							return array("Found" => true);
						}
					}
				}
				else
				{
					throw new Exception("Error:  Unable to match this part info to the list of valid parts.");
				}
			}
			else
			{
				throw new Exception("Error: Unable to execute query.");
			}
		}
		
		public function ReplacePart($plexID, $oldPartID, $newPartInfo, $replaceReason)
		{
		
			if ( is_null($plexID) || $plexID == "" || !preg_match('/^\d+$/',$plexID) )
			{
				throw new Exception("Error:  Invalid input for ID value.");
			}
			
			if ( is_null($oldPartID) || $oldPartID == "" || !preg_match('/^\d+$/',$oldPartID) )
			{
				throw new Exception("Error:  Invalid input for old part id value.");
			}
			
			if ( is_null($newPartInfo) || $newPartInfo == "")
			{
				throw new Exception("Error:  Invalid input for part info value.");
			}
			
			if ( is_null($replaceReason) )
			{
				$replaceReason = "";
			}
			
			// Get info about the part being removed:
			$stmt = $this->db->prepare("SELECT PartSerialNum, ScanUser, ScanTime FROM PlexPartRecords WHERE PlexRecord = ? AND PlexPartRecordID = ?");
			if ($stmt->execute(array($plexID, $oldPartID)))
			{
				if ($stmt->rowCount() != 0)
				{
					$row = $stmt->fetch();

					// Insert dirty record
					$stmt = $this->db->prepare("INSERT INTO PlexInvalidEntryLog (PlexRecord, PartInfo, ScanUser, ScanTime, ErrorReason) VALUES (?, ?, ?, ?, ?)");
					if (!$stmt->execute(array($plexID, $row["PartSerialNum"], $row["ScanUser"], $row["ScanTime"], $replaceReason)))
					{
						throw new Exception("Error:  Unable to log error.");
					}
			
					// remove the old part
					try
					{
						$this->RemovePart($plexID, $oldPartID);
					}
					catch (Exception $e)
					{
						throw new Exception($e->getMessage());
					}
				
					// add the new part
					try
					{
						$this->AddPart($plexID, $newPartInfo, true);
					}
					catch (Exception $e)
					{
						throw new Exception($e->getMessage());
					}
					
					return(array("ReplaceStatus" => true));
				}
				else
				{
					throw new Exception("Error:  Unable to find part to be replaced.");
				}
			}
			else
			{
				throw new Exception("Error:  Unable to get info about part id:" . $oldPartID);
			}
		}
}

?>
