<?php
class BarcodeInfo {
	protected $db;
	public function __construct($db) {
		$this->db = $db;
	}
	
	public function GetAllAvailableBarcodes() {
		$sql = "SELECT b.PartId, p.PartNum, b.Size, b.BarcodeText, b.IsActive, b.HriText
				FROM BarcodeDefinitions b, PlexParts p
				WHERE b.PartId = p.PartID
				ORDER BY p.PartNum";
		$results = array();
		$stmt = $this->db->prepare($sql);
		if($stmt->execute()) {
			while($row = $stmt->fetch()){
				array_push($results, $row);
			}
		}
		return $results;
	}
	
	public function GetBarcodeByPartId($id) {	
		if ( is_null($id) || $id == "")
		{
			throw new Exception("Error:  Invalid part number input.");
		}
			
		if (!ctype_alnum($id))
		{
			throw new Exception("Error:  Invalid part input.  Part must be alphanumeric (a-Z 0-9)");
		}
		$stmt = $this->db->prepare("SELECT PartId, Size, BarcodeText, HRIText, IsActive FROM BarcodeDefinitions WHERE PartId = ?");
		
		if ($stmt->execute(array($id)))
		{
			return $stmt->fetch();
		}
		else
		{
			throw new Exception("Error:  Unable to look up barcode information for part ID: $id");
		}
	}
	
	public function SaveNewBarcode($partId, $size, $barcodeText, $hriText=null, $isActive=1) {
		$grantAccess = false;
		if ( $_SESSION['AdminAccess'] == true )
		$grantAccess = true;
		
		if(!$grantAccess)
		{
			throw new Exception ("Error:  You do not have permission to add parts.");
		}
			
		if ( is_null($partId) || $partId == "")
		{
			throw new Exception("Error:  Invalid part number input.");
		}
			
		if (!ctype_alnum($partId))
		{
			throw new Exception("Error:  Invalid part input.  Part must be alphanumeric (a-Z 0-9)");
		}
		
		$stmt = $this->db->prepare(
				"INSERT INTO BarcodeDefinitions (PartId, Size, BarcodeText, HRIText, IsActive, CreatedDate, LastModifiedDate, UserId)
				 VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)");
		if ($stmt->execute(array($partId, $size, $barcodeText, $hriText, $isActive, $_SESSION['user']['UserID'])))
		{
			return(array("PartID" => $this->db->lastInsertId()));
		}
		else
		{
			throw new Exception("Error:  Unable to insert part number $partId");
		}
	}
	
	public function DeletedBarcodeById($partId) {
	$grantAccess = false;
		if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
		
		if(!$grantAccess)
		{
			throw new Exception ("Error:  You do not have permission to add parts.");
		}
			
		if ( is_null($partId) || $partId == "")
		{
			throw new Exception("Error:  Invalid part number input.");
		}
		
		$stmt = $this->db->prepare("DELETE FROM BarcodeDefinitions WHERE PartId = ?");
		if($stmt->execute(array($partId))){
			return TRUE;
		}
		else{
			throw new Exception("Error:  Unable to update barcode for part number $partId"); 
		}
	}
	
	public function UpdateBarcodeById($partId, $size, $barcodeText, $hriText=null, $isActive=1) {
		$grantAccess = false;
		if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
		
		if(!$grantAccess)
		{
			throw new Exception ("Error:  You do not have permission to add parts.");
		}
			
		if ( is_null($partId) || $partId == "")
		{
			throw new Exception("Error:  Invalid part number input.");
		}
			
		if (!ctype_alnum($partId))
		{
			throw new Exception("Error:  Invalid part input.  Part must be alphanumeric (a-Z 0-9)");
		}
		
		$stmt = $this->db->prepare(
				"UPDATE BarcodeDefinitions SET
				Size = ?, BarcodeText = ?, HRIText = ?, IsActive = ?, LastModifiedDate = NOW(), UserID = ?
				WHERE PartId = ?
				");
		if ($stmt->execute(array($size, $barcodeText, $hriText, $isActive, $_SESSION['user']['UserID'], $partId)))
		{
			return true;
		}
		else
		{
			throw new Exception("Error:  Unable to update barcode for part number $partId");
		}
		
	}
	
	public function GetAllAvailableBarcodeSizes(){
	$grantAccess = false;
		if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
		
		if(!$grantAccess)
		{
			throw new Exception ("Error:  You do not have permission to add parts.");
		}
		$stmt = $this->db->prepare("SELECT Id, Name from BarcodeSizes where IsActive = 1");
		$results = array();
		if($stmt->execute()){
			while($row = $stmt->fetch()){
				array_push($results, $row);
			}
		}
		return $results;
	}
	
	public function GetPropertiesByBarcodeSizeId($id){
		$grantAccess = false;
		if ( $_SESSION['AdminAccess'] == true )
			$grantAccess = true;
		if(!$grantAccess)
		{
			throw new Exception ("Error:  You do not have permission to add parts.");
		}
		if ( is_null($id) || $id == "")
		{
			throw new Exception("Error:  Invalid barcode size id!");
		}
		
		$stmt = $this->db->prepare("SELECT * from BarcodeSizes where Id = ?");
		
		if ($stmt->execute(array($id)))
		{
			return $stmt->fetch();
		}
		else
		{
			throw new Exception("Error:  Unable to look up barcode information for part ID: $id");
		}
	}
	
	public function GetNextSerialByBarcodeId($id){
		if ( is_null($id) || $id == "")
		{
			throw new Exception("Error:  Invalid barcode size id!");
		}
		$stmt = $this->db->prepare("SELECT NextSerial from BarcodeSerials where BarcodeId = ? and Date like ?");
		if($stmt->execute(array($id, date(Ymd)))){
			$res = $stmt->fetch();
			return $res['NextSerial'];
		}
		else {
			return false;
		}
	}
	
	public function UpdateNextSerialByBarcodeId($id, $nextSerial){
		if(!is_null($this->GetNextSerialByBarcodeId($id))){
			$stmt = $this->db->prepare("UPDATE BarcodeSerials set NextSerial = ? where BarcodeId = ? and Date = ?");
		}else {
			$stmt = $this->db->prepare("INSERT into BarcodeSerials (NextSerial, BarcodeId, Date) values (?, ?, ?)");
		}
		if($stmt->execute(array($nextSerial, $id, date(Ymd)))){
			return true;
		}
		return false;
	}
}