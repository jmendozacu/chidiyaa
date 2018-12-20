<?php

class TM_Crawler_Block_Adminhtml_Crawler_Grid_Renderer_State
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $state = $row->getData($this->getColumn()->getIndex());

        if (TM_Crawler_Model_Crawler::STATE_NEW === $state
            || TM_Crawler_Model_Crawler::STATE_COMPLETED === $state) {

            return $options[$state];
        }

        return $options[$state]
            . '<br/>'
            . '<pre style="padding: 5px 0 5px 10px;">'
            . implode("\n", array(
                Mage::helper('tmcrawler')->__('Current State'),
                str_pad(Mage::helper('catalog')->__('Type'), 10) . $row->getCurrentType(),
                str_pad(Mage::helper('catalog')->__('Store'), 10) . Mage::app()->getStore($row->getCurrentStoreId())->getName(),
                str_pad(Mage::helper('directory')->__('Currency'), 10) . $row->getCurrentCurrency(),
                str_pad(Mage::helper('tmcrawler')->__('Offset'), 10) . $row->getOffset()
            ))
            . '</pre>';
    }
}
