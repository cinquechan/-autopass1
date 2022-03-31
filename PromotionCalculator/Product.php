<?php 

namespace PromotionCalculator;

Class Product{

	protected $id, $name, $code, $price, $original_price=null;

	public function __construct($id, $baseinfo, $price, $sale_price=null){
		$this->id = $id;

		// --------------------
		// Should read by DB
		if (empty($sale_price)){
			$this->price = $price;
		}else{
			$this->price 			= $sale_price;
			$this->original_price 	= $price;
		}
		$this->readProduct($baseinfo);
		// --------------------
	}

	public function getProperty($prop){
		return property_exists($this, $prop) ? $this->$prop : null;
	}

	protected function readProduct($baseinfo){
		foreach ($baseinfo as $prop => $data) {
			$this->$prop = $data;
		}
	}

}

 ?>