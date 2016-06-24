<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cityware\Format;

/**
 * Description of Arrays
 *
 * @author fsvxavier
 */
final class Arrays {

    public static function getObjectToArray($obj) {
        $array = array(); // noisy $array does not exist

        if (is_array($obj)) {
            $arrObj = $obj;
        } elseif (is_object($obj) AND method_exists($obj, 'toArray')) {
            $arrObj = $obj->toArray();
        } elseif (is_object($obj) AND method_exists($obj, 'getArrayCopy')) {
            $arrObj = $obj->getArrayCopy();
        } elseif (is_object($obj)) {
            $arrObj = get_object_vars($obj);
        }

        foreach ($arrObj as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->getArrayObjectToArray($val) : $val;
            $array[$key] = $val;
        }
        return $array;
    }

    /**
     * Get a random value from an array.
     *
     * @param array $array
     * @param int   $numReq The amount of values to return
     *
     * @return mixed
     */
    public static function array_rand_value(array $array, $numReq = 1) {
        if (!count($array)) {
            return;
        }
        $keys = array_rand($array, $numReq);
        if ($numReq === 1) {
            return $array[$keys];
        }
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Get a random value from an array, with the ability to skew the results.
     * Example: array_rand_weighted(['foo' => 1, 'bar' => 2]) has a 66% chance of returning bar.
     *
     * @param array $array
     *
     * @return mixed
     */
    public static function array_rand_weighted(array $array) {
        $options = [];
        foreach ($array as $option => $weight) {
            for ($i = 0; $i < $weight; ++$i) {
                $options[] = $option;
            }
        }
        return array_rand_value($options);
    }

    /**
     * Determine if all given needles are present in the haystack.
     *
     * @param array|string $needles
     * @param array        $haystack
     *
     * @return bool
     */
    public static function values_in_array($needles, array $haystack) {
        if (!is_array($needles)) {
            $needles = [$needles];
        }
        return count(array_intersect($needles, $haystack)) === count($needles);
    }

    /**
     * Determine if all given needles are present in the haystack as array keys.
     *
     * @param array|string $needles
     * @param array        $haystack
     *
     * @return bool
     */
    public static function array_keys_exist($needles, array $haystack) {
        if (!is_array($needles)) {
            return array_key_exists($needles, $haystack);
        }
        return values_in_array($needles, array_keys($haystack));
    }

    /**
     * Returns an array with two elements.
     *
     * Iterates over each value in the array passing them to the callback function.
     * If the callback function returns true, the current value from array is returned in the first
     * element of result array. If not, it is return in the second element of result array.
     *
     * Array keys are preserved.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    public static function array_split_filter(array $array, callable $callback) {
        $passesFilter = array_filter($array, $callback);
        $negatedCallback = function ($item) use ($callback) {
            return !$callback($item);
        };
        $doesNotPassFilter = array_filter($array, $negatedCallback);
        return [$passesFilter, $doesNotPassFilter];
    }

    /**
     * Split an array in the given amount of pieces.
     *
     * @param array $array
     * @param int   $numberOfPieces
     * @param bool  $preserveKeys
     *
     * @return array
     */
    public static function array_split(array $array, $numberOfPieces = 2, $preserveKeys = false) {
        if (count($array) === 0) {
            return [];
        }
        $splitSize = ceil(count($array) / $numberOfPieces);
        return array_chunk($array, $splitSize, $preserveKeys);
    }

    /**
     * Returns an array with the unique values from all the given arrays.
     *
     * @param \array[] $arrays
     *
     * @return array
     */
    public static function array_merge_values(array ...$arrays) {
        $allValues = array_reduce($arrays, function ($carry, $array) {
            return array_merge($carry, $array);
        }, []);
        return array_values(array_unique($allValues));
    }

    /**
     * Flatten an array of arrays. The `$levels` parameter specifies how deep you want to
     * recurse in the array. If `$levels` is -1, the function will recurse infinitely.
     *
     * @param array $array
     * @param int   $levels
     *
     * @return array
     */
    public static function array_flatten(array $array, $levels = -1) {
        if ($levels === 0) {
            return $array;
        }
        $flattened = [];
        if ($levels !== -1) {
            --$levels;
        }
        foreach ($array as $element) {
            $flattened = array_merge($flattened, is_array($element) ? array_flatten($element, $levels) : [$element]);
        }
        return $flattened;
    }

    /**
     * take an array and split into the given number of arrays with equal number of elements
     * if an uneven number of elements one (or more) arrays may have more elements then the others
     *
     * @example http://snippi.com/s/9ls9sug
     *
     * @param array The array we want to split
     * @param int The number of sections we want
     * @return array The resulting split array
     */
    public static function splitArray($array, $sections) {
        if (count($array) < $sections) {
            $chunkSize = 1;
        } else {
            $chunkSize = (count($array) / $sections);
        }
        return array_chunk($array, $chunkSize, true);
    }

    /**
     * Add new elements to the given array after the element with the supplied key
     *
     * @example http://snippi.com/s/6trt9kq
     *
     * @param array The array we want to add to
     * @param string|int The key we wish to add our new elements after.
     * @param array The elements we wish to add
     * @return array The resulting array with new elements
     */
    public static function addAfter($array, $key, $newElements) {
        $offset = self::getOffsetByKey($array, $key);
        if ($offset >= 0) {
            // increment cause we want to actually splice in from the element AFTER the one we found
            $offset++;
            // get the slice, and insert the new elements and rebuild the array
            $arrayItems = array_splice($array, $offset);
            $newElements += $arrayItems;
            $array += $newElements;
        }
        return $array;
    }

    /**
     * get the offset of an element within an array based on the key
     * useful for associative arrays
     *
     * @param array The containing array
     * @param string The key to search for
     * @return int|null The offset within an array | null if not found
     */
    public static function getOffsetByKey($array, $needle) {
        $offset = 0;
        foreach ($array as $key => $value) {
            if ($key === $needle) {
                return $offset;
            }
            $offset++;
        }
        return null;
    }

    /**
     * get the offset of an element within an array based on the element value
     * useful for associative arrays
     *
     * @param array The containing array
     * @param string The value to search for
     * @return int|null The offset within an array | null if not found
     */
    public static function getOffsetByValue($array, $needle) {
        $offset = 0;
        foreach ($array as $key => $value) {
            if ($value === $needle) {
                return $offset;
            }
            $offset++;
        }
        return null;
    }

    /**
     * Move Item
     *
     * Moves an existing array item to reposition it after another item.
     *
     * @param array The array we want to do the reordering in
     * @param string|int The element key we wish to move
     * @param array The element key that'll be before the one we're moving
     * @return array The resulting array with reordered elements
     */
    public static function moveItem($array, $key, $moveAfter) {
        if (!isset($array[$key]) || !isset($array[$moveAfter])) {
            return $array;
        }
        $moveItem = array(
            $key => $array[$key]
        );
        unset($array[$key]);
        $result = self::addAfter($array, $moveAfter, $moveItem);
        return $result;
    }
    
    
    
    
    

    /**
     * Remove keys from input array that are not in the whitelist
     *
     *     // Get the values "username", "password" from $_POST
     *     $auth = self::filterKeys($_POST, array('username', 'password'));
     * or
     *     $auth = self::filterKeys($_POST, 'username', 'password');
     *
     * @param array $array
     * @param mixed $keyWhitelist array or any number of strings as parameters
     *
     * @return array
     */
    public static function filterKeys(array $array, $keyWhitelist) {
        if (!is_array($keyWhitelist)) {
            $keyWhitelist = func_get_args();
            unset($keyWhitelist[0]);
        }
        foreach ($array as $key => $_) {
            if (!in_array($key, $keyWhitelist, true)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Remove keys from input array that are in the blacklist
     *
     * instead of
     *      $_ = $importedRow['id'];
     *         unset( $importedRow['id'] );
     *         $idMap[$_] = mysql::addRow( $table, $importedRow );
     *
     * use:
     *      $idMap[$importedRow['id']] = mysql::addRow( $table, self::blacklistKeys( $importedRow, 'id' ) );
     *
     * @param array        $array
     * @param array|string $keyBlacklist
     * @param ...
     *
     * @return array
     */
    public static function blacklistKeys(array $array, $keyBlacklist) {
        if (!is_array($keyBlacklist)) {
            $keyBlacklist = func_get_args();
            unset($keyBlacklist[0]);
        }
        foreach ($array as $key => $_) {
            if (in_array($key, $keyBlacklist)) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Get the needed keys from the array - adding those that are not present
     *
     *     // Get the values "username", "password" from $_POST
     *     $auth = self::extract($_POST, array('username', 'password'));
     *
     * @param array $array
     * @param mixed $keys array or any number of strings as parameters
     *
     * @return array
     */
    public static function extract(array $array, $keys) {
        if (!is_array($keys)) {
            $keys = func_get_args();
            unset($keys[0]);
        }
        $found = array();
        foreach ($keys as $key) {
            $found[$key] = isset($array[$key]) ? $array[$key] : NULL;
        }
        return $found;
    }

    /**
     * Get first array member or key
     *
     * @param array $array
     * @param bool  $getKey
     *
     * @return mixed
     */
    public static function first($array, $getKey = FALSE) {
        if ($getKey) {
            $array = array_keys($array);
        }
        return is_array($array) ? reset($array) : NULL;
    }

    public static function last($array, $getKey = FALSE) {
        if ($getKey) {
            $array = array_keys($array);
        }
        return is_array($array) ? end($array) : NULL;
    }

    /**
     * shorthand for
     *
     * $value = isset($array[$key])? $array[$key] : $default;
     *
     * also accepts null instead of array
     *
     * @param null|array  $array
     * @param int|string  $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = NULL) {
        return is_array($array) && array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Retrieve a single key from an array. If the key does not exist in the array, NULL will be returned. Supports
     * nested keys, pass as much keys as needed, used to avoid multiple isset checks for fear of E_NOTICE
     *
     * [!] difference from self::get(): suppports multiple keys, but does not support a default value
     *
     *     // Get the value "sorting" from $_GET, if it exists
     *     $sorting = self::path($_GET, 'sorting');
     *
     *     // Get the value $_POST['data']['username']
     *     $username = self::path($_POST, 'data', 'username');
     * OR
     *     // Get the value $_POST['data']['username']
     *     $username = self::path($_POST, array('data', 'username'));
     *
     *
     *     $a['a']['b']['c'] = 'd';
     *        self::path($a,array('a','b'));
     *     > array('c'=>'d')
     *
     * @static
     *
     * @param array        $array
     * @param string|array $key
     * @param string       $otherKeys more keys as needed
     *
     * @return mixed
     */
    public static function path($array, $key, $otherKeys = null) {
        if (is_array($key)) {
            // take the first array member as key and leave the others for further processing
            $_ = array_shift($key);
            $otherKeys = $key;
            $key = $_;
        }
        if (!empty($otherKeys)) {
            $argv = func_get_args();
            if (count($argv) > 3) { // may be true first time, not in recursion
                if (!is_array($otherKeys)) {
                    $otherKeys = array($otherKeys);
                }
                unset($argv[0], $argv[1], $argv[2]);
                $otherKeys += $argv;
            }
            if (is_array($otherKeys)) {
                $nextKey = array_shift($otherKeys);
            } else {
                $nextKey = $otherKeys;
                $otherKeys = null;
            }
            return isset($array[$key]) ? self::path($array[$key], $nextKey, $otherKeys) : null;
        }
        return isset($array[$key]) ? $array[$key] : null;
    }

    /**
     * same as path, but unsets the value from the array
     *
     * @static
     *
     * @param      $array
     * @param      $key
     * @param null $otherKeys
     *
     * @return mixed
     */
    public static function popPath(&$array, $key, $otherKeys = null) {
        if (is_array($key)) {
            // take the first array member as key and leave the others for further processing
            $_ = array_shift($key);
            $otherKeys = $key;
            $key = $_;
        }
        if (!empty($otherKeys)) {
            $argv = func_get_args();
            if (count($argv) > 3) { // may be true first time, not in recursion
                if (!is_array($otherKeys)) {
                    $otherKeys = array($otherKeys);
                }
                unset($argv[0], $argv[1], $argv[2]);
                $otherKeys += $argv;
            }
            if (is_array($otherKeys)) {
                $nextKey = array_shift($otherKeys);
            } else {
                $nextKey = $otherKeys;
                $otherKeys = null;
            }
            return isset($array[$key]) ? self::popPath($array[$key], $nextKey, $otherKeys) : null;
        }
        $ret = isset($array[$key]) ? $array[$key] : null;
        unset($array[$key]);
        return $ret;
    }

    /**
     * removes leafless nodes
     *
     * @static
     *
     * @param $array
     *
     * @return array
     */
    public static function clearEmpty($array) {
        if (empty($array)){
            return array();
        }
        foreach ($array as $key => $val) {
            if (!is_array($val)){
                continue;
            }
            if (empty($val)) {
                unset($array[$key]);
            } else {
                $cleanVal = self::clearEmpty($val);
                if (empty($cleanVal)) {
                    unset($array[$key]);
                } else {
                    $array[$key] = $cleanVal;
                }
            }
        }
        return $array;
    }

    public static function setPath(& $array, $keys, $value) {
        // Set current $array to inner-most array path
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (ctype_digit($key)) {
                // Make the key an integer
                $key = (int) $key;
            }
            if (!isset($array[$key])) {
                $array[$key] = array();
            }
            $array = & $array[$key];
        }
        // Set key on inner-most array
        $array[array_shift($keys)] = $value;
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array.
     *
     *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
     *
     *     // Flatten the array
     *     $array = self::flatten($array);
     *
     *     // The array will now be
     *     array('one' => 'something', 'two' => 'other');
     *
     * [!!] The keys of array values will be discarded.
     *
     * @param   array   array to flatten
     *
     * @return  array
     */
    public static function flatten($array) {
        $flat = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flat += self::flatten($value);
            } else {
                $flat[$key] = $value;
            }
        }
        return $flat;
    }

    /**
     * Adds a value to the beginning of an associative array.
     *
     *     // Add an empty value to the start of a select list
     *     self::unshift($array, 'none', 'Select a value');
     *
     * @param   array  $array array to modify
     * @param   string $key array key name
     * @param   mixed  $val array value
     *
     * @return  array
     */
    public static function unshift(array &$array, $key, $val) {
        $array = array_reverse($array, TRUE);
        $array[$key] = $val;
        $array = array_reverse($array, TRUE);
        return $array;
    }

    /**
     * Tests if an array is associative or not.
     *
     *     // Returns TRUE
     *     self::is_assoc(array('username' => 'john.doe'));
     *
     *     // Returns FALSE
     *     self::is_assoc('foo', 'bar');
     *
     * @param   array   array to check
     *
     * @return  boolean
     */
    public static function isAssoc(array $array) {
        // Keys of the array
        $keys = array_keys($array);
        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * convert to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array            $data
     * @param string           $rootNodeName
     * @param string           $numericName name given to numeric nodes
     * @param SimpleXMLElement $xml
     *
     * @return string
     */
    public static function toXml($data, $rootNodeName = 'data', $numericName = 'unknownNode', $xml = null) {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }
        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
        }
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = $numericName;
            }
            // replace anything not alpha numeric
            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // recrusive call.
                self::toXml($value, $rootNodeName, $numericName, $node);
            } else {
                $tmp = $xml->addChild($key);
                $tmp[0] = $value;
            }
        }
        // pass back as string. or simple xml object if you want!
        return $xml->asXML();
    }

