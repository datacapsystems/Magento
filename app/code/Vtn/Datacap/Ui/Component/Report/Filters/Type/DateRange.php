<?php
/**
 * Vtn_Datacap DateRange
 * @category VTN
 * @package Vtn_Datacap
 * @version 1.0.0
 * @author VTNetzwelt
 */
namespace Vtn\Datacap\Ui\Component\Report\Filters\Type;

/**
 * Class DateRange
 */
class DateRange extends \Magento\Ui\Component\Filters\Type\Date
{
    /**
     * Datacap date format
     *
     * @var string
     */
    protected static $dateFormat = 'Y-m-d\TH:i:00O';
}

