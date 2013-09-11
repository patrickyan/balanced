<?php

require_once(EXTENSIONS . '/balanced/lib/class.balancedgeneral.php');

class Extension_Balanced extends Extension {

	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/

	public function getSubscribedDelegates() {
		return array(
			array(
				'page' => '/blueprints/events/',
				'delegate' => 'EventPreEdit',
				'callback' => 'actionEventPreEdit'
			),
			array(
				'page' => '/blueprints/events/new/',
				'delegate' => 'AppendEventFilter',
				'callback' => 'actionAppendEventFilter'
			),
			array(
				'page' => '/blueprints/events/edit/',
				'delegate' => 'AppendEventFilter',
				'callback' => 'actionAppendEventFilter'
			),
			array(
				'page' => '/blueprints/events/',
				'delegate' => 'AppendEventFilterDocumentation',
				'callback' => 'actionAppendEventFilterDocumentation'
			),
			array(
				'page' => '/frontend/',
				'delegate' => 'EventPreSaveFilter',
				'callback' => 'actionEventPreSaveFilter'
			),
			array(
				'page' => '/frontend/',
				'delegate' => 'EventPostSaveFilter',
				'callback' => 'actionEventPostSaveFilter'
			),
			array(
				'page' => '/system/preferences/',
				'delegate' => 'AddCustomPreferenceFieldsets',
				'callback' => 'actionAddCustomPreferenceFieldsets'
			),
			array(
				'page' => '/system/preferences/',
				'delegate' => 'Save',
				'callback' => 'actionSave'
			)
		);
	}

	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

	public function actionEventPreEdit($context) {
		// Your code goes here...
	}

	public function actionAppendEventFilter($context) {
		$filters = Balanced_General::getAllFilters();

		foreach ($filters as $key => $val) {
			if (is_array($context['selected'])) {
				$selected = in_array($key, $context['selected']);
				$context['options'][] = array($key, $selected, $val);
			}
		}
	}

	public function actionAppendEventFilterDocumentation($context) {
		// Todo not firing
		var_dump($context);
	}

	public function actionEventPreSaveFilter($context) {
		$filters = $context['event']->eParamFILTERS;
		$proceed = false;

		foreach ($filters as $key => $val) {
			if (in_array($val, array_keys(Balanced_General::getAllFilters()))) {
				$proceed = true;
			}
		}

		if(!$proceed) return true;
		//print_r($_POST); die();

		if(!isset($_SESSION['symphony-balanced'])) {

			Balanced\Settings::$api_key = Balanced_General::getApiKey();

			$fields = $_POST['balanced'];

			// Convert handles if Symphony standard
			foreach ($fields as $key => $val) {
				$key = str_replace('-', '_', $key);
				$fields[$key] = $val;
			}

			foreach ($filters as $key => $val) {
				if (in_array($val, array_keys(Balanced_General::getAllFilters()))) {

					try {
						switch($val) {
							case 'Balanced_Customer-create':
								$balanced = new Balanced\Customer($fields);
								$balanced = $balanced->save();
								break;
							case 'Balanced_Customer-create-addCard':
								$balanced = new Balanced\Customer($fields);
								$balanced->addCard($fields['card_uri']);
								$balanced = $balanced->save();
								break;
							case 'Balanced_Customer-create-addBankAccount':
								$balanced = new Balanced\Customer($fields);
								$balanced->addBankAccount($fields['bank_account_uri']);
								$balanced = $balanced->save();
								break;
							case 'Balanced_Customer-update':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								unset($fields['id']);
								$balanced = Balanced_General::setBalancedFieldsToUpdate($balanced, $fields);
								$balanced = $balanced->save();
								break;
							case 'Balanced_Customer-delete':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = $balanced->unstore();
								break;
							case 'Balanced_Customer-addCard':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = $balanced->addCard($fields['card_uri']);
								break;
							case 'Balanced_Customer-addBankAccount':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = $balanced->addBankAccount($fields['bank_account_uri']);
								break;
							case 'Balanced_Customer-bankAccount-verification-create':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = Balanced\BankAccount::get($balanced['bank_accounts_uri']);
								$balanced = $balanced->verify();
								break;
							case 'Balanced_BankAccount-verification-create':
								$balanced = Balanced\BankAccount::get($fields['bank_account_uri']);
								$balanced = $balanced->verify();
								break;
							case 'Balanced_BankAccountVerification-update':
								$balanced = Balanced\BankAccountVerification::get($fields['verification_uri']);
								$balanced['amount_1'] = $fields['amount_1'];
								$balanced['amount_2'] = $fields['amount_2'];
								$balanced = $balanced->save();
								break;
							case 'Balanced_Debit-create':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = $balanced->debit($fields);
								break;
							case 'Balanced_Debit-refund':
								$balanced = Balanced\Debit::get($fields['debit_uri']);
								$balanced = $balanced->refund();
								break;
							case 'Balanced_Credit-create':
								$balanced = Balanced\Customer::get($fields['customer_uri']);
								$balanced = $balanced->credit($fields);
						}
					} catch (Balanced\Errors\DuplicateAccountEmailAddress $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\InvalidAmount $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\InvalidRoutingNumber $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\InvalidBankAccountNumber $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\Declined $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotAssociateMerchantWithAccount $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\AccountIsAlreadyAMerchant $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\NoFundingSource $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\NoFundingDestination $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CardAlreadyAssociated $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotAssociateCard $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\BankAccountAlreadyAssociated $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\AddressVerificationFailed $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\MarketplaceAlreadyCreated $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\IdentityVerificationFailed $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\InsufficientFunds $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotHold $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotCredit $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotDebit $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\CannotRefund $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Balanced\Errors\BankAccountVerificationFailure $e) {
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					} catch (Exception $e) {
						// Something else happened, completely unrelated to Balanced
						$context['messages'][] = array('balanced', false, $e->getMessage());
						Balanced_General::emailPrimaryDeveloper($e->getMessage());
						return $context;
					}
				}
			}

		} else {
			$balanced = unserialize($_SESSION['symphony-balanced']);

			// Ensure updated balanced[...] fields replace empty fields
			foreach($balanced as $key => $val) {
				if(empty($val) && isset($_POST['balanced'][$key])) {
					$balanced[$key] = $_POST['balanced'][$key];
					// Todo consider updating balanced if user changes an optional field after tripe creation but prior to symphony event success
				}
			}
		}

		if (!empty($balanced)) {
			// Convert balanced object to array so that it can be looped
			if(is_object($balanced)) {
				$balanced = Balanced_General::convertObjectToArray($balanced);

				foreach($balanced as $key => $val){
					if(is_object($val)) {
						$balanced[$key] = Balanced_General::convertObjectToArray($val);
					}
				}
			}

			// Add values of response for Symphony event to process
			if(is_array($context['fields'])) {
				$context['fields'] = array_merge(Balanced_General::addBalancedFieldsToSymphonyEventFields($balanced), $context['fields']);
			} else {
				$context['fields'] = Balanced_General::addBalancedFieldsToSymphonyEventFields($balanced);
			}
			// Create the post data cookie element
			General::array_to_xml($context['post_values'], $balanced, true);

			// Add balanced response to session in case event fails
			$_SESSION['symphony-balanced'] = serialize($balanced);
		}

		return $context;
	}

