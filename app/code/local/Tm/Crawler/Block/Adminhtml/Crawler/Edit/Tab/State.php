<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit_Tab_State
    extends TM_Crawler_Block_Adminhtml_Crawler_Edit_Tab_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = $this->getCrawler();
        $form  = new Varien_Data_Form();
        $form->setHtmlIdPrefix('crawler_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $this->getTabLabel()
        ));

        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $states = Mage::getModel('tmcrawler/system_config_source_state');
        $fieldset->addField('state', 'label', array(
            'name'     => 'state',
            'label'    => Mage::helper('tmcrawler')->__('State'),
            'title'    => Mage::helper('tmcrawler')->__('State'),
            'values'   => $states->toOptionArray(),
            'disabled' => $isElementDisabled
        ));

        $fieldset->addField('started_at', 'label', array(
            'name'     => 'started_at',
            'label'    => Mage::helper('tmcrawler')->__('Started At'),
            'title'    => Mage::helper('tmcrawler')->__('Started At'),
            'disabled' => $isElementDisabled
        ));
        if ($model->getCompletedAt()) {
            $fieldset->addField('completed_at', 'label', array(
                'name'     => 'completed_at',
                'label'    => Mage::helper('tmcrawler')->__('Completed At'),
                'title'    => Mage::helper('tmcrawler')->__('Completed At'),
                'disabled' => $isElementDisabled
            ));
        }

        $fieldset->addField('crawled_urls', 'label', array(
            'name'  => 'crawled_urls',
            'label' => Mage::helper('tmcrawler')->__('Crawled Urls'),
            'title' => Mage::helper('tmcrawler')->__('Crawled Urls')
        ));

        if (in_array($model->getState(), array(TM_Crawler_Model_Crawler::STATE_PENDING, TM_Crawler_Model_Crawler::STATE_RUNNING))) {
            $fieldset->addField('offset', 'label', array(
                'name'     => 'offset',
                'label'    => Mage::helper('tmcrawler')->__('Offset'),
                'title'    => Mage::helper('tmcrawler')->__('Offset'),
                'disabled' => $isElementDisabled
            ));

            $fieldset->addField('current_type', 'label', array(
                'name'     => 'current_type',
                'label'    => Mage::helper('tmcrawler')->__('Type'),
                'title'    => Mage::helper('tmcrawler')->__('Type'),
                'disabled' => $isElementDisabled,
                'value'    => $model->getCurrentType()
            ));

            $fieldset->addField('current_store', 'label', array(
                'name'     => 'current_store',
                'label'    => Mage::helper('catalog')->__('Store'),
                'title'    => Mage::helper('catalog')->__('Store'),
                'disabled' => $isElementDisabled,
                'value'    => $model->getCurrentStoreId()
            ));

            $fieldset->addField('current_currency', 'label', array(
                'name'     => 'current_currency',
                'label'    => Mage::helper('adminhtml')->__('Currency'),
                'title'    => Mage::helper('adminhtml')->__('Currency'),
                'disabled' => $isElementDisabled,
                'value'    => $model->getCurrentCurrency()
            ));
        }

        $form->addValues($model->getData());
        $form->setFieldNameSuffix('crawler');
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('tmcrawler')->__('Current State');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('tmcrawler')->__('Current State');
    }

    public function canShowTab()
    {
        return (bool)$this->getCrawler()->getId();
    }
}
