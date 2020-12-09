<?php

/**
 * Vtn_Datacap ActionPredispatch
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Observer\Controller;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\HTTP\Client\Curl;

/**
 * ActionPredispatch class
 */
class ActionPredispatch implements \Magento\Framework\Event\ObserverInterface
{

    
    const MODULE_NAME = 'Vtn_Datacap';

    const URL = "https://mageplugins.pub.vtnetzwelt.com/index.php";

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Undocumented variable
     *
     * @var Data
     */
    protected $data;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMeta;

    /**
     *
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var EncoderInterface
     */
    protected $encode;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @param ManagerInterface $messageManager
     * @param Session $backendSession
     * @param DateTime $date
     * @param StoreManagerInterface $storeManagerInterface
     * @param ProductMetadataInterface $productMetadataInterface
     * @param ModuleListInterface $moduleListInterface
     * @param EncoderInterface $encoderInterface
     * @param Curl $curl
     * @param Data $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Session $backendSession,
        DateTime $date,
        StoreManagerInterface $storeManagerInterface,
        ProductMetadataInterface $productMetadataInterface,
        ModuleListInterface $moduleListInterface,
        EncoderInterface $encoderInterface,
        Curl $curl,
        Data $data
    ) {
        $this->messageManager = $messageManager;
        $this->backendSession = $backendSession;
        $this->date = $date;
        $this->storeManager = $storeManagerInterface;
        $this->productMeta = $productMetadataInterface;
        $this->moduleList = $moduleListInterface;
        $this->encode = $encoderInterface;
        $this->curl = $curl;
        $this->data = $data;
    }



    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        if (!$this->backendSession->isLoggedIn()) {
            return; // Isn't logged in
        }

        if ($observer->getRequest()->isXmlHttpRequest()) {
            return; // It's ajax request
        }

        if ($observer->getRequest()->getMethod() == 'POST') {
            return; // It's post request
        }


        $this->checkReviews();
    }

    /**
     * Check if any pending review exists
     * @return void
     */
    protected function checkReviews()
    {
        $params = [
            "user_site_url" => $this->storeManager->getStore()->getBaseUrl(),
            "user_site_admin_url" => $this->data->getHomePageUrl(),
            "site_mage_version" => $this->productMeta->getVersion(),
            "installed_extension_version" => $this->getVersion(),
            "store_key" => $this->getEncodeUrl($this->storeManager->getStore()->getBaseUrl()),
            "created_at" => $this->date->date(),
            "extension_name" => self::MODULE_NAME
        ];

        try {
            $response = $this->backendSession->getData("has_response");
            
            if (empty($response)) {
                $headers = ["Content-Type" => "application/json", "Accept" => "application/json"];
                $this->curl->setHeaders($headers);
                $this->curl->post(self::URL, json_encode($params));
                $response = json_decode($this->curl->getBody(), true);
                $this->backendSession->setData("has_response", $response);
                $this->addNotice($response);
            } else {
                $this->addNotice($response);
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     * Get Vesrion
     * @return void
     */
    public function getVersion()
    {
        return $this->moduleList
            ->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * Encode Url
     *
     * @param string $url
     * @return void
     */
    public function getEncodeUrl($url)
    {

        return $this->encode->encode($url);
    }

    /**
     * Add Notice
     *
     * @param array $response
     * @return void
     */
    public function addNotice($response)
    {
        if ($response["update_available"]) {
            $this->messageManager->addNotice(
                __("Datacap new verison is available <a href='%1' target='_blank'>click</a> here to download", $response["download_link"])
            );
        }
    }
}
