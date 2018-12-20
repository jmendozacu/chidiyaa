<?php
class Delhivery_Godam_IndexController extends Mage_Core_Controller_Front_Action {
     /**
     * Function to render index controller action
     */
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}