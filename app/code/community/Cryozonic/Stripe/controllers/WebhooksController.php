<?php
/**
 * Cryozonic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Single Domain License
 * that is available through the world-wide-web at this URL:
 * http://cryozonic.com/licenses/stripe.html
 * If you are unable to obtain it through the world-wide-web,
 * please send an email to info@cryozonic.com so we can send
 * you a copy immediately.
 *
 * @category   Cryozonic
 * @package    Cryozonic_Stripe
 * @copyright  Copyright (c) Cryozonic Ltd (http://cryozonic.com)
 */

class Cryozonic_Stripe_WebhooksController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        // Retrieve the request's body and parse it as JSON
        $body = $this->getRequest()->getRawBody();
        $event = json_decode($body, true);
        $object = $event['data']['object'];

        // Ignore unsupported payment methods
        $types = array('three_d_secure');
        if (!in_array($object['type'], $types))
            return;

        try
        {
            $metadata = $object['metadata'];
            if (empty($metadata['Order #']))
                throw new Exception("Received source.chargeable webhook but there was no Order # in the source's metadata - ignoring");

            $orderId = $metadata['Order #'];
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if (!$order)
                throw new Exception("Received source.chargeable webhook with Order #$orderId but could not find the order in Magento - ignoring");

            switch ($event['type'])
            {
                case 'source.chargeable':
                    $this->charge($order, $object);
                    break;

                case 'source.canceled':
                case 'source.failed':
                    $order->addStatusHistoryComment("Authorization failed.");
                    Mage::helper('cryozonic_stripe')->cancelOrCloseOrder($order);
                    break;

                default:
                    break;
            }
        }
        catch (Exception $e)
        {
            Mage::logException($e);
        }
    }

    protected function charge($order, $object)
    {
        $orderId = $order->getIncrementId();

        $payment = $order->getPayment();
        if (!$payment)
            throw new Exception("Could not load payment method for order #$orderId");

        $orderSourceId = $payment->getAdditionalInformation('source_id');
        $webhookSourceId = $object['id'];
        if ($orderSourceId != $webhookSourceId)
            throw new Exception("Received source.chargeable webhook for order #$orderId but the source ID on the webhook $webhookSourceId was different than the one on the order $orderSourceId");

        $stripe = Mage::getModel('cryozonic_stripe/standard');
        $stripe->setInfoInstance($payment);

        try
        {
            // Charge the card
            if (Mage::getStoreConfig('payment/cryozonic_stripe/payment_action') == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE)
            {
                Mage::helper('cryozonic_stripe')->captureOrder($order);
                $comment = "Payment authorized and captured in Stripe";
            }
            else
            {
                $stripe->createCharge($payment, false);
                $comment = "Payment authorized in Stripe";
            }
            $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, $comment);
            $transaction->setIsClosed(0);
            $transaction->save();
            $payment->save();

            // Send the order email
            if ($order->getCanSendNewEmailFlag()) {
                try {
                    $order->queueNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            $order->save();

            // Save the card
            if ($payment->getAdditionalInformation('save_card'))
                $stripe->addCardToCustomer($object['three_d_secure']['card']);
        }
        catch (\Stripe\Error\Card $e)
        {
            $comment = "Order could not be charged because of a card error: " . $e->getMessage();
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
        catch (\Stripe\Error $e)
        {
            Mage::logException($e);
            $comment = "Order could not be charged because of a Stripe error.";
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
        catch (\Exception $e)
        {
            Mage::logException($e);
            $comment = "Order could not be charged because of server side error.";
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
    }
}