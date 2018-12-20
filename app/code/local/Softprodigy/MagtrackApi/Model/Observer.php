<?php
class Softprodigy_MagtrackApi_Model_Observer {

    public function pushNotification($observer) {
        $order_id = $observer->getData('order_ids');
		//$order = Mage::getModel('sales/order')->load($order_id);
		$params = array();
		try
		{
			$settings = Mage::getModel('softprodigy_magtrackapi/settings');
			if($settings->getSetting('registration_id') != "") {
				$store_data = $this->total_sales_today($params);
				if($settings->getSetting('new_orders') == 1) {
					$this->sendNotification('Your store have a new order.',$order_id);
				}
				
				if($settings->getSetting('sales_over_100') == 1 && $store_data['revenue'] > 100) {
					$this->sendNotification("Your store have crossed $100 Sales today, Congratulations!",$order_id);
				}
				
				if($settings->getSetting('sales_over_1000') == 1 && $store_data['revenue'] > 1000) {
					$this->sendNotification("Your store have crossed $1000 Sales today, Congratulations!",$order_id);
				}
				
				if($settings->getSetting('orders_above_10') == 1 && $store_data['orders'] > 10) {
					$this->sendNotification("Your store have crossed 10 orders today, Congratulations!",$order_id);
				}
				
				if($settings->getSetting('orders_above_50') == 1 && $store_data['orders'] > 50) {
					$this->sendNotification("Your store have crossed 50 orders today, Congratulations!",$order_id);
				}
			}
		
		}
		catch(Exception $e)
		{
			//echo $e->getMessage();
			//die;
		}
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

	public function sendNotification($message,$order_id) {
		$collection = Mage::getResourceModel('sales/order_collection');
		$collection->addAttributeToFilter('entity_id',$order_id);
		$order_data = $this->convertOrderData($collection, 0);
		$params = array();
		$graph_data = $this->graph_data($params);
		$data = $this->total_sales_today($params);
		$top_row['New Customers'] = $this->new_customers($params);
		$top_row['New Orders'] = $data['orders'];
		$top_row['New Sales'] = Mage::helper('core')->currency($data['revenue'], true, false);
		$sales_report['Total Sales'] = Mage::helper('core')->currency($this->total_sales($params), true, false)."~1";
		$sales_report['Total Sales This Week'] = Mage::helper('core')->currency($this->total_sales_this_week($params), true, false)."~2";
		$sales_report['Total Sales This Month'] = Mage::helper('core')->currency($this->total_sales_this_month($params), true, false)."~3";
		$sales_report['Total Sales This Year'] = Mage::helper('core')->currency($this->total_sales_this_year($params), true, false)."~4";
		$sales_report['Total Sales Today'] = Mage::helper('core')->currency($data['revenue'], true, false)."~5";
		$dashboard_data['top_row'] = $top_row;
		$dashboard_data['sales_report'] = $sales_report;
		
		$settings = Mage::getModel('softprodigy_magtrackapi/settings');
		define( 'API_ACCESS_KEY', 'AIzaSyDhBR5fOU4FEkS48lJdj_cVqINUbGSOl68' );  //AIzaSyDfMyPWtO3Ow-Cc2JbyPdWRP9Io9rmleNQ
		$deviceId[] = $settings->getSetting('registration_id');
		$registrationIds = $deviceId;
		$msg = array
		(
			'message' => "$message",
			'title'	=> 'This is a title. title',
			'subtitle'	=> 'This is a subtitle. subtitle',
			'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
			'vibrate'	=> 1,
			'sound'	=> 1,
			'order_data' => $order_data,
			'graph_data' => $graph_data,
			'dashboard_data' => $dashboard_data
		);

		$fields = array
		(
			'registration_ids' => $registrationIds,
			'data'	=> $msg
		);
		
		$headers = array
		(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$result = curl_exec($ch );
		curl_close( $ch );
	}
	
	public function convertOrderData($collection, $group = '0'){
        foreach ($collection as $order_item) {
			$order = $order_item->getData();
			$ids_temp[] = $order['entity_id']; //list ids for next
			$items = $order_item->getItemsCollection();
			$sku = $items->getFirstItem()->getSku();
			if(count($items) > 1){
				$sku .= ', ...';
			}
			$is_new = 0;
			$is_unread = 0;
			$updated_date = date('Y-m-d', strtotime(Mage::helper('core')->formatDate($order['updated_at'], 'medium', true))); 
			$updated_time = date('H:i:s', strtotime(Mage::helper('core')->formatDate($order['updated_at'], 'medium', true))); 
			
			$shippingAddress = $order_item->getShippingAddress();
			if($shippingAddress) {
				$shippingDetail = $shippingAddress->getData();
				$telephone = $shippingDetail['telephone'];
			}
			
			$data[] = array(
				'id'                => (int)$order['entity_id'],
				'customer_name'     => $order['customer_firstname'].' '.$order['customer_middlename'].' '.$order['customer_lastname'],//->getCustomerName(),
				'customer_email'    => $order['customer_email'],//->getCustomerEmail(),
				'telephone'			=> $telephone,
				'increment'         => $order['increment_id'],//->getIncrementId(),
				'date'              => $updated_date,
				'time'              => $updated_time,
				'sub_total'			=> Mage::helper('core')->currency($order['base_subtotal'], true, false),
				'shipping_amount'	=> Mage::helper('core')->currency($order['base_shipping_amount'], true, false),
				'tax_amount'		=> Mage::helper('core')->currency($order['base_tax_amount'], true, false),
				'grand_total'       => Mage::helper('core')->currency($order['base_grand_total'], true, false),
				'status'            => $order['status'],//->getStatus(),
				'sku'               => $sku,
				'is_new'            => $is_new,
				'is_unread'         => $is_unread
			);
			unset($shippingAddress);
		}
        return $data;
    }
    
   
	public function new_customers($params) {
		$toDate = date('Y-m-d H:i:s');
		$fromDate = date('Y-m-d H:i:s', strtotime("-1 day"));
		$toDate = date('Y-m-d H:i:s', strtotime($toDate));
		$fromDate = gmdate("Y-m-d H:i:s", strtotime($fromDate));
		$toDate = gmdate("Y-m-d H:i:s", strtotime($toDate));
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
		return $result;
	}
}
