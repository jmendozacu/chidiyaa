<?php

class TM_Cache_Helper_Chart extends Mage_Core_Helper_Abstract
{
    protected $_rangeRules = array(
        '1m'  => array('step' => '10 ss', 'from' => 'now - 1 minute'),
        '1h'  => array('step' => '10 mm', 'from' => 'now - 1 hour'),
        '24h' => array('step' => '1 HH',  'from' => 'now - 1 day'),
        '7d'  => array('step' => '1 dd',  'from' => 'now - 7 days')
        // '1y'  => array('step' => '1 MM',  'from' => 'now - 1 year')
    );

    protected $_stepRules = array(
        '10 ss' => array('left' => 18, 'right' => 7, 'concat' => '0'),
        '10 mm' => array('left' => 15, 'right' => 4, 'concat' => '0'),
        '1 HH'  => array('left' => 13, 'right' => 2, 'concat' => ':00'),
        '1 dd'  => array('left' => 10, 'right' => 5, 'concat' => '')
        // '1month'    => array('left' => 7,  'right' => 2, 'concat' => '')
    );

    public function getRangeLabels()
    {
        return array(
            '1m'  => $this->__('Last Minute'),
            '1h'  => $this->__('Last Hour'),
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days')
        );
    }

    public function getRangeRules($range = null)
    {
        if (null === $range) {
            return $this->_rangeRules;
        }
        return $this->_rangeRules[$range];
    }

    public function getStepRules($step = null)
    {
        if (null === $step) {
            return $this->_stepRules;
        }
        return $this->_stepRules[$step];
    }

    /**
     * Retrieve chart labels for timerange with specified step
     *
     * @param  int $from    Unix time
     * @param  int $to      Unix time
     * @param  string $step See the _stepRules keys
     * @return array        Array with long and short labels
     * Example:
     *     [0] => (long => 2014-08-01 06:00 short => 06:00)
     *     [1] => (long => 2014-08-01 07:00 short => 07:00)
     *     [2] => (long => 2014-08-01 08:00 short => 08:00)
     * OR
     *     [0] => (long => 2014-07-30 short => 07-30)
     *     [1] => (long => 2014-07-31 short => 07-31)
     *     [2] => (long => 2014-08-01 short => 08-01)
     * and so on
     */
    public function getLabels($from, $to, $step)
    {
        $labels = array();
        $stepRule = $this->getStepRules($step);
        $from = Mage::app()->getLocale()->date($from, null, null, false);
        $to   = Mage::app()->getLocale()->date($to, null, null, false);
        list($datepart, $part) = explode(' ', $step);

        do {
            $label = $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $long  = substr($label, 0, $stepRule['left']);
            $short = substr($long, - $stepRule['right']);
            $labels[] = array(
                'long'  => $long  . $stepRule['concat'],
                'short' => $short . $stepRule['concat']
            );
            $to->sub($datepart, $part);
        } while ($from->compare($to) < 0);

        return array_reverse($labels);
    }
}