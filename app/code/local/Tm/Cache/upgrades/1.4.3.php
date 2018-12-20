<?php

class TM_Cache_Upgrade_1_4_3 extends TM_Core_Model_Module_Upgrade
{
    public function up()
    {
        // $types = array('tmcache');
        // $allTypes = Mage::app()->useCache();

        // $updatedTypes = 0;
        // foreach ($types as $code) {
        //     if (empty($allTypes[$code])) {
        //         $allTypes[$code] = 1;
        //         $updatedTypes++;
        //     }
        // }
        // if ($updatedTypes > 0) {
        //     Mage::app()->saveUseCache($allTypes);
        // }
        //
        // enable all cache types
        $allTypes = Mage::app()->useCache();
        foreach ($allTypes as $key => &$code) {
            $code = '1';
        }
        Mage::app()->saveUseCache($allTypes);
    }
}
