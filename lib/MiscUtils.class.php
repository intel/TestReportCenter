<?php

/**
 * Contains a set of miscellaneous functions and utilities.
 */
class MiscUtils
{
	/**
	 * Slugify the given string.
	 *
	 * @param string $text The string to slugify.
	 *
	 * @return string The slugified string.
	 */
	static public function slugify($text, $separator = "-")
	{
		$text = preg_replace("/\W+/", $separator, $text);
		$text = strtolower(trim($text, $separator));

		return $text;
	}

	/**
	 * Camelize the given string.
	 *
	 * @param string $value The string to camelize.
	 * @param bool $lcfirst Lower case the first character of the string.
	 *
	 * @return string The camelized string.
	 */
	static public function camelize($value, $lcfirst = true)
	{
		$value = preg_replace("/([_-\s]?([a-z0-9]+))/e", "ucwords('\\2')", $value);
		return ($lcfirst ? strtolower($value[0]) : strtoupper($value[0])).substr($value, 1);
	}

	/**
	 * Generates a randomized token for user profiles.
	 * This token is used in REST and external APIs to identify user and query data.
	 *
	 * @return string The token.
	 */
	static public function generateToken()
	{
		// Generate unique token based on random time
		list($usec, $sec) = explode(" ", microtime());
		$rand_num = substr(sha1((int)($usec*1000000 * ($sec/1000000))), 0, 20);

		return $rand_num;
	}

	/**
	 * Convert a datetime to timestamp.
	 *
	 * @param datetime $datetime The datetime to convert.
	 *
	 * @return int The timestamp.
	 */
    static public function datetime2timestamp($datetime)
    {
        list($date, $time) = explode(' ', $datetime);
        list($year, $month, $day) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);

        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $timestamp;
    }

	/**
	 * Return elapsed time between two datetimes in human readable format.
	 *
	 * @param datetime $start Initial datetime.
	 * @param datetime $end Final datetime (default is current time).
	 *
	 * @return string The formatted elapsed time.
	 */
	static public function timeBetween($start, $end=null)
	{
		// Convert datetimes to timestamps
		$start = MiscUtils::datetime2timestamp($start);
	  	$end = is_null($end) ? time() : MiscUtils::datetime2timestamp($end);

		$SECOND = 1;
		$MINUTE = 60 * $SECOND;
		$HOUR = 60 * $MINUTE;
		$DAY = 24 * $HOUR;
		$WEEK = 7 * $DAY;
		$MONTH = 30 * $DAY;
		$YEAR = 365 * $DAY;

		$increments = array(
			array($SECOND, 'second'),
			array($MINUTE, 'minute'),
			array($HOUR, 'hour'),
			array($DAY, 'day'),
			array($WEEK, 'week'),
			array($MONTH, 'month'),
			array($YEAR, 'year')
		);

		$diff = $end - $start;
		$plural = '';
		$units = ceil($diff/$increments[count($increments)-1][0]);
		$unit = $increments[count($increments)-1][1];

		for($i = 1; $i < count($increments); $i++)
		{
			if($increments[$i-1][0] <= $diff && $diff < $increments[$i][0])
			{
				$units = ceil($diff/$increments[$i-1][0]);
				$unit = $increments[$i-1][1];
				break;
			}
		}

		if($units > 1) $plural = 's';
		return sprintf("%d %s%s ago", $units, $unit, $plural);
	}

	/**
	 * Format a text to transform TRC' wiki markups into HTML tags.
	 *
	 * @param string $text The text to format.
	 *
	 * @return The formatted text.
	 */
	static public function formatWikimarkups($text)
	{
		// Convert ASCII code of simple quotes
		$text = preg_replace('/&#039;/', '\'', $text);
		// Reformat tags in case of some corner cases.
		$text = preg_replace('/\]{2},\[{2}/', ']], [[', $text);
		// Replace sub headers
		$text = preg_replace('/={3}(.*)={3}/', '<h5>${1}</h5>', $text);
		// Replace headers
		$text = preg_replace('/={2}(.*)={2}/', '<h4>${1}</h4>', $text);
		// Replace bold
		$text = preg_replace('/\'{3}(.*)\'{3}/', '<strong>${1}</strong>', $text);
		// Replace italic
		$text = preg_replace('/\'{2}(.*)\'{2}/', '<em>${1}</em>', $text);
		// Replace links to bugs complementary tool
		$text = preg_replace('/\[{2}([^\s]+)\]{2}/', '<a href="'.sfConfig::get("app_bug_complementary_tool", "").'${1}" title="${1}">${1}</a>', $text);
		// Replace URLs
		$text = preg_replace('/\[{2}(([^\s]+) ([^\s]+))\]{2}/', '<a href="${2}" title="${3}">${3}</a>', $text);
		// Replace bullets list
		$text = preg_replace('/\*(.*)[\n\r]?/', '<li>${1}</li>', $text);

		return $text;
	}

	/**
	 * Get a column of a bi-dimensional array.
	 *
	 * @param array The array to parse.
	 * @param column The column to retrieve.
	 * @param unique TRUE to retrieve only one occurrence of each value, FALSE otherwise.
	 *
	 * @return A simple array just containing the old column.
	 */
	static function arrayColumn($array, $column, $unique=false)
	{
		$ret = array();

		foreach($array as $key => $value)
		{
			if($array[$key][$column]!="")
			if($unique)
			{
				if(!in_array($array[$key][$column], $ret))
					array_push($ret, $array[$key][$column]);
			}
			else
			{
				array_push($ret, $array[$key][$column]);
			}
		}

		return $ret;
	}

	/**
	 * Delete a directory and its files.
	 *
	 * @param dirPath The path to the directory to delete.
	 */
	static function deleteDir($dirPath)
	{
		if(is_dir($dirPath))
		{
			if(substr($dirPath, strlen($dirPath) - 1, 1) != '/')
			{
				$dirPath .= '/';
			}

			$files = glob($dirPath . '*', GLOB_MARK);
			foreach ($files as $file)
			{
				if(is_dir($file))
				{
					deleteDir($file);
				}
				else
				{
					unlink($file);
				}
			}

			return rmdir($dirPath);
		}

		return false;
	}

	/**
	 * Get URI from a given URL.
	 *
	 * @param url The url to parse.
	 */
	static function getUri($prefix, $url)
	{
		if (sfConfig::get('sf_environment') == 'prod')
		{
			$url = substr($url, strpos($url, $prefix) + strlen($prefix));
		}
		else
		{
			$script = 'frontend_dev.php';
			$url = substr($url, strpos($url, $script) + strlen($script));
		}
		return $url;
	}

	/**
	 * Compute the median from a list of numbers.
	 *
	 * @return integer The median.
	 */
	static function median()
	{
		$args = func_get_args();

		switch(func_num_args())
		{
			case 0:
				trigger_error('median() requires at least one parameter', E_USER_WARNING);
				return false;
				break;

			case 1:
				$args = array_pop($args);
				// fallthrough

			default:
				if(!is_array($args)) {
					trigger_error('median() requires a list of numbers to operate on or an array of numbers', E_USER_NOTICE);
					return false;
				}

				sort($args);

				$n = count($args);
				$h = intval($n / 2);

				if($n % 2 == 0) {
					$median = ($args[$h] + $args[$h-1]) / 2;
				} else {
					$median = $args[$h];
				}

				break;
		}

		return $median;
	}
}
