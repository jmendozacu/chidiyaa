<?php

class TM_Crawler_Model_System_Config_Source_Type
{
    public function getOptions()
    {
        return array(
            TM_Crawler_Model_Crawler::TYPE_CATEGORY => Mage::helper('catalog')->__('Category'),
            TM_Crawler_Model_Crawler::TYPE_PRODUCT => Mage::helper('catalog')->__('Product'),
            TM_Crawler_Model_Crawler::TYPE_CMS => Mage::helper('cms')->__('CMS')
        );
    }

    public function toOptionArray()
    {
        $options = array();
        foreach ($this->getOptions() as $value => $label) {
            $options[] = array(
                'label' => $label,
                'value' => $value
            );
        }
        return $options;
    }
}
