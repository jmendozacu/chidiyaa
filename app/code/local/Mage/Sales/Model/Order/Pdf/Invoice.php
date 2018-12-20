<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2014 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Products'),
            'feed' => 35
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 290,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 435,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Price'),
            'feed'  => 360,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Tax'),
            'feed'  => 495,
            'align' => 'right'
        );

        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Subtotal'),
            'feed'  => 565,
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );

        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page  = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
             
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
            );
           $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page, 10);
        $docHeader = $this->getDocHeaderCoordinates();
        $page->drawText(
            Mage::helper('sales')->__('Invoice Date: ') . Mage::helper('core')->formatDate(
                $invoice->getCreatedAtStoreDate(), 'medium', false
            ), 35, $docHeader[1] - 25, 'UTF-8');

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            array_reverse($this->insertTotals($page, $invoice));
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
            $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode(); // store currency symbol eg. $ 
			$currency_symbol = Mage::app()->getLocale()->currency( $currency_code )->getSymbol();
            $invoicePdf = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoice->getIncrementId());
            $order = $invoicePdf->getOrder();
            $order->getSubtotal();
            $IncludedTax = $order->getSubtotal()*(5.25/100);
        }
         $page->setFillColor(Zend_Pdf_Color_Html::color('#ffffff'))->drawText('Chidiyaa  Crafts', 300, 786, 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#ffffff'))->drawText('704, Agrim Apartment, Sec 43,', 300, 774, 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#ffffff'))->drawText('Gurgaon 122002, Haryana,India.', 300, 762, 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#ffffff'))->drawText('TIN # 06931838709, GSTIN # 06AIKPR9475N1Z7   ', 300, 750, 'UTF-8');
          
         
         /*$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText("VAT : ", 420, ($this->y), 'UTF-8');*/
        /* $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText($IncludedTax, 530, ($this->y), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText($currency_symbol, 525, ($this->y), 'UTF-8');*/
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("We hereby certify that our registration certificate under the Haryana Value Added Tax Act is in force on  ", 35, ($this->y-20), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("which the sale of the goods specified in this Tax Invoice has been effected by us and it shall be accounted  ", 35, ($this->y-30), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("for in the turnover of sales while filling of return and due tax if any, payable on the sale has been paid or", 35, ($this->y-40), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText(" shall be paid.", 35, ($this->y-50), 'UTF-8');
		 $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("EXCHANGE: PLS VISIT CHIDIYAA.COM FOR EXCHANGE POLICY", 35, ($this->y-70), 'UTF-8');
		 $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("E&OE", 35, ($this->y-80), 'UTF-8');

        $this->_afterGetPdf();
        return $pdf;
    }

     /**
     * Insert order to pdf page
     *
     * @param Zend_Pdf_Page $page
     * @param Mage_Sales_Model_Order $obj
     * @param bool $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $top, 570, $top - 55);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates(array(25, $top, 570, $top - 55));
        $this->_setFontRegular($page, 10);

        if ($putOrderId) {
            $page->drawText(
                Mage::helper('sales')->__('Order # ') . $order->getRealOrderId(), 35, ($top -= 35), 'UTF-8'
            );
        }
        $page->drawText(
            Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate(
                $order->getCreatedAtStoreDate(), 'medium', false
            ),
            35,
            ($top -= 10),
            'UTF-8'
        );

        $top -= 10;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $top, 275, ($top - 25));
        $page->drawRectangle(275, $top, 570, ($top - 25));

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $pdfaddr = $order->getShippingAddress()->format('pdf');
            $region = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
            $appendTxt = '';
            if($region->getId()){
              $state_code = $region->getCode();
              $appendTxt .= "State Code: ".$state_code."";
            }
            $pdfaddr .= "|".$appendTxt;
            $shippingAddress = $this->_formatAddress($pdfaddr);
            $shippingMethod  = $order->getShippingDescription();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($page, 12);
        $page->drawText(Mage::helper('sales')->__('Sold to:'), 35, ($top - 15), 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('Ship to:'), 285, ($top - 15), 'UTF-8');
        } else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), 285, ($top - 15), 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(25, ($top - 25), 570, $top - 33 - $addressesHeight);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $page->drawText(strip_tags(ltrim($part)), 35, $this->y, 'UTF-8');
                    $this->y -= 15;
                }
            }
        }

        $addressesEndY = $this->y;

        if (!$order->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $text = array();
                    foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(strip_tags(ltrim($part)), 285, $this->y, 'UTF-8');
                        $this->y -= 15;
                    }
                }
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 275, $this->y-25);
            $page->drawRectangle(275, $this->y, 570, $this->y-25);

            $this->y -= 15;
            $this->_setFontBold($page, 12);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), 285, $this->y , 'UTF-8');

            $this->y -=10;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page, 10);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments   = $this->y - 15;
        }
        else {
            $yPayments   = $addressesStartY;
            $paymentLeft = 285;
        }

        foreach ($payment as $value){
            if (trim($value) != '') {
                //Printing "Payment Method" lines
                $value = preg_replace('/<br[^>]*>/i', "\n", $value);
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $page->drawText(strip_tags(trim($_value)), $paymentLeft, $yPayments, 'UTF-8');
                    $yPayments -= 15;
                }
            }
        }

        if ($order->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $page->drawLine(25,  ($top - 25), 25,  $yPayments);
            $page->drawLine(570, ($top - 25), 570, $yPayments);
            $page->drawLine(25,  $yPayments,  570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin    = 15;
            $methodStartY = $this->y;
            $this->y     -= 15;

            foreach (Mage::helper('core/string')->str_split($shippingMethod, 45, true, true) as $_value) {
                $page->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " "
                . $order->formatPriceTxt($order->getShippingAmount()) . ")";

            $page->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = array();
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $page->drawLine(400, $yShipments, 400, $yShipments - 10);
                //$page->drawLine(510, $yShipments, 510, $yShipments - 10);

                $this->_setFontRegular($page, 9);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                //$page->drawText(Mage::helper('sales')->__('Carrier'), 290, $yShipments - 7 , 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($page, 8);
                foreach ($tracks as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode != 'custom') {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    } else {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, 292, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), 410, $yShipments , 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(25,  $methodStartY, 25,  $currentY); //left
            $page->drawLine(25,  $currentY,     570, $currentY); //bottom
            $page->drawLine(570, $currentY,     570, $methodStartY); //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
