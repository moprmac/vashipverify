<?php
class RegexInfo
{
	protected $db;
 
        public function __construct($db)
        {
            $this->db = $db;
        }
		
		public function GetExpressions($regexFilter)
		{
			$expressionList = array();
			if ( is_null($regexFilter) || $regexFilter == "")
			{
				$regexFilter = '';
			}

			$stmt = $this->db->prepare("SELECT RegexID, RegularExpression FROM Regex WHERE RegularExpression like ?");
			if ($stmt->execute(array("%$regexFilter%")))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($expressionList, $row);
				}
				
				return($expressionList);
			}
			else
			{
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function AddExpression($newExpression)
		{
			$grantAccess = false;
			if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
			
			if(!$grantAccess)
			{
				throw new Exception ("Error:  You do not have permission to add expressions.");
			}
			
			if ( is_null($newExpression) || $newExpression == "")
			{
				throw new Exception("Error:  Invalid part number input.");
			}
			
			// check to see if it already exists
			$stmt = $this->db->prepare("SELECT RegexID FROM Regex where RegularExpression = ?");
			if ($stmt->execute(array($newExpression)))
			{
				if ($stmt->rowCount() == 0)
				{
					$stmt = $this->db->prepare("INSERT INTO Regex (RegularExpression) VALUES (?)");
					if ($stmt->execute(array($newExpression)))
					{
						return(array("RegexID" => $this->db->lastInsertId()));
					}
					else
					{
						throw new Exception("Error:  Unable to insert expression $newExpression");
					}
				}
				else
				{
					$row = $stmt->fetch();
					throw new Exception("Error:  Expression $newExpression already exists with ID of " . $row["RegexID"]);
				}
			}
			else
			{
				throw new Exception("Error:  Unable to query expression table.");
			}
		}
}
?>