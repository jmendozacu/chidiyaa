<?php
class Softprodigy_Catalog_Block_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    public function setCollection($collection)
    {
        parent::setCollection($collection);
 
        if ($this->getCurrentOrder()) {
            if($this->getCurrentOrder() == 'qty_ordered') {
                $this->getCollection()->getSelect()
                     ->joinLeft(
                            array('sfoi' => $collection->getResource()->getTable('sales/order_item')),
                             'e.entity_id = sfoi.product_id',
                             array('qty_ordered' => 'SUM(sfoi.qty_ordered)')
                         )
                     ->group('e.entity_id')
                     ->order('qty_ordered ' . $this->getCurrentDirection());
            } else {
                $this->getCollection()
                     ->setOrder($this->getCurrentOrder(), $this->getCurrentDirection())->getSelect();
            }
        }
 
        return $this;
    }
}
