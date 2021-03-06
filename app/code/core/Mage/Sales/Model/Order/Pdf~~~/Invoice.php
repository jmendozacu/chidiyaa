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
         $page->setFillColor(Zend_Pdf_Color_Html::color('#ffffff'))->drawText('TIN # 06931838709   ', 300, 750, 'UTF-8');
         /*$page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText("VAT : ", 420, ($this->y), 'UTF-8');*/
        /* $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText($IncludedTax, 530, ($this->y), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#000000'))->drawText($currency_symbol, 525, ($this->y), 'UTF-8');*/
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("We hereby certify that our registration certificate under the Haryana Value Added Tax Act is in force on which the sale of ", 35, ($this->y-20), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("the goods specified in this Tax Invoice has been effected by us and it shall be accounted for in the turnover of sales while ", 35, ($this->y-30), 'UTF-8');
         $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("filling of return and due tax if any, payable on the sale has been paid or shall be paid.", 35, ($this->y-40), 'UTF-8');
		 $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("EXCHANGE: PLS VISIT CHIDIYAA.COM FOR EXCHANGE POLICY", 35, ($this->y-70), 'UTF-8');
		 $page->setFillColor(Zend_Pdf_Color_Html::color('#646869'))->drawText("E&OE", 35, ($this->y-80), 'UTF-8');

        $this->_afterGetPdf();
        return $pdf;
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
