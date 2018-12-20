<?php

class TM_Cache_Block_Adminhtml_Widget_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    /**
     * Fixed using the cache_ids for selectAll method
     *
     * @return string
     */
    public function getGridIdsJson()
    {
        if (!$this->getUseSelectAll()) {
            return '';
        }

        $gridIds = $this->getParentBlock()->getCollection()->getAllCacheIds();

        if(!empty($gridIds)) {
            return join(",", $gridIds);
        }
        return '';
    }
}
