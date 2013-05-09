<?php

class SendMail
{
	protected $db;
 
        public function __construct($db)
        {
            $this->db = $db;
        }
		
		public function NotifySupervisor($subject, $message)
		{
			$addresses = array();
			$stmt = $this->db->prepare("SELECT email FROM UserDB WHERE email IS NOT NULL AND TRIM(email) <> ''");
			if ($stmt->execute())
			{
				while ($row = $stmt->fetch())
				{
					array_push($addresses, $row["email"]);
				}
				$to = implode(", ", $addresses);

				$from = "qa@usuiusa.com";
				$headers = "From:" . $from;
				//echo($to);
				//echo("<br/>");
				mail($to,$subject,$message,$headers);
			}
		
		}
		
}

?>