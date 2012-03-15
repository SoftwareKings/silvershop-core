<?php
/**
 * Calculates the shipping cost of an order, by taking the products
 * and calculating the shipping weight, based on an array set in _config
 *
 * ASSUMPTION: The total order weight can be at maximum the last item
 * in the $shippingCosts array.
 *
 * @package shop
 * @subpackage modifiers
 */
class WeightShippingModifier extends ShippingModifier {
		
	protected static $weight_cost = array(
		0.5 => 10,
		1 => 20,
		999 => 50
	);
	protected $weight = 0;
	
	static function set_weight_costs($costs){
		self::$weight_cost = $costs;
	}
	
	/**
	* Calculates shipping cost based on Product Weight.
	*/
	function value($subtotal = 0){
		$totalWeight = $this->Weight();
		if(!$totalWeight){
			return $this->Amount = 0;
		}
		$amount = 0;
		
		foreach(self::$weight_cost as $weight => $cost) {
			if($totalWeight <= $weight){
				$amount =  $cost;
				break;
			}
		}
		return $this->Amount = $amount;
	}
	
	function TableTitle(){
		return parent::TableTitle()." (".$this->Weight()." kg)";
	}
	
	/**
	 * Calculate the total weight of the order
	 * @return number
	 */
	function Weight(){
		if($this->weight){
			return $this->weight;
		}
		$weight = 0;
		$order = $this->Order();
		if($order && $orderItems = $order->Items()) {
			foreach($orderItems as $orderItem){
				if($product = $orderItem->Product()){
					$weight = $weight + ($product->Weight * $orderItem->Quantity);
				}
			}
		}
		return $this->weight = $weight;
	}

}