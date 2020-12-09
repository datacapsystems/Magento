<?php
/**
 * Vtn_Datacap TransferRefundFactory
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

class TransferRefundFactory implements TransferFactoryInterface
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
    private $developmentUrl = "https://pay-cert.dcap.com/v1/credit/return";

    /**
     *
     * @var string
     */
    private $productionUrl = "https://pay.dcap.com/v1/credit/return";

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DatacapPrintLog
     */
    private $log;
    

    /**
     *
     * @param TransferBuilder $transferBuilder
     * @param Config $config
     * @param DatacapPrintLog $datacapPrintLog
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        Config $config,
        DatacapPrintLog $datacapPrintLog
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
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
        $apiReques = [];
        $url = $this->getUrl();
        $apiRequest["Token"] = $request["Token"];
        $apiRequest["Amount"] = $request["Amount"];

        $this->log->writeLog($apiReques);
        
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
            return $this->developmentUrl;
        }
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
