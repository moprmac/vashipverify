<?php
require_once 'php-barcode.php';
class DataMatrix {
	protected $fontSize = 8;   // GD1 in px ; GD2 in point
	protected $marge = 10;   // between barcode and hri in pixel
	protected $x = 20;  // barcode center
	protected $y = 20;  // barcode center
	protected $height = 0;   // barcode height in 1D ; module size in 2D
	protected $width = 2;    // barcode height in 1D ; not use in 2D
	protected $angle = 0;
	protected $type = 'datamatrix';
	protected $text;
	
	public function __construct($options = array()){
		//Todo Use to initialize options
	}
	
	public function setModuleSize($size){
		if(!is_numeric($size)) {
			throw InvalidArgumentException('Only numeric modular sizes are allowed');
		}else if( $size < 0 || $size > 10) {
			throw OutOfRangeException('Size accepts numbers from 0 - 10');
		} else{
			$this->width = $size;
			$this->x = $size * 10;
			$this->y = $size * 10;
		}
		return $this;
	}
	
	public function setData($data){
		$this->text;
		return $this;
	}
	
	public function generateGd(){
		$dims = $size*20;
		$im     = imagecreatetruecolor($dims, $dims);
		$black  = ImageColorAllocate($im,0x00,0x00,0x00);
		$white  = ImageColorAllocate($im,0xff,0xff,0xff);
		$red    = ImageColorAllocate($im,0xff,0x00,0x00);
		$blue   = ImageColorAllocate($im,0x00,0x00,0xff);
		imagefilledrectangle($im, 0, 0, $dims, $dims, $white);
		
		$data = Barcode::gd(
			$im, $black, $this->x, $this->y, 
			$this->angle, $this->type, 
			array('code'=>$this->data), 
			$this->width, $this->height
		);
		header('Content-type: image/gif');
		imagegif($im);
		imagedestroy($im);
	}
}

?>