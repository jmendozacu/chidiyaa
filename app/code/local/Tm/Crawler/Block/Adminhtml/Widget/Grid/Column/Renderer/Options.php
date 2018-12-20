<?php

class TM_Crawler_Block_Adminhtml_Widget_Grid_Column_Renderer_Options
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());

            // modification
            if (!$separator = $this->getColumn()->getSeparator()) {
                $separator = ',';
            }
            $value = explode($separator, $value);

            $res = array();
            foreach ($value as $item) {
                if (isset($options[$item])) {
                    $res[] = $this->escapeHtml($options[$item]);
                }
                elseif ($showMissingOptionValues) {
                    $res[] = $this->escapeHtml($item);
                }
            }
            return implode(', ', $res);
        }
    }
}
