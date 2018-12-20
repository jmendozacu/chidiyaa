<?php

/**
 * Copy of Mage_Core_Model_App methods
 *
 * dispatchEvent is modified to run observers that coming after tmcache observer
 */
class TM_Cache_Model_EventDispatcher
{
    protected $_events = array(
        'frontend' => array() // tmcache is inside frontend area only
    );

    /**
     * Dispatches only skipped observers, that coming after tmcache
     */
    public function dispatchEvent($eventName, $args)
    {
        foreach ($this->_events as $area => $events) {
            if (!isset($events[$eventName])) {
                $eventConfig = Mage::app()->getConfig()->getEventConfig($area, $eventName);
                if (!$eventConfig) {
                    $this->_events[$area][$eventName] = false;
                    continue;
                }
                $observers = array();
                foreach ($eventConfig->observers->children() as $obsName => $obsConfig) {
                    $observers[$obsName] = array(
                        'type'  => (string)$obsConfig->type,
                        'model' => $obsConfig->class ? (string)$obsConfig->class : $obsConfig->getClassName(),
                        'method'=> (string)$obsConfig->method,
                        'args'  => (array)$obsConfig->args,
                    );
                }
                $events[$eventName]['observers'] = $observers;
                $this->_events[$area][$eventName]['observers'] = $observers;
            }
            if (false === $events[$eventName]) {
                continue;
            } else {
                $event = new Varien_Event($args);
                $event->setName($eventName);
                $observer = new Varien_Event_Observer();
            }

            $obsNames = array_keys($events[$eventName]['observers']);
            $offset   = array_search('tmcache', $obsNames) + 1;
            $_events  = array_slice($events[$eventName]['observers'], $offset, null, true);
            foreach ($_events as $obsName => $obs) {
                $observer->setData(array('event' => $event));
                Varien_Profiler::start('OBSERVER: ' . $obsName);
                switch ($obs['type']) {
                    case 'disabled':
                        break;
                    case 'object':
                    case 'model':
                        $method = $obs['method'];
                        $observer->addData($args);
                        $object = Mage::getModel($obs['model']);
                        $this->_callObserverMethod($object, $method, $observer);
                        break;
                    default:
                        $method = $obs['method'];
                        $observer->addData($args);
                        $object = Mage::getSingleton($obs['model']);
                        $this->_callObserverMethod($object, $method, $observer);
                        break;
                }
                Varien_Profiler::stop('OBSERVER: ' . $obsName);
            }
        }
        return $this;
    }

    /**
     * Performs non-existent observer method calls protection
     *
     * @param object $object
     * @param string $method
     * @param Varien_Event_Observer $observer
     * @return Mage_Core_Model_App
     * @throws Mage_Core_Exception
     */
    protected function _callObserverMethod($object, $method, $observer)
    {
        if (method_exists($object, $method)) {
            $object->$method($observer);
        } elseif (Mage::getIsDeveloperMode()) {
            Mage::throwException('Method "'.$method.'" is not defined in "'.get_class($object).'"');
        }
        return $this;
    }
}
