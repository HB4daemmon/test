<?php
class kantwait_Tips_Model_Observer
{
	public function saveQuoteBefore($evt)
	{
		$quote = $evt->getQuote();
		$post = Mage::app()->getFrontController()->getRequest()->getPost();
		if(isset($post['tips']['select'])){
			$var = $post['tips']['select'];
			$quote->setSelect($var);
		}
	}
	
	public function saveQuoteAfter($evt)
	{

		$quote = $evt->getQuote();
		if($quote->getSelect()){
			$var = $quote->getSelect();
			if(!empty($var)){
				$model = Mage::getModel('tips/custom_quote');
				$model->deteleByQuote($quote->getId(),'select');
				$model->setQuoteId($quote->getId());
				$model->setKey('select');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	
	public function loadQuoteAfter($evt)
	{
		$quote = $evt->getQuote();
		$model = Mage::getModel('tips/custom_quote');
        //var_dump($model);
		$data = $model->getByQuote($quote->getId());
		foreach($data as $key => $value){
			$quote->setData($key,$value);
		}
	}
	
	public function saveOrderAfter($evt)
	{
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		if($quote->getSelect()){
			$var = $quote->getSelect();
			if(!empty($var)){
				$model = Mage::getModel('tips/custom_order');
				$model->deleteByOrder($order->getId(),'select');
				$model->setOrderId($order->getId());
				$model->setKey('select');
				$model->setValue($var);
				$order->setSelect($var);
				$model->save();
			}
		}
	}
	
	public function loadOrderAfter($evt)
	{
		$order = $evt->getOrder();
		$model = Mage::getModel('tips/custom_order');
		$data = $model->getByOrder($order->getId());
		foreach($data as $key => $value){
			$order->setData($key,$value);
		}
	}
}