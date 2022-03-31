<?php 

namespace PromotionCalculator;

use PromotionCalculator\Products\GiveawayProduct;

Class Order{

	private $id, $user_id, $original_total, $discounted_total, $grant_total, $products=[], $products_ids=[], $quantities=[], $promotions=[], $promotions_ids=[], $promotions_amount=[], $calculated=0, $calculated_datetime;

	public function __construct($id, $user_id, $date = null){
		$this->id 			= $id;
		$this->user_id 		= $user_id;
	}

	public function getProperty($prop){
		return property_exists($this, $prop) ? $this->$prop : null;
	}

	public function addProduct(Product $product, $qty=1){
		$product_index = array_search($product_id = $product->getProperty('id'), $this->products_ids);
		if ($product_index===false){
			$this->products_ids[] = $product_id;
			$this->products[] = $product;
			$this->quantities[] = $qty;
		}else{
			$this->quantities[$product_index] += $qty;
		}
		return $this;
	}

	public function deleteProduct($id){
		$del_index = array_search($id, $this->products_ids);
		if ($del_index!==false){
			array_splice($this->products, $del_index, 1);
			array_splice($this->products_ids, $del_index, 1);
			array_splice($this->quantities, $del_index, 1);
		}
		return $this;
	}

	public function addPromotion(Promotion $promotion){
		$promotion_id = $promotion->getProperty('id');
		if (in_array($promotion_id = $promotion->getProperty('id'), $this->promotions_ids)){
			echo "Duplicate Promotion: ".$promotion_id;
			return;
		};
		// ******************************
		// 檢查是否有衝突之折扣(e.g.同樣為訂單折價)
		// code... 
		// ******************************
		$this->promotions_ids[] = $promotion_id;
		$this->promotions_amount[] = 0;
		$this->promotions[] = $promotion;
		return $this;
	}

	public function deletePromotion($id){
		$del_index = array_search($id, $this->promotions_ids);
		if ($del_index!==false){
			array_splice($this->promotions, $del_index, 1);
			array_splice($this->promotions_ids, $del_index, 1);
			array_splice($this->promotions_amount, $del_index, 1);
		}
		return $this;
	}

	public function updatePromotionAmount($id, $amount): void{
		$index = array_search($id, $this->promotions_ids);
		if ($index!==false && $amount>0)
			$this->promotions_amount[$index] = $amount;
	}

	public function findPromotion($id){
		$index = array_search($id, $this->promotions_ids);
		if ($index===false)
			return false;
		return $this->promotions_amount[$index];
	}

	public function getQuantity($product_id){
		$index = array_search($product_id, $this->products_ids);
		return $index!==false ? $this->quantities[$index] : 0;
	}

	public function printProducts(): void{
		echo "<style>table, table th, table td{border: 1px solid #000}</style>";
		echo "<table>";
		echo "<tr>";
		echo "<th>產品名稱</th>";
		echo "<th>產品編號</th>";
		echo "<th>數量</th>";
		echo "<th>單價</th>";
		echo "</tr>";
		foreach ($this->products as $key => $product) {
			$product_name = $product instanceof GiveawayProduct ? $product->getProperty("name")."(贈品)" : $product->getProperty("name");
			$product_code = $product->getProperty("code");
			echo "<tr>";
			echo "<td>{$product_name}</td>";
			echo "<td>{$product_code}</td>";
			echo "<td>{$this->quantities[$key]}</td>";
			echo "<td>{$product->getProperty("price")}</td>";
			echo "</tr>";
		}
		echo "</table>";
	}

	// 為了測試要手動把訂單設定成其他月份
	public function done(){
		$this->calculated = 1;
		$this->calculated_datetime = date_create("now");
	}


	// 為了測試要手動把訂單設定成其他月份
	public function overrideDate($datetime){
		$this->calculated_datetime = $datetime;
	}

}

 ?>