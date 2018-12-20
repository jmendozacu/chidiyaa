<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Customcarrier
 *
 * @author root
 */
class Softprodigy_Bulkgenerate_Resource_Paymentoption extends Mage_Core_Model_Resource_Db_Abstract {
    
    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = array();

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = array();

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = array();

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('bulkgenerate/paymentoption', 'id');
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param Varien_Object $object
     * @throws Mage_Core_Exception
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    public function uploadAndImport(Varien_Object $object) {

        if (empty($_FILES['groups']['tmp_name']['general']['fields']['paymentimport']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['general']['fields']['paymentimport']['value'];
        $website = Mage::app()->getWebsite($object->getScopeId());//resulting empty

        $this->_importWebsiteId = (int) $website->getId();
        $this->_importUniqueHash = array();
        $this->_importErrors = array();
        $this->_importedRows = 0;

        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 2) {
            $io->streamClose();
            Mage::throwException(Mage::helper('shipping')->__('Invalid Csv File Format'));
        }


        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber = 1;
            $importData = array();
            
            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }
                // delete old data by website and condition name
                $rowCatids = array();
                $allPaymentMethods = Mage::getModel('payment/config')->getAllMethods();
                // echo "<pre>";
                $allMethods = array_keys($allPaymentMethods);
                if (!empty($allMethods)) {
                    foreach ($allMethods as $_Code) {
                        if($_Code == $csvLine[0]){
                            $condition = array(
                                'website_id = ?' => $this->_importWebsiteId,
                                'payment_code = ?' => $_Code
                            );
                            $adapter->delete($this->getMainTable(), $condition);
                            $csvLine[0] = $_Code;
                            $row = $this->_getImportRow($csvLine, $rowNumber);
                            if ($row !== false) {
                                $importData[] = $row;
                            }
                        }
                    }
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('shipping')->__('An error occurred while import csv.'));
        }

        $adapter->commit();

        if ($this->_importErrors) {
            $error = Mage::helper('shipping')->__('File has not been imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;
    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0) {
        // validate row
        
        if (count($row) < 2) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Invalid csv format in the Row #%s', $rowNumber);
            return false;
        }

        // strip whitespace from the beginning and end of each row
        foreach ($row as $k => $v) {
            $row[$k] = trim($v);
        }

        $paymentcode = $row[0];
        $capture_mode = !empty($row[1]) ? 1 : 0;
          
        // protect from duplicate
        $hash = sprintf("%s-%s", $paymentcode, $capture_mode);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('shipping')->__('Duplicate Row #%s .', $rowNumber);
            return false;
        }
        
        $this->_importUniqueHash[$hash] = true;


        return array(
            $this->_importWebsiteId, // website_id
            $paymentcode, //paymentcode
            $capture_mode
        );
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return Mage_Shipping_Model_Resource_Carrier_Tablerate
     */
    protected function _saveImportData(array $data) {
        if (!empty($data)) {
            $columns = array('website_id', 'payment_code', 'capture_mode');
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }
    
}
