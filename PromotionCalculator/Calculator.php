<?php 

namespace PromotionCalculator;

Class Calculator{

	public function __construct(){
		
	}

	public function getProperty($prop){
		return property_exists($this, $prop) ? $this->$prop : null;
	}

	// private static function calculateProducts($products, $quantities){
	// 	$sum = 0;
	// 	foreach ($products as $key => $product){
	// 		$sum += $product->getProperty("price")*$quantities[$key];
	// 	}
	// 	return $sum;
	// }

	public static function calculateOrder(Order $order, User $user){
		// $products_total = self::calculateProducts($order->getProperty("products"), $order->getProperty("quantities"));

		$products = $order->getProperty("products");
		$quantities = $order->getProperty("quantities");
		$products_total = 0;
		foreach ($products as $key => $product){
			$products_total += $product->getProperty("price")*$quantities[$key];
		}

		$promotions = $order->getProperty("promotions");
		$discount = 0;
		foreach ($promotions as $key => $promotion){
			$promote_detail = $promotion->getAllProperties();
			$applied = 0;
			switch ($promote_detail["threshold_type"]) {
				case 'amount':
					if ($products_total>=$promote_detail['threshold_value'])
						$applied = 1;
					break;
				case 'quantity':
					if ( !empty($promote_detail['threshold_target']) && $order->getQuantity($promote_detail['threshold_target'])>=$promote_detail['threshold_value'] )
						$applied = 1;
					break;
				default:
					break;
			}

			if (!empty($promote_detail['limit_type'])){
				$max_discount = 0;
				switch ($promote_detail['limit_type']) {
					case 'times':
						switch ($promote_detail['limit_unit']){
							case 'self':
								$comparedValue = $promote_detail['used_times'];
								break;
							case 'user':
								$comparedValue = $user->getPromotionsUsed($promote_detail['id'])['times'];
								break;
							case 'month':
								$comparedValue = $user->getPromotionsUsed($promote_detail['id'], date("Y-m"));
								break;
							default:
								$comparedValue = -1;
						}
						if ($comparedValue>=0 && $comparedValue>=$promote_detail['limit_value'])
							$applied = 0;
						break;
					case 'amount':
						switch ($promote_detail['limit_unit']){
							case 'self':
								$comparedValue = $promote_detail['used_value'];
								break;
							case 'user':
								$comparedValue = $user->getPromotionsUsed($promote_detail['id'])['amount'];
								break;
							case 'month':
								$comparedValue = $user->getPromotionsUsed($promote_detail['id'], date("Y-m"))['amount'];
								break;
							default:
								$comparedValue = -1;
						}
						if ($comparedValue>=0 && $comparedValue+$products_total>=$promote_detail['limit_value']){
							if ($comparedValue<$promote_detail['limit_value'])
								$max_discount = $promote_detail['limit_value'] - $comparedValue;
							else
								$applied = 0;
						}
						break;
					default:
						break;
				}
			}
			if ($applied){
				$discount = 0;
				switch ($promote_detail['promote_type']) {
					case 'amount':
						$discount = $promote_detail['promote_value'];
						break;
					case 'percentage':
						$discount = floor($products_total * $promote_detail['promote_value'])/100;
						break;
					case 'giveaway':
						$order->addProduct($promote_detail['promote_value'], 1);
						break;					
					default:
						break;
				}
				if (!empty($max_discount))
					$discount = min($discount, $max_discount);
				$promotion->triggerUsed($discount);
				$order->updatePromotionAmount($promote_detail['id'], $discount);
			}else{
				$order->deletePromotion($promote_detail['id']);
			}
		}

		$order->done();

		return [
			'products_total' 	=> $products_total,
			'discount' 			=> $discount,
			'order_total' 		=> $products_total - $discount,
		];

	}

}

 ?>