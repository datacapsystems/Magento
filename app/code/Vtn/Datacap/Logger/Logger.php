<?php

/**
 * Vtn_Datacap Logger
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Logger;

class Logger extends \Monolog\Logger implements DatacapLoggerInterface
{

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function info($message, array $context = array())
    {
        $message = print_r($message, true);

        return parent::info($message, $context);
    }

}
