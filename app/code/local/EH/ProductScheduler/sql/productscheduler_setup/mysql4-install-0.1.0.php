<?php

$installer = $this;
$installer->startSetup();


$model=Mage::getModel('eav/entity_setup','core_setup');

$data=array(
	'type'=>'datetime',
	'input'=>'date',
	'label'=>'Start Date',
	'global'=>Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'required'=>'0',
	'is_comparable'=>'0',
	'is_searchable'=>'0',
	'is_unique'=>'1',
	'is_configurable'=>'1',
	'user_defined'=>'1',
);
$model->addAttribute('catalog_product','eh_schedule_start_date',$data);

$data['label']='End Date';
$model->addAttribute('catalog_product','eh_schedule_end_date',$data);


$installer->addAttribute('catalog_product', "eh_schedule_status", array(
    'type'       => 'int',
    'input'      => 'select',
    'label'      => 'Status',
    'user_defined'=>'1',
    'sort_order' => 1000,
    'required'   => false,
    'global'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'backend'    => 'eav/entity_attribute_backend_array',
    'option'     => array (
        'values' => array(
            0 => 'Disable',
            1 => 'Enable',
        )
    ),

));

$scheduleStartDateAttributeId=$model->getAttribute('catalog_product','eh_schedule_start_date');
$scheduleEndDateAttributeId=$model->getAttribute('catalog_product','eh_schedule_end_date');
$scheduleProductStatusAttributeId=$model->getAttribute('catalog_product','eh_schedule_status');

$entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
$sets = Mage::getModel('eav/entity_attribute_set')->getResourceCollection()->addFilter('entity_type_id', $entityTypeId);

foreach ($sets as $set){
    $modelGroup = Mage::getModel('eav/entity_attribute_group');
    $modelGroup->setAttributeGroupName('Product Schedule')->setAttributeSetId($set->getId())->setSortOrder(150);
    $modelGroup->save();
}
		
$allAttributeSetIds=$model->getAllAttributeSetIds('catalog_product');
foreach ($allAttributeSetIds as $attributeSetId) {
	try{
		$attributeGroupId=$model->getAttributeGroup('catalog_product',$attributeSetId,'Product Schedule');
	}
	catch(Exception $e){
		$attributeGroupId=$model->getDefaultAttributeGroupId('catalog/product',$attributeSetId);
	}
	
	$model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId['attribute_group_id'],$scheduleStartDateAttributeId['attribute_id']);
    $model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId['attribute_group_id'],$scheduleEndDateAttributeId['attribute_id']);
    $model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId['attribute_group_id'],$scheduleProductStatusAttributeId['attribute_id']);
}


$installer->endSetup();
