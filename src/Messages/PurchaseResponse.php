<?php

namespace Omnipay\PayU\Messages;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        $isSuccess = isset($this->data['status']['statusCode']) && 'SUCCESS' !== $this->data['status']['statusCode'];

        return ! $this->isRedirect() && $isSuccess;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return isset($this->data['redirectUri']) && is_string($this->data['redirectUri']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            return $this->data['redirectUri'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        if (isset($this->data['extOrderId']) && ! empty($this->data['extOrderId'])) {
            return (string) $this->data['extOrderId'];
        }

        if (isset($this->request->getParameters()['transactionId']) && ! empty($this->request->getParameters()['transactionId'])) {
            return $this->request->getParameters()['transactionId'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        if (isset($this->data['orderId']) && ! empty($this->data['orderId'])) {
            return (string)$this->data['orderId'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        if (!$this->isSuccessful() && isset($this->data['status']['statusDesc']) && isset($this->data['status']['statusDesc'])) {
            return $this->data['status']['statusDesc'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        if (!$this->isSuccessful() && isset($this->data['status']['code']) && isset($this->data['status']['code'])) {
            return $this->data['status']['code'];
        }

        return null;
    }
}
