<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of IndexController
 *
 * @author root
 */
class Softprodigy_Orderexport_IndexController extends Mage_Adminhtml_Controller_Action {

    public function exportAction() {
        $orderIds = $this->getRequest()->getParam('order_ids');
        Mage::register('selected_orders',$orderIds, true);
        $fileName = 'sales-report-' . gmdate('Y-m-d-H-i-s') . '.csv';
        $grid = $this->getLayout()->createBlock('orderexport/adminhtml_order_export');
        //var_dump($grid->getCsvFile());
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

}
