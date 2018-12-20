<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Observer
 *
 * @author root
 */
class Softprodigy_Orderexport_Model_Observer {

    public function addMassAction($observer) {
        $block = $observer->getEvent()->getBlock();
        if (get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'sales_order') {
            
            $block->addItem('export_sel_orders', array(
                'label' => 'Export Selected Orders',
                'url' => Mage::app()->getStore()->getUrl('orderexport/index/export'),
            ));
            
        }
    }

}
