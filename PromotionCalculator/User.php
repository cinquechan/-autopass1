<?php 

namespace PromotionCalculator;

Class User{

	private $id, $order_list=[];

	public function __construct($id){
		$this->id = $id;
	}

	public function getProperty($prop){
		return property_exists($this, $prop) ? $this->$prop : null;
	}

	public function newOrder($id){
		$order = new Order($id, $this->id);
		$this->order_list[] = $order;
		return $order;
	}

	public function getPromotionsUsed($promotion_id, $month = null){
		$used_count 	= 0;
		$used_amount 	= 0;
		foreach ($this->order_list as $key => $order){
			if (!$order->getProperty("calculated"))
				continue;
			// 理論上應該要有「單位(month/year)」及其值，這邊先簡化當作只有月份比對
			if (!empty($month) && $month!=$order->getProperty("calculated_datetime")->format("Y-m"))
				continue;
			if ($amount = $order->findPromotion($promotion_id)){
				$used_count++;
				$used_amount += $amount;
			}
		}
		return [
			'times' 	=> $used_count,
			'amount'	=> $used_amount,
		];
	}
	
}

 ?>