<?php

namespace Omnipay\PayU\Messages;

use Omnipay\Common\Message\AbstractResponse;

class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return 'COMPLETED' === $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function isCancelled()
    {
        return in_array($this->getCode(), ['CANCELED', 'REJECTED'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        if (isset($this->data['orders'][0]['extOrderId']) && ! empty($this->data['orders'][0]['extOrderId'])) {
            return (string) $this->data['orders'][0]['extOrderId'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        if (isset($this->data['orders'][0]['orderId']) && ! empty($this->data['orders'][0]['orderId'])) {
            return (string) $this->data['orders'][0]['orderId'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->data['orders'][0]['status'];
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return in_array($this->getCode(), ['PENDING', 'WAITING_FOR_CONFIRMATION', 'NEW']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentReference()
    {
        if (isset($this->data['properties'])) {
            $properties        = $this->data['properties'];
            $paymentIdProperty = array_filter($properties, function ($item) {
                return $item['name'] === 'PAYMENT_ID';
            });

            if (isset($paymentIdProperty[0]['value'])) {
                return (string) $paymentIdProperty[0]['value'];
            }
        }

        return null;
    }
}
