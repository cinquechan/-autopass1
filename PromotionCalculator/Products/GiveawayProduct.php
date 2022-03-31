<?php 

namespace PromotionCalculator\Products;

use PromotionCalculator\Product;

Class GiveawayProduct extends Product{
	private $product_real_id;
	
	public function __construct($id, $baseinfo){
		$this->id = "ga".$id;
		$this->product_real_id = $id;
		$this->price = 0;
		// Should read by DB: product + giveaway settings
		$this->readProduct($baseinfo);
	}

	public function getRealId(){
		return $this->product_real_id;
	}

}

 ?>