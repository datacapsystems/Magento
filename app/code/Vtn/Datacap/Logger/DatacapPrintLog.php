<?php

/**
 * Vtn_Datacap DatacapPrintLog
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Logger;

use Vtn\Datacap\Logger\DatacapLoggerInterface;
use Vtn\Datacap\Gateway\Config\Config;
use Magento\Framework\Exception\CouldNotSaveException;
class DatacapPrintLog
{

    const SANDBOX = "sandbox";

    /**
     *
     * @var DatacapLoggerInterface
     */
    private $log;

    /**
     * @var Config
     */
    private $config;


    /**
     *
     *
     * @param DatacapLoggerInterface $datacapLoggerInterface
     * @param Config $config
     */
    public function __construct(DatacapLoggerInterface $datacapLoggerInterface, Config $config)
    {
        $this->log = $datacapLoggerInterface;
        $this->config = $config;
    }

    /**
     * @param array $data
     * @param int $storeId
     * @return void
     */
    public function writeLog($data, $storeId = null)
    {
        try {

            if ($this->config->getDebugStatus($storeId)) {
                $this->log->info($data);
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            throw new CouldNotSaveException(
                __('1% Sorry, but something went wrong',$ex->getMessage())
            );
        }
    }
}
