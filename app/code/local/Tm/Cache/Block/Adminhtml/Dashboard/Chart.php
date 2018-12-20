<?php

class TM_Cache_Block_Adminhtml_Dashboard_Chart extends Mage_Adminhtml_Block_Template
{
    const RANGE = '7d';

    protected function _beforeToHtml()
    {
        $this->setChartData(
            Mage::getResourceModel('tmcache/log')
                ->getRecentHitsAndMisses($this->getRange())
        );

        return parent::_beforeToHtml();
    }

    public function getLabels()
    {
        $labels = array();
        foreach ($this->getChartData() as $record) {
            $labels[] = $record['label'];
        }
        return $labels;
    }

    public function getHits()
    {
        $hits = array();
        foreach ($this->getChartData() as $record) {
            $hits[] = $record['hit'];
        }
        return $hits;
    }

    public function getMisses()
    {
        $misses = array();
        foreach ($this->getChartData() as $record) {
            $misses[] = $record['miss'];
        }
        return $misses;
    }

    public function getRangeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id'    => 'range',
                'class' => 'select'
            ))
            ->setName('range')
            ->setOptions(Mage::helper('tmcache/chart')->getRangeLabels())
            ->setValue($this->getRange());

        return $select->getHtml();
    }

    public function getRange()
    {
        $range = $this->getData('range');
        if (!$range) {
            $range = self::RANGE;
        }
        return $range;
    }
}