    public static function childCount($arr) {
        $count = 0;
        if (is_array($arr)) {
            foreach ($arr as $v) {
                $count += self::childCount($v);
            }
        } else {
            $count++;
        }
        return $count;
    }

    /**
     * Unserializes an XML string, returning a multi-dimensional associative array, optionally runs a callback on
     * all non-array data
     *
     * Notes:
     *  Root XML tags are stripped
     *  Due to its recursive nature, unserialize_xml() will also support SimpleXMLElement objects and arrays as input
     *  Uses simplexml_load_string() for XML parsing, see SimpleXML documentation for more info
     *
     * @static
     *
     * @param mixed    $input
     * @param callback $callback
     * @param bool     $_recurse used internally, do not pass any value
     *
     * @return array|false Returns false on all failure
     */
    public static function fromXml($input, $callback = NULL, $_recurse = FALSE) {
        // Get input, loading an xml string with simplexml if its the top level of recursion
        $data = ( (!$_recurse ) && is_string($input) ) ? simplexml_load_string($input) : $input;
        // Convert SimpleXMLElements to array
        if ($data instanceof SimpleXMLElement) {
            $data = (array) $data;
        }
        // Recurse into arrays
        if (is_array($data)){
            foreach ($data as &$item) {
                $item = self::fromXml($item, $callback, TRUE);
            }
        }
        // Run callback and return
        return (!is_array($data) && is_callable($callback) ) ? call_user_func($callback, $data) : $data;
    }

