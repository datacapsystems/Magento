<?php

/**
 * Vtn_Datacap TransferFactory
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Vtn\Datacap\Gateway\Config\Config;
use Vtn\Datacap\Logger\DatacapPrintLog;
use Magento\Framework\Exception\CouldNotSaveException;

class TransferFactory implements TransferFactoryInterface
{

    public const PRODUCTION = "production";

    public const SANDBOX = "sandbox";

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     *
     * @var Config
     */
    private $config;

    /**
     *
     * @var string
     */
    private $developmentUrl = "https://pay-cert.dcap.com/v1/credit/preauth/";

    /**
     *
     * @var string
     */
    private $productionUrl = "https://pay.dcap.com/v1/credit/preauth/";


    /**
     *
     * @var string
     */
    private $authCaptDevelopmentUrl = "https://pay-cert.dcap.com/v1/credit/sale/";

    /**
     *
     * @var string
     */
    private $authCaptProductionUrl = "https://pay.dcap.com/v1/credit/sale/";

    /**
     *
     * @var DatacapPrintLog
     */
    private $logger;

    
    /**
     *
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param DatacapPrintLog $logger
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        DatacapPrintLog $logger
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {

        $log = [
            'request' => $request,
            'client' => static::class
        ];

        $storeId = $this->config->getStoreId();

        $environment = $this->config->getEnvironment($storeId);
        $paymentAction = $this->config->getPaymentAction($storeId);

        $this->logger->writeLog($log, $storeId);
       
        try {
            $url = $this->getUrl($environment, $paymentAction);
            $apiRequest["Token"] = $request["datacap_payment_token"];
            $apiRequest["Amount"] = $request["amount"];
            $headers = array("Content-Type" => "application/json", "Accept" => "application/json", "Authorization" => $request["datacap_mid"]);
            return $this->transferBuilder
                ->setMethod('POST')
                ->setHeaders($headers)
                ->setUri($url)
                ->setBody($apiRequest)
                ->build();
        } catch (\Exception $e) {
            $log["message"] = $e->getMessage();
            $this->logger->writeLog($log);
            throw new CouldNotSaveException(
                __('There was a problem processing your order. Please review your card details or contact the merchant for help.')
            );
        }
    }


    /**
     *
     * @param string $environment
     * @param string $paymentAction
     * @return string
     */
    public function getUrl($environment, $paymentAction)
    {
        $url = "";
        if ($environment == self::PRODUCTION && $paymentAction == "authorize") {
            $url =  $this->productionUrl;
        } elseif ($environment == self::PRODUCTION && $paymentAction == "authorize_capture") {
            $url =  $this->authCaptProductionUrl;
        } elseif ($environment == self::SANDBOX && $paymentAction == "authorize") {
            $url =  $this->developmentUrl;
        } else {
            $url =  $this->authCaptDevelopmentUrl;
        }
        return $url;
    }


    
}
