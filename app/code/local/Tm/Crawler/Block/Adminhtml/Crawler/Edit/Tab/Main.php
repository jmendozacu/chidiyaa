<?php

class TM_Crawler_Block_Adminhtml_Crawler_Edit_Tab_Main
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

        if ($model->getCrawlerId()) {
            $fieldset->addField('crawler_id', 'hidden', array(
                'name' => 'crawler_id'
            ));
        }

        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $fieldset->addField('identifier', 'text', array(
            'name'     => 'identifier',
            'label'    => Mage::helper('cms')->__('Identifier'),
            'title'    => Mage::helper('cms')->__('Identifier'),
            'required' => true,
            'disabled' => $isElementDisabled
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_ids', 'multiselect', array(
                'name'     => 'store_ids[]',
                'label'    => Mage::helper('cms')->__('Store View'),
                'title'    => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false), // params: empty, all
                'disabled' => $isElementDisabled
            ));
        } else {
            $fieldset->addField('store_ids', 'hidden', array(
                'name'  => 'store_ids[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
            $model->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $types = Mage::getModel('tmcrawler/system_config_source_type');
        $fieldset->addField('type', 'multiselect', array(
            'label'  => Mage::helper('catalog')->__('Type'),
            'title'  => Mage::helper('catalog')->__('Type'),
            'name'   => 'type',
            'values' => $types->toOptionArray(),
            'disabled' => $isElementDisabled
        ));

        $codes  = Mage::app()->getStore()->getAvailableCurrencyCodes(true);
        $locale = Mage::app()->getLocale();
        $currencies = array();
        foreach ($codes as $code) {
            $currencies[] = array(
                'label' => $locale->getTranslation($code, 'nametocurrency'),
                'value' => $code
            );
        }
        $fieldset->addField('currencies', 'multiselect', array(
            'label'  => Mage::helper('directory')->__('Allowed Currencies'),
            'title'  => Mage::helper('directory')->__('Allowed Currencies'),
            'name'   => 'currencies',
            'values' => $currencies,
            'disabled' => $isElementDisabled
        ));

        $fieldset->addField('user_agents', 'text', array(
            'label' => Mage::helper('tmcrawler')->__('User Agents'),
            'title' => Mage::helper('tmcrawler')->__('User Agents'),
            'note'  =>
                Mage::helper('tmcrawler')->__('Examples:') .
                '<br/>' .
                Mage::helper('tmcrawler')->__('%s - to emulate iPhone user agent', '<strong>iPhone</strong>') .
                '<br/>' .
                Mage::helper('tmcrawler')->__('%s - to emulate iPhone and Android agents separately', '<strong>iPhone,Android</strong>') .
                '<br/>' .
                "<span style='color:#f00'>" .
                Mage::helper('tmcrawler')->__("CAUTION: DON'T USE MULTIPLE AGENTS IF ALL OF THEM USE THE SAME DESIGN") .
                "</span>"
            ,
            'name'   => 'user_agents',
            'values' => $types->toOptionArray(),
            'disabled' => $isElementDisabled
        ));

        $fieldset->addField('status', 'select', array(
            'label'  => Mage::helper('catalog')->__('Status'),
            'title'  => Mage::helper('catalog')->__('Status'),
            'name'   => 'status',
            'values' => array(
                '1' => Mage::helper('catalog')->__('Enabled'),
                '0' => Mage::helper('catalog')->__('Disabed')
            ),
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('status', '1');
        }

        $fieldset = $form->addFieldset('perfomance_fieldset', array(
            'legend' => Mage::helper('tmcrawler')->__('Perfomance')
        ));

        $fieldset->addField('interval', 'text', array(
            'name'     => 'interval',
            'label'    => Mage::helper('tmcrawler')->__('Interval'),
            'title'    => Mage::helper('tmcrawler')->__('Interval'),
            'note'     => Mage::helper('tmcrawler')->__('Restart completed crawler once per X hours'),
            'required' => true,
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('interval', 24);
        }

        $concurrency = array();
        for ($i = 2; $i <= 20; $i++) {
            $concurrency[$i] = $i;
        }
        $fieldset->addField('concurrency', 'select', array(
            'name'     => 'concurrency',
            'label'    => Mage::helper('tmcrawler')->__('Concurrency'),
            'title'    => Mage::helper('tmcrawler')->__('Concurrency'),
            'note'     => Mage::helper('tmcrawler')->__('Simultaneous requests count. Set the high values if your server can handle them.'),
            'required' => true,
            'values'   => $concurrency,
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('concurrency', 2);
        }

        $fieldset->addField('max_response_time', 'text', array(
            'name'     => 'max_response_time',
            'label'    => Mage::helper('tmcrawler')->__('Max Avg. Response Time'),
            'title'    => Mage::helper('tmcrawler')->__('Max Avg. Response Time'),
            'note'     => Mage::helper('tmcrawler')->__('Crawler will be paused till next cronjob, if response time will be bigger.'),
            'required' => true,
            'disabled' => $isElementDisabled
        ));
        if (!$model->getMaxResponseTime()) {
            $model->setData('max_response_time', 10);
        }

        $fieldset->addField('clean_cache', 'select', array(
            'label'  => Mage::helper('tmcrawler')->__('Clean full page cache before crawl'),
            'title'  => Mage::helper('tmcrawler')->__('Clean full page cache before crawl'),
            'note'   => Mage::helper('tmcrawler')->__('Requested single cache entry will be cleaned only'),
            'name'   => 'clean_cache',
            'values' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No')
            ),
            'disabled' => $isElementDisabled
        ));
        if (!$model->getId()) {
            $model->setData('clean_cache', '1');
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
        return Mage::helper('cms')->__('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('General Information');
    }
}
