<?php
class Softprodigy_Message_Block_Adminhtml_Email_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('email_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('message')->__("Send Message"));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('message_form', array(
          'content'   => $this->getLayout()->createBlock('message/adminhtml_email_edit_tab_form')->toHtml(),
      ));     
      return parent::_beforeToHtml();
  }
}
