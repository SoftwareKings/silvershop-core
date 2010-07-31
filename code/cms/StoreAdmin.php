<?php

class StoreAdmin extends ModelAdmin{

	static $url_segment = 'orders';

	static $menu_title = 'Orders';

	static $menu_priority = 1;

	//static $url_priority = 50;

	public static $managed_models = array('Order','Payment','OrderStatusLog', 'OrderItem', 'OrderModifier');

	public static $collection_controller_class = 'StoreAdmin_CollectionController';

	public static $record_controller_class = 'StoreAdmin_RecordController';

	public static function set_managed_models(array $array) {
		self::$managed_models = $array;
	}

	function init() {
		parent::init();
		Requirements::themedCSS("OrderReport");
	}


}

class StoreAdmin_CollectionController extends ModelAdmin_CollectionController {

	//public function CreateForm() {return false;}
	public function ImportForm() {return false;}
}

//remove delete action
class StoreAdmin_RecordController extends ModelAdmin_RecordController {

	public function EditForm() {
		$form = parent::EditForm();
		$form->Actions()->removeByName('Delete');
		return $form;
	}
}
