<?php
namespace Omnipay\PayU\Messages;

class PurchaseRequest extends AbstractRequest
{
    /**
     * Get the order number.
     *
     * @return string|int
     */
    public function getOrderNumber()
    {
        return $this->getParameter('orderNumber');
    }

    /**
     * Set the order number.
     *
     * @param  array $order
     * @return $this
     */
    public function setOrderNumber($order)
    {
        return $this->setParameter('orderNumber', $order);
    }

    /**
     * Gets the buyer.
     *
     * @return array
     */
    public function getBuyer()
    {
        return $this->getParameter('buyer');
    }

    /**
     * Sets the buyer.
     *
     * @param  array $buyer
     * @return $this
     */
    public function setBuyer($buyer)
    {
        return $this->setParameter('buyer', $buyer);
    }

    /**
     * Gets the payment methods.
     *
     * @return mixed
     */
    public function getPayMethods()
    {
        return $this->getParameter('payMethods');
    }

    /**
     * Sets the payment methods.
     *
     * @param  array $methods
     * @return $this
     */
    public function setPayMethods($methods)
    {
        return $this->setParameter('payMethods', $methods);
    }

    /**
     * Gets the payment settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->getParameter('settings');
    }

    /**
     * Gets the payment setting.
     *
     * @param  array $settings
     * @return $this
     */
    public function setSettings($settings)
    {
        return $this->setParameter('settings', $settings);
    }

    /**
     * Send the request with specified data.
     *
     * @param  mixed $data The data to send.
     * @return \Omnipay\PayU\Messages\PurchaseResponse
     */
    public function sendData($data)
    {
        $apiUrl = $this->getApiUrl() . '/api/v2_1/orders';

        if (isset($data['extOrderId'])) {
            $this->setTransactionId($data['extOrderId']);
        }

        $response = $this->httpClient->request('POST', $apiUrl, [
            'Content-Type'  => 'application/json',
            'Authorization' => $this->getAccessToken(),
        ], json_encode($data));

        $response = new PurchaseResponse(
            $this,
            json_decode($response->getBody()->getContents(), true)
        );

        return $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->validate('amount', 'currency', 'items');

        $data                  = [];
        $data['totalAmount']   = $this->toAmount($this->getAmount());
        $data['currencyCode']  = strtoupper($this->getCurrency());
        $data['merchantPosId'] = $this->getPosId();
        $data['continueUrl']   = $this->getReturnUrl();
        $data['notifyUrl']     = $this->getNotifyUrl();
        $data['description']   = $this->getDescription();
        $data['customerIp']    = $this->getClientIp() ?: $this->httpRequest->getClientIp();

        if ($this->getOrderNumber()) {
            $data['extOrderId'] = $this->getOrderNumber();
        }

        if ($this->getBuyer()) {
            $data['buyer'] = $this->getBuyer();
        }

        if ($this->getPayMethods()) {
            $data['payMethods'] = $this->getPayMethods();
        }

        if ($this->getSettings()) {
            $data['settings'] = $this->getSettings();
        }

        if ($items = $this->getItems()) {
            $data['products'] = [];

            foreach ($items as $i => $item) {
                $data['products'][$i] = [
                    'name'      => $item->getName(),
                    'unitPrice' => $this->toAmount($this->formatCurrency($item->getPrice())),
                    'quantity'  => $item->getQuantity(),
                ];
            }
        }

        return $data;
    }
}
