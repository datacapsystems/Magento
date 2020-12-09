<?php

/**
 * Vtn_Datacap ResponseHandler
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Gateway\Helper;

use Magento\Framework\Exception\CouldNotSaveException;

class ResponseHandler
{

    private const SUCCESS = "Approved";

    /**
     * Status
     *
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $refNo;

    /**
     * Return Code
     *
     * @var int
     */
    private $returnCode;

    /**
     *
     * @var string
     */
    private $ccType;

    /**
     * Message
     *
     * @var string
     */
    private $messgae;

    /**
     * Auth code
     *
     * @var [type]
     */
    private $authCode;


    private $accountNo;
    /**
     * Token
     *
     * @var int
     */
    private $token;



    /**
     * Get the value of status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the value of refNo
     */
    public function getRefNo()
    {
        return $this->refNo;
    }


    /**
     * Get the value of returnCode
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * Get the value of messgae
     */
    public function getMessgae()
    {
        return $this->messgae;
    }

    public function validateResponse($responseData)
    {
        $response = json_decode($responseData[0], true);
        if (isset($response["Status"]) && $response["Status"] == self::SUCCESS) {
            $this->status = $response["Status"];
            $this->messgae = $response["Message"];
            $this->refNo = $response["RefNo"];
            $this->token = $response["Token"];
            $this->accountNo =  $response["Account"];
            $this->ccType = $response["Brand"];
            return $response;
        } else {
            throw new CouldNotSaveException(
                __('There was a problem processing your order. Please review your card details or contact the merchant for help.')
            );
        }
    }

    /**
     * Get the value of authCode
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * Get the value of token
     */
    public function getToken()
    {
        return $this->token;
    }
    /**
     * Get the value of accountNo
     */
    public function getAccountNo()
    {
        return $this->accountNo;
    }

    /**
     * Get the value of ccType
     */
    public function getCcType()
    {
        return $this->ccType;
    }
}
