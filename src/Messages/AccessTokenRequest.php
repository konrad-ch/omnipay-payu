<?php
namespace Omnipay\PayU\Messages;

use Omnipay\Common\Message\AbstractRequest;

class AccessTokenRequest extends AbstractRequest
{
    const OAUTH_CONTEXT = '/pl/standard/user/oauth/authorize';

    /** @var string */
    private $apiUrl;

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->setParameter('clientId', $clientId);
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->setParameter('clientSecret', $clientSecret);
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $response = $this->httpClient->request('POST', $this->apiUrl . static::OAUTH_CONTEXT, [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($data, '', '&'));

        return new AccessTokenResponse(
            $this,
            json_decode($response->getBody()->getContents(), true)
        );
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->parameters->get('clientId'),
            'client_secret' => $this->parameters->get('clientSecret'),
        ];
    }
}
