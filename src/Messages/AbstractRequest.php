<?php

namespace Omnipay\PayU\Messages;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    /**
     * Get the gateway POS ID.
     *
     * @return string
     */
    public function getPosId()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway POS ID.
     *
     * @param  string|int $value
     * @return $this
     */
    public function setPosID($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Gets the API url.
     *
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->getParameter('apiUrl');
    }

    /**
     * Sets the API URL.
     *
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }

    /**
     * Gets the access token.
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->getParameter('accessToken');
    }

    /**
     * Sets the access token.
     *
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->setParameter('accessToken', $accessToken);
    }

    /**
     * Correct the amount.
     *
     * @see http://developers.payu.com/en/restapi.html#creating_new_order_api
     *
     * @param  float|string $value
     * @return int
     */
    protected function toAmount($value)
    {
        return (int) round($value * 100);
    }
}
