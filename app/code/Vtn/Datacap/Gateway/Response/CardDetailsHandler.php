<?php
/**
 * Vtn_Datacap CardDetailsHandler
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Gateway\Response;

use Vtn\Datacap\Gateway\Config\Config;
use Vtn\Datacap\Gateway\SubjectReader;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Vtn\Datacap\Gateway\Helper\ResponseHandler;

/**
 * Class CardDetailsHandler
 */
class CardDetailsHandler implements HandlerInterface
{
    const CARD_TYPE = 'Brand';

    const CARD_LAST4 = 'Account';

    const CARD_NUMBER = 'Account';
    /**
     * Response Handler
     *
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        ResponseHandler $responseHandler
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->responseHandler = $responseHandler;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $creditCard = $this->responseHandler->validateResponse($response);

        /**
         * @TODO after changes in sales module should be refactored for new interfaces
         */

        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);
       
        $payment->setCcLast4($creditCard[self::CARD_LAST4]);

        // set card details to additional info
        $payment->setAdditionalInformation(self::CARD_NUMBER, 'xxxx-' . $creditCard[self::CARD_LAST4]);
        $payment->setAdditionalInformation(OrderPaymentInterface::CC_TYPE, $creditCard[self::CARD_TYPE]);
    }

    /**
     * Get type of credit card mapped from Datacap
     *
     * @param string $type
     * @return array
     */
    public function getCreditCardType($type)
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCcTypesMapper();

        return $mapper[$replaced];
    }
}
