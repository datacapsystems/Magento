<?php

/**
 * Vtn_Datacap RefundDataBuilder
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Gateway\Request;

use Vtn\Datacap\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Vtn\Datacap\Gateway\Config\Config;
use Magento\Sales\Model\Order\Payment;

class RefundDataBuilder implements BuilderInterface
{
    use Formatter;

    const DATACAP_MID = "datacap_mid";
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     *
     * @var integer
     */
    private $token = 0;


    /**
     * @var Config
     */
    private $config;

    /**
     *
     * @param SubjectReader $subjectReader
     * @param Config $config
   
     */
    public function __construct(SubjectReader $subjectReader, Config $config)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $additionDataJson = $payment->getAdditionalData();
        $additionData = json_decode($additionDataJson, true);
        $storeId = $this->getStoreId();

        if (isset($additionData["Token"])) {
            $this->token = $additionData["Token"];
        }
        $amount = null;
        try {
            $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));
        } catch (\InvalidArgumentException $e) {
           throw new \InvalidArgumentException($e->getMessage());
        }

        return [
            'Token' => $this->token,
            "Amount" => $amount,
            self::DATACAP_MID => $this->config->getMerchantAccountId($storeId)
        ];
    }


    /**
     * StoreId function
     *
     * @return storeId
     */
    public function getStoreId()
    {
        return $this->config->getStoreId();
    }
}