	public function actionEventPostSaveFilter($context) {
		// Clear session saved response
		unset($_SESSION['symphony-balanced']);
	}

	public function actionAddCustomPreferenceFieldsets($context) {
		// If the Payment Gateway Interface extension is installed, don't
		// double display the preference, unless this function is called from
		// the `pgi-loader` context.
		if (in_array('pgi_loader', Symphony::ExtensionManager()->listInstalledHandles()) xor isset($context['pgi-loader'])) return;

		$fieldset = new XMLElement('fieldset');
		$fieldset->setAttribute('class', 'settings');
		$fieldset->appendChild(new XMLElement('legend', __('Balanced')));

		$div = new XMLElement('div', null);
		$group = new XMLElement('div', null, array('class' => 'group'));

		// Build the Gateway Mode
		$label = new XMLElement('label', __('Balanced Mode'));
		$options = array(
			array('test', Balanced_General::isTestMode(), __('Test')),
			array('live', !Balanced_General::isTestMode(), __('Live'))
		);

		$label->appendChild(Widget::Select('settings[balanced][gateway-mode]', $options));
		$div->appendChild($label);
		$fieldset->appendChild($div);

		// Live Public API Key
		$label = new XMLElement('label', __('Live API key secret'));
		$label->appendChild(
			Widget::Input('settings[balanced][live-api-key]', Symphony::Configuration()->get("live-api-key", 'balanced'))
		);
		$group->appendChild($label);

		// Test Public API Key
		$label = new XMLElement('label', __('Test API key secret'));
		$label->appendChild(
			Widget::Input('settings[balanced][test-api-key]', Symphony::Configuration()->get("test-api-key", 'balanced'))
		);
		$group->appendChild($label);

		$fieldset->appendChild($group);
		$context['wrapper']->appendChild($fieldset);
	}

	public function actionSave($context) {
		$settings = $context['settings'];

		Symphony::Configuration()->set('test-api-key', $settings['balanced']['test-api-key'], 'balanced');
		Symphony::Configuration()->set('live-api-key', $settings['balanced']['live-api-key'], 'balanced');
		Symphony::Configuration()->set('gateway-mode', $settings['balanced']['gateway-mode'], 'balanced');

		return Symphony::Configuration()->write();
	}

	public function install() {
		// Create balanced_customer_uri field database:
		Symphony::Database()->query("
			CREATE TABLE IF NOT EXISTS `tbl_fields_balanced_customer_uri` (
			 `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
			  `field_id` INT(11) unsigned NOT NULL,
			  `validator` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			  `disabled` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
			  PRIMARY KEY (`id`),
			  KEY `field_id` (`field_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");

		// Create balanced_customer_link field database:
		Symphony::Database()->query("
			CREATE TABLE IF NOT EXISTS `tbl_fields_balanced_customer_link` (
			  `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
			  `field_id` INT(11) unsigned NOT NULL,
			  `related_field_id` VARCHAR(255) NOT NULL,
			  `show_association` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
			  `disabled` enum('yes','no') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'yes',
			  PRIMARY KEY (`id`),
			  KEY `field_id` (`field_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");
	}

	public function uninstall() {
		// Drop field tables:
		Symphony::Database()->query("DROP TABLE `tbl_fields_balanced_customer_uri`");
		Symphony::Database()->query("DROP TABLE `tbl_fields_balanced_customer_link`");

		// Clean configuration
		Symphony::Configuration()->remove('test-api-key', 'balanced');
		Symphony::Configuration()->remove('live-api-key', 'balanced');
		Symphony::Configuration()->remove('gateway-mode', 'balanced');

		return Symphony::Configuration()->write();
	}

//    public function fetchNavigation() {
//        return array(
//            array(
//                'location' => 1000,
//                'name' => __('Balanced'),
//                'children' => array(
//                    array(
//                        'name' => __('Plans'),
//                        'link' => '/plans/'
//                    ),
//                    array(
//                        'name' => __('Coupons'),
//                        'link' => '/coupons/'
//                    )
//                )
//            )
//        );
//    }
}