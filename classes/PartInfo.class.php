<?php
class PartInfo
{
	protected $db;
 
        public function __construct($db)
        {
            $this->db = $db;
        }
		
		public function GetPartByID($partID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid part ID");
			}
			
			$stmt = $this->db->prepare("SELECT T1.PartID, T1.PartNum, T1.PartAdded as DateAdded, T1.Active, T1.PartRetired as DateDeactivated, T3.RegularExpression FROM PlexParts T1 LEFT OUTER JOIN PartRegex T2 on (T1.PartID = T2.PartID) LEFT OUTER JOIN Regex T3 on (T2.RegexID = T3.RegexID) WHERE T1.PartID = ?");
			if ($stmt->execute(array($partID)))
			{
				return($stmt->fetch());
			}
			else
			{
				throw new Exception("Error:  Unable to look up information for part ID: $partID");
			}
		}
		
		public function GetParts($partNum)
		{
			$partList = array();
			if ( is_null($partNum) || $partNum == "")
			{
				$partNum = '';
			}

			$stmt = $this->db->prepare("SELECT PartID, PartNum, PartAdded as DateAdded, Active FROM PlexParts WHERE PartNum like ?");
			if ($stmt->execute(array("$partNum%")))
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
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function GetAllParts() {
			$partList = array();
			$stmt = $this->db->prepare("SELECT PartID, PartNum from PlexParts WHERE Active = 1 ORDER BY PartNum");
			if($stmt->execute()){
				while($row = $stmt->fetch()){
					array_push($partList, $row);
				}
			}
			return $partList;
		}
		
		public function GetAllInternalParts() {
			$partList = array();
			$stmt = $this->db->prepare("SELECT PartID, PartNum from PlexParts WHERE Active = 1 and IntPartNum = 1 ORDER BY PartNum");
			if($stmt->execute()){
				while($row = $stmt->fetch()){
					array_push($partList, $row);
				}
			}
			return $partList;
		}
		
		public function AddPart($partNum)
		{
			$grantAccess = false;
			if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
			
			if(!$grantAccess)
			{
				throw new Exception ("Error:  You do not have permission to add parts.");
			}
			
			if ( is_null($partNum) || $partNum == "")
			{
				throw new Exception("Error:  Invalid part number input.");
			}
			
			if (!ctype_alnum($partNum))
			{
				throw new Exception("Error:  Invalid part input.  Part must be alphanumeric (a-Z 0-9)");
			}
			
			// check to see if it already exists
			$stmt = $this->db->prepare("SELECT PartID FROM PlexParts where PartNum = ?");
			if ($stmt->execute(array($partNum)))
			{
				if ($stmt->rowCount() == 0)
				{
					$stmt = $this->db->prepare("INSERT INTO PlexParts (PartNum, PartAdded, Active) VALUES (?, NOW(), 1)");
					if ($stmt->execute(array($partNum)))
					{
						return(array("PartID" => $this->db->lastInsertId()));
					}
					else
					{
						throw new Exception("Error:  Unable to insert part number $partNum");
					}
				}
				else
				{
					$row = $stmt->fetch();
					throw new Exception("Error:  Part $partNum already exists with ID of " . $row["PartID"]);
				}
			}
			else
			{
				throw new Exception("Error:  Unable to query parts table.");
			}
		}
		
		public function GetExpressions($regexFilter)
		{
			$regexList = array();
			if ( is_null($regexFilter) || $regexFilter == "")
			{
				$regexFilter = '';
			}

			$stmt = $this->db->prepare("SELECT RegexID, RegularExpression FROM Regex WHERE RegularExpression like ?");
			if ($stmt->execute(array("$regexFilter%")))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($regexList, $row);
				}
				
				return($regexList);
			}
			else
			{
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function UpdateRegex($partID, $regexID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid part ID");
			}
			
			if ( is_null($regexID) || $regexID == "" || !preg_match('/^\d+$/',$regexID))
			{
				throw new Exception("Error:  Invalid regex ID");
			}
			
			$stmt = $this->db->prepare("SELECT PlexRegexID FROM PartRegex WHERE PartID = ?");
			if ($stmt->execute(array($partID)))
			{
				if ($stmt->rowCount() == 0)
				{
					$stmt = $this->db->prepare("INSERT INTO PartRegex (PartID, RegexID) VALUES (?, ?)");
					if (!$stmt->execute(array($partID, $regexID)))
					{
						throw new Exception("Error:  Unable to add new regular expression for partID: $partID");
					}
				}
				elseif ($stmt->rowCount() == 1)
				{
					$stmt = $this->db->prepare("UPDATE PartRegex SET RegexID = ? WHERE PartID = ?");
					if (!$stmt->execute(array($regexID, $partID)))
					{
						throw new Exception("Error:  Unable to update regular expression for partID: $partID");
					}
					
				}
				else
				{
					throw new Exception("Error:  More than one regular expression exists for part $partID");
				}
				
				// Return the new expression.
				$stmt = $this->db->prepare("SELECT RegularExpression as NewRegex FROM Regex WHERE RegexID = ?");
				if ($stmt->execute(array($regexID)))
				{
					return($stmt->fetch());
				}
				else
				{
					throw new Exception("Error:  Unable to get new regular expression for regexID: $regexID");
				}
			}
			else
			{
				throw new Exception("Error:  Unable to update regex.");
			}
		}
		
		public function ActivatePart($partID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid part ID");
			}
			
			$stmt = $this->db->prepare("Update PlexParts SET Active = 1, PartRetired = null WHERE PartID = ?");
			if ($stmt->execute(array($partID)))
			{
				return(true);
			}
			else
			{
				throw new Exception("Error:  Unable to update active/inactive status for part ID: $partID");
			}
		}
		
		public function RetirePart($partID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid part ID");
			}
			
			$stmt = $this->db->prepare("Update PlexParts SET Active = 0, PartRetired = NOW() WHERE PartID = ?");
			if ($stmt->execute(array($partID)))
			{
				return(true);
			}
			else
			{
				throw new Exception("Error:  Unable to update active/inactive status for part ID: $partID");
			}
		}
		
		public function GetAlternateParts($partID)
		{
			$partList = array();
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid PartID.");
			}

			$stmt = $this->db->prepare("SELECT T2.PartID, T2.PartNum, T2.PartAdded as DateAdded, T2.Active FROM `PlexPartAlternatives` T1 INNER JOIN `PlexParts` T2 ON (T1.CorrespondingPartID = T2.PartID) WHERE T1.MainPartID = ?");
			if ($stmt->execute(array($partID)))
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
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function AddAlternatePart($partID, $altPartID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid PartID.");
			}
			
			if ( is_null($altPartID) || $altPartID == "" || !preg_match('/^\d+$/',$altPartID))
			{
				throw new Exception("Error:  Invalid Alternate PartID.");
			}
			
			$stmt = $this->db->prepare("SELECT * FROM PlexPartAlternatives WHERE MainPartID = ? AND CorrespondingPartID = ?");
			if ($stmt->execute(array($partID, $altPartID)))
			{
				if ($stmt->rowCount() == 0)
				{
					// no duplicate, add
					$stmt = $this->db->prepare("INSERT INTO PlexPartAlternatives (MainPartID, CorrespondingPartID, Active, InstantiateDate) VALUES (?, ?, 1, NOW())");
					if ($stmt->execute(array($partID, $altPartID)))
					{
						return(true);
					}
					else
					{
						return(false);
					}
				}
				else
				{
					throw new Exception("Error:  That part already exists as an alternate part.");
				}
			}
			else
			{
				throw new Exception("Error:  Unable to add alternate part.");
			}
		}
		
		public function RemoveAlternatePart($partID, $altPartID)
		{
			if ( is_null($partID) || $partID == "" || !preg_match('/^\d+$/',$partID))
			{
				throw new Exception("Error:  Invalid PartID.");
			}
			
			if ( is_null($altPartID) || $altPartID == "" || !preg_match('/^\d+$/',$altPartID))
			{
				throw new Exception("Error:  Invalid Alternate PartID.");
			}
			
			$stmt = $this->db->prepare("DELETE FROM PlexPartAlternatives WHERE MainPartID = ? AND CorrespondingPartID = ?");
			if ($stmt->execute(array($partID, $altPartID)))
			{
				return(true);
			}
			else
			{
				return(false);
			}
		}
}