<?php

/**
 * Vtn_Datacap CaptureDataBuilder
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
use Magento\Store\Model\StoreManagerInterface;
use Vtn\Datacap\Logger\DatacapPrintLog;
use Vtn\Datacap\Gateway\Request\DatacapPaymentToken;
use Magento\Framework\Exception\CouldNotSaveException;

class CaptureDataBuilder implements BuilderInterface
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
     *
     * @var integer
     */
    private $refNo = 0;

    /**
     * Undocumented variable
     *
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     *
     * @var DatacapPrintLog
     */
    private $log;


    /**
     *
     * @var DatacapPaymentToken
     */
    private $datacapPaymentToken;

    /**
     *
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader, Config $config, StoreManagerInterface $storeManagerInterface, DatacapPrintLog $datacapPrintLog, DatacapPaymentToken $datacapPaymentToken)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->log = $datacapPrintLog;
        $this->datacapPaymentToken = $datacapPaymentToken;
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
        $this->log->writeLog($additionData);
        $storeId = $this->config->getStoreId();

        if (isset($additionData["Token"]) && isset($additionData["RefNo"])) {

            $this->token = $additionData["Token"];
            $this->refNo = $additionData["RefNo"];
        } else {
            $this->token = $this->datacapPaymentToken->getToken($payment);
        }

        if (empty($this->token)) {
            throw new CouldNotSaveException(
                __('Payment token is empty. Please try to place the order again.')
            );
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
            "RefNo" => $this->refNo,
            self::DATACAP_MID => $this->config->getMerchantAccountId($storeId)
        ];
    }


    
}
