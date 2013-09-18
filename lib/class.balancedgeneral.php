<?php

require_once(__DIR__ . '/autoload.php');

Httpful\Bootstrap::init();
RESTful\Bootstrap::init();
Balanced\Bootstrap::init();

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
		return $dollars * 100;
	}

	public static function centsToDollars($cents) {
		return $cents / 100;
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

	public static function addBalancedFieldsToSymphonyEventFields($response) {
//print_r($response); die();
		foreach ($response as $key => $val) {
			$key = str_replace('_', '-', $key);
			if (!is_object($val) && !is_array($val) && !empty($val)) {
				$result[$key] = $val;
			} elseif (!is_object($val) && !empty($val)) {
				foreach($val as $k => $v) {
					if(!empty($v)) {
						$key = str_replace('_', '-', $k);
						$result[$key . '-' . $k] = $v;
					}
				}
				self::addBalancedFieldsToSymphonyEventFields($result);
			}
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


}