    public static function insertAtIndex($array, $newElement, $index) {
        /*         * * get the start of the array ** */
        $start = array_slice($array, 0, $index);
        /*         * * get the end of the array ** */
        $end = array_slice($array, $index);
        /*         * * add the new element to the array ** */
        $start[] = $newElement;
        /*         * * glue them back together and return ** */
        return array_merge($start, $end);
    }

    /**
     * groups the values of an array by the specified pattern. Note that col=>* will NOT unset col from every row, it
     * will be present in key and values
     *
     * @static
     * @throws Exception
     *
     * @param array  $array
     * @param string $format
     *  examples:
     *      'key=>key2', 'key=>key2;key3', 'key=>*', 'key[]=>key2', 'key[key2]=>key3', 'key[key2][key3]=>key4',
     *      'key[key2][key3][]=>*'
     * @param bool   $keepKey
     *
     * @return array
     */
    public static function makeHierarchy(array $array, $format, $keepKey = false) {
        
        $matches = null;
        preg_match('#([^=\[]+)((?:\[(?:[^\]]*)\])*)=>(.+)#', $format, $matches);
        try {
            list(, $key, $braces, $columns ) = $matches;
        } catch (\Exception $e) {
            throw new \Exception('Invalid format pattern' . get_defined_vars());
        }
        if ($braces) {
            preg_match_all('#\[([^\]]*)\]#', $braces, $matches);
            $nestedKeys = $matches[1];
        }
        $values = $columns === '*' ? $columns : explode(';', $columns);
        $hasMultipleValues = $values === '*' || isset($values[1]);
        $rows = array();
        foreach ($array as $row) {
            if ($hasMultipleValues) {
                if ($values === '*') {
                    $result = $row;
                } else {
                    foreach ($values as $v) {
                        $result[$v] = $row[$v];
                    }
                }
            } else {
                $result = $row[$columns];
            }
            if ($braces) {
                isset($rows[$row[$key]]) or $rows[$row[$key]] = array();
                $cont = &$rows[$row[$key]];
                foreach ($nestedKeys as $nestedKey) {
                    if ($values === '*') {
                        if (!$keepKey) {
                            unset($result[$nestedKey]);
                        }
                    }
                    if ($nestedKey) {
                        isset($cont[$row[$nestedKey]]) or $cont[$row[$nestedKey]] = array();
                        $cont = &$cont[$row[$nestedKey]];
                    } else {
                        $cont[] = array();
                        $cont = &$cont[self::last($cont, TRUE)];
                    }
                }
                $cont = $result;
            } else {
                $rows[$row[$key]] = $result;
            }
        }
        return $rows;
    }

