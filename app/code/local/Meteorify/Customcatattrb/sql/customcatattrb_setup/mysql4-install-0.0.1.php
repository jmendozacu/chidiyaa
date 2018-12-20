<?php
$installer = $this;
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
 //'backend'           => 'eav/entity_attribute_source_boolean',
$installer->addAttribute('catalog_category', 'show_catimage', array(
    'type'              => 'int',
    'frontend'          => '',
    'label'             => 'Show Category Image',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'group'             => 'General Information'
));

//~ $installer->addAttributeToGroup(
    //~ $entityTypeId,
    //~ $attributeSetId,
    //~ $attributeGroupId,
    //~ 'show_catimage',
    //~ '4'
//~ );

$installer->endSetup();
?>
