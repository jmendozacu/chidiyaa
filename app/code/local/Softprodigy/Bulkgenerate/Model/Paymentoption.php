<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Paymentoption
 *
 * @author root
 */
class Softprodigy_Bulkgenerate_Model_Paymentoption extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('bulkgenerate/paymentoption');
    }
}
