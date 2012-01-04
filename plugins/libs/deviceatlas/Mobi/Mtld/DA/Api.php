<?php
/**
 * @package Mobi_Mtld_DA
 * @copyright Copyright © 2008 by mTLD Top Level Domain Limited.  All rights reserved.
 * Portions copyright © 2008 by Argo Interactive Limited.
 * Portions copyright © 2008 by Nokia Inc.
 * Portions copyright © 2008 by Telecom Italia Mobile S.p.A.
 * Portions copyright © 2008 by Volantis Systems Limited.
 * Portions copyright © 2002-2008 by Andreas Staeding.
 * Portions copyright © 2008 by Zandan.
 * @version 1.4.2
 */

/**
 * Class definitions for custom errors
 */
define('Mobi_Mtld_DA_Exception', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Exception' . DIRECTORY_SEPARATOR);

require_once Mobi_Mtld_DA_Exception . 'JsonException.php';
require_once Mobi_Mtld_DA_Exception . 'InvalidPropertyException.php';
require_once Mobi_Mtld_DA_Exception . 'UnknownPropertyException.php';
require_once Mobi_Mtld_DA_Exception . 'IncorrectPropertyTypeException.php';

/**
 * Used to load the recognition tree and perform lookups of all properties, or
 * individual properties. <br />
 * 
 * <b>Note:</b> Due to limitations in the level of recursion allowed, versions of PHP
 * older than 5.2.3 will be unable to load the JSON data file.
 * i.e. DeviceAtlas must be run with PHP version 5.2.3 or later.<br />
 * 
 * Typical usage is as follows: <br />
 * <code>
 * $tree = Mobi_Mtld_DA_Api::getTreeFromFile("sample/Sample.json");
 * $prps = Mobi_Mtld_DA_Api::getProperties($tree, "Nokia6680...");
 * $prop = Mobi_Mtld_DA_Api::getProperty($tree, "Nokia6680...", "displayWidth");
 * </code>
 * <br />
 * <br /> Note that you should normally use the user-agent that was received in
 * the device's HTTP request. In a PHP environment, you would do this as follows:
 * <br />
 * <code>
 * $ua = $_SERVER['HTTP_USER_AGENT'];;
 * $displayWidth = Mobi_Mtld_DA_Api::getPropertyAsInteger($tree, $ua, "displayWidth");
 * </code>
 * <br />
 * <br /> (Also note the use of the strongly typed property accessor) <br /> In
 * some contexts, the user-agent you want to recognise may have been provided in a
 * different header. Opera's mobile browser, for example, makes requests via an
 * HTTP proxy, which rewrites the headers. in that case, the original device's
 * user-agent is in the HTTP_X_OPERAMINI_PHONE_UA header, and the following code
 * could be used: <br />
 * <code>
 * $opera_header = "HTTP_X_OPERAMINI_PHONE_UA";
 * if (array_key_exists($opera_header, $_SERVER) {
 *   $ua = $_SERVER[$opera_header];
 * } else {
 *   $ua = $_SERVER['HTTP_USER_AGENT'];
 * }
 * $screen_width = Mobi_Mtld_DA_Api::getPropertyAsInteger($tree, $ua, "displayWidth");
 * </code>
 *
 * @author MTLD (dotMobi)
 * @version $Id: Api.php 2830 2008-05-13 10:48:55Z ahopebailie $
 */
class Mobi_Mtld_DA_Api {
	/**
	 * Returns a tree from a JSON string
	 * @return array tree
	 *
	 * @param string $json The string of json data.
	 *
	 * @throws JsonException
	 */
	public static function getTreeFromString($json) {
		if(version_compare(PHP_VERSION, "5.2.3") < 0){
			throw new Mobi_Mtld_Da_Exception_JsonException(
				"DeviceAtlas requires PHP version 5.2.3 or later to load the Json data.",
			Mobi_Mtld_Da_Exception_JsonException::PHP_VERSION);
		}

		$tree = json_decode($json, true);

		if($tree === FALSE || !is_array($tree)){
			throw new Mobi_Mtld_Da_Exception_JsonException(
				"Unable to load Json data.",
			Mobi_Mtld_Da_Exception_JsonException::JSON_DECODE);
		}
		elseif (!array_key_exists("$", $tree)){
			throw new Mobi_Mtld_Da_Exception_JsonException(
				"Bad data loaded into the tree.",
			Mobi_Mtld_Da_Exception_JsonException::BAD_DATA);
		}
		elseif($tree["$"]["Ver"] < 0.7) {
			throw new Mobi_Mtld_Da_Exception_JsonException(
				"DeviceAtlas json file must be v0.7 or greater. Please download a more recent version.",
			Mobi_Mtld_Da_Exception_JsonException::JSON_VERSION);
		}
		$pr = array();
		$pn = array();
		foreach($tree["p"] as $key=>$value) {
			$pr[$value] = $key;
			$pn[substr($value, 1)] = $key;
		}
		$tree["pr"] = $pr;
		$tree["pn"] = $pn;
    if(!isset($tree['r'])) {
      $tree['r'] = array();
    }
		return $tree;
	}

	/**
	 * Returns a tree from a JSON file.
	 * Use an absolute path name to be sure of success if the current working directory is not clear.
	 * @return array tree
	 *
	 * @param string $filename The location of the file to read in.
	 *
	 * @throws JsonException
	 */
	public static function getTreeFromFile($filename) {
		$json = file_get_contents($filename);
		if($json === FALSE){
			throw new Mobi_Mtld_Da_Exception_JsonException(
				"Unable to load file:" . $filename,
			Mobi_Mtld_Da_Exception_JsonException::FILE_ERROR);
		}
		return self::getTreeFromString($json);
	}

	/**
	 * Returns the revision number of the tree
	 * @return integer revision
	 *
	 * @param array &$tree Previously generated tree
	 */
	public static function getTreeRevision(array &$tree) {
		return self::_getRevisionFromKeyword($tree['$']["Rev"]);
	}

	/**
	 * Returns the revision number of this API
	 * @return integer revision
	 */
	public static function getApiRevision() {
		return self::_getRevisionFromKeyword('$Rev: 2830 $');
	}

	/**
	 * Returns an array of known property names.
	 * Returns all properties available for all user agents in this tree, with their data type names
	 * @return array properties
	 *
	 * @param array &$tree Previously generated tree
	 */
	public static function listProperties(array &$tree) {
		$types = array(
			"s"=>"string",
			"b"=>"boolean",
			"i"=>"integer",
			"d"=>"date",
			"u"=>"unknown"
			);
			$listProperties = array();
			foreach($tree['p'] as $property) {
				$listProperties[substr($property, 1)] = $types[$property{0}];
			}
			return $listProperties;
	}

	/**
	 * Returns an array of known properties (as strings) for the user agent
	 * @return array properties
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 */
	public static function getProperties(array &$tree, $userAgent) {
		return self::_getProperties($tree, $userAgent, false);
	}

	/**
	 * Returns an array of known properties (as typed) for the user agent
	 * @return array properties
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent string From the device's User-Agent header
	 */
	public static function getPropertiesAsTyped(array &$tree, $userAgent) {
		return self::_getProperties($tree, $userAgent, true);
	}

	/**
	 * Returns a value for the named property for this user agent
	 * @return string property
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 *
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 */
	public static function getProperty(array &$tree, $userAgent, $property) {
		return self::_getProperty($tree, $userAgent, $property, false);
	}

	/**
	 * Strongly typed property accessor.
	 * Returns a boolean property.
	 * (Throws an exception if the property is actually of another type.)
	 * 
	 * @return boolean property
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 *
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 */
	public static function getPropertyAsBoolean(array &$tree, $userAgent, $property) {
		self::_propertyTypeCheck($tree, $property, "b", "boolean");
		return self::_getProperty($tree, $userAgent, $property, true);
	}

	/**
	 * Strongly typed property accessor.
	 * Returns a date property.
	 * (Throws an exception if the property is actually of another type.)
	 * 
	 * @return string property
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 *  
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 *
	 */
	public static function getPropertyAsDate(array &$tree, $userAgent, $property) {
		self::_propertyTypeCheck($tree, $property, "d", "string");
		return self::_getProperty($tree, $userAgent, $property, true);
	}

	/**
	 * Strongly typed property accessor.
	 * Returns an integer property.
	 * (Throws an exception if the property is actually of another type.)
	 * 
	 * @return integer property
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 * 
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 *
	 */
	public static function getPropertyAsInteger(array &$tree, $userAgent, $property) {
		self::_propertyTypeCheck($tree, $property, "i", "integer");
		return self::_getProperty($tree, $userAgent, $property, true);
	}

	/**
	 * Strongly typed property accessor.
	 * Returns a string property.
	 * (Throws an exception if the property is actually of another type.)
	 * 
	 * @return string property
	 *
	 * @param array &$tree Previously generated tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 *  
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 *
	 */
	public static function getPropertyAsString(array &$tree, $userAgent, $property) {
		self::_propertyTypeCheck($tree, $property, "s", "string");
		return self::_getProperty($tree, $userAgent, $property, true);
	}

	//PRIVATE FUNCTIONS

	/**
	 * Formats the SVN revision string to return a number
	 * @return integer revision number
	 * @access private
	 * @param string $keyword
	 */
	private static function _getRevisionFromKeyword($keyword) {
		return trim(str_replace('$', "", substr($keyword, 6)));
	}

	/**
	 * Returns an array of known properties for the user agent.
	 * Allows the values of properties to be forced to be strings.
	 * @return array properties
	 *
	 * @access private
	 * 
	 * @param array &$tree previously generated tree
	 * @param string $userAgent string from the device's User-Agent header
	 * @param boolean $typedValues whether values in the hashmap are typed
	 */
	private static function _getProperties(array &$tree, $userAgent, $typedValues) {
		$idProperties = array();
		$matched = "";
		$sought = NULL;
    $rules = $tree['r'][1];
		self::_seekProperties($tree['t'], trim($userAgent), $idProperties, $sought, $matched, $rules);
		$properties = array();
		foreach($idProperties as $id=>$value) {
			if ($typedValues) {
				$properties[self::_propertyFromId($tree, $id)] = self::_valueAsTypedFromId($tree, $value, $id);
			} else {
				$properties[self::_propertyFromId($tree, $id)] = self::_valueFromId($tree, $value);
			}
		}
		$properties["_matched"] = $matched;
		$properties["_unmatched"] = substr($userAgent, strlen($matched));
		return $properties;
	}

	/**
	 * Returns a value for the named property for this user agent.
	 * Allows the value to be typed or forced as a string.
	 * @return Object property
	 *
	 * @param array &$tree Previously generated HashMap tree
	 * @param string $userAgent String from the device's User-Agent header
	 * @param string $property The name of the property to return
	 *
	 * @access private
	 *
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException, Mobi_Mtld_Da_Exception_InvalidPropertyException
	 */
	private static function _getProperty(array &$tree, $userAgent, $property, $typedValue) {
		$propertyId = self::_idFromProperty($tree, $property);
		$idProperties = array();
		$sought = array($propertyId => 1);
		$matched = "";
		$unmatched = "";
    $rules = $tree['r'][1];
		self::_seekProperties($tree['t'], trim($userAgent), $idProperties, $sought, $matched, $rules);
		if(count($idProperties) == 0){
			throw new Mobi_Mtld_Da_Exception_InvalidPropertyException("The property \"" . $property . "\" is invalid for the User Agent:\"" . $userAgent . "\"");
		}
		return self::_valueFromId($tree, $idProperties[$propertyId]);
	}

	/**
	 * Return the coded ID for a property's name
	 * @return string id
	 *
	 * @param array &$tree
	 * @param string $property
	 *
	 * @access private
	 *
	 * @throws Mobi_Mtld_Da_Exception_UnknownPropertyException
	 */
	private static function _idFromProperty(array &$tree, $property) {
		if(isset($tree['pn'][$property])){
			return $tree['pn'][$property];
		} else {
			throw new Mobi_Mtld_Da_Exception_UnknownPropertyException("The property \"" . $property . "\" is not known in this tree.");
		}
	}

	/**
	 * Return the name for a property's coded ID
	 * @return string property
	 *
	 * @param array &$tree
	 * @param string $id
	 *
	 * @access private
	 */
	private static function _propertyFromId(array &$tree, $id) {
		return substr($tree['p'][$id], 1);
	}

	/**
	 * Checks that the property is of the supplied type or throws an error.
	 * @return string property
	 *
	 * @param array &$tree Previously generated HashMap tree
	 * @param array $property The name of the property to return
	 * @param string $prefix The type prefix (i for integer)
	 * @param string $typeName Easy readable type name
	 *
	 * @access private
	 *
	 * @throws Mobi_Mtld_Da_Exception_IncorrectPropertyTypeException
	 */
	private static function _propertyTypeCheck(array &$tree, $property, $prefix, $typeName) {
	  if (!isset($tree["pr"][$prefix . $property])) {
	    throw new Mobi_Mtld_Da_Exception_IncorrectPropertyTypeException(
			$property . " is not of type " . $typeName);
		}
	}

	/**
	 * Seek properties for a user agent within a node. 
	 * This is designed to be recursed, and only externally called with the node representing the top of the tree
	 *
	 * @param array &$node
	 * @param string $string
	 * @param array &$properties Properties found
	 * @param array &$sought Properties being sought
	 * @param string &$matched Part of UA that has been matched
	 * 
	 * @access private
	 */
	private static function _seekProperties(array &$node, $string, array &$properties, &$sought, &$matched, &$rules) {
		$unmatched = $string;
		if (array_key_exists('d', $node)) {
			if ($sought !== NULL && count($sought) == 0) {
				return;
			}
			foreach($node['d'] as $property => $value) {
				if ($sought === NULL || isset($sought[$property])) {
					$properties[$property] = $value;
				}
				if ($sought !== NULL &&
				( !isset($node['m']) || ( isset($node['m']) && !isset($node['m'][$property]) ) ) ){
					unset($sought[$property]);
				}
			}
		}
		if(isset($node['c'])) {
      if(array_key_exists('r', $node)) {
// if I have rules for the children, try running them first
        global $debug_da;
//      echo "<pre>\n";
//      echo "I have rules to run for the current node.\n";
        $rules_count = count($node['r']);
        for($counter=0; $counter<$rules_count; $counter++) {
          $rule_id = $node['r'][$counter];
//        echo "remaining UA portion went from\n<strong>$string</strong> to\n";
          if($debug_da) {
            fwrite($debug_da, "\trule:\t[{$rules[$rule_id]}]\n");
            fwrite($debug_da, "\tfrom:\t[{$string}]\n");
          }
          $string = preg_replace('/'.$rules[$rule_id].'/', '', $string);
          if($debug_da) {
            fwrite($debug_da, "\tto:\t[{$string}]\n");
          }
//        echo "<strong>$new_string</strong>\n";
          // Make the remaining portion of the UA the new string only if it's non-empty
        }
//      echo "</pre>\n";
      }

			for($c = 1; $c < strlen($string) + 1; $c++) {
				$seek = substr($string, 0, $c);
				if(isset($node['c'][$seek])) {
					$matched .= $seek;
					self::_seekProperties($node['c'][$seek], substr($string, $c), $properties, $sought, $matched, $rules);
					break;
				}
			}
		}
	}

	/**
	 * Returns the property value typed using PHP function settype().
	 * @return Object property
	 *
	 * @param array &$tree
	 * @param string $id
	 * @param string $propertyId
	 *
	 * @access private
	 */
	private static function _valueAsTypedFromId(array &$tree, $id, $propertyId) {
		$obj = $tree['v'][$id];
		switch ($tree['p'][$propertyId]{0}) {
			case 's':
				settype($obj, "string");
				break;
			case 'b':
				settype($obj, "boolean");
				break;
			case 'i':
				settype($obj, "integer");
				break;
			case 'd':
				settype($obj, "string");
				break;
		}
		return $obj;
	}

	/**
	 * Return the value for a value's coded ID
	 * @return string value
	 *
	 * @param array &$tree
	 * @param string $id
	 *
	 * @access private
	 */
	private static function _valueFromId(array &$tree, $id) {
		return $tree['v'][$id];
	}

}
