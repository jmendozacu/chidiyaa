<?php

class TM_Cache_Block_Adminhtml_Log_Grid_Renderer_Theme
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $json   = $row->getData($this->getColumn()->getIndex());
        $params = Mage::helper('core')->jsonDecode($json);
        return $params['package'] . '/' . $params['theme'];
    }
}
