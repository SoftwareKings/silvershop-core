<?php

class PaymentForm extends CheckoutForm{

	protected $failurelink;

	/**
	 * @var OrderProcessor
	 */
	protected $orderProcessor;

	public function __construct($controller, $name, CheckoutComponentConfig $config) {
		parent::__construct($controller, $name, $config);

		$this->orderProcessor = Injector::inst()->create('OrderProcessor', $config->getOrder());
	}

	public function setFailureLink($link) {
		$this->failurelink = $link;
	}

	public function checkoutSubmit($data, $form) {
		//form validation has passed by this point, so we can save data
		$this->config->setData($form->getData());
		$order = $this->config->getOrder();
		$gateway = Checkout::get($order)->getSelectedPaymentMethod(false);
		if(GatewayInfo::is_offsite($gateway) || GatewayInfo::is_manual($gateway)){

			return $this->submitpayment($data, $form);
		}

		return $this->controller->redirect(
			$this->controller->Link('payment') //assumes CheckoutPage
		);
	}

	/**
	 * Behaviour can be overwritten by creating a processPaymentResponse method
	 * on the controller owning this form. It takes a Symfony\Component\HttpFoundation\Response argument,
	 * and expects an SS_HTTPResponse in return.
	 */
	public function submitpayment($data, $form) {
		$data = $form->getData();
		$data['cancelUrl'] = $this->getFailureUrl() ? $this->getFailureUrl() : $this->controller->Link();
		$order = $this->config->getOrder();
		$order->calculate();
		$paymentResponse = $this->orderProcessor->makePayment(
			Checkout::get($order)->getSelectedPaymentMethod(false),
			$data
		);

		$response = null;
		if($paymentResponse){
			if($this->controller->hasMethod('processPaymentResponse')) {
				$response = $this->controller->processPaymentResponse($paymentResponse);
			} else if($paymentResponse->isRedirect() || $paymentResponse->isSuccessful()){
				$response = $paymentResponse->redirect();
			} else {
				$form->sessionMessage($response->getMessage(), 'bad');
				$response = $this->controller->redirectBack();
			}
		} else {
			$form->sessionMessage($this->orderProcessor->getError(), 'bad');
			$response = $this->controller->redirectBack();
		}

		return $response;
	}

	/**
	 * @param OrderProcessor $processor
	 */
	public function setOrderProcessor(OrderProcessor $processor) {
		$this->orderProcessor = $processor;
	}

	/**
	 * @return OrderProcessor
	 */
	public function getOrderProcessor() {
		return $this->orderProcessor;
	}

}
