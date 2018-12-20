<?php

class Softprodigy_Message_Block_Adminhtml_Email_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('message_form', array('legend'=>Mage::helper('message')->__("Messages Link")));
     $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('message')->__('Enter Email'),
          'required'  => true,
          'name'      => 'email',
      ));
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('message')->__('Enter name'),
          'required'  => true,
          'name'      => 'name',
      ));
      /*$fieldset->addField('send_message', 'textarea', array(
          'label'     => Mage::helper('message')->__('Enter Message'),
          'required'  => true,
          'name'      => 'send_message',
      ));*/
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
