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
class Softprodigy_MagtrackApi_Model_Api_Settings extends Softprodigy_MagtrackApi_Model_Api_Abstract
{
    /**
     * api page Dashboard
     */
    /**
     * call=settings &
     * params={
     *      date_range: 1d|7d|15d|30d|3m|6m|1y|2y|lt,
     *      order_status: string|array('pending','complete')
     * }
     */
    public function apiIndex($params){
        $settings = Mage::getModel('softprodigy_magtrackapi/settings');
        if(isset($params['registration_id']) && $params['registration_id'] !=''){
            $settings->saveSetting($params['registration_id'], 'registration_id');
        }
        if(isset($params['new_orders'])){
            $settings->saveSetting($params['new_orders'], 'new_orders');
        }
        if(isset($params['sales_over_100'])){
            $settings->saveSetting($params['sales_over_100'], 'sales_over_100');
        }
        if(isset($params['sales_over_1000'])){
            $settings->saveSetting($params['sales_over_1000'], 'sales_over_1000');
        }
        if(isset($params['orders_above_10'])){
            $settings->saveSetting($params['orders_above_10'], 'orders_above_10');
        }
        if(isset($params['orders_above_50'])){
            $settings->saveSetting($params['orders_above_50'], 'orders_above_50');
        }
        //clear cache
        Mage::app()->getCacheInstance()->cleanType("config");
        return true;
    }
}
