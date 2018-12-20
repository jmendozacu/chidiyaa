<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shipping carrier table rate grid block
 * WARNING: This grid used for export table rates
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Softprodigy_Bulkgenerate_Block_Adminhtml_Paymentoptions_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * Define grid properties
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('paymentTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = Mage::app()->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if (is_null($this->_websiteId)) {
            $this->_websiteId = Mage::app()->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return collection
     */
    protected function _prepareCollection()
    {
        /** @var $collection Softprodigy_Customcarrier_Model_Mysql4_Categoryopt_Collection */
        $collection = Mage::getModel('bulkgenerate/paymentoption')->getCollection();
        
        $allPaymentMethods = Mage::getModel('payment/config')->getAllMethods();
        // echo "<pre>";
        $allMethods = array_keys($allPaymentMethods);
        $newColl = clone $collection;
        $collection = new Varien_Data_Collection();

        foreach($allMethods as $method_code){
            $val = $newColl->getItemByColumnValue('payment_code', $method_code);
            
            $array = array(
                    'payment_code' => $method_code,
                    'capture_mode' => (($val and $val->getCaptureMode())? $val->getCaptureMode(): 0)
                );
             $rowObj = new Varien_Object();
             $rowObj->setData($array);
             $collection->addItem($rowObj);
        }
       

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn('payment_code', array(
            'header'    => Mage::helper('adminhtml')->__('payment_code'),
            'index'     => 'payment_code',
        ));
        
        $this->addColumn('capture_mode', array(
            'header'    => Mage::helper('adminhtml')->__('online_capture'),
            'index'     => 'capture_mode',
        ));
        
        
        return parent::_prepareColumns();
    }
}
