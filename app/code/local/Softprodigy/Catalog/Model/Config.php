<?php
class Softprodigy_Catalog_Model_Config extends Mage_Catalog_Model_Config
{
    public function getAttributeUsedForSortByArray()
    {
        return array_merge(
			parent::getAttributeUsedForSortByArray(),
			array('qty_ordered' => Mage::helper('catalog')->__('Bestsellers'))
		);
    }
}
