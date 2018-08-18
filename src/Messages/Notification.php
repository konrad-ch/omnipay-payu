<?php

namespace Omnipay\PayU\Messages;

use Symfony\Component\HttpFoundation\Request;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Exception\InvalidRequestException;

class Notification implements NotificationInterface
{
    /* Constants */
    const STATUS_ON_HOLD = 'on-hold';
    const OPEN_PAY_U_SIGNATURE = 'OpenPayU-Signature';
    const X_OPEN_PAY_U_SIGNATURE = 'X-OpenPayU-Signature';

    /**
     * The request client.
     *
     * @var \Omnipay\Common\Http\ClientInterface
     */
    protected $httpClient;

    /**
     * The HTTP request object.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $httpRequest;

    /**
     * The second key.
     *
     * @var string
     */
    protected $secondKey;

    /**
     * Cache the data.
     *
     * @var \stdClass
     */
    protected $cachedData;

    /**
     * Constructor.
     *
     * @param \Omnipay\Common\Http\ClientInterface      $httpRequest
     * @param \Symfony\Component\HttpFoundation\Request $httpClient
     * @param string                                    $secondKey
     */
    public function __construct($httpRequest, $httpClient, $secondKey)
    {
        $this->httpRequest = $httpRequest;
        $this->httpClient  = $httpClient;
        $this->secondKey   = $secondKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (! $this->cachedData) {
            $content = trim($this->httpRequest->getContent());

            $incomingSignature = $this->getSignature($this->httpRequest);

            $sign = Signature::parse($incomingSignature);

            if ($sign && Signature::verify($content, $sign->signature, $this->secondKey, $sign->algorithm)) {
                $this->cachedData = json_decode($content);
            } else {
                throw new InvalidRequestException('Invalid signature - ' . ($sign ? $sign->signature : 'unknown'));
            }
        }

        return $this->cachedData;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionReference()
    {
        if (isset($this->getData()->order->extOrderId) && ! empty($this->getData()->order->extOrderId)) {
            return (string) $this->getData()->order->extOrderId;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionStatus()
    {
        if ($this->getData()) {
            $status = $this->getData()->order->status;

            if ('COMPLETED' === $status) {
                return NotificationInterface::STATUS_COMPLETED;
            }

            if ('PENDING' === $status) {
                return NotificationInterface::STATUS_PENDING;
            }

            if ('WAITING_FOR_CONFIRMATION' === $status) {
                return static::STATUS_ON_HOLD;
            }

            if (in_array($status, ['CANCELLED', 'REJECTED'])) {
                return NotificationInterface::STATUS_FAILED;
            }

            throw new InvalidRequestException('We have received unknown status "' . $status . '"');
        }

        return null;
    }

    /**
     * @param Request $request
     *
     * @return string
     * @throws InvalidRequestException
     */
    protected function getSignature(Request $request)
    {
        if ($request->headers->has(self::OPEN_PAY_U_SIGNATURE)) {
            return $request->headers->get(self::OPEN_PAY_U_SIGNATURE);
        }

        if ($request->headers->has(self::X_OPEN_PAY_U_SIGNATURE)) {
            return $request->headers->get(self::X_OPEN_PAY_U_SIGNATURE);
        }

        throw new InvalidRequestException('There is no ' . self::OPEN_PAY_U_SIGNATURE . ' or ' . self::X_OPEN_PAY_U_SIGNATURE . ' header present in request');
    }
}
