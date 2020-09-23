<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight;

use Pupilsight\Contracts\Database\Connection;

/**
 * CSV Generator
 *
 * @version	19th April 2016
 * @since	14th April 2016
 * @author	Craig Rayner
 */
class csv
{
	
	/**
	 * string
	 */
	static private $title;
	
	/**
	 * Generate
	 *
	 * direct output of csv to browser.
	 *
	 * @version	19th April 2016
	 * @since	14th April 2016
	 * @param	Object	Connection
	 * @param	string	Title
	 * @param	string	Header (Must be formated in csv)
	 * @return	void
	 */
	static public function generate( Connection $pdo, $title, $header = NULL)
	{
		self::$title = self::testTitle($title);
		$start = true;
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: text/csv");
		header('Content-Disposition: attachment; filename="'.self::$title.'";' );
		while ($row = $pdo->getResult()->fetch()) 
		{
			if ($start)
			{
				$start = false;
				if ($header === NULL)
				{
					$header = '';
					foreach ($row as $colName=>$value)
						$header .= self::encodeCSVField($colName).',';
					$header = rtrim($header, ",") . "\n";
					echo $header;
				}
				else
					echo $header;
			}
			$line = '';
			foreach($row as $value)
				$line .= self::encodeCSVField($value).',';
			$line = rtrim($line, ",") . "\n";
			echo $line;
		}
	}
	
	/**
	 * Test Title
	 *
	 * @version	14th April 2016
	 * @since	14th April 2016
	 * @param	string	Title
	 * @return	string	Title
	 */
	static private function testTitle($title)
	{
		$x = explode('.',$title);
		if (count($x) >= 2)
			array_pop($x);
		$x[] = 'csv';
		return implode('.', $x);
	}

	
	/**
	 * encode CSV Field
	 *
	 * @version	14th April 2016
	 * @since	14th April 2016
	 * @param	string	CSV Data
	 * @return	string	CSV Data
	 */
	static private function encodeCSVField($string) 
	{
		if(strpos($string, ',') !== false || strpos($string, '"') !== false || strpos($string, "\n") !== false) 
			$string = '"' . str_replace('"', '""', $string) . '"';
		return $string;
	}
}