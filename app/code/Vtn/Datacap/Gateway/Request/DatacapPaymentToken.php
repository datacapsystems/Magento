<?php
/**
* Vtn_Datacap DatacapPaymentToken
* @category VTN
* @package Vtn_Datacap
* @version 1.0.0
* @author VTNetzwelt
*/

namespace Vtn\Datacap\Gateway\Request;

class DatacapPaymentToken
{
    /**
     *
     * @var string
     */
    protected $token;

    /**
     *
     * @param object $payment
     * @return string
     */
    public function getToken($payment)
    {
        $additionalInformation = $payment->getAdditionalInformation();
        if (isset($additionalInformation["cc_datacap_details"])) {
            $dataCapinfo = $additionalInformation["cc_datacap_details"];
            if ($dataCapinfo["datacap_payment_token"]) {
                return $dataCapinfo["datacap_payment_token"];
            }
        }
        
    }
}
