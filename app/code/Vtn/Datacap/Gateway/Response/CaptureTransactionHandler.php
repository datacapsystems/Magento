<?php
/**
 * Vtn_Datacap CaptureTransactionHandler
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */

namespace Vtn\Datacap\Gateway\Response;

use Vtn\Datacap\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order;
use Vtn\Datacap\Gateway\Helper\ResponseHandler;
use Psr\Log\LoggerInterface;
use Vtn\Datacap\Logger\DatacapPrintLog;
use Vtn\Datacap\Observer\DatacapAddCcData;
use Magento\Framework\Exception\CouldNotSaveException;


class CaptureTransactionHandler implements HandlerInterface
{

    const PAYMENT_INFO = 'datacap_payment_info';

    const CCNUMBER = "datacapPaymentCCNumber";

    const CCTYPE = "datacapPaymentCCType";

    const STATUS = "datacapPaymentStatus";


    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var DatacapPrintLog
     */
    private $customLogger;

   
    /**
     * Response Handler
     *
     * @var ResponseHandler
     */
    private $responseHandler;
    
    /**
     * 
     *
     * @param SubjectReader $subjectReader
     * @param ResponseHandler $responseHandler
     * @param LoggerInterface $loggerInterface
     * @param DatacapPrintLog $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        ResponseHandler $responseHandler,
        LoggerInterface $loggerInterface,
        DatacapPrintLog $logger
    ) {
        $this->responseHandler = $responseHandler;
        $this->subjectReader = $subjectReader;
        $this->logger = $loggerInterface;
        $this->customLogger = $logger;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $responseData)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
       

        try {
            if ($paymentDO->getPayment() instanceof Payment) {
                /** @var Payment $orderPayment */
                $response = $this->responseHandler->validateResponse($responseData);
                $log = ["client" => static::class,"response"=>$response];
                $this->customLogger->writeLog($log);

                $orderPayment = $paymentDO->getPayment();
                $this->setTransactionId($orderPayment, $this->responseHandler->getRefNo());
                $orderPayment->setAdditionalData(json_encode($response));

                $orderStatus = $orderPayment->getMethodInstance()->getConfigData('order_status');
                $orderState = $this->_getOrderstate($orderStatus);

                $orderPayment->getOrder()->setCustomerNoteNotify(0);
                $message = __('The Payment Status is %1. for the Capture Transaction ', $this->responseHandler->getStatus());
                $orderPayment->addTransactionCommentsToOrder(
                    $this->responseHandler->getRefNo(),
                    $message
                );

                if (!empty($orderState)) {
                    $orderPayment->getOrder()->setData('state', $orderState);
                    $orderPayment->getOrder()->setData('status', $orderStatus);
                }

                $orderPayment->unsAdditionalInformation(DatacapAddCcData::CC_DETAILS);
                $orderPayment->setAdditionalInformation(self::CCNUMBER, $this->responseHandler->getAccountNo());
                $orderPayment->setAdditionalInformation(self::CCTYPE, $this->responseHandler->getCcType());
                $orderPayment->setAdditionalInformation(self::STATUS, $this->responseHandler->getStatus());
                $orderPayment->setIsTransactionClosed($this->shouldCloseTransaction());
                $closed = $this->shouldCloseParentTransaction($orderPayment);
                $orderPayment->setShouldCloseParentTransaction($closed);
            }
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $this->logger->critical($message);
            throw new CouldNotSaveException(
                __('There was a problem processing your order. Please review your card details or contact the merchant for help.')
            );
        } 
    }

    /**
     * @param Payment $orderPayment
     * 
     * @return void
     */
    protected function setTransactionId(Payment $orderPayment, $refNo)
    {
        $orderPayment->setTransactionId($refNo);
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     */
    protected function shouldCloseTransaction()
    {
        return false;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return false;
    }

    /**
     * @param $orderStatus
     * @return string
     */
    protected function _getOrderstate($orderStatus)
    {
        $orderState = '';
        if ($orderStatus == Order::STATE_PENDING_PAYMENT) {
            $orderState = Order::STATE_PENDING_PAYMENT;
        } elseif ($orderStatus == Order::STATE_PAYMENT_REVIEW) {
            $orderState = Order::STATE_PAYMENT_REVIEW;
        }

        return $orderState;
    }
}
