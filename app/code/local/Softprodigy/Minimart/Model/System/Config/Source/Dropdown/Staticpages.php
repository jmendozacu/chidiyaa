<?php
class Softprodigy_Minimart_Model_System_Config_Source_Dropdown_Staticpages
{
    public function toOptionArray()
    {
		$collection = Mage::getModel('cms/page')->getCollection()
					->addFieldToFilter('is_active', 1);
					
		$pages = array();
        if ($collection->getSize()) {
            foreach($collection as $page) {
                $pages[] = array(
                        'value' => (int)$page->getId(),
                        'label' => $page->getTitle(),
                    );
            }
        }
        return $pages;			
	}
}					