    /**
     * Recursive version of [array_map](http://php.net/array_map), applies the
     * same callback to all elements in an array, including sub-arrays.
     *
     *     // Apply "strip_tags" to every element in the array
     *     $array = self::map('strip_tags', $array);
     *
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param mixed $callback callback applied to every element in the array
     * @param array $array  array to map
     * @param bool  $applyToKeys
     *
     * @return  array
     */
    public static function map($callback, $array, $applyToKeys = FALSE) {
        if ($applyToKeys) {
            $newArr = array();
        }
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if ($applyToKeys) {
                    $newArr[call_user_func($callback, $key)] = self::map($callback, $val, $applyToKeys);
                } else {
                    $array[$key] = self::map($callback, $val);
                }
            } else {
                if ($applyToKeys) {
                    $newArr[call_user_func($callback, $key)] = $val;
                } else {
                    $array[$key] = call_user_func($callback, $val);
                }
            }
        }
        if ($applyToKeys) {
            return $newArr;
        } else {
            return $array;
        }
    }

    /**
     * Merges one or more arrays recursively and preserves all keys.
     * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
     *
     *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     *     $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     *     // John and Mary are married, merge them together
     *     $john = self::merge($john, $mary);
     *
     *     // The output of $john will now be:
     *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param   array $a1 initial array
     * @param   array $a2 array to merge, accepts any number of arrays
     *
     * @return  array
     */ 
    public static function merge(array $a1, array $a2) {
        $result = array();
        for ($i = 0, $total = func_num_args(); $i < $total; $i++) {
            // Get the next array
            $arr = func_get_arg($i);
            // Is the array associative?
            $assoc = self::isAssoc($arr);
            foreach ($arr as $key => $val) {
                if (isset($result[$key])) {
                    if (is_array($val) AND is_array($result[$key])) {
                        if (self::isAssoc($val)) {
                            // Associative arrays are merged recursively
                            $result[$key] = self::merge($result[$key], $val);
                        } else {
                            // Find the values that are not already present
                            $diff = array_diff($val, $result[$key]);
                            // Indexed arrays are merged to prevent duplicates
                            $result[$key] = array_merge($result[$key], $diff);
                        }
                    } else {
                        if ($assoc) {
                            // Associative values are replaced
                            $result[$key] = $val;
                        } elseif (!in_array($val, $result, TRUE)) {
                            // Indexed values are added only if they do not yet exist
                            $result[] = $val;
                        }
                    }
                } else {
                    // New values are added
                    $result[$key] = $val;
                }
            }
        }
        return $result;
    }

    public static function renameKey(array &$arr, $oldKey, $newKey) {
        $offset = self::searchKey($arr, $oldKey);
        if ($offset !== FALSE) {
            $keys = array_keys($arr);
            $keys[$offset] = $newKey;
            $arr = array_combine($keys, $arr);
        }
    }

    public static function searchKey($arr, $key) {
        $foo = array($key => NULL);
        return array_search(key($foo), array_keys($arr), TRUE);
    }

    /**
     * array_unique for multi-arrays
     *
     * @param $array
     *
     * @return array
     */
    public static function unique($array) {
        return array_map("unserialize", array_unique(array_map("serialize", $array)));
    }

}
