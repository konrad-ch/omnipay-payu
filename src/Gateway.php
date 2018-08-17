<?php

namespace Omnipay\PayU;

use Omnipay\Common\AbstractGateway;
use Omnipay\PayU\Messages\Notification;
use Omnipay\PayU\Messages\PurchaseRequest;
use Omnipay\PayU\Messages\AccessTokenRequest;
use Omnipay\PayU\Messages\CompletePurchaseRequest;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * @method \Omnipay\Common\Message\ResponseInterface capture(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface refund(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface authorize(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface completeAuthorize(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface void(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface createCard(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface updateCard(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface deleteCard(array $options = array())
 */
class Gateway extends AbstractGateway
{
    const URL_SANDBOX = 'https://secure.snd.payu.com';
    const URL_PRODUCTION = 'https://secure.payu.com';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'PayU';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultParameters()
    {
        return [
            'posId'        => '',
            'secondKey'    => '',
            'clientSecret' => '',
            'testMode'     => true,
            'posAuthKey'   => null,
        ];
    }

    /**
     * Gets the second key.
     *
     * @return string
     */
    public function getSecondKey()
    {
        return $this->getParameter('secondKey');
    }

    /**
     * Sets the second key.
     *
     * @param  string $secondKey
     * @return $this
     */
    public function setSecondKey($secondKey)
    {
        return $this->setParameter('secondKey', $secondKey);
    }

    /**
     * Gets the POS ID.
     *
     * @return $this
     */
    public function getPosId()
    {
        return $this->getParameter('posId');
    }

    /**
     * Sets the POS ID.
     *
     * @param  string $posId
     * @return $this
     */
    public function setPosId($posId)
    {
        return $this->setParameter('posId', $posId);
    }

    /**
     * Gets the client secret.
     *
     * @return $this
     */
    public function getClientSecret()
    {
        return $this->getParameter('clientSecret');
    }

    /**
     * Sets the client secret.
     *
     * @param  string $clientSecret
     * @return $this
     */
    public function setClientSecret($clientSecret)
    {
        return $this->setParameter('clientSecret', $clientSecret);
    }

    /**
     * Gets the POS auth key.
     *
     * @return $this
     */
    public function getPosAuthKey()
    {
        return $this->getParameter('posAuthKey');
    }

    /**
     * Sets the POS auth key.
     *
     * @param  string|null $posAuthKey
     * @return $this
     */
    public function setPosAuthKey($posAuthKey = null)
    {
        return $this->setParameter('posAuthKey', $posAuthKey);
    }

    /**
     * @return \Omnipay\PayU\Messages\AccessTokenResponse
     *
     * @throws InvalidRequestException
     */
    public function getAccessToken()
    {
        $response = $this->createRequest(AccessTokenRequest::class, [
            'clientId'     => $this->getParameter('posId'),
            'clientSecret' => $this->getParameter('clientSecret'),
            'apiUrl'       => $this->getApiUrl()
        ])->send();

        if (! $response->isSuccessful()) {
			throw new InvalidRequestException( 'Could not retrieve Oauth access token.' );
        }

        return $response;
    }

    /**
     * Sets the access token.
     *
     * @param  string $accessToken
     * @return $this
     */
    protected function setAccessToken($accessToken)
    {
        return $this->setParameter('accessToken', $accessToken);
    }

    /**
     * @return string
     */
    protected function getApiUrl()
    {
        if ($this->getTestMode()) {
            return self::URL_SANDBOX;
        }

        return self::URL_PRODUCTION;
    }

    /**
     * Sets the API URL.
     *
     * @param  string $apiUrl
     * @return $this
     */
    protected function setApiUrl($apiUrl)
    {
        return $this->setParameter('apiUrl', $apiUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);

        $this->setApiUrl($this->getApiUrl());

        return $this;
    }

    /**
     * Create the purchase request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\PayU\Messages\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        $this->setAccessToken($this->getAccessToken()->getAccessToken());

        return $this->createRequest(PurchaseRequest::class, $parameters);
    }

    /**
     * @param  array $parameters
     * @return \Omnipay\PayU\Messages\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = [])
    {
        $this->setAccessToken($this->getAccessToken()->getAccessToken());

        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    /**
     * Create the notification.
     *
     * @return Notification
     */
    public function acceptNotification()
    {
        return new Notification($this->httpRequest, $this->httpClient, $this->getSecondKey());
    }
}
