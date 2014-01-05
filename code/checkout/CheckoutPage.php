<?php
/**
 * CheckoutPage is a CMS page-type that shows the order
 * details to the customer for their current shopping
 * cart on the site. It also lets the customer review
 * the items in their cart, and manipulate them (add more,
 * deduct or remove items completely). The most important
 * thing is that the {@link CheckoutPage_Controller} handles
 * the {@link OrderForm} form instance, allowing the customer
 * to fill out their shipping details, confirming their order
 * and making a payment.
 *
 * @see CheckoutPage_Controller->Order()
 * @see OrderForm
 * @see CheckoutPage_Controller->OrderForm()
 *
 * The CheckoutPage_Controller is also responsible for setting
 * up the modifier forms for each of the OrderModifiers that are
 * enabled on the site (if applicable - some don't require a form
 * for user input). A usual implementation of a modifier form would
 * be something like allowing the customer to enter a discount code
 * so they can receive a discount on their order.
 *
 * @see OrderModifier
 * @see CheckoutPage_Controller->ModifierForms()
 *
 * @package shop
 */
class CheckoutPage extends Page {

	public static $db = array(
		'PurchaseComplete' => 'HTMLText'
	);

	static $icon = 'shop/images/icons/money';

	/**
	 * Returns the link to the checkout page on this site
	 *
	 * @param boolean $urlSegment If set to TRUE, only returns the URLSegment field
	 * @return string Link to checkout page
	 */
	static function find_link($urlSegment = false, $action = null, $id = null) {
		if(!$page = CheckoutPage::get()->first()) {
			return Controller::join_links(Director::baseURL(),CheckoutPage_Controller::$url_segment);
		}
		$id = ($id)? "/".$id : "";
		return ($urlSegment) ? $page->URLSegment : Controller::join_links($page->Link($action),$id);
	}

	/**
	 * Only allow one checkout page
	 */
	function canCreate($member = null) {
		return !CheckoutPage::get()->exists();
	}

	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(
			HtmlEditorField::create('PurchaseComplete', 'Purchase Complete', 4)
				->setDescription("This message is included in reciept email, after the customer submits the checkout")
		),'Metadata');
		return $fields;
	}
	
}

class CheckoutPage_Controller extends Page_Controller {
	
	static $url_segment = "checkout";

	public static $extensions = array(
		'OrderManipulation'
	);

	static $allowed_actions = array(
		'OrderForm',
		'payment',
		'PaymentForm'
	);
	
	/**
	 * Display a title if there is no model, or no title.
	 */
	public function Title() {
		if($this->Title)
			return $this->Title;
		return _t('CheckoutPage.TITLE',"Checkout");
	}

	function OrderForm() {
		$order = $this->Cart();
		if(!(bool)$order){
			return false;
		}

		return new CheckoutForm(
			$this,
			'OrderForm', 
			new SinglePageCheckoutComponentConfig($order)
		);
	}

	function payment(){
		return array(
			'Title' => 'Make Payment',
			'OrderForm' => $this->PaymentForm()
		);
	}

	function PaymentForm(){
		$order = $this->Cart();
		if(!(bool)$order){
			return false;
		}
		$config = new CheckoutComponentConfig($order);
		$config->AddComponent(new OnsitePaymentCheckoutComponent());
		$form = new CheckoutForm($this, "PaymentForm", $config);

		return $form;
	}

}