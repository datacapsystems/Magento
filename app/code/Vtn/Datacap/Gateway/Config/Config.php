<?php
/**
 * Vtn_Datacap Configuration
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_TOKEN = 'datacap_token';
    const KEY_MID = 'datacap_ecommerce_mid';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_DEBUG = 'debug';
    const KEY_ORDER_STATUS = 'order_status';
    const KEY_CURRENCY = 'currency';
    const FRAUD_PROTECTION = 'fraudprotection';
    const DEFAULT_PATH_PATTERN = 'payment/datacap_gateway/';
    const PAYMENT_ACTION = "payment_action";
    const KEY_CC_TYPES_DATACAP_MAPPER = "cctypes_datacap_mapper";
    

    /**
     * @var Json
     */
    private $serializer;

    /**
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;


    /**
     * Datacap config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        StoreManagerInterface $storeManagerInterface,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManagerInterface;
    }

    /**
     * Return the country specific card type config
     *
     * @param int|null $storeId
     * @return array
     */
    public function getCountrySpecificCardTypeConfig($storeId = null)
    {
        $countryCardTypes = $this->getValue(self::KEY_COUNTRY_CREDIT_CARD, $storeId);
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAvailableCardTypes($storeId = null)
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES, $storeId);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Datacap card types
     *
     * @return array
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::KEY_CC_TYPES_DATACAP_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }

    public function getDebugStatus($storeId = null)
    {
        return $this->getValue(Config::KEY_DEBUG, $storeId);
    }

    /**
     * Gets list of card types available for country.
     *
     * @param string $country
     * @param int|null $storeId
     * @return array
     */
    public function getCountryAvailableCardTypes($country, $storeId = null)
    {
        $types = $this->getCountrySpecificCardTypeConfig($storeId);

        return (!empty($types[$country])) ? $types[$country] : [];
    }



    /**
     * Gets value of configured environment.
     *
     * Possible values: production or sandbox.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(Config::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * Gets merchant ID.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTokenId($storeId = null)
    {
        return $this->getValue(Config::KEY_TOKEN, $storeId);
    }

    public function getPaymentAction($storeId = null)
    {
        return $this->getValue(self::PAYMENT_ACTION, $storeId);
    }

    /**
     * Gets Merchant account ID.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantAccountId($storeId = null)
    {
        return $this->getValue(self::KEY_MID, $storeId);
    }


    /**
     * Checks if fraud protection is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function hasFraudProtection($storeId = null)
    {
        return (bool) $this->getValue(Config::FRAUD_PROTECTION, $storeId);
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool) $this->getValue(self::KEY_ACTIVE, $storeId);
    }


    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        $field = self::DEFAULT_PATH_PATTERN . $field;

        return $this->scopeConfig->getValue($field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
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
