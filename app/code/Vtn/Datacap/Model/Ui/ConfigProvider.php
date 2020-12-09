<?php

/**
 * Vtn_Datacap ConfigProvider
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Model\Ui;

use Vtn\Datacap\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Class ConfigProvider
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'datacap_gateway';


    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    public $clientToken = '';

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     *
     * @var CcConfig
     */
    private $ccConfig;

    /**
     *
     * @var PaymentConfig
     */
    private $paymentConfig;
    /**
     * Constructor
     *
     * @param Config $config
     * @param SessionManagerInterface $session
     */
    public function __construct(
        Config $config,
        SessionManagerInterface $session,
        CcConfig $ccConfig,
        PaymentConfig $paymentConfig
    ) {
        $this->config = $config;
        $this->session = $session;
        $this->ccConfig = $ccConfig;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $storeId = $this->session->getStoreId();
        $isActive = $this->config->isActive($storeId);
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $isActive,
                    'datacapToken' => $this->getToken(),
                    'countrySpecificCardTypes' => [self::CODE => $this->config->getCountrySpecificCardTypeConfig($storeId)],
                    'availableCardTypes' => [$this->config->getAvailableCardTypes($storeId)],
                    'environment' => $this->config->getEnvironment($storeId),
                    'hasFraudProtection' => $this->config->hasFraudProtection($storeId),
                    'months' => [$this->ccConfig->getCcMonths()],
                    'years' => [$this->ccConfig->getCcYears()],
                    'cvvImageUrl' => [$this->ccConfig->getCvvImageUrl()]

                ]
            ],
        ];
    }

    /**
     * Generate a new client token if necessary
     *
     * @return string
     */
    public function getToken()
    {

        $storeId = $this->session->getStoreId();
        return $this->config->getTokenId($storeId);
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getCcAvailableTypes($storeId)
    {
        $types = $this->paymentConfig->getCcTypes();

        $availableTypes = $this->config->getAvailableCardTypes($storeId);
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }
}
