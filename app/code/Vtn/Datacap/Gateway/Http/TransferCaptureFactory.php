<?php
/**
 * Vtn_Datacap TransferCaptureFactory
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
use Magento\Store\Model\StoreManagerInterface;
use Vtn\Datacap\Logger\DatacapPrintLog;

class TransferCaptureFactory implements TransferFactoryInterface
{
    
    public const PRODUCTION = "production";

    public const SANDBOX = "sandbox";

    /**
     *
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     *
     * @var string
     */
    private $devdevelopmentUrl = "https://pay-cert.dcap.com/v1/credit/sale";

    /**
     *
     * @var string
     */
    private $productionUrl = "https://pay.dcap.com/v1/credit/sale";

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param StoreManagerInterface $storeManagerInterface
     * @param DatacapPrintLog $datacapPrintLog
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        DatacapPrintLog $datacapPrintLog
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->storeManager = $storeManagerInterface;
        $this->log = $datacapPrintLog;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $apiRequest = [];
        $url = $this->getUrl();
        $apiRequest["Token"] = $request["Token"];
        $apiRequest["Amount"] = $request["Amount"];

        $log = [
            "client" => static::class,
            "data" => $apiRequest
        ];
        $this->log->writeLog($log);
        $headers = array("Content-Type" => "application/json", "Accept" => "application/json", "Authorization" => $request["datacap_mid"]);
        return $this->transferBuilder
            ->setMethod('POST')
            ->setHeaders($headers)
            ->setUri($url)
            ->setBody($apiRequest)
            ->build();
    }


    /**
     * Url function
     *
     * @return Url
     */
    public function getUrl()
    {
        $storeId = $this->getStoreId();
        $environment = $this->config->getEnvironment($storeId);
        if ($environment == self::PRODUCTION) {
            return $this->productionUrl;
        } else {
            return $this->devdevelopmentUrl;
        }
    }


    /**
     * StoreId function
     *
     * @return storeId
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
