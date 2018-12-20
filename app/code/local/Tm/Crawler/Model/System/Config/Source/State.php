<?php

class TM_Crawler_Model_System_Config_Source_State
{
    public function getOptions()
    {
        return array(
            TM_Crawler_Model_Crawler::STATE_NEW => Mage::helper('tmcrawler')->__('New'),
            TM_Crawler_Model_Crawler::STATE_RUNNING => Mage::helper('tmcrawler')->__('Running'),
            TM_Crawler_Model_Crawler::STATE_PENDING => Mage::helper('tmcrawler')->__('Pending'),
            TM_Crawler_Model_Crawler::STATE_COMPLETED => Mage::helper('tmcrawler')->__('Completed')
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
