<?php

	require_once TOOLKIT . '/class.datasource.php';
	require_once FACE . '/interface.datasource.php';
	require_once(EXTENSIONS . '/balanced/lib/class.balancedgeneral.php');

	Class BalancedDatasource extends DataSource implements iDatasource {

		public static function getName() {
			return __('Balanced');
		}

		public static function getClass() {
			return __CLASS__;
		}

		public function getSource() {
			return self::getClass();
		}

		public static function getTemplate(){
			return EXTENSIONS . '/balanced/templates/blueprints.datasource.tpl';
		}

		public function settings() {
			$settings = array();

			$settings[self::getClass()]['params'] = $this->dsParamPARAMS;
			$settings[self::getClass()]['uris'] = implode(', ', (array)$this->dsParamURIS);

			return $settings;
		}

	/*-------------------------------------------------------------------------
		Utilities
	-------------------------------------------------------------------------*/

		/**
		 * Returns the source value for display in the Datasources index
		 *
		 * @param string $file
		 *  The path to the Datasource file
		 * @return string
		 */
		public function getSourceColumn($handle) {
			return 'Balanced';
		}

	/*-------------------------------------------------------------------------
		Editor
	-------------------------------------------------------------------------*/

		public static function buildEditor(XMLElement $wrapper, array &$errors = array(), array $settings = null, $handle = null) {
			$settings = $settings[self::getClass()];

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings contextual duplicator ' . __CLASS__);
			$fieldset->appendChild(new XMLElement('legend', self::getName()));

			// URIs
			$fieldwrapper = new XMLElement('div');
			$fieldwrapper->setAttribute('class', 'suggestable');
			$label = new XMLElement('label', __('URIs to retrieve') . '<i>' . __('Optional') . '</i>');
			$input = Widget::Input('fields[' . self::getClass() . '][uris]', $settings['uris']);
			$label->appendChild($input);
			$fieldwrapper->appendChild($label);
			$fieldset->appendChild($fieldwrapper);

			// Suggest existing groups
			//$storage = new Balanced();
			//$groups = $storage->getGroups();

			/*if(!empty($groups)) {
				$tags = new XMLElement('ul', null, array('class' => 'tags'));
				foreach($groups as $group) {
					$tags->appendChild(new XMLElement('li', $group));
				}
				$fieldset->appendChild($tags);
			}*/

			// Output parameters
			$input = Widget::Input('fields[' . self::getClass() . '][params]', '1', 'checkbox');
			if(intval($settings['params']) == 1) {
				$input->setAttribute('checked', 'checked');
			}
			$label = Widget::Label();
			$label->setValue(__('%s Output groups as parameters', array($input->generate())));
			$fieldset->appendChild($label);

			$wrapper->appendChild($fieldset);
		}

		public static function validate(array &$settings, array &$errors) {
			return true;
		}

		public static function prepare(array $settings, array $params, $template) {
			$settings = $settings[self::getClass()];

			// URIs
			$uris = explode(',', $settings['uris']);
			if(!empty($uris)) {
				foreach($uris as $uri) {
					if(trim($uri) == '') continue;
					$string .= "\t\t\t'" . trim($uri) . "'," . PHP_EOL;
				}
				$template = str_replace('<!-- URIS -->', trim($string), $template);
			}

			// Add dependencies
			if(preg_match_all('@(\$ds-[0-9a-z_\.\-]+)@i', $template, $matches)){
				$dependencies = General::array_remove_duplicates($matches[1]);
				$template = str_replace('<!-- DS DEPENDENCY LIST -->', "'" . implode("', '", $dependencies) . "'", $template);
			}

			// Return template with settings
			return sprintf($template,
				$params['rootelement'],
				$settings['params']
			);
		}

	/*-------------------------------------------------------------------------
		Execution
	-------------------------------------------------------------------------*/

		public function grab(array &$param_pool = null) {
			$result = new XMLElement($this->dsParamROOTELEMENT);
			$uris = array();

			// Get uris
			if(!empty($this->dsParamURIS)) {
				foreach($this->dsParamURIS as $key => $value) {
					$uris[$key] = $this->__processParametersInString($value, $this->_env);
					//print_r($uris);die();
				}
			}
			else {
				//$uris = $retrived->get();
			}

			$retrieved = Balanced_General::retrieveEntities($uris);
			// Build XML
			Balanced_General::buildXML($result, $retrieved);

			// Add output parameters
			if(intval($this->dsParamPARAMS) == 1 && is_array($uris)) {
				foreach($uris as $name => $values) {
					$param_pool['ds-' . $this->dsParamROOTELEMENT . '.' . $name] = array_keys((array)$values);
				}
			}

			return $result;
		}
	}

	return 'BalancedDatasource';
