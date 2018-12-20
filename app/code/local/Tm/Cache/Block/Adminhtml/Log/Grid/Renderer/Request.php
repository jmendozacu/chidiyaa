<?php

class TM_Cache_Block_Adminhtml_Log_Grid_Renderer_Request
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
        $value  = array();
        foreach (array('request_params', 'request_uri') as $key) {
            if (empty($params[$key])) {
                continue;
            }
            $value[] = $params[$key];
        }
        return '<div class="word-break divider-hr" style="width: 200px;">'
            . implode('<hr/>', $value)
            . '</div>';
    }
}
