<?php

	require_once(TOOLKIT . '/class.event.php');
	require_once(EXTENSIONS . '/balanced/lib/class.balancedgeneral.php');

	Class eventbalanced_webhooks extends SectionEvent{

		public static function about(){
			return array(
				'name' => 'Balanced: Webhooks Router',
				'author' => array(
					'name' => 'Patrick Yan',
					'website' => 'http://patrickyan.net',
					'email' => 'pat.yan@gmail.com'),
				'version' => '1.0',
				'release-date' => '2013-09-10'
			);
		}

		public function priority(){
			return self::kHIGH;
		}

		public static function allowEditorToParse(){
			return false;
		}

		public static function documentation(){
			return '
			<p>Attach this event to the page you have instructed Balanced to post its webhooks. This event should be accompanied by Symphony events which have a filter prefixed with "Balanced Webhook".</p>';
		}

		public function load(){
			Balanced\Settings::$api_key = Balanced_General::getApiKey();

			$body = @file_get_contents('php://input');
			$event = json_decode($body, true);

			$type = explode('.', $event['type']);

			$sEvent = $this->__getRoute();

			switch($type[0]) {
				case 'debit':
					$_POST['fields'] = Balanced_General::addBalancedFieldsToSymphonyEventFields($event['data']['object']);
					$_POST['action'][$sEvent['debit']] = 1;
					break;
				case 'credit':
					$_POST['fields'] = Balanced_General::addBalancedFieldsToSymphonyEventFields($event['data']['object']);
					$_POST['action'][$sEvent['credit']] = 1;
					break;
				case 'hold':
					$_POST['fields'] = Balanced_General::addBalancedFieldsToSymphonyEventFields($event['data']['object']);
					$_POST['action'][$sEvent['hold']] = 1;
					break;
				case 'refund':
					$_POST['fields'] = Balanced_General::addBalancedFieldsToSymphonyEventFields($event['data']['object']);
					$_POST['action'][$sEvent['refund']] = 1;
					break;
			}

		}

		private  function __getRoute(){
			$page = Frontend::Page()->resolvePage();
			$events = explode(',', $page['events']);

			$result = array();

			// Get each event's filters
			foreach ($events as $event) {
				if($event != 'balanced_webhooks') {
					$class = 'event' . $event;
					$ext = new $class();

					// Fid Balanced event filter
					foreach ($ext->eParamFILTERS as $filter) {
						if(strstr($filter, 'balanced_'))
							$name = str_replace('balanced_', '', $filter);
					}
					$result[$name] = $ext->ROOTELEMENT;
				}
			}
			return $result;
		}
	}
