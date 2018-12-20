<?php

class Softprodigy_Message_Block_Adminhtml_Email_Edit2_Tab2_Form2 extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $Ids = $this->getRequest()->getParam('id');
      Mage::getSingleton('core/session')->setCustomerId(serialize($Ids));
      $this->setForm($form);
      $fieldset = $form->addFieldset('message_form', array('legend'=>Mage::helper('message')->__("Messages Link")));
      $fieldset->addField('subject', 'text', array(
          'label'     => Mage::helper('message')->__('Mail Subject'),
          'required'  => true,
          'name'      => 'subject',
          'value'     => $phone,
      ));
		$wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config');
        $fieldset->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('message')->__('Description'),
            'title'     => Mage::helper('message')->__('Description'),
            'style'     => 'height: 15em;',
            'wysiwyg'   => true,
            'required'  => true,
            'config'    => $wysiwygConfig
        ));
      if ( Mage::getSingleton('adminhtml/session')->getMessageData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMessageData());
          Mage::getSingleton('adminhtml/session')->setMessageData(null);
      } elseif ( Mage::registry('message_data') ) {
          $form->setValues(Mage::registry('message_data')->getData());
      }
      return parent::_prepareForm();
  }
}
