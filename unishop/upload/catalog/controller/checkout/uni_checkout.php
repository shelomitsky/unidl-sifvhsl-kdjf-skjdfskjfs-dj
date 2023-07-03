<?php 
class ControllerCheckoutUniCheckout extends Controller {
	private $method_pickup = 'pickup';
	private $method_pickup_code = 'unicheckout.pickup';
	private $method_delivery = 'delivery';
	
	public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		
		$settings = $uniset['checkout'];
		
		$this->load->model('account/custom_field');
	
		if(isset($this->session->data['shipping_address_id']))	{
			unset($this->session->data['shipping_address_id']);
		}
		
		$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/checkout.css');
		$this->document->addScript('catalog/view/theme/unishop2/js/jquery.maskedinput.min.js');
		
		if(isset($uniset['dadata']['checkout']['status']) && $uniset['dadata']['token']) {
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/dadata.css');
			$this->document->addScript('catalog/view/theme/unishop2/js/dadata.js');
		}
		
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/uni_checkout');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('checkout/uni_checkout', '', true)
		];
		
		$data['currency'] = $this->session->data['currency'];
		
		if (!isset($this->session->data['guest']['customer_group_id'])) {
			$this->session->data['guest']['customer_group_id'] = (int)$this->config->get('config_customer_group_id');
		}
		
		if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
			$this->response->redirect($this->url->link('checkout/cart'));
		}
		
		$firstname = isset($this->session->data['firstname']) ? $this->session->data['firstname'] : '';
		$lastname = isset($this->session->data['lastname']) ? $this->session->data['lastname'] : '';
		$email = isset($this->session->data['email']) ? $this->session->data['email'] : '';
		$telephone = isset($this->session->data['telephone']) ? $this->session->data['telephone'] : '';
		
		$data['customer_id'] = '';
				
		if($this->customer->isLogged()) {
			$this->load->model('account/address');
			
			$firstname = $this->customer->getFirstName();
			$lastname = $this->customer->getLastName();
			$email = $this->customer->getEmail();
			$telephone = $this->customer->getTelephone();
			
			$data['customer_id'] = $this->session->data['customer_id'];
			
			unset($this->session->data['shipping_method']);							
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_address_id']);
			unset($this->session->data['payment_address']);
			unset($this->session->data['payment_address_id']);
			unset($this->session->data['payment_method']);	
			unset($this->session->data['payment_methods']);

			unset($this->session->data['guest']);
			unset($this->session->data['account']);
			unset($this->session->data['shipping_country_id']);
			unset($this->session->data['shipping_zone_id']);
			unset($this->session->data['payment_country_id']);
			unset($this->session->data['payment_zone_id']);
		}
		
		$data['comment'] = isset($this->session->data['comment']) ? $this->session->data['comment'] : '';
		
		$this->load->model('account/customer_group');

		$data['customer_groups'] = [];
		
		if (is_array($this->config->get('config_customer_group_display'))) {
			$customer_groups = $this->model_account_customer_group->getCustomerGroups();
			
			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$data['customer_groups'][] = $customer_group;
				}
			}
		}
		
		$data['customer_group_id'] = isset($this->session->data['guest']['customer_group_id']) ? $this->session->data['guest']['customer_group_id'] : $this->config->get('config_customer_group_id');
		
		$data['is_shipping'] = $this->cart->hasShipping() ? true : false;
		
		$data['checkout_guest'] = $this->config->get('config_checkout_guest');
		
		$data['inputs'] = [
			'firstname'	=> [
				'placeholder'	=> isset($settings['name']['text'][$lang_id]) ? $settings['name']['text'][$lang_id] : '',
				'value'			=> $firstname,
				'type'			=> 'text',
				'status' 		=> 1,
				'required' 		=> 1
			],
			'lastname'	=> [
				'placeholder'	=> isset($settings['lastname']['text'][$lang_id]) ? $settings['lastname']['text'][$lang_id] : '',
				'value'			=> $lastname,
				'type'			=> 'text',
				'status' 		=> isset($settings['lastname']['status']) ? true : false,
				'required' 		=> isset($settings['lastname']['required']) ? true : false
			],
			'telephone'	=> [
				'placeholder'	=> isset($settings['telephone']['text'][$lang_id]) ? $settings['telephone']['text'][$lang_id] : '',
				'value'			=> $telephone,
				'type'			=> 'tel',
				'status' 		=> isset($settings['telephone']['status']) ? true : false,
				'required' 		=> isset($settings['telephone']['required']) ? true : false
			],
			'email'	=> [
				'placeholder'	=> isset($settings['email']['text'][$lang_id]) ? $settings['email']['text'][$lang_id] : '',
				'value'			=> $email,
				'type'			=> 'email',
				'status' 		=> isset($settings['email']['status']) ? true : false,
				'required' 		=> isset($settings['email']['required']) ? true : false
			]
		];
		
		$data['mask_telephone'] = isset($settings['telephone']['mask'][$lang_id]) ? $uniset['checkout']['telephone']['mask'][$lang_id] : '';
		
		$data['show_popup_login'] = isset($uniset['login_form']['popup']) && !$this->customer->isLogged() ? true : false;
		
		$data['password_text'] = isset($settings['password']['text'][$lang_id]) ? $settings['password']['text'][$lang_id] : '';
		
		$data['show_password_confirm'] = isset($settings['password_confirm']['status']) ? true : false;
		$data['password_confirm_text'] = isset($settings['password_confirm']['text'][$lang_id]) ? $settings['password_confirm']['text'][$lang_id] : '';
		
		$data['checkout_passgen'] = isset($settings['passgen']) ? true : false;
		
		$data['metric_id'] = isset($uniset['checkout']['metric_id']) ? $uniset['checkout']['metric_id'] : 0;
		$data['metric_taget_id'] = isset($uniset['checkout']['metric_target_id']) ? $uniset['checkout']['metric_target_id'] : 0;
		$data['metric_target'] = isset($uniset['checkout']['metric_target']) ? $uniset['checkout']['metric_target'] : '';
		$data['analytic_category'] = isset($uniset['checkout']['analytic_category']) ? $uniset['checkout']['analytic_category'] : '';
		$data['analytic_action'] = isset($uniset['checkout']['analytic_action']) ? $uniset['checkout']['analytic_action'] : '';
		
		if(!isset($this->session->data['shipping_method_reserved'])) {
			$this->session->data['shipping_method_reserved'] = [];
		}
		
		if(!isset($this->session->data['unicheckout_pickup'])) {
			$this->session->data['unicheckout_pickup'] = [];
		}
		
		$data['custom_fields'] = $this->custom_field('account');
		$data['address'] = $this->address();
		$data['shipping_method'] = $this->shipping_method();
		
		$default_method = isset($uniset['checkout']['pickup']['not_default']) && $data['shipping_method'] ? $this->method_delivery : $this->method_pickup; 
		
		if(!isset($this->session->data['selected_method']) || !$data['shipping_method']) {
			$this->session->data['selected_method'] = $default_method;
		}
		
		$data['pickup_items'] = $this->setPickupItem();
		
		$data['payment_method'] = $this->payment_method();
		$data['cart'] = $this->cart();
		$data['totals'] = $this->totals();
		
		$data['default_method'] = $default_method;
		$data['selected_method'] = $this->session->data['selected_method'];
		
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('checkout/uni_checkout', $data));
  	}
	
	public function validate() {
		
		if(!$this->validateRequest()) {
			return;
		}
		
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/uni_checkout');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$settings = $uniset['checkout'];
		
		$this->load->model('account/custom_field');
		$this->load->model('account/customer');
		$this->load->model('account/customer_group');
		
		$json = [];
		
		if (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) {
			$json['error']['error_warning'] = $this->language->get('error_stock');
		}
		
		if(isset($this->session->data['error_minimum'])) {
			$json['error']['minimum'] = $this->session->data['error_minimum'];
		}
		
		if(isset($this->session->data['error_minimum_summ'])) {
			$json['error']['minimum_summ'] = $this->session->data['error_minimum_summ'];
		}
		
		if (!$this->cart->hasProducts() && !empty($this->session->data['vouchers'])) {
			unset($json['error']);
		}
		
		$firstname = isset($this->request->post['firstname']) ? trim($this->request->post['firstname']) : '';
		$lastname = isset($this->request->post['lastname']) ? trim($this->request->post['lastname']) : '';
		$telephone = isset($this->request->post['telephone']) ? trim($this->request->post['telephone']) : '';
		$email = isset($this->request->post['email']) ? trim($this->request->post['email']) : '';
		
		if(isset($settings['passgen'])) {
			$this->request->post['password'] = $this->getNewPassword();
		}
		
		$password = isset($this->request->post['password']) ? trim($this->request->post['password']) : '';
		$password_confirm = isset($this->request->post['password_confirm']) ? trim($this->request->post['password_confirm']) : '';
		
		if (!$this->config->get('config_checkout_guest') && !$this->customer->isLogged() && !isset($settings['passgen']) && !$password) {
			$json['error']['error_warning'] = sprintf($this->language->get('error_checkout_guest'), $this->url->link('account/login'), $this->url->link('account/register'));
		}
		
		if (mb_strlen($firstname) < 2 || mb_strlen($firstname) > 32) {
			$json['error']['firstname'] = $this->language->get('error_firstname');
		} else {
			$this->session->data['firstname'] = htmlspecialchars(strip_tags($firstname), ENT_QUOTES, 'UTF-8');
		}
		
		if(isset($settings['lastname']['status'])) {
			if ((isset($settings['lastname']['required']) || $lastname) && (mb_strlen($lastname) < 2 || mb_strlen($lastname) > 32)) {
				$json['error']['lastname'] = $this->language->get('error_lastname');
			} else {
				$this->session->data['lastname'] = htmlspecialchars(strip_tags($lastname), ENT_QUOTES, 'UTF-8');
			}
		} else {
			$this->session->data['lastname'] = $this->customer->isLogged() ? $this->customer->getLastName() : '';
		}
		
		if(isset($settings['email']['status'])) {
			if(((isset($settings['email']['required']) || $email) || (isset($settings['passgen']) && $email) || isset($this->request->post['add-new-customer'])) && (mb_strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
				$json['error']['email'] = $this->language->get('error_email');
			} else {
				$this->session->data['email'] = htmlspecialchars(strip_tags($email), ENT_QUOTES, 'UTF-8');
			}
		} else {
			$this->session->data['email'] = $this->customer->isLogged() ? $this->customer->getEmail() : '';
		}
		
		if(!$this->customer->isLogged() && (isset($this->request->post['add-new-customer']) || isset($settings['passgen']))) {
			if ($this->model_account_customer->getTotalCustomersByEmail($email)) {
				$json['error']['email'] = sprintf($this->language->get('error_exists'), $this->url->link('account/login'));
			}
		}
		
		if(isset($settings['telephone']['status'])) {
			if ((isset($settings['telephone']['required']) || $telephone) && (mb_strlen($telephone) < 2 || mb_strlen($telephone) > 32 || strpos($telephone, '_'))) {
				$json['error']['telephone'] = $this->language->get('error_telephone');
			} else {
				$this->session->data['telephone'] = htmlspecialchars(strip_tags($telephone), ENT_QUOTES, 'UTF-8');
			}
		} else {
			$this->session->data['telephone'] = $this->customer->isLogged() ? $this->customer->getTelephone() : '';
		}
		
		if(!$this->customer->isLogged() && isset($this->request->post['add-new-customer'])) {
			if (mb_strlen($password) < 4 || mb_strlen($password) > 20) {
				$json['error']['password'] = $this->language->get('error_password');
			}
			
			if(isset($settings['password_confirm']['status'])) {
				if ($password_confirm != $password) {
					$json['error']['password_confirm'] = $this->language->get('error_confirm');
				}
			}
		}
		
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = (int)$this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		}
		
		$validate_address = $this->cart->hasShipping() ? true : false;
		
		if(isset($settings['pickup']['status']) && $this->session->data['unicheckout_pickup'] && $this->session->data['selected_method'] == $this->method_pickup){
			$validate_address = false;
		}
		
		if($validate_address) {
			if (isset($this->request->post['existing-address'])) {
				$this->load->model('account/address');
						
				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->language->get('error_address');
				}
			} else {
				$counry_id = isset($this->request->post['country_id']) ? (int)$this->request->post['country_id'] : $this->config->get('config_country_id');
				$zone_id = isset($this->request->post['zone_id']) ? (int)$this->request->post['zone_id'] : $this->config->get('config_zone_id');
				$city = isset($this->request->post['city']) ? trim($this->request->post['city']) : '';
				$postcode = isset($this->request->post['postcode']) ? trim($this->request->post['postcode']) : '';
				$address_1 = isset($this->request->post['address_1']) ? trim($this->request->post['address_1']) : '';
				$address_2 = isset($this->request->post['address_2']) ? trim($this->request->post['address_2']) : '';
				
				if(isset($uniset['checkout']['country']['status']) && !$counry_id) {
					$json['error']['country_id'] = $this->language->get('error_country');
				}
				
				if(isset($uniset['checkout']['zone']['status']) && !$zone_id) {				
					$json['error']['zone_id'] = $this->language->get('error_zone');
				}
				
				if(isset($settings['city']['status'])) {
					if ((isset($settings['city']['required']) || $city) && (mb_strlen($city) < 2 || mb_strlen($city) > 128)) {
						$json['error']['city'] = $this->language->get('error_city');
					}
				}
				
				if(isset($settings['postcode']['status'])) {
					if((isset($settings['postcode']['required']) || $postcode) && (mb_strlen($postcode) < 2 || mb_strlen($postcode) > 10)) {
						$json['error']['postcode'] = $this->language->get('error_postcode');
					}
				}
				
				if(isset($settings['address']['status'])) {
					if ((isset($settings['address']['required']) || $address_1) && (mb_strlen($address_1) < 3 || mb_strlen($address_1) > 128)) {
						$json['error']['address_1'] = $this->language->get('error_address_1');
					}
				}
				
				if(isset($settings['address2']['status'])) {
					if ((isset($settings['address2']['required']) || $address_2) && (mb_strlen($address_2) < 3 || mb_strlen($address_2) > 128)) {
						$json['error']['address_2'] = $this->language->get('error_address_2');
					}
				}
			}		
		}
		
		//shipping method
		if ($this->cart->hasProducts() && $this->cart->hasShipping() && $validate_address) {
			if (!isset($this->request->post['shipping_method'])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				$shipping = explode('.', $this->request->post['shipping_method']);
				if (!isset($shipping[0]) || !isset($shipping[1])/* || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])*/) {
					$json['error']['warning'] = $this->language->get('error_shipping');
				}
			}						
		}
		
		//payment method
		if ($this->cart->hasProducts()) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			}						
		}
		
		//agree
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
				
			if ($information_info && !isset($this->request->post['confirm'])) {
				$json['error']['confirm'] = sprintf($this->language->get('error_agree'), $information_info['title']);
			}
		}
		
		//custom field
		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
				$json['error']['custom_field['.$custom_field['location'].']['.$custom_field['custom_field_id'].']'] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                $json['error']['custom_field['.$custom_field['location'].']['.$custom_field['custom_field_id'].']'] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
            }
		}
		
		if(!isset($json['error']['custom_field[account]']) && isset($this->request->post['custom_field']['account'])) {
			$this->session->data['custom_field'] = $this->request->post['custom_field']['account'];
		}
		
		if(!isset($json['error']['custom_field[address]']) && isset($this->request->post['custom_field']['address'])) {
			$this->session->data['payment_address']['custom_field'] = $this->request->post['custom_field']['address'];
		}
		
		$this->session->data['comment'] = isset($this->request->post['comment']) ? strip_tags($this->request->post['comment']) : '';
		
		if (!$json) {
			if (!$this->customer->isLogged()) {
				$this->session->data['account'] = 'guest';

				$this->session->data['guest']['firstname'] = $this->session->data['firstname'];
				$this->session->data['guest']['lastname'] = $this->session->data['lastname'];
				$this->session->data['guest']['email'] = $this->session->data['email'];
				$this->session->data['guest']['telephone'] = $this->session->data['telephone'];
				$this->session->data['guest']['customer_group_id'] = $customer_group_id;
				$this->session->data['guest']['fax'] = isset($this->request->post['fax']) ? $this->request->post['fax'] : '';
				
				//add new customer
				if((isset($this->request->post['add-new-customer']) || isset($settings['passgen'])) && ($email && $email != $settings['email']['cap'])) {
					$this->addNewCustomer();
				}
			}
		
			//add new address
			if (isset($this->request->post['new-address'])) {
				$this->addNewAddress();
			}
		
			$cart = new Cart\Cart($this->registry);
			
			//products in cart	
			$products_in_cart = $this->cart->getProducts();
			
			$products = [];
			
			$currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : '';
			
			if($products_in_cart) {
				foreach($products_in_cart as $product) {
					
					$opt = '';
					
					if($product['option']) {
						foreach($product['option'] as $option) {
							$opt .= $option['name'].': '.$option['value'];
						}
					}
					
					$products[] = [
						'id' 		=> $product['product_id'],
						'name' 		=> $product['name'],
						'variant'	=> $opt,
						'quantity'	=> $product['quantity'],
						'price'		=> $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency)
					]; 
				}
			}
			
			//confirm checkout
			$payment_method = $this->session->data['payment_method'];
			
			if (isset($this->session->data['payment_method']['parent'])) {
				$payment_arr = explode('.', $payment_method['parent']);
			} else {
				$payment_arr = explode('.', $payment_method['code']);
			}

			$payment = $payment_arr[0];
			
			$json['success']['products'] = $products;
			$json['success']['order_id'] = $this->addOrder();
			$json['success']['payment'] = $this->load->controller('extension/payment/'.$payment);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}	
	
	private function addNewCustomer() {
		$uniset = $this->config->get('config_unishop2');
		
		$this->load->language('extension/module/uni_othertext');
		
		$this->load->model('account/customer');
		$this->load->model('account/customer_group');
		
		$this->session->data['account'] = 'register';
		$this->session->data['checkout_customer_id'] = true;
		
		$this->session->data['uni_add_customer'] = [
			'email' 	=> sprintf($this->language->get('text_new_user_email'), $this->request->post['email']),
			'password'  => sprintf($this->language->get('text_new_user_password'), $this->request->post['password'])
		];
			
		$this->session->data['customer_id'] = $customer_id = $this->model_account_customer->addCustomer($this->request->post);
		
		unset($this->session->data['uni_add_customer']);
		unset($this->session->data['approve_customer']);
		
		$this->customer->login($this->request->post['email'], $this->request->post['password']);
		
		$customer_group_id = $this->customer->getGroupId();
		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);
			
		if ($customer_group_info && $customer_group_info['approval']) {
			$this->session->data['approve_customer'] = true;
		}
		
		$this->addNewAddress();
		
		if($this->customer->isLogged()) {
			unset($this->session->data['guest']);
		}
	}
	
	private function addNewAddress() {
		$this->load->model('account/customer');
		$this->load->model('account/address');
		
		if(!$this->customer->isLogged() && isset($this->session->data['approve_customer'])) {
			$customer_id = $this->session->data['customer_id'];
		} else {
			$customer_id = $this->customer->getId();
		}
		
		$this->request->post['default'] = true;
			
		$this->model_account_address->addAddress($customer_id, $this->request->post);
	}

	public function address() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		$lang_id = $this->config->get('config_language_id');
		
		$settings = $uniset['checkout'];
		
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/uni_checkout');
		
		$this->load->model('account/customer');
		$this->load->model('account/custom_field');
		
		$data['ip'] = $this->request->server['REMOTE_ADDR'];
		
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = (int)$this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		}
		
		$custom_field_error = [];
		
		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);
		
		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
				$custom_field_error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                $custom_field_error['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
            }
		}
		
		$custom_field = [];
		
		if(!$custom_field_error) {
			if (isset($this->request->post['custom_field']['address'])) {
				$custom_field = $this->request->post['custom_field']['address'];
			} elseif (isset($this->session->data['payment_address']['custom_field'])) {
				$custom_field = $this->session->data['payment_address']['custom_field'];
			}
			
			$this->session->data['payment_address']['custom_field'] = $custom_field;
		}
		
		$data['new_address'] = $new_address = isset($this->request->post['new-address']) ? true : false;
		
		//address for guest or new address for registered customer
		if(!$this->customer->isLogged() || $new_address) {
			
			$address_1 = '';
			
			if(isset($this->request->post['address_1'])) {
				$address_1 = $this->request->post['address_1'];
			} elseif (isset($this->session->data['payment_address']['address_1'])) {
				$address_1 = $this->session->data['payment_address']['address_1'];
			}
			
			$address_2 = '';
			
			if(isset($this->request->post['address_2'])) {
				$address_2 = $this->request->post['address_2'];
			} elseif (isset($this->session->data['payment_address']['address_2'])) {
				$address_2 = $this->session->data['payment_address']['address_2'];
			}
			
			$company = '';
			
			if(isset($this->request->post['company'])) {
				$company = $this->request->post['company'];
			} elseif (isset($this->session->data['payment_address']['company'])) {
				$company = $this->session->data['payment_address']['company'];
			}
			
			$postcode = '';
			
			if(isset($this->request->post['postcode'])) {
				$postcode = $this->request->post['postcode'];
			} elseif (isset($this->session->data['payment_address']['postcode'])) {
				$postcode = $this->session->data['payment_address']['postcode'];
			}
			
			$city = '';
			
			if(isset($this->request->post['city'])) {
				$city = $this->request->post['city'];
			} elseif (isset($this->session->data['payment_address']['city'])) {
				$city = $this->session->data['payment_address']['city'];
			}
			
			$zone_id = $this->config->get('config_zone_id');
			
			if(isset($this->request->post['zone_id'])) {
				$zone_id = $this->request->post['zone_id'];
			} elseif (isset($this->session->data['payment_address']['zone_id'])) {
				$zone_id = $this->session->data['payment_address']['zone_id'];
			}
			
			$this->load->model('localisation/zone');	
			$zone_info = $this->model_localisation_zone->getZone($zone_id);
			
			$zone_name = isset($zone_info['name']) ? $zone_info['name'] : '';
			$zone_code = isset($zone_info['code']) ? $zone_info['code'] : '';
			
			$country_id = $this->config->get('config_country_id');

			if(isset($this->request->post['country_id'])) {
				$country_id = $this->request->post['country_id'];
			} elseif (isset($this->session->data['payment_address']['country_id'])) {
				$country_id = $this->session->data['payment_address']['country_id'];
			}
			
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($country_id);
			
			$country		= isset($country_info['name']) ? $country_info['name'] : '';
			$iso_code_2		= isset($country_info['iso_code_2']) ? $country_info['iso_code_2'] : '';
			$iso_code_3		= isset($country_info['iso_code_3']) ? $country_info['iso_code_3'] : '';
			$address_format = isset($country_info['address_format']) ? $country_info['address_format'] : '';
			
			$address = [
				'address_1'			=> $address_1,
				'address_2' 		=> $address_2,
				'company' 			=> $company,
				'postcode'			=> $postcode,
				'city' 				=> $city,
				'zone_id' 			=> $zone_id,
				'zone' 				=> $zone_name,
				'zone_code' 		=> $zone_code,
				'country_id'		=> $country_id,
				'country' 			=> $country,
				'iso_code_2' 		=> $iso_code_2,
				'iso_code_3' 		=> $iso_code_3,
				'address_format' 	=> $address_format,
				'custom_field'		=> $custom_field
			];
		}
		
		//address for registered customer
		if($this->customer->isLogged() && !$new_address) {
			$this->load->model('account/address');	
			$data['address_id'] = $address_id = isset($this->request->post['address_id']) ? $this->request->post['address_id'] : $this->customer->getAddressId();
			$this->session->data['payment_address_id'] = $this->session->data['shipping_address_id'] = $address_id;
			$address = $this->model_account_address->getAddress($address_id);
		}
		
		//if address is empty
		if(!$address) {
			$this->load->model('localisation/zone');	
			$zone_info = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));
			
			$zone_name = isset($zone_info['name']) ? $zone_info['name'] : '';
			$zone_code = isset($zone_info['code']) ? $zone_info['code'] : '';
			
			$this->load->model('localisation/country');
			$country_info = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));
			
			$country		= isset($country_info['name']) ? $country_info['name'] : '';
			$iso_code_2		= isset($country_info['iso_code_2']) ? $country_info['iso_code_2'] : '';
			$iso_code_3		= isset($country_info['iso_code_3']) ? $country_info['iso_code_3'] : '';
			$address_format = isset($country_info['address_format']) ? $country_info['address_format'] : '';
		
			$address = [
				'address_1'			=> $this->language->get('text_address_unspecified'),
				'address_2' 		=> '',
				'company' 			=> '',
				'postcode'			=> $this->language->get('text_postcode_unspecified'),
				'city' 				=> $this->language->get('text_city_unspecified'),
				'zone_id' 			=> $this->config->get('config_zone_id'),
				'zone' 				=> $zone_name,
				'zone_code' 		=> $zone_code,
				'country_id'		=> $this->config->get('config_country_id'),
				'country' 			=> $country,
				'iso_code_2' 		=> $iso_code_2,
				'iso_code_3' 		=> $iso_code_3,
				'address_format' 	=> $address_format,
				'custom_field'		=> $custom_field
			];
		}
		
		$this->session->data['shipping_address'] = $this->session->data['payment_address'] = $address;
		
		$data['customer_id'] = $this->customer->isLogged() ? $this->customer->getId() : '';
		$data['is_shipping'] = $this->cart->hasShipping() ? true : false;
		
		$this->load->model('account/address');
		$data['addresses'] = $this->customer->getId() ? $this->model_account_address->getAddresses() : [];
		
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		
		$data['country'] = $address['country'];
		$data['country_id'] = $address['country_id'];
		$data['zone'] = $address['zone'];
		$data['zone_id'] = $address['zone_id'];
		
		$city = $address['city'];
		$postcode = $address['postcode'];
		$address_1 = $address['address_1'];
		$address_2 = $address['address_2'];
		
		//clear new address inputs  for registered customer
		if($this->customer->isLogged() || $new_address) {
			//$data['city'] = '';
			//$data['postcode'] = '';
			//$data['address_1'] = '';
			//$data['address_2'] = '';
		}
		
		$data['inputs'] = [
			'city'	=> [
				'placeholder'	=> isset($settings['city']['text'][$lang_id]) ? $settings['city']['text'][$lang_id] : '',
				'value'			=> $city,
				'type'			=> 'text',
				'status' 		=> isset($settings['city']['status']) ? true : false,
				'required' 		=> isset($settings['city']['required']) ? true : false
			],
			'postcode'	=> [
				'placeholder'	=> isset($settings['postcode']['text'][$lang_id]) ? $settings['postcode']['text'][$lang_id] : '',
				'value'			=> $postcode,
				'type'			=> 'text',
				'status' 		=> isset($settings['postcode']['status']) ? true : false,
				'required' 		=> isset($settings['postcode']['required']) ? true : false
			],
			'address_1'	=> [
				'placeholder'	=> isset($settings['address']['text'][$lang_id]) ? $settings['address']['text'][$lang_id] : '',
				'value'			=> $address_1,
				'type'			=> 'text',
				'status' 		=> isset($settings['address']['status']) ? true : false,
				'required' 		=> isset($settings['address']['required']) ? true : false
			],
			'address_2'	=> [
				'placeholder'	=> isset($settings['address2']['text'][$lang_id]) ? $settings['address2']['text'][$lang_id] : '',
				'value'			=> $address_2,
				'type'			=> 'tel',
				'status' 		=> isset($settings['address2']['status']) ? true : false,
				'required' 		=> isset($settings['address2']['required']) ? true : false
			],
		];
		
		$data['show_country'] = isset($settings['country']['status']) ? true : false;
		$data['show_zone'] = isset($settings['zone']['status']) ? true : false;
		
		$data['custom_fields'] = $this->custom_field('address');
		
		return $this->load->view('checkout/uni_address', $data);
	}
	
	public function addressRender() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->address());
		}
	}
	
	public function setPickupItem() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = $this->config->get('config_language_id');
		$pickup_items = [];
		
		if(isset($uniset['checkout']['pickup']['status'])) {
			if(isset($this->request->post['unicheckout_pickup_item_id'])) {
				$id = (int)$this->request->post['unicheckout_pickup_item_id'];
			} else if (isset($this->session->data['unicheckout_pickup']['id'])) {
				$id = (int)$this->session->data['unicheckout_pickup']['id'];
			} else {
				$id = 0;
			}
			
			foreach($uniset['checkout']['pickup']['items'] as $key => $item) {
				if(isset($item['title'][$lang_id]) && $item['title'][$lang_id]) {
					$pickup_items[] = [
						'id'			=> $key,
						'title' 		=> $item['title'][$lang_id],
						'address' 		=> html_entity_decode($item['address'][$lang_id], ENT_QUOTES, 'UTF-8'),
						'working_hours' => html_entity_decode($item['working_hours'][$lang_id], ENT_QUOTES, 'UTF-8'),
						'shelf_life' 	=> html_entity_decode($item['shelf_life'][$lang_id], ENT_QUOTES, 'UTF-8'),
						'map'		 	=> $item['map'][$lang_id],
						'selected'		=> $id == $key ? true : false
					]; 
				}
			}

			if($pickup_items && isset($pickup_items[$id])) {
				$this->session->data['unicheckout_pickup'] = [
					'id' 			=> (int)$pickup_items[$id]['id'],
					'code'			=> $this->method_pickup_code,
					'title'			=> $pickup_items[$id]['title'],
					'cost' 		   	=> 0,
					'tax_class_id'	=> 0,
					'text' 			=> ''
				];
				
				if($this->session->data['selected_method'] == $this->method_pickup) {				
					$this->session->data['shipping_method'] = $this->session->data['unicheckout_pickup'];
				}
			}
		}
		
		return $pickup_items;
	}
	
	public function setReceiptMethod() {
		$receipt_method = '';
		$json = [];
		
		if(isset($this->request->post['unicheckout_receipt_method'])) {
			$receipt_method = $this->request->post['unicheckout_receipt_method'];
			
			if($receipt_method == $this->method_pickup) {
				$this->session->data['selected_method'] = $this->method_pickup;
				$this->session->data['shipping_method'] = $this->session->data['unicheckout_pickup'];
			} else if($receipt_method == $this->method_delivery) {
				$this->session->data['selected_method'] = $this->method_delivery;
				$this->session->data['shipping_method'] = $this->session->data['shipping_method_reserved'];
			}
			
			$json['success'] = true;
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function shipping_method() {
		$uniset = $this->config->get('config_unishop2');
		
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/uni_checkout');
		
		if(isset($this->session->data['shipping_address'])) {
			$shipping_address = $this->session->data['shipping_address'];
		} else {
			$shipping_address = [
				'country_id' => $this->config->get('config_country_id'), 
				'zone_id'    => $this->config->get('config_zone_id'), 
				'firstname'  => '', 
				'lastname'   => '', 
				'company'    => '', 
				'address_1'  => '', 
				'city'       => '', 
				'iso_code2'  => '', 
				'iso_code3'  => ''
			];
		}
		
		if(!isset($this->session->data['shipping_method'])) {
			$this->session->data['shipping_method'] = [
				'code' 			=> '',
				'title' 		=> '',
				'cost' 			=> 0,
				'tax_class_id'	=> 0,
				'text' 			=> ''
			];
		}
		
		$method_data = [];

		if ($shipping_address) {
			$this->tax->setShippingAddress($shipping_address['country_id'], $shipping_address['zone_id']);
			
			$this->load->model('setting/extension');
			$results = $this->model_setting_extension->getExtensions('shipping');
			
			foreach ($results as $result) {
				if ($this->config->get('shipping_' . $result['code'] . '_status')) {
					
					$this->load->model('extension/shipping/' . $result['code']);
					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($shipping_address);
					
					if ($quote) {
						$method_data[$result['code']] = [
							'title'      => isset($uniset['checkout_shipping_title']) ? $quote['title'] : '',
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						];
					}
				}
			}

			//array_multisort(array_column($method_data, 'sort_order'), SORT_ASC, $method_data);
			
			$sort_order = [];
			
			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $method_data);
		}

		$data['shipping_methods'] = $this->session->data['shipping_methods'] = $method_data;
		
		$code = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '';
		
		if(isset($this->request->post['shipping_method']) && $code != $this->method_pickup_code) {
			$shipping_method = $this->request->post['shipping_method'];
			
			$shipping = explode('.', $shipping_method);
		
			if(isset($shipping[0]) && isset($shipping[1]) && isset($method_data[$shipping[0]]['quote'][$shipping[1]])) {
				$this->session->data['shipping_method'] = $this->session->data['shipping_method_reserved'] = $method_data[$shipping[0]]['quote'][$shipping[1]];
			} else {
				unset($this->session->data['shipping_method']);
			}
		}
		
		if((!isset($this->session->data['shipping_method']) || !$code) && $method_data) {
		
			$first_method = [];
			
			$quotes = array_column($method_data, 'quote');
			
			if($quotes && isset($quotes[0])) {
				$first_method = array_shift($quotes[0]);
			}
			
			if($first_method) {
				$this->session->data['shipping_method'] = $this->session->data['shipping_method_reserved'] = $first_method;
			}
		}
		
		$data['code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '';
		
		$data['error_warning'] = (empty($this->session->data['shipping_methods'])) ? sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact')) : '';
		
		if($this->cart->hasShipping()) {
			return $this->load->view('checkout/uni_shipping', $data);
		} else {
			return false;
		}
  	}
	
	public function shippingRender() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->shipping_method());
		}
	}
  	
  	public function payment_method() {
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/uni_checkout');
		
		if(isset($this->session->data['payment_address'])) {
			$payment_address = $this->session->data['payment_address'];
		} else {
			$payment_address = [
				'country_id' => $this->config->get('config_country_id'), 
				'zone_id'    => $this->config->get('config_zone_id'), 
				'firstname'  => '', 
				'lastname'   => '', 
				'company'    => '', 
				'address_1'  => '', 
				'city'       => '', 
				'iso_code2'  => '', 
				'iso_code3'  => ''
			];
		}
		
		if (!isset($this->session->data['payment_zone_id'])) { 
			$this->session->data['payment_zone_id '] = $payment_address['zone_id'];
		}
		
		$this->tax->setPaymentAddress($payment_address['country_id'], $payment_address['zone_id']);
		
		$method_data = [];

		if ($payment_address) {
			
			$total_data = [];					
			$total = 0;
			$taxes = $this->cart->getTaxes();
			
			$total_data = [
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			];
			
			$this->load->model('setting/extension');
			$results = $this->model_setting_extension->getExtensions('total');
			
			$sort_order = []; 
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}
			
			$results = $this->model_setting_extension->getExtensions('payment');
			
			$recurring = $this->cart->hasRecurringProducts();

			foreach ($results as $result) {
				if ($this->config->get('payment_' . $result['code'] . '_status')) {
					$this->load->model('extension/payment/' . $result['code']);
					$method = $this->{'model_extension_payment_' . $result['code']}->getMethod($payment_address, $total);

					if ($method) {
						if ($recurring) {
							if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
								if (isset($method['quote']) ){
									foreach ($method['quote'] as $quote) {
										$method_data[$quote['code']] = $quote;
									}
								} else {
									$method_data[$result['code']] = $method;
								}
							}
						} else {
							if (isset($method['quote']) ){
								foreach ($method['quote'] as $quote) {
									$method_data[$quote['code']] = $quote;
								}
							} else {
								$method_data[$result['code']] = $method;
							}
						}
					}
				}
			}

			//array_multisort(array_column($method_data, 'sort_order'), SORT_ASC, $method_data);
			
			$sort_order = [];
			
			foreach ($method_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $method_data);
		}
		
		$data['payment_methods'] = $this->session->data['payment_methods'] = $method_data;
		
		if(isset($this->request->post['payment_method'])) {
			$payment_method = $this->request->post['payment_method'];
			
			if(isset($method_data[$payment_method])) {
				$this->session->data['payment_method'] = $method_data[$payment_method];
			} else {
				unset($this->session->data['payment_method']);
			}
		}
		
		if(!isset($this->session->data['payment_method']) && $method_data) {
			$this->session->data['payment_method'] = array_shift($method_data);
		}
		
		$data['code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';
   
		$data['error_warning'] = empty($this->session->data['payment_methods']) ? sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact')) : '';
		
		return $this->load->view('checkout/uni_payment', $data);
  	}
	
	public function paymentRender() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->payment_method());
		}
	}
	
	public function custom_field($location = '') {
		$data['custom_fields'] = [];
		
		$this->load->model('account/customer');
		$this->load->model('account/custom_field');
		
		if($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getGroupId();
		} else {
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = (int)$this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}
		}
		
		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);
			
		foreach ($custom_fields as $custom_field) {
			if ($custom_field['location'] == $location) {
				$data['custom_fields'][] = $custom_field;
			}
		}
		
		if ($this->customer->isLogged()) {
			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
			
			if($customer_info['custom_field'] && !isset($this->session->data['custom_field'])) {
				$this->session->data['custom_field'] = json_decode($customer_info['custom_field'], true);
			}
		}
		
		$data['checked'] = [];
		
		if($location = 'account' && isset($this->session->data['custom_field'])) {
			$data['checked']['account'] = $this->session->data['custom_field'];
		}
		
		if($location = 'address' && isset($this->session->data['payment_address']['custom_field'])) {
			$data['checked']['address'] = $this->session->data['payment_address']['custom_field'];
		}
		
		return $this->load->view('checkout/uni_custom_field', $data);
	}
	
	public function customFieldRenderAccount() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->custom_field('account'));
		}
	}
	
	public function cart(){
		$this->load->language('product/product');
		$this->load->language('checkout/cart');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('checkout/uni_checkout');
		
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
        
		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = [];
		}
			
		$points = $this->customer->getRewardPoints();
		
		$points_total = 0;
			
		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}
		
		$data['reward_user'] = $points;
		
		if($points_total) {
			$data['entry_reward'] = sprintf($this->language->get('entry_reward'), $points, ($points <= $points_total ? $points : $points_total));
		} else {
			$data['entry_reward'] = '';
		}
		
		$data['error_warning'] = [];

		if (isset($this->error['warning'])) {
			$data['error_warning'][] = $this->error['warning'];
		}
		
		if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
      		$data['error_warning'][] = $this->language->get('error_stock');
		}
			
		if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
			$data['error_warning'][] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
		}
		
		if (!$this->config->get('config_checkout_guest') && !$this->customer->isLogged() && !isset($uniset['checkout']['passgen'])) {
			$data['error_warning'][] = sprintf($this->language->get('error_checkout_guest'), $this->url->link('account/login'), $this->url->link('account/register'));
		}
						
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$currency = $this->session->data['currency'];
		
		$this->load->model('tool/image');
		$this->load->model('tool/upload');
		
		$total_products_summ = 0;
		
		$hide_model = isset($uniset['checkout']['hide_model']) ? true : false;

        $data['products'] = [];

        $products = $this->cart->getProducts();
			
        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $data['error_warning'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				$this->session->data['error_minimum'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			} else {
				unset($this->session->data['error_minimum']);
			}

            if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_'.$this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_cart_height'));
			} else {
                $image = '';
            }

            $option_data = [];
			
			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = [
					'name'  => $option['name'],
					'value' => (mb_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				];
			}

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $currency);
            } else {
                $price = false;
            }

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $currency);
            } else {
                $total = false;
            }
			
			$total_products_summ += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];
			
			$recurring = '';

			if ($product['recurring']) {
				$frequencies = [
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year')
				];

				if ($product['recurring']['trial']) {
					$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
				}

				if ($product['recurring']['duration']) {
					$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				} else {
					$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
				}
			}

            $data['products'][] = [
				'cart_id'  			  => $product['cart_id'],
                'product_id'          => $product['product_id'],
                'thumb'               => $image,
                'name'                => $product['name'],
                'model'               => !$hide_model ? $product['model'] : '',
                'option'              => $option_data,
                'quantity'            => $product['quantity'],
				'minimum'             => $product['minimum'],
                'stock'     		  => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward'              => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                'price'               => $price,
                'total'               => $total,
                'href'                => $this->url->link('product/product', 'product_id='.$product['product_id']),
                'remove'              => $this->url->link('checkout/cart', 'remove='.$product['product_id']),
                'recurring'           => $recurring,
            ];
		}
			
		$data['related'] = isset($uniset['checkout_related_product']) ? $uniset['checkout_related_product'] : '';
		$data['checkout_related_text'] = isset($uniset[$language_id]['checkout_related_text']) ? $uniset[$language_id]['checkout_related_text'] : '';
		$data['products_related'] = $this->getRelatedProduct();

        $data['products_recurring'] = [];
            
		$data['vouchers'] = [];
		
		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = [
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
					'remove'      => $this->url->link('checkout/cart', 'remove='.$key)   
				];
				
				$total_products_summ += $voucher['amount'];
			}
		}
						 
		$data['coupon_status'] = $this->config->get('total_coupon_status');
			
		if (isset($this->request->post['coupon'])) {
			$data['coupon'] = $this->request->post['coupon'];			
		} elseif (isset($this->session->data['coupon'])) {
			$data['coupon'] = $this->session->data['coupon'];
		} else {
			$data['coupon'] = '';
		}
			
		$data['voucher_status'] = $this->config->get('total_voucher_status');
			
		if (isset($this->request->post['voucher'])) {
			$data['voucher'] = $this->request->post['voucher'];				
		} elseif (isset($this->session->data['voucher'])) {
			$data['voucher'] = $this->session->data['voucher'];
		} else {
			$data['voucher'] = '';
		}
			
		$data['reward_status'] = ($points && $points_total && $this->config->get('total_reward_status'));
			
		if (isset($this->request->post['reward'])) {
			$data['reward'] = $this->request->post['reward'];				
		} elseif (isset($this->session->data['reward'])) {
			$data['reward'] = $this->session->data['reward'];
		} else {
			$data['reward'] = '';
		}

		if(isset($uniset['checkout']['min_summ']) && $uniset['checkout']['min_summ'] > 0 && ($uniset['checkout']['min_summ'] > $total_products_summ)) {
			$this->session->data['error_minimum_summ'] = sprintf($this->language->get('error_minimum_summ'), $this->currency->format($uniset['checkout']['min_summ'], $currency));
			$data['error_warning'][] = sprintf($this->language->get('error_minimum_summ'), $this->currency->format($uniset['checkout']['min_summ'], $currency));		
		} else {
			unset($this->session->data['error_minimum_summ']);
		}
		
		return $this->load->view('checkout/uni_cart', $data);
	}
	
	public function cartRender() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->cart());
		}
	}
	
	private function totals() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$this->load->language('checkout/cart');
		$this->load->language('checkout/checkout');
		$this->load->language('checkout/uni_checkout');
		
		$this->load->model('setting/extension');
		
		$data['product_total'] = $this->cart->countProducts();
		$data['weight_total'] = $this->config->get('config_cart_weight') && $this->cart->getWeight() ? $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')) : '';
							
		$total = 0;
		$taxes = $this->cart->getTaxes();
		$totals = [];
		
		$total_data = [
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		];
			 
		$results = $this->model_setting_extension->getExtensions('total');
			
		$sort_order = []; 
			
		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}
			
		array_multisort($sort_order, SORT_ASC, $results);
		
		if(isset($uniset['checkout']['pickup']['status']) && $this->session->data['unicheckout_pickup'] && $this->session->data['selected_method'] == $this->method_pickup) {
			$this->config->set('total_shipping_status', false);
		}
		
		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}
			
		$sort_order = []; 
		
		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $totals);
		
		$data['totals'] = [];
	
		$i = 1;
		
		foreach ($totals as $total) {
			$key = $total['code'] == 'tax' ? $total['code'].'_'.$i++ : $total['code'];
			
			$data['totals'][$key] = [
				'title'	=> $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
				'code' 	=> $total['code']
			];
		}
		
		if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
			$data['totals'] = [];
		}
		
		$data['confirm'] = isset($this->session->data['confirm']) ? $this->session->data['confirm'] : '';
		
		$data['text_confirm'] = '';
		
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));

			if ($information_info) {
				$data['text_confirm'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			}
		}
		
		return $this->load->view('checkout/uni_totals', $data);
	}
	
	public function totalsRender() {
		if($this->validateRequest()) {
			$this->response->setOutput($this->totals());
		}
	}
		
	private function addOrder() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$this->load->language('checkout/checkout');
		
		$this->load->model('account/customer');
		$this->load->model('setting/extension');

		if (!$this->cart->hasShipping()) {
			unset($this->session->data['shipping_address']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}
		
		$currency = $this->session->data['currency'];

		$order_data = [];
		
		$total_data = [];
		$total = 0;
		$taxes = $this->cart->getTaxes();
		
		$totals = [];
		
		$total_data = [
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		];

		$sort_order = [];

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_'.$value['code'].'_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);
		
		if(isset($uniset['checkout']['pickup']['status']) && $this->session->data['unicheckout_pickup'] && $this->session->data['selected_method'] == $this->method_pickup) {
			$this->config->set('total_shipping_status', false);
		}

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = []; 

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		
		array_multisort($sort_order, SORT_ASC, $totals);
		
		$order_data['totals'] = $totals;

		$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		$order_data['store_id'] = $this->config->get('config_store_id');
		$order_data['store_name'] = $this->config->get('config_name');
		$order_data['store_url'] = $order_data['store_id'] ? $this->config->get('config_url') : ($this->config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
		
		$order_data['firstname'] = $this->session->data['firstname'];
		$order_data['lastname'] = $this->session->data['lastname'];
		$order_data['email'] = $this->session->data['email'] ? $this->session->data['email'] : $uniset['checkout']['email']['cap'];
		$order_data['telephone'] = $this->session->data['telephone'];
		
		if ($this->customer->isLogged()) {
			$customer_id = $this->customer->getId();
			$customer_info = $this->model_account_customer->getCustomer($customer_id);
			$order_data['customer_id'] = $customer_id;
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['fax'] = $customer_info['fax'];
		} elseif(isset($this->session->data['approve_customer'])) {
			$customer_id = $this->session->data['customer_id'];
			$customer_info = $this->model_account_customer->getCustomer($customer_id);
			$order_data['customer_id'] = $customer_id;
			$order_data['customer_group_id'] = $customer_info['customer_group_id'];
			$order_data['fax'] = $customer_info['fax'];
			
			unset($this->session->data['approve_customer']);
		} else {
			$order_data['customer_id'] = 0;
			$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
			$order_data['fax'] = $this->session->data['guest']['fax'];
		}
			
		$order_data['custom_field'] = isset($this->session->data['custom_field']) ? $this->session->data['custom_field'] : [];

		$order_data['payment_firstname'] = $order_data['firstname'];
		$order_data['payment_lastname'] = $order_data['lastname'];
		$order_data['payment_company'] = $this->session->data['payment_address']['company'];
		$order_data['payment_city'] = $this->session->data['payment_address']['city'];
		$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
		$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
		$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
		$order_data['payment_country'] = $this->session->data['payment_address']['country'];
		$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
		$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
		$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : []);
		$order_data['payment_address_1'] =  $this->session->data['payment_address']['address_1'];
		$order_data['payment_address_2'] =  $this->session->data['payment_address']['address_2'];

		$order_data['payment_method'] = isset($this->session->data['payment_method']['title']) ? $this->session->data['payment_method']['title'] : '';
		$order_data['payment_code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';

		if ($this->cart->hasShipping()) {
			$order_data['shipping_firstname'] = $order_data['firstname'];
			$order_data['shipping_lastname'] = $order_data['lastname'];
			$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
			$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
			$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
			$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
			$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
			$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
			$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
			$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
			$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : []);
			$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
			$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];

			$order_data['shipping_method'] = isset($this->session->data['shipping_method']['title']) ? $this->session->data['shipping_method']['title'] : '';
			$order_data['shipping_code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '';
		} else {
			$order_data['shipping_firstname'] = '';
			$order_data['shipping_lastname'] = '';
			$order_data['shipping_company'] = '';
			$order_data['shipping_address_1'] = '';
			$order_data['shipping_address_2'] = '';
			$order_data['shipping_city'] = '';
			$order_data['shipping_postcode'] = '';
			$order_data['shipping_zone'] = '';
			$order_data['shipping_zone_id'] = '';
			$order_data['shipping_country'] = '';
			$order_data['shipping_country_id'] = '';
			$order_data['shipping_address_format'] = '';
			$order_data['shipping_custom_field'] = [];
			$order_data['shipping_method'] = '';
			$order_data['shipping_code'] = '';
		}
		
		if(isset($uniset['checkout']['pickup']['status']) && $this->session->data['unicheckout_pickup'] && $this->session->data['selected_method'] == $this->method_pickup) {
			$order_data['shipping_address_1'] = '';
			$order_data['shipping_city'] = '';
			$order_data['shipping_method'] = sprintf($this->language->get('text_pickup_in_order'), $this->session->data['unicheckout_pickup']['title']);
			$order_data['shipping_code'] = '';
		}

		$order_data['products'] = [];

		foreach ($this->cart->getProducts() as $product) {
			$option_data = [];

			foreach ($product['option'] as $option) {
				$option_data[] = [
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'option_id'               => $option['option_id'],
					'option_value_id'         => $option['option_value_id'],
					'name'                    => $option['name'],
					'value'                   => $option['value'],
					'type'                    => $option['type']
				];
			}

			$order_data['products'][] = [
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'download'   => $product['download'],
				'quantity'   => $product['quantity'],
				'subtract'   => $product['subtract'],
				'price'      => $product['price'],
				'total'      => $product['total'],
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']
			];
		}

		$order_data['vouchers'] = [];

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$order_data['vouchers'][] = [
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'],
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']
				];
			}
		}

		$order_data['comment'] = $this->session->data['comment'];
			
		$order_data['total'] = $total;
		
		if (isset($this->request->cookie['tracking'])) {
			$order_data['tracking'] = $this->request->cookie['tracking'];

			$subtotal = $this->cart->getSubTotal();

			// Affiliate
			$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

			if ($affiliate_info) {
				$order_data['affiliate_id'] = $affiliate_info['customer_id'];
				$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
			} else {
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
			}

			// Marketing
			$this->load->model('checkout/marketing');

			$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

			if ($marketing_info) {
				$order_data['marketing_id'] = $marketing_info['marketing_id'];
			} else {
				$order_data['marketing_id'] = 0;
			}
		} else {
			$order_data['affiliate_id'] = 0;
			$order_data['commission'] = 0;
			$order_data['marketing_id'] = 0;
			$order_data['tracking'] = '';
		}

		$order_data['language_id'] = $this->config->get('config_language_id');
		$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		$order_data['currency_code'] = $this->session->data['currency'];
		$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
			$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		} else {
			$order_data['forwarded_ip'] = '';
		}

		if (isset($this->request->server['HTTP_USER_AGENT'])) {
			$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		} else {
			$order_data['user_agent'] = '';
		}

		if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
			$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		} else {
			$order_data['accept_language'] = '';
		}

		$this->load->model('checkout/order');
		$order_id = $this->model_checkout_order->addOrder($order_data);
		$this->session->data['order_id'] = $order_id;
		
		return $order_id;
  	}
	
	public function country() {
		$json = [];
		
		$this->load->model('localisation/country');

    	$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);
		
		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = [
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']		
			];
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function resetMethod () {
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['unicheckout_pickup']);
	}
	
	private function getNewPassword() {
		$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
		$pass_length = 8; 
		$chars_length = mb_strlen($chars)-1; 
		$password = ''; 

		while($pass_length--) {
			$password .= $chars[rand(0, $chars_length)];
		}
		
		return $password;
	}
	
	private function getRelatedProduct() {
		$uniset = $this->config->get('config_unishop2');
		$language_id = $this->config->get('config_language_id');
		
		$this->load->model('tool/image');
		$this->load->model('extension/module/uni_related');
		$this->load->model('extension/module/uni_new_data');
		
		$currency = $this->session->data['currency'];
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$data['checkout_related_text'] = $uniset[$language_id]['checkout_related_text'];		
			
		$products = [];
		
		if($this->cart->getProducts()) {
			
			$results = $this->model_extension_module_uni_related->getRelated();
				
			foreach ($results as $result) {

				$image = $result['image'] ? $this->model_tool_image->resize($result['image'], 110, 110) : '';
				
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$price = false;
				}
						
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $currency);
				} else {
					$special = false;
				}
					
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $currency);
				} else {
					$tax = false;
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
				
				$new_data = $this->model_extension_module_uni_new_data->getNewData($result);
			
				if($new_data['special_date_end']) {
					$data['show_timer'] = true;
				}
					
				if($result['quantity'] > 0)	{
					$products[] = [
						'product_id' 	=> $result['product_id'],
						'thumb'   	 	=> $image,
						'name'    		=> $result['name'],
						'price'   	 	=> $price,
						'special' 	 	=> $special,
						'price_value' 	=> $this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'special_value'	=> $this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax'))*$this->currency->getValue($currency),
						'tax'        	=> $tax,
						'rating'     	=> $rating,
						'num_reviews' 	=> $result['reviews'],
						'quantity' 		=> $result['quantity'],
						'minimum' 		=> $result['minimum'],
						'stickers' 		=> $new_data['stickers'],
						'options'		=> $new_data['options'],
						'reviews'   	=> sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
						'href'    		=> $this->url->link('product/product', 'product_id=' . $result['product_id']),
						'cart_btn_icon' => $uniset[$language_id]['cart_btn_icon'],
						'cart_btn_text' => $uniset[$language_id]['cart_btn_text'],
					];
				}
			}
		}
		
		return $products;
	}
	
	private function validateRequest() {
		if (!isset($this->request->server['HTTP_X_REQUESTED_WITH']) || strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			return false;
		} else {
			return true;
		}
	}
}