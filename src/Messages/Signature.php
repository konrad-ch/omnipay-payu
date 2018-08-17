<?php

namespace Omnipay\PayU\Messages;

/**
 * Methods below is derived from OpenPayU_Util.
 *
 * @see https://github.com/PayU/openpayu_php/blob/master/lib/OpenPayU/Util.php
 *
 * @package Omnipay\PayU\Messages
 */
class Signature
{
    /**
     * Function generate sign data
     *
     * @param array $data
     * @param string $algorithm
     * @param string $merchantPosId
     * @param string $signatureKey
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function generate(
        array $data,
        $algorithm = 'SHA-256',
        $merchantPosId = '',
        $signatureKey = ''
    ) {
        if (empty($signatureKey)) {
            throw new \InvalidArgumentException('Merchant Signature Key should not be null or empty.');
        }

        if (empty($merchantPosId)) {
            throw new \InvalidArgumentException('MerchantPosId should not be null or empty.');
        }

        $contentForSign = '';
        ksort($data);

        foreach ($data as $key => $value) {
            $contentForSign .= $key . '=' . urlencode($value) . '&';
        }

        if (in_array($algorithm, ['SHA-256', 'SHA'])) {
            $hashAlgorithm = 'sha256';
            $algorithm     = 'SHA-256';
        } elseif ($algorithm === 'SHA-384') {
            $hashAlgorithm = 'sha384';
            $algorithm     = 'SHA-384';
        } elseif ($algorithm === 'SHA-512') {
            $hashAlgorithm = 'sha512';
            $algorithm     = 'SHA-512';
        }

        $signature = hash($hashAlgorithm, $contentForSign . $signatureKey);
        $signData  = 'sender=' . $merchantPosId . ';algorithm=' . $algorithm . ';signature=' . $signature;

        return $signData;
    }

    /**
     * Function returns signature data object
     *
     * @param  string $data
     * @return \stdClass|null
     */
    public static function parse($data)
    {
        if (empty($data)) {
            return null;
        }

        $signatureData = [];

        $list = explode(';', rtrim($data, ';'));
        if (empty($list)) {
            return null;
        }

        foreach ($list as $value) {
            $explode = explode('=', $value);

            if (count($explode) !== 2) {
                return null;
            }

            $signatureData[$explode[0]] = $explode[1];
        }

        return (object) $signatureData;
    }

    /**
     * Function returns signature validate
     *
     * @param string $message
     * @param string $signature
     * @param string $signatureKey
     * @param string $algorithm
     *
     * @return bool
     */
    public static function verify($message, $signature, $signatureKey, $algorithm = 'MD5')
    {
        if (isset($signature)) {
            if ($algorithm === 'MD5') {
                $hash = md5($message . $signatureKey);
            } elseif (in_array($algorithm, ['SHA', 'SHA1', 'SHA-1'])) {
                $hash = sha1($message . $signatureKey);
            } else {
                $hash = hash('sha256', $message . $signatureKey);
            }

            if (strcmp($signature, $hash) === 0) {
                return true;
            }
        }

        return false;
    }
}
