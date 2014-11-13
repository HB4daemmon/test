<?php
class Excellence_Custom_Model_Observer
{
	/**
     * �� Quote ���󱻱�������ݿ�֮ǰ�� �÷����ᱻ����
     * ������յ� POST ���������ݣ��������Զ��� Text �ı��������ݱ��浽 Quote ������
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
     * �� Quote ���󱻱�������ݿ�֮�� �÷����ᱻ����
     * �� Quote ���󱻱�������ݿ�֮�� ���ǽ��Զ��� Text �ı����е�
     * ���ݱ��浽 sales_quote_custom ����
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
     * �� load() ������ Quote �����б�����ʱ��
     * ���Ǵ����ݿ��ж�ȡ�����Զ����ı����е�ֵ�������Ż� Quote ������
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
     * �� Order ���󱻱�������ݿ�֮�� �÷����ᱻ����
     * �������ǽ� Quote �����Զ����ı����ֵ���浽 Order ����: sales_order_custom
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
     * �� $order->load() ��ɺ� �÷����ᱻ����
     * �������Ǵ����ݿ��ж�ȡ�����Զ����ı����е�ֵ���ŵ� Order ������
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