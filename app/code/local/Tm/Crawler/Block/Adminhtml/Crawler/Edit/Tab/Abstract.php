<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit_Tab_Abstract
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function getCrawler()
    {
        return Mage::registry('crawler');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return !$this->canShowTab();
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/' . $action);
    }
}
