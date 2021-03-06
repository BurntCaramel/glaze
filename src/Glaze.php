<?php
/*
** Copyright 2014 Patrick Smith
** http://www.burntcaramel.com/
** Released under the MIT license: http://opensource.org/licenses/MIT
*/

namespace BurntCaramel\Glaze
{
	class Glaze
	{
		const TYPE_TEXT = 'text';
		const TYPE_URL = 'URL';
		const TYPE_EMAIL_ADDRESS = 'emailAddress';
		const TYPE_EMAIL_ADDRESS_MAILTO_URL = 'emailAddressMailtoURL';
		const TYPE_SPACED_LIST_ATTRIBUTE = 'spacedListAttribute';
		const TYPE_UNSAFE_HTML = 'unsafeHTML';
		const TYPE_PREGLAZED = Glaze::TYPE_UNSAFE_HTML;
		
		/**
		*	Preserve the text for HTML output (escapes it)
		*
		*	@param string $string The string to preserve
		*	@return string The escaped string
		*/
		static public function text($string)
		{
			// Convert to UTF-8 if it's not already.
			$string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
			// Encode quotes too: this function covers general and attribute text in one.
			return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
		}
	
		/**
		*	Preserve the URL for HTML output (escapes it)
		*
		*	@param string $URLString The URL to preserve
		*	@return string The escaped URL string
		*/
		static public function URL($URLString)
		{
			$mailto = 'mailto:';
			if (strncmp($URLString, $mailto, strlen($mailto)) === 0):
				return self::emailAddressMailtoURL(substr($URLString, strlen($mailto)));
			else:
				return self::text($URLString);
			endif;
		}
	
		static protected function numericallyEncodeString($string)
		{
			// Based on http://github.com/mdesign/md.spam_me_not.ee_addon/blob/master/plugins/pi.md_spam_me_not.php
			$stringLength = strlen($string);
			$stringDisplay = '';
			for ($i = 0; $i < $stringLength; $i++):
				$method = rand(1, 2);
				$characterOrdinal = ord($string[$i]);
				$encodedCharacter = '&#' .( ($method === 1)? $characterOrdinal : 'x' .dechex($characterOrdinal) ). ';';
				$stringDisplay .= $encodedCharacter;
			endfor;
		
			return $stringDisplay;
		}
	
		/**
		*	Preserve the email address for semi-obsfucated HTML output
		*
		*	@param string $emailAddress The email address to preserve
		*	@return string The semi-obsfucated and escaped email address
		*/
		static public function emailAddress($emailAddress)
		{
			return self::numericallyEncodeString($emailAddress);
		}
	
		/**
		*	Preserve the email address for semi-obsfucated HTML output and display it as a mailto: URL
		*
		*	@param string $emailAddress The email address to display
		*	@return string The semi-obsfucated and escaped mailto: email address URL
		*/
		static public function emailAddressMailtoURL($emailAddress)
		{
			$emailAddressParts = explode('@', $emailAddress);
			$emailAddressURL = 'mailto:' .rawurlencode($emailAddressParts[0]). '@' .rawurlencode($emailAddressParts[1]);
	
			return self::numericallyEncodeString($emailAddressURL);
		}
	
		/**
		*	Use preferred types for certain attributes.
		*
		*	@param string $attributeName The name of the attribute
		*	@return string The preferred type
		*/
		static public function defaultTypeForAttributeName($attributeName)
		{
			$attributeName = strtolower($attributeName);
	
			if ($attributeName === 'class'):
				return self::TYPE_SPACED_LIST_ATTRIBUTE;
			elseif ($attributeName === 'href' || $attributeName === 'src'):
				return self::TYPE_URL;
			endif;
	
			return self::TYPE_TEXT;
		}
	
		/**
		*	Process the input value, the method depending on the optional `$valueType` argument (default ::TYPE_TEXT)
		*
		*	@param string $value The name of the attribute
		*	@param string $valueType Optional type to process the value as
		*	@return string The preserved value
		*/
		static public function value($value, $valueType = null)
		{
			if ($valueType === self::TYPE_UNSAFE_HTML) {
				return $value;
			}
			else if ($valueType === self::TYPE_URL) {
				return self::URL($value);
			}
			else if ($valueType === self::TYPE_EMAIL_ADDRESS) {
				return self::emailAddress($value);
			}
			else if ($valueType === self::TYPE_EMAIL_ADDRESS_MAILTO_URL) {
				return self::emailAddressMailtoURL($value);
			}
		
			if (($valueType === self::TYPE_SPACED_LIST_ATTRIBUTE) && is_array($value)) {
				$value = implode(' ', $value);
			}
	
			return self::text($value);
		}
	
		static public function check(&$potentialContent)
		{
			if (!isset($potentialContent) || ($potentialContent === false)) {
				return false;
			}
			else if (is_array($potentialContent) && count($potentialContent) === 0) {
				return false;
			}
			
			return $potentialContent;
		}
	}
	
	function burntCheck(&$valueToCheck, $default = null)
	{
		if (isset($valueToCheck)) {
			return $valueToCheck;
		}
		else {
			return $default;
		}
	}
}