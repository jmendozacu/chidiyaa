<?php

class Softprodigy_Message_Block_Adminhtml_Email_Edit1_Tab1_Form1 extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
     $customerID   =   $this->getRequest()->getParam('id');
      $customer = Mage::getModel('customer/customer')->load($customerID);
      $name = $customer->getFirstname() . ' ' . $customer->getLastname();
      $phone = $customer->getEmail();
      $this->setForm($form);
      $fieldset = $form->addFieldset('message_form', array('legend'=>Mage::helper('message')->__("Messages Link")));
       $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('message')->__('Enter Email Id'),
          'required'  => true,
          'name'      => 'email',
          'value'     => $phone,
      ));
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('message')->__('Enter name'),
          'required'  => true,
          'name'      => 'name',
          'value'     => $name,
      ));
	$fieldset->addField('subject', 'text', array(
          'label'     => Mage::helper('message')->__('Mail Subject'),
          'required'  => true,
          'name'      => 'subject',
      ));
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config');
        $fieldset->addField('description', 'editor', array(
            'name'      => 'description',
            'label'     => Mage::helper('message')->__('Description'),
            'title'     => Mage::helper('message')->__('Description'),
            'style'     => 'height: 15em;',
            'wysiwyg'   => true,
            'required'  => false,
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
