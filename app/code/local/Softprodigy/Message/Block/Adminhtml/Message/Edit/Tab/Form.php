<?php

class Softprodigy_Message_Block_Adminhtml_Message_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('message_form', array('legend'=>Mage::helper('message')->__("Messages Link")));
     $fieldset->addField('number', 'text', array(
          'label'     => Mage::helper('message')->__('Enter number'),
          'required'  => true,
          'name'      => 'number',
      ));
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('message')->__('Enter name'),
          'required'  => true,
          'name'      => 'name',
      ));
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
