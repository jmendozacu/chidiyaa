<?php

class TM_Cache_Block_Adminhtml_Log_Grid_Renderer_Params
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

        foreach ($this->getColumnsToUnset() as $key) {
            unset($params[$key]);
        }

        return '<div class="word-break" style="width: 300px;">'
            . str_replace(',"', ', "', Mage::helper('core')->jsonEncode($params))
            . '</div>';
    }

    public function getColumnsToUnset()
    {
        $cols = $this->getData('columns_to_unset');
        if (null === $cols) {
            return array();
        }
        return $cols;
    }
}
