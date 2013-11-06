<?php

require_once(__DIR__ . '/autoload.php');

Httpful\Bootstrap::init();
RESTful\Bootstrap::init();
Balanced\Bootstrap::init();

Balanced\Settings::$api_key = Balanced_General::getApiKey();

Abstract Class Balanced_General {

	/**
	 * Check if Balanced is set to test mode
	 * @return bool
	 */
	public static function isTestMode() {
		return Symphony::Configuration()->get('gateway-mode', 'balanced') == 'test';
	}

	/**
	 * Get API Key based on current mode
	 * @return string
	 */
	public static function getApiKey() {
		if(self::isTestMode())
			return Symphony::Configuration()->get('test-api-key', 'balanced');
		else
			return Symphony::Configuration()->get('live-api-key', 'balanced');
	}

	/**
	 * Get Marketplace URI based on current mode
	 * @return string
	 */
	public static function getMarketplaceUri() {
		if(self::isTestMode())
			return Symphony::Configuration()->get('test-marketplace-uri', 'balanced');
		else
			return Symphony::Configuration()->get('live-marketplace-uri', 'balanced');
	}

	public static function getAllFilters() {
		// key = Balanced_Class-staticmethod-method
		return array(
			'Balanced_Customer-create' => 'Balanced: Create Customer',
			'Balanced_Customer-create-addCard' => 'Balanced: Create Customer and Add Card',
			'Balanced_Customer-create-addBankAccount' => 'Balanced: Create Customer and Add Bank Account',
			'Balanced_Customer-update' => 'Balanced: Update Customer',
			'Balanced_Customer-delete' => 'Balanced: Delete Customer',
			'Balanced_Customer-addCard' => 'Balanced: Add Card to Customer',
			'Balanced_Customer-addBankAccount' => 'Balanced: Add Bank Account to Customer',
			'Balanced_BankAccount-verification-create' => 'Balanced: Create Bank Account Verification',
			'Balanced_BankAccountVerification-update' => 'Balanced: Update Bank Account Verification',
			'Balanced_Debit-create' => 'Balanced: Create Debit',
			'Balanced_Debit-refund' => 'Balanced: Refund Debit',
			'Balanced_Credit-create' => 'Balanced: Create Credit',
			'balanced_debit' => 'Balanced Webhook: Debit',
			'balanced_credit' => 'Balanced Webhook: Credit',
			'balanced_hold' => 'Balanced Webhook: Hold',
			'balanced_refund' => 'Balanced Webhook: Refund'
		);
	}

	public static function dollarsToCents($dollars) {
		return floor( $dollars * 100 );
	}

	public static function centsToDollars($cents) {
		return floor( $cents ) / 100;
	}

	public static function calculateFees($subamount, $feeVariable, $feeFixed) {
		$fees = 0;
		if (isset($feeVariable)) {
			if (substr($feeVariable,-1) === '%') {
				$feeVariable = rtrim($feeVariable, '%');
			}
			$fees = $subamount * $feeVariable / 100;
		}
		if (isset($feeFixed)) {
			$fees = $fees + self::dollarsToCents($feeFixed);
		}

		return ceil( $fees );
	}

	public static function contentUrl() {
		return SYMPHONY_URL . '/extension/balanced/';
	}

	public static function emailPrimaryDeveloper($message) {
		if($primary = Symphony::Database()->fetchRow(0, "SELECT `first_name`, `last_name`, `email` FROM `tbl_authors` WHERE `primary` = 'yes'")) {
			$email = Email::create();

			$email->sender_name = (EmailGatewayManager::getDefaultGateway() == 'sendmail' ? Symphony::Configuration()->get('from_name', 'email_sendmail') : Symphony::Configuration()->get('from_name', 'email_smtp'));
			$email->sender_email_address = (EmailGatewayManager::getDefaultGateway() == 'sendmail' ? Symphony::Configuration()->get('from_address', 'email_sendmail') : Symphony::Configuration()->get('from_address', 'email_smtp'));

			$email->recipients = array($primary['first_name'] . ' ' . $primary['last_name'] => $primary['email']);
			$email->text_plain = $message;
			$email->subject = 'Balanced Error';

			return $email->send();
		}
	}

	public static function setBalancedFieldsToUpdate($object, $fields) {
		foreach ($fields as $key => $val) {
			$object->$key = $val;
		}
		return $object;
	}

	public static function prepareFieldsForSymphony($response) {
		foreach ($response as $key => $val) {
			$key = str_replace('_', '-', $key);
			if (!is_object($val) && !is_array($val) && !empty($val)) {
				$result[$key] = $val;
			} elseif (!is_object($val) && !empty($val)) {
				foreach($val as $k => $v) {
					if(!empty($v)) {
						$k = str_replace('_', '-', $k);
						$result[$key . '-' . $k] = $v;
					}
				}
				self::prepareFieldsForSymphony($result);
			}
		}
		return $result;
	}

	public static function translateFields($response) {
		$result = array();
		foreach ($response as $key => $val) {
			if ($key[0] === '_') {
				$key = substr($key, 1);
			}
			if (is_numeric($key[0])) {
				$key = 'i-' . $key;
			}
			if (is_numeric($key)) {
				$key = 'i-' . $key;
			}
			$key = str_replace('_', '-', $key);
			if (is_array($val)) {
				$val = self::translateFields($val);
			}
			$result[$key] = $val;
		}
		return $result;
	}

	public static function convertObjectToArray($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = self::convertObjectToArray($value);
			}
			return $result;
		}
		return $data;
	}

	public static function getType($uri)
	{
		$types = array(
			/*
			'entity' => array (
				'uri search string',
				'Method name',
			)
			*/
			'customer' => array(
				'/customers/',
				'Balanced\Customer',
			),
			'card' => array(
				'/cards/',
				'Balanced\Card',
			),
			'bank-account' => array(
				'/bank_accounts/',
				'Balanced\BankAccount',
			),
			'bank-account-verification' => array(
				'/verifications/',
				'Balanced\BankAccountVerification',
			),
			'credit' => array(
				'/credits/',
				'Balanced\Credit',
			),
			'debit' => array(
				'/debits/',
				'Balanced\Debit',
			),
			'hold' => array(
				'/holds/',
				'Balanced\Hold',
			),
			'refund' => array(
				'/refunds/',
				'Balanced\Refund',
			),
			'event' => array(
				'/events/',
				'Balanced\Event',
			),
		);

		$type = null;

		foreach ($types as $haystack => $item) {
			if (strpos($uri, $item[0]) !== false) {
				$type = $haystack;
				break;
			}
		}

		return $type;

	}

	public static function retrieveEntities(array $uris)
	{
		$result = array();
		$index = 0;
		foreach ($uris as $key => $value) {
			$uritype = self::getType($value);

			try {
				switch($uritype) {
					case 'customer':
						$getitem = Balanced\Customer::get($value);
						break;
					case 'card':
						$getitem = Balanced\Card::get($value);
						break;
					case 'bank-account':
						$getitem = Balanced\BankAccount::get($value);
						break;
					case 'bank-account-verification':
						$getitem = Balanced\BankAccountVerification::get($value);
						break;
					case 'credit':
						$getitem = Balanced\Credit::get($value);
						break;
					case 'debit':
						$getitem = Balanced\Debit::get($value);
						break;
					case 'hold':
						$getitem = Balanced\Hold::get($value);
						break;
					case 'refund':
						$getitem = Balanced\Refund::get($value);
						break;
					case 'event':
						$getitem = Balanced\Event::get($value);
						break;
				}
				$getitem = self::convertObjectToArray($getitem);

				$result[$index][$uritype] = self::translateFields($getitem);
				$index++;

			} catch (Exception $e) {

			}
		}
		return $result;
	}

	/**
	 * Build retrieved results based on an array of items and add all nested items.
	 *
	 * @param XMLElement $parent
	 *  The element the items should be added to
	 * @param array $items
	 *  The items array
	 * @param boolean $count_as_attribute
	 *  If set to true, counts will be added as attributes
	 */
	public static function buildXML($parent, $items) {
		if(!is_array($items)) return;
		// Create groups
		foreach($items as $key => $value) {
			self::itemsToXML($parent, $value);
		}

	}


	/**
	 * Convert an array of items to XML, setting all counts as variables.
	 *
	 * @param XMLElement $parent
	 *  The element the items should be added to
	 * @param array $items
	 *  The items array
	 * @param boolean $count_as_attribute
	 *  If set to true, counts will be added as attributes
	 */
	public static function itemsToXML($parent, $items) {
		if(!is_array($items)) return;

		foreach($items as $key => $value) {
			$item = new XMLElement($key);

			// Nested items
			if(is_array($value)) {
				self::itemsToXML($item, $value);
				$parent->appendChild($item);
			}

			// Other values
			else {
				$item->setValue(General::sanitize($value));
				$parent->appendChild($item);
			}
		}
	}


}