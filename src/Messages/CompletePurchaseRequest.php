<?php

namespace Omnipay\PayU\Messages;

class CompletePurchaseRequest extends AbstractRequest
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $url = $this->getApiUrl() . '/api/v2_1/orders/' . urlencode($this->getTransactionReference());

        $response = $this->httpClient->request('GET', $url, [
            'Content-Type'  => 'application/json',
            'Authorization' => $this->getAccessToken(),
        ]);

        $response = new CompletePurchaseResponse($this,
            json_decode($response->getBody()->getContents(), true)
        );

        return $this->response = $response;
    }
}
