<?php
/**
 * Vtn_Datacap Client
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Http\Client\Zend;
use Vtn\Datacap\Logger\DatacapPrintLog as DatacapLog;

class Client implements ClientInterface
{

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DatacapLog
     */
    private $datacapLog;

    /**
     * @var Zend
     */
    private $paymentZendCurlClient;

    /**
     * @param Logger $logger
     * @param Zend $paymentZendCurlClient
     * @param DatacapLog $datacapLog
     */
    public function __construct(
        Logger $logger,
        Zend $paymentZendCurlClient,
        DatacapLog $datacapLog
    ) {
        $this->logger = $logger;
        $this->paymentZendCurlClient = $paymentZendCurlClient;
        $this->datacapLog = $datacapLog;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        
        $response = $this->paymentZendCurlClient->placeRequest($transferObject);
        $this->datacapLog->writeLog($response);
        return $response;
    }
}
