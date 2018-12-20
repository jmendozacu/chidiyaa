<?php
/**
 * Softprodigy
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Softprodigy.com license that is
 * available through the world-wide-web at this URL:
 * http://www.Softprodigy.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagTrack
 */

/**
 * SimiSalestracking Api Dashboard Server Model
 * 
 * @category    Softprodigy
 * @package     Softprodigy_MagtrackApi
 * @author      Softprodigy Developer
 */
class Softprodigy_MagtrackApi_Model_Api_Dashboard extends Softprodigy_MagtrackApi_Model_Api_Abstract
{
    /**
     * api page Dashboard
     * 
     * ?call=bestsellers 
     * & params = {
     *      "page":"1",
     *      "limit":"10",
     *      "store":"1",
     *      "order_status":"string|array()",
     *      "date_range":"1d|7d|15d|30d|3m|6m|1y|2y|lt"
     * }
     */
    public function apiIndex($params){
		if($params['graph'] == 1) {
			return array('graph' => $this->graph_data($params));
		} else {
			$data = $this->total_sales_today($params);
			$top_row['New Customers'] = $this->new_customers($params);
			$top_row['New Orders'] = $data['orders'];
			$top_row['New Sales'] = Mage::helper('core')->currency($data['revenue'], true, false);
			$sales_report['Total Sales'] = Mage::helper('core')->currency($this->total_sales($params), true, false)."~1";
			$sales_report['Total Sales This Week'] = Mage::helper('core')->currency($this->total_sales_this_week($params), true, false)."~2";
			$sales_report['Total Sales This Month'] = Mage::helper('core')->currency($this->total_sales_this_month($params), true, false)."~3";
			$sales_report['Total Sales This Year'] = Mage::helper('core')->currency($this->total_sales_this_year($params), true, false)."~4";
			$sales_report['Total Sales Today'] = Mage::helper('core')->currency($data['revenue'], true, false)."~5";
			return array(
				'top_row'        =>  $top_row,
				'sales_report'        =>  $sales_report
			);
		}
    }
    
    public function new_customers($params) {
		$toDate = date('Y-m-d H:i:s');
		$fromDate = date('Y-m-d H:i:s', strtotime("-1 day"));
		$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		$customers = Mage::getModel('customer/customer')->getCollection()
				->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
		return count($customers);
	}
	
    public function total_sales($params) {
		$isFilter = $params['store'] || $params['website'] || $params['group'];
        $collection = Mage::getResourceModel('reports/order_collection')
            ->calculateTotals($isFilter);

        if ($params['store']) {
            $collection->addFieldToFilter('store_id', $params['store']);
        } else if ($params['website']){
            $storeIds = Mage::app()->getWebsite($params['website'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($params['group']){
            $storeIds = Mage::app()->getGroup($params['group'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();
        return $totals->getRevenue();
	}
	
    public function total_sales_today($params) {
		$isFilter = $params['store'] || $params['website'] || $params['group'];
        $period_24h = '24h';
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period_24h)
            ->calculateTotals($isFilter);

        if ($params['store']) {
            $collection->addFieldToFilter('store_id', $params['store']);
        } else if ($params['website']){
            $storeIds = Mage::app()->getWebsite($params['website'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($params['group']){
            $storeIds = Mage::app()->getGroup($params['group'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();
        $data['revenue'] = $totals->getRevenue();
        $data['orders'] = $totals->getQuantity()*1;
        return $data;
	}
    
    public function total_sales_this_week($params) {
		$isFilter = $params['store'] || $params['website'] || $params['group'];
        $period_24h = '7d';
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period_24h)
            ->calculateTotals($isFilter);

        if ($params['store']) {
            $collection->addFieldToFilter('store_id', $params['store']);
        } else if ($params['website']){
            $storeIds = Mage::app()->getWebsite($params['website'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($params['group']){
            $storeIds = Mage::app()->getGroup($params['group'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();
        return $totals->getRevenue();
	}
	
	public function total_sales_this_month($params) {
		$isFilter = $params['store'] || $params['website'] || $params['group'];
        $period_24h = '1m';
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period_24h)
            ->calculateTotals($isFilter);

        if ($params['store']) {
            $collection->addFieldToFilter('store_id', $params['store']);
        } else if ($params['website']){
            $storeIds = Mage::app()->getWebsite($params['website'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($params['group']){
            $storeIds = Mage::app()->getGroup($params['group'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();
        return $totals->getRevenue();
	}

	
	public function total_sales_this_year($params) {
		$isFilter = $params['store'] || $params['website'] || $params['group'];
        $period_24h = '1y';
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period_24h)
            ->calculateTotals($isFilter);

        if ($params['store']) {
            $collection->addFieldToFilter('store_id', $params['store']);
        } else if ($params['website']){
            $storeIds = Mage::app()->getWebsite($params['website'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } else if ($params['group']){
            $storeIds = Mage::app()->getGroup($params['group'])->getStoreIds();
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        } elseif (!$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }

        $collection->load();

        $totals = $collection->getFirstItem();
        return $totals->getRevenue();
	}
	
	public function graph_data($params) {
		$timezoneLocal = Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        
        list ($dateStart, $dateEnd) = Mage::getResourceModel('reports/order_collection')
            ->getDateRange('1m', '', '', true);

        $dateStart->setTimezone($timezoneLocal);
        $dateEnd->setTimezone($timezoneLocal);
        
        $startTime = strtotime($dateStart->toString('yyyy-MM-dd'));
		$endTime = strtotime($dateEnd->toString('yyyy-MM-dd'));
		$result = array();
		$y = 0;
        for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
			$thisDate = date('Y-m-d', $i);
			$thisDate_starttime = $thisDate." 00:00:00";
			$thisDate_endtime = $thisDate." 23:59:59";
			$orders = Mage::getModel('sales/order')->getCollection()
				->addAttributeToFilter('created_at', array('from'=>$thisDate_starttime, 'to'=>$thisDate_endtime))
				->addAttributeToFilter('status', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
			$result[$y]['date'] = date('j', $i);
			$result[$y]['Orders'] = count($orders);
			$result[$y]['Revenue'] = '';
			$result[$y]['Value 3'] = '';
			$y++;
		}
		/*for($y=0;$y<=30;$y++) {
			$result[$y]['date'] = $y+1;
			$result[$y]['Orders'] = $y;
			$result[$y]['Revenue'] = '';
			$result[$y]['Value 3'] = '';
		}*/
		return $result;
	}
}
