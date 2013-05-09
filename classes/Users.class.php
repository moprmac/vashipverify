<?php
class Users
{
	protected $db;
 
        public function __construct($db)
        {
            $this->db = $db;
        }
		
		public function GetUsers($userName)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
			
			$userList = array();
			if ( is_null($userName) || $userName == "")
			{
				$userName = '';
			}

			$stmt = $this->db->prepare("SELECT UserID, login, first_name, last_name, email, Active FROM UserDB WHERE login like ?");
			if ($stmt->execute(array("$userName%")))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($userList, $row);
				}
				
				return($userList);
			}
			else
			{
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function GetUserByID($userID)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
			
			if ( is_null($userID) || $userID == "" || !preg_match('/^\d+$/',$userID))
			{
				throw new Exception("Error:  Invalid UserID");
			}
			
			$stmt = $this->db->prepare("SELECT UserID, login, first_name, last_name, email, Active FROM UserDB WHERE UserID = ?");
			if ($stmt->execute(array($userID)))
			{
				return($stmt->fetch());
			}
			else
			{
				throw new Exception("Error:  Unable to query for user.");
			}
		}
		
		public function GetUserByUsername($userName)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
			
			if ( is_null($userName) || $userName == "")
			{
				throw new Exception("Error:  Invalid login");
			}
			
			$stmt = $this->db->prepare("SELECT UserID, login, first_name, last_name, email, Active FROM UserDB WHERE login = ?");
			if ($stmt->execute(array($userName)))
			{
				return($stmt->fetch());
			}
			else
			{
				throw new Exception("Error:  Unable to query for user.");
			}
		}
		
		public function GetUserAccess($userID)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}

			$userAccessList = array();
			if ( is_null($userID) || $userID == "" || !preg_match('/^\d+$/',$userID))
			{
				throw new Exception("Error:  Invalid UserID");
			}

			$stmt = $this->db->prepare("SELECT T1.UserID, T1.StatusID, T2.Status FROM UserAccess T1 INNER JOIN AccessLevels T2 on (T1.StatusID = T2.StatusID)  WHERE T1.UserID =  ?");
			if ($stmt->execute(array($userID)))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($userAccessList, $row);
				}
				
				return($userAccessList);
			}
			else
			{
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function ChangeUserPassword($userID, $password)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
		}
		
		public function SetUserPrivelages($userID, $accessID)
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
		}
		
		public function ListAccessLevels()
		{
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
			
			$userAccessList = array();

			$stmt = $this->db->prepare("SELECT StatusID, Status FROM AccessLevels");
			if ($stmt->execute(array($userID)))
			{
				// PDO::FETCH_ASSOC == Return as associatiave array, should do this by default based on db.inc
				while ($row = $stmt->fetch())
				{
					array_push($userAccessList, $row);
				}
				
				return($userAccessList);
			}
			else
			{
				throw new Exception("Error: Unable to execute part list query.");
			}
		}
		
		public function UpdateUser($userID, $firstName, $lastName, $email, $newPassword, $active, $access)
		{
			
			if (!$this->CheckAccess())
			{
				throw new Exception("Error:  You do not have permission to perform this action.");
			}
			
			if ( is_null($userID) || $userID == "" || !preg_match('/^\d+$/',$userID))
			{
				throw new Exception("Error:  Invalid UserID");
			}
			
			if ( is_null($newPassword) || $newPassword == "" )
			{
				// Password did not change
				$stmt = $this->db->prepare("UPDATE UserDB SET first_name = ?, last_name = ?, email = ? WHERE UserID = ?");
				if (!$stmt->execute(array($firstName, $lastName, $email, $userID)))
				{
					throw new Exception("Error:  Unable to update user attributes.");
				}
			}
			else
			{
				// Password did change
				 // A salt is randomly generated here to protect again brute force attacks 
				// and rainbow table attacks.  The following statement generates a hex 
				// representation of an 8 byte salt.  Representing this in hex provides 
				// no additional security, but makes it easier for humans to read. 
				// For more information: 
				// http://en.wikipedia.org/wiki/Salt_%28cryptography%29 
				// http://en.wikipedia.org/wiki/Brute-force_attack 
				// http://en.wikipedia.org/wiki/Rainbow_table 
				$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); 
				 
				// This hashes the PWD with the salt so that it can be stored securely 
				// in your database.  The output of this next statement is a 64 byte hex 
				// string representing the 32 byte sha256 hash of the PWD.  The original 
				// PWD cannot be recovered from the hash.  For more information: 
				// http://en.wikipedia.org/wiki/Cryptographic_hash_function 
				$PWD = hash('sha256', $newPassword . $salt); 
				 
				// Next we hash the hash value 65536 more times.  The purpose of this is to 
				// protect against brute force attacks.  Now an attacker must compute the hash 65537 
				// times for each guess they make against a PWD, whereas if the PWD 
				// were hashed only once the attacker would have been able to make 65537 different  
				// guesses in the same amount of time instead of only one. 
				for($round = 0; $round < 65536; $round++) 
				{ 
					$PWD = hash('sha256', $PWD . $salt); 
				} 
				
				$stmt = $this->db->prepare("UPDATE UserDB SET first_name = ?, last_name = ?, email = ?, PWD = ?, salt = ? WHERE UserID = ?");
				if (!$stmt->execute(array($firstName, $lastName, $email, $PWD, $salt, $userID)))
				{
					throw new Exception("Error:  Unable to update user attributes.");
				}
			}
			
			if ($active == 1)
			{
				$stmt = $this->db->prepare("Update UserDB SET Active = 1 WHERE UserID = ?");
				if(!$stmt->execute(array($userID)))
				{
					throw new Exception("Error:  Unable to update user active flag.");
				}
			}
			else
			{
				$stmt = $this->db->prepare("Update UserDB SET Active = 0 WHERE UserID = ?");
				if(!$stmt->execute(array($userID)))
				{
					throw new Exception("Error:  Unable to update user active flag.");
				}
			}
			

			//$arrayList = array();
			if (is_array ($access))
			{
				
				$stmt = $this->db->prepare("DELETE FROM UserAccess WHERE UserID = ?");
				if (!$stmt->execute(array($userID)))
				{
					throw new Exception("Error:  Unable to update user access.");
				}
				
				try
				{
					for($i = 0; $i < count($access); $i++)
					{
						if (is_array($access[$i]))
						{
							// this appares to be a 2 dimentional array or something.  Can't figure it out.
							for($j = 0; $j < count($access[$i]); $j++)
							{
								$stmt = $this->db->prepare("INSERT INTO UserAccess (UserID, StatusID) VALUES (?, ?)");
								if(!$stmt->execute(array($userID, (int)$access[$i][$j])))
								{
									throw new Exception("Error:  Unable to update user access.");
								}
							}
						}
					}
				}
				catch (Exception $e)
				{
					throw new Exception($e.message);
				}
			}
			return(true);
		}
		
		private function CheckAccess()
		{
			if ( $_SESSION['AdminAccess'] == true )
				$grantAccess = true;
			else
				$grantAccess = false;

			return($grantAccess);
		}
}


?>