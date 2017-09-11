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

class Cryozonic_Stripe_Helper_Data extends Mage_Payment_Helper_Data
{
    public function getBillingAddress($quote = null)
    {
        $quote = $this->getSessionQuote();

        if (!empty($quote) && $quote->getBillingAddress())
            return $quote->getBillingAddress();

        return null;
    }

    public function getSessionQuote()
    {
        // If we are in the back office
        if (Mage::app()->getStore()->isAdmin())
        {
            return Mage::getSingleton('adminhtml/sales_order_create')->getQuote();
        }
        // If we are a user
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    public function getSanitizedBillingInfo()
    {
        $billingAddress = $this->getBillingAddress();
        if (!$billingAddress) return null;

        $quote = $this->getSessionQuote();

        $postcode = $billingAddress->getData('postcode');
        $email = $billingAddress->getEmail();
        $name = $billingAddress->getName();
        $city = $billingAddress->getCity();
        $country = $billingAddress->getCountryId();
        $phone = $billingAddress->getTelephone();
        $state = $billingAddress->getRegion();

        if (empty($name) && $quote->getCustomerFirstname())
        {
            $name = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        }

        if (empty($email))
        {
            if (Mage::getSingleton('customer/session')->isLoggedIn())
            {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $email = $customer->getEmail();
            }
            else
            {
                if ($quote)
                    $email = $quote->getCustomerEmail();
            }
        }

        $line1 = null;
        $line2 = null;
        $street = explode('\n', $billingAddress->getData('street'));
        if (!empty($street) && is_array($street) && count($street))
        {
            $line1 = $street[0];

            if (!empty($street[1]))
                $line2 = $street[1];
        }

        // Sanitization
        $line1 = preg_replace("/\r|\n/", " ", $line1);
        $line1 = addslashes($line1);
        if (empty($line1))
            $line1 = null;

        return array(
            'name' => $name,
            'line1' => $line1,
            'line2' => $line2,
            'postcode' => $postcode,
            'email' => $email,
            'city' => $city,
            'phone' => $phone,
            'state' => $state,
            'country' => $country
        );
    }

    // Removes decorative strings that Magento adds to the transaction ID
    public function cleanToken($token)
    {
        return preg_replace('/-.*$/', '', $token);
    }

    public function cancelOrder($orderId, $isIncremental = false)
    {
        try
        {
            if (!$orderId)
                throw new Exception("Could not load order ID from session data.");

            if ($isIncremental)
                $order = Mage::getModel('sales/order')->load($orderId, 'increment_id');
            else
                $order = Mage::getModel('sales/order')->load($orderId);

            if (!$order)
                throw new Exception("Could not load order with ID $orderId.");

            $this->cancelOrCloseOrder($order);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
        }
    }

    public function cancelOrCloseOrder($order)
    {
        $transaction = Mage::getModel('core/resource_transaction');

        // When in Authorize & Capture, uncaptured invoices exist, so we should cancel them first
        $service = Mage::getModel('sales/service_order', $order);

        foreach($order->getInvoiceCollection() as $invoice)
        {
            if ($invoice->canCancel())
            {
                $invoice->cancel();
                $transaction->addObject($invoice);
            }
        }

        // When all invoices have been canceled, the order can be canceled
        if ($order->canCancel())
        {
            $order->cancel();
            $transaction->addObject($order);
        }

        $transaction->save();
    }

    public function captureOrder($order)
    {
        foreach($order->getInvoiceCollection() as $invoice)
        {
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->capture();
            $invoice->save();
        }
    }

    public function invoiceOrder($order)
    {
        $transaction = Mage::getModel('core/resource_transaction');

        // This will kick in with "Authorize Only" mode, but not with "Authorize & Capture"
        if ($order->canInvoice())
        {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();

            $transaction->addObject($invoice)
                        ->addObject($order)
                        ->save();

            // try
            // {
            //     $invoice->sendEmail(true);
            // }
            // catch (Exception $e)
            // {
            //     Mage::logException($e);
            // }
        }
        // Invoices have already been generated with Authorize & Capture, but have not actually been captured because
        // the source is not chargeable yet. These should have a pending status.
        else
        {
            foreach($order->getInvoiceCollection() as $invoice)
            {
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $transaction->addObject($invoice);
            }

            $transaction->addObject($order)->save();
        }

        return $invoice;
    }
}
