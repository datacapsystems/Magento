<?php

/**
 * Vtn_Datacap DatacapTokenDataBuilder
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Gateway\Request;

use Vtn\Datacap\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Vtn\Datacap\Gateway\Config\Config;
use Vtn\Datacap\Gateway\Request\DatacapPaymentToken;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException;
use Vtn\Datacap\Logger\DatacapPrintLog;

class DatacapTokenDataBuilder implements BuilderInterface
{

    const DATACAP_TOKEN = "datacap_token";

    const DATACAP_PAYMENT_TOKEN = "datacap_payment_token";

    const DATACAP_MID = "datacap_mid";

    /**
     * 
     * @var Config
     */
    private $config;

    /**
     *
     * @var DatacapPaymentToken
     */
    private $datacapPaymentToken;

    /**
     *
     * @var integer
     */
    private $paymentToken = 0;

    /**
     *
     * @var DatacapPrintLog
     */
    private $logger;


    /**
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param DatacapPaymentToken $datacapPaymentToken
     * @param DatacapPrintLog $logger
     */
    public function __construct(Config $config, SubjectReader $subjectReader, DatacapPaymentToken $datacapPaymentToken, DatacapPrintLog $logger)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->datacapPaymentToken = $datacapPaymentToken;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
      
        $storeId = $this->config->getStoreId();
        $this->paymentToken = $this->datacapPaymentToken->getToken($payment);

        $log = [
            'request' => $this->paymentToken,
            'client' => static::class
        ];
        $this->logger->writeLog($log);

        if (empty($this->paymentToken)) {
            throw new CouldNotSaveException(
                __('Payment token is empty. Please try to place the order again.')
            );
        }
        return [
            self::DATACAP_TOKEN => $this->config->getTokenId($storeId),
            self::DATACAP_PAYMENT_TOKEN => $this->paymentToken,
            self::DATACAP_MID => $this->config->getMerchantAccountId($storeId)
        ];
    }
}
