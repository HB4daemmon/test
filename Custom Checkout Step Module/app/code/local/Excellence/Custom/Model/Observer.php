<?php
class Excellence_Custom_Model_Observer
{
	/**
     * 在 Quote 对象被保存进数据库之前， 该方法会被调用
     * 这里接收到 POST 过来的数据，将我们自定义 Text 文本域中内容保存到 Quote 对象中
     */
	public function saveQuoteBefore($evt){
		/*
		 $quote = $evt->getQuote();
		 $post = Mage::app()->getFrontController()->getRequest()->getPost();
		 if(isset($post['custom']['ssn'])){
			$var = $post['custom']['ssn'];
			$quote->setSsn($var);
			}
			*/
	}
	
	/**
     * 在 Quote 对象被保存进数据库之后， 该方法会被调用
     * 当 Quote 对象被保存进数据库之后， 我们将自定义 Text 文本域中的
     * 内容保存到 sales_quote_custom 表中
     */
	public function saveQuoteAfter($evt){
		$quote = $evt->getQuote();
		if($quote->getSsn()){
			$var = $quote->getSsn();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_quote');
				$model->deteleByQuote($quote->getId(),'ssn');
				$model->setQuoteId($quote->getId());
				$model->setKey('ssn');
				$model->setValue($var);
				$model->save();
			}
		}
		if($quote->getExcellenceLike()){
			$var = $quote->getExcellenceLike();

			if(!empty($var)){
				$model = Mage::getModel('custom/custom_quote');
				$model->deteleByQuote($quote->getId(),'excellence_like');
				$model->setQuoteId($quote->getId());
				$model->setKey('excellence_like');
				$model->setValue($var);
				$model->save();
			}
		}
		if($quote->getExcellenceLike2()){
			$var = $quote->getExcellenceLike2();

			if(!empty($var)){
				$model = Mage::getModel('custom/custom_quote');
				$model->deteleByQuote($quote->getId(),'excellence_like2');
				$model->setQuoteId($quote->getId());
				$model->setKey('excellence_like2');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	
	/**
     * 当 load() 方法在 Quote 对象中被调用时，
     * 我们从数据库中读取我们自定义文本域中的值并将它放回 Quote 对象中
     */
	public function loadQuoteAfter($evt){
		$quote = $evt->getQuote();
		$model = Mage::getModel('custom/custom_quote');
		$data = $model->getByQuote($quote->getId());
		foreach($data as $key => $value){
			$quote->setData($key,$value);
		}
	}
	
	/**
     * 在 Order 对象被保存进数据库之后， 该方法会被调用
     * 这里我们将 Quote 表中自定义文本域的值保存到 Order 表中: sales_order_custom
     */
	public function saveOrderAfter($evt){
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		if($quote->getSsn()){
			$var = $quote->getSsn();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_order');
				$model->deleteByOrder($order->getId(),'ssn');
				$model->setOrderId($order->getId());
				$model->setKey('ssn');
				$model->setValue($var);
				$order->setSsn($var);
				$model->save();
			}
		}
		if($quote->getExcellenceLike()){
			$var = $quote->getExcellenceLike();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_order');
				$model->deleteByOrder($quote->getId(),'excellence_like');
				$model->setOrderId($order->getId());
				$model->setKey('excellence_like');
				$model->setValue($var);
				$model->save();
			}
		}
		if($quote->getExcellenceLike2()){
			$var = $quote->getExcellenceLike2();
			if(!empty($var)){
				$model = Mage::getModel('custom/custom_order');
				$model->deleteByOrder($quote->getId(),'excellence_like2');
				$model->setOrderId($order->getId());
				$model->setKey('excellence_like2');
				$model->setValue($var);
				$model->save();
			}
		}
	}
	
	/**
     * 当 $order->load() 完成后， 该方法会被调用
     * 这里我们从数据库中读取我们自定义文本域中的值并放到 Order 对象中
     */
	public function loadOrderAfter($evt){
		$order = $evt->getOrder();
		$model = Mage::getModel('custom/custom_order');
		$data = $model->getByOrder($order->getId());
		foreach($data as $key => $value){
			$order->setData($key,$value);
		}
	}

}