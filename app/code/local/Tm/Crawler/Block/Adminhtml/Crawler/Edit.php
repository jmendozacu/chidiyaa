<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'tmcrawler';
        $this->_objectId   = 'crawler_id';
        $this->_controller = 'adminhtml_crawler';

        parent::__construct();

        if ($this->getCrawler()->getId() && $this->_isAllowedAction('run')) {
            $this->_addButton('run', array(
                'label'   => Mage::helper('adminhtml')->__('Run'),
                'onclick' => 'run(); return false;'
            ));
        }

        if ($this->_isAllowedAction('save')) {
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                'class'     => 'save',
            ), -100);
        } else {
            $this->_removeButton('save');
        }

        if (!$this->_isAllowedAction('delete')) {
            $this->_removeButton('delete');
        }
    }

    public function getCrawler()
    {
        return Mage::registry('crawler');
    }

    public function getHeaderText()
    {
        if ($this->getCrawler()->getId()) {
            return Mage::helper('tmcrawler')->__(
                "Edit Crawler '%s'",
                $this->escapeHtml($this->getCrawler()->getIdentifier())
            );
        } else {
            return Mage::helper('tmcrawler')->__('New Crawler');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        // if (in_array($action, array('save', 'delete', 'run'))) {
        //     if (TM_Crawler_Model_Crawler::STATE_RUNNING === $this->getCrawler()->getState()) {
        //         return false;
        //     }
        // }
        return Mage::getSingleton('admin/session')->isAllowed('templates_master/tmcache/crawler/crawler/' . $action);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'active_tab' => '{{tab_id}}'
        ));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current' => true
        ));
    }

    public function getRunUrl()
    {
        return $this->getUrl('*/*/run', array(
            '_current'   => true
        ));
    }

    public function getRefreshUrl()
    {
        return $this->getUrl('*/*/*', array(
            '_current'   => true,
            'active_tab' => '{{tab_id}}'
        ));
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $tabsBlock = $this->getLayout()->getBlock('tmcrawler_crawler_edit_tabs');
        if ($tabsBlock) {
            $tabsBlockJsObject = $tabsBlock->getJsObjectName();
            $tabsBlockPrefix   = $tabsBlock->getId() . '_';
        } else {
            $tabsBlockJsObject = 'crawler_tabsJsTabs';
            $tabsBlockPrefix   = 'crawler_tabs_';
        }

        $this->_formScripts[] = "
            function getUrl(urlTemplate) {
                var tabsIdValue = " . $tabsBlockJsObject . ".activeTab.id;
                var tabsBlockPrefix = '" . $tabsBlockPrefix . "';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                return template.evaluate({tab_id:tabsIdValue});
            }

            function saveAndContinueEdit(urlTemplate) {
                editForm.submit(getUrl(urlTemplate));
            }

            function run() {
                if (!confirm('" . Mage::helper('tmcrawler')->__('This may take a while. Are you sure you want to run it manually?') . "')) {
                    return false;
                }
                window.open('".$this->getRunUrl()."');
                setTimeout(function() {
                    setLocation(getUrl('".$this->getRefreshUrl()."'));
                }, 500);
                return false;
            }
        ";
        return parent::_prepareLayout();
    }
}
