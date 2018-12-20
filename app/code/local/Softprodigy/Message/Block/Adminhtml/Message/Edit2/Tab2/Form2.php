<?php

class Softprodigy_Message_Block_Adminhtml_Message_Edit2_Tab2_Form2 extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $Ids = $this->getRequest()->getParam('id');
      Mage::getSingleton('core/session')->setCustomerId(serialize($Ids));
      $this->setForm($form);
      $fieldset = $form->addFieldset('message_form', array('legend'=>Mage::helper('message')->__("Messages Link")));
      $fieldset->addField('send_message', 'textarea', array(
          'label'     => Mage::helper('message')->__('Enter Message'),
          'required'  => true,
          'name'      => 'send_message',
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
