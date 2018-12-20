<?php
/**
 * FireText Magento Extension - v0.1.2
 * Copyright (c) 2014 Insite Digital
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    FireText
 * @subpackage SMS
 * @license    http://opensource.org/licenses/MIT  MIT License
 * @author     Ryan Ward <ryan.ward@insitedigital.co.uk>
 *
 * Class FireText_SMS_Block_Adminhtml_System_Config_Fieldset_Banner
 */
class Softprodigy_Message_Block_Adminhtml_System_Config_Fieldset_Banner
	extends Mage_Adminhtml_Block_Abstract
	implements Varien_Data_Form_Element_Renderer_Interface
{
	/**
	 * Render fieldset html
	 *
	 * @param Varien_Data_Form_Element_Abstract $element
	 * @return string
	 */
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		return '<div class="admin-softprodigy-info">
				    <div class="instructions">
				        <strong>'.
			 $this->__('This extension will send text messages to customers with '.
			 		   'a mobile number based on certain order updates specified below').
					   '.<br/>'.
			 $this->__('Please note: ').'</strong>'.
			 $this->__('The following variables will be available in the templates below,
				        they must be specified inside double curly brackets, an example
				        template is provided for the Order Create Event').'.<br/>
				        Customer Name: <strong>{{customer.name}}</strong><br/>
				        Customer Forename: <strong>{{customer.forename}}</strong><br/>
				        Customer Surname: <strong>{{customer.surname}}</strong><br/>
				        Customer Email: <strong>{{customer.email}}</strong><br/>
				        Store Name: <strong>{{store.name}}</strong><br/>
				        Store Email: <strong>{{store.email}}</strong><br/>
				        Store Phone: <strong>{{store.phone}}</strong><br/>
				        Order Increment ID: <strong>{{order.increment}}</strong><br/>
				        Order Magento ID: <strong>{{order.id}}</strong><br/>
				        Order Grand Total: <strong>{{order.total}}</strong><br/>
				        Order Discount Total: <strong>{{order.discount}}</strong><br/>
				        Order Shipping Total: <strong>{{order.shipping}}</strong><br/>
				        Payment Method Title: <strong>{{payment.title}}</strong></br/>
				    </div>
				    <div class="clear"></div>
				</div>';
	}
}
