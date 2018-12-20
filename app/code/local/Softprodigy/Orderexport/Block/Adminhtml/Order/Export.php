<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Export
 *
 * @author root
 */
class Softprodigy_Orderexport_Block_Adminhtml_Order_Export extends Mage_Adminhtml_Block_Widget_Grid {

    //put your code here
    public function __construct() {
        parent::__construct();
        $this->setId('orderCustomGrid');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection() {
        $collection = new Varien_Data_Collection();
        $order_ids = Mage::registry('selected_orders');
        if (!empty($order_ids) and is_array($order_ids)) {
            $resourceModel = Mage::getSingleton('core/resource');
            $ocollection = Mage::getModel('sales/order')->getCollection();
            $ocollection->addFieldToFilter('entity_id', array('in' => $order_ids));
            $ocollection->getSelect()->joinRight(
                    array('orad' => $resourceModel->getTableName('sales/order_address')),
                    "orad.parent_id=main_table.entity_id and address_type='shipping'", 
                    array("shipping_name" => "TRIM(BOTH ' ' FROM CONCAT_WS(' ', orad.firstname, orad.middlename, orad.lastname))"
                        ,'city','region','country_id','street','postcode','telephone','company'
                        )
            );

            $ocollection->getSelect()->joinRight(
                    array('orpay' => $resourceModel->getTableName('sales/order_payment')), 'orpay.parent_id=main_table.entity_id', array('pay_method' => 'orpay.method', 'base_amount_paid', 'base_amount_ordered')
            );

            $ocollection->getSelect()->order('entity_id DESC');
            
            $itmcollection = Mage::getModel('sales/order_item')->getCollection();
            $itmcollection->addFieldToFilter('order_id', array('in' => $order_ids));

            $invcollection = Mage::getResourceModel('sales/order_invoice_collection');
            $invcollection->addFieldToFilter('order_id', array('in' => $order_ids));

            foreach ($ocollection as $order) {
                $item = new Varien_Object();
                $item->setData($order->getData());
                $item->setCodAmt('');
                if ($order->getPayMethod() == "cashondelivery") {
                  //  $item->setCodAmt($order->getOrderCurrency()->formatPrecision($order->getGrandTotal(), 2, array(), false, false));
                    $item->setCodAmt(number_format($order->getGrandTotal(),2));
                 }
                
                // $item->setGrantAmt($order->getOrderCurrency()->formatPrecision($order->getGrandTotal(), 2, array(), false, false));
                   $item->setGrantAmt(number_format($order->getGrandTotal(),2));
                
                $item->setPayementTitle(Mage::getStoreConfig('payment/' . $order->getPayMethod() . '/title'));

                $oItms = $itmcollection->getItemsByColumnValue('order_id', $order->getId());
                $itmnames = array();
                $itmWt = 0;
                foreach ($oItms as $oitm) {
                    $itmnames[] = $oitm->getsku();
                    $itmWt += ($oitm->getweight() ? (float) $oitm->getweight() : 0);
                }
                $item->setItemNames(implode(",", $itmnames));
                $item->setItemsWeight($itmWt);

                $invItms = $invcollection->getItemsByColumnValue('order_id', $order->getId());
                $invIncr = array();
                $invIncrDate = array();
                foreach ($invItms as $invItm) {
                    $invIncr[] = $invItm->getIncrementId();
                    $invIncrDate[] = $invItm->getCreatedAt();
                }
                
                $item->setStreet(trim($order->getCompany().' '.$order->getStreet()));
                
                $item->setInvoices(implode(",", $invIncr));
                $item->setInvoiceDates(implode(",", $invIncrDate));
                $collection->addItem($item);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('increment_id', array(
            'header' => Mage::helper('orderexport')->__('Order No'),
            'index' => 'increment_id',
            'align' => 'center'
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('orderexport')->__('Consignee Name'),
            'index' => 'shipping_name',
            'align' => 'center'
        ));

        $this->addColumn('city', array(
            'header' => Mage::helper('orderexport')->__('City'),
            'index' => 'city',
            'align' => 'center'
        ));
        $this->addColumn('region', array(
            'header' => Mage::helper('orderexport')->__('State'),
            'index' => 'region',
            'align' => 'center'
        ));


        $this->addColumn('country_id', array(
            'header' => Mage::helper('orderexport')->__('Country'),
            'index' => 'country_id',
            'align' => 'center'
        ));

        $this->addColumn('street', array(
            'header' => Mage::helper('orderexport')->__('Address'),
            'index' => 'street',
            'align' => 'center'
        ));
        $this->addColumn('postcode', array(
            'header' => Mage::helper('orderexport')->__('Pincode'),
            'index' => 'postcode',
            'align' => 'center'
        ));
        $this->addColumn('telephone', array(
            'header' => Mage::helper('orderexport')->__('Telephone'),
            'index' => 'telephone',
            'align' => 'center'
        ));
        $this->addColumn('mobile_no', array(
            'header' => Mage::helper('orderexport')->__('Mobile'),
            'index' => 'mobile_no',
            'align' => 'center'
        ));
        $this->addColumn('items_weight', array(
            'header' => Mage::helper('orderexport')->__('Weight'),
            'index' => 'items_weight',
            'align' => 'center'
        ));
        $this->addColumn('payement_title', array(
            'header' => Mage::helper('orderexport')->__('Payment Mode'),
            'index' => 'payement_title',
            'align' => 'center'
        ));

        $this->addColumn('grant_amt', array(
            'header' => Mage::helper('orderexport')->__('Package Amount'),
            'index' => 'grant_amt',
            'align' => 'center'
        ));
        $this->addColumn('cod_amt', array(
            'header' => Mage::helper('orderexport')->__('Cod Amount'),
            'index' => 'cod_amt',
            'align' => 'center'
        ));
        $this->addColumn('item_names', array(
            'header' => Mage::helper('orderexport')->__('Product to be Shipped'),
            'index' => 'item_names',
            'align' => 'center'
        ));

        $this->addColumn('invoices', array(
            'header' => Mage::helper('orderexport')->__('Invoice No'),
            'index' => 'invoices',
            'align' => 'center'
        ));
        $this->addColumn('invoice_dates', array(
            'header' => Mage::helper('orderexport')->__('Seller Inv Date'),
            'index' => 'invoice_dates',
            'align' => 'center'
        ));
        $this->addExportType('*/*/export', Mage::helper('orderexport')->__('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row) {
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
