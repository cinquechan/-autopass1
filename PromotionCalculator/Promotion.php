<?php 

namespace PromotionCalculator;

Class Promotion{

	private $id, $threshold_type, $threshold_target, $threshold_value, $promote_type, $promote_value, $limit_type, $limit_unit, $limit_value, $used_times=0, $used_value=0;

	public function __construct($id, $promote_content){
		$this->id = $id;
		// 條件: amount:X元, quantity:X件
		$this->threshold_type 	= $promote_content['threshold_type'];
		$this->threshold_value 	= $promote_content['threshold_value'];
		// 指定產品
		if (isset($promote_content['threshold_target']))
			$this->threshold_target = $promote_content['threshold_target'];

		// 折扣: amount:Y元, percentage:Z%, giveaway:指定商品
		$this->promote_type 	= $promote_content['promote_type'];
		$this->promote_value 	= $promote_content['promote_value'];

		// 折扣上限
		if (!empty($promote_content['limit_type'])){
			// 限制類型: times:N次, amount:N元
			$this->limit_type 		= $promote_content['limit_type'];
			// 限制對象: self:折扣本身, user:每人, month:每月
			// 理論上應該要有時間比對單位(month/year..etc)，這邊先簡化當作只有月份比對
			$this->limit_unit 		= $promote_content['limit_unit'];
			$this->limit_value 		= $promote_content['limit_value'];
		}
	}

	public function getProperty($prop){
		return property_exists($this, $prop) ? $this->$prop : null;
	}

	public function getAllProperties(){
		return [
			'id'				=> $this->id, 
			'threshold_type'	=> $this->threshold_type, 
			'threshold_target'	=> $this->threshold_target, 
			'threshold_value'	=> $this->threshold_value, 
			'promote_type'		=> $this->promote_type, 
			'promote_value'		=> $this->promote_value, 
			'limit_type'		=> $this->limit_type, 
			'limit_unit'		=> $this->limit_unit, 
			'limit_value'		=> $this->limit_value, 
			'used_times'		=> $this->used_times,
			'used_value'		=> $this->used_value,
		];
	}

	public function triggerUsed($amount = 0){
		$this->used_times++;
		$this->used_value += $amount;
	}

}

 ?>