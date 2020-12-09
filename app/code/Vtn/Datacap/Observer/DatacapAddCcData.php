<?php
/**
 * Vtn_Datacap DatacapAddCcData
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;


class DatacapAddCcData extends AbstractDataAssignObserver
{
    /**
     * @var array
     */
    public const CC_DETAILS = 'cc_datacap_details';

    /**
     *
     * @var array
     */
    private $ccKeys = [
        'datacap_payment_token',
       
    ];
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $dataObject = $this->readDataArgument($observer);

        $additionalData = $dataObject->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $ccData = array_intersect_key($additionalData, array_flip($this->ccKeys));
        if (count($ccData) !== count($this->ccKeys)) {
            return;
        }
        $paymentModel = $this->readPaymentModelArgument($observer);

        $paymentModel->setAdditionalInformation(
            Self::CC_DETAILS,
            $this->sortCcData($ccData)
        );

        // CC data should be stored explicitly
        foreach ($ccData as $ccKey => $ccValue) {
            $paymentModel->setData($ccKey, $ccValue);
        }
    }

    /**
     * @param array $ccData
     * @return array
     */
    private function sortCcData(array $ccData)
    {
        $r = [];
        foreach ($this->ccKeys as $key) {
            $r[$key] = isset($ccData[$key]) ? $ccData[$key] : null;
        }

        return $r;
    }
}
