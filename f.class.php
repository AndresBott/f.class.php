<?php

/*
 * f.class -  php helper class for Andres Bott
 *
 * Copyright (c) 2013 Andrés Bott
 * Author: Andres Bott -> www.andresbott.com
 *
 * Version: 0.2.1
 * Date :2016.01.10
 *
 * licensed under the LGPL license:
 *   http://www.gnu.org/licenses/lgpl.html
 *   you can use this software everywere, even in comertical and closed proyects, but this software remains open, and free even if you make changes to it
 *
 */

define("FCLASSVERSION","0.2.1");

class f {
	static public function getVersion(){
		return FCLASSVERSION;
	}


//##########################################################################################################################################################
// String Functions
//##########################################################################################################################################################
	static function stringDeleteFromLeft($string,$cantidad){
		return substr($string, $cantidad);
	}

	static function stringDeleteFromRight($string,$cantidad){
		return substr($string,0,-1*$cantidad);
	}

	static function stringGetFromLeft($cadena, $n){
		return substr($cadena,0,$n);
	}

	static function stringGetFromRight($cadena, $n){
		return substr($cadena, strlen($cadena) - $n, $n);
	}

	static function isAsciiSafe( $string = '' ) {
		if(preg_match('/[^\x20-\x7f]/', $string)){
			return true;
		}else{
			return false;
		}
	}




//##########################################################################################################################################################
// Array Functions
//##########################################################################################################################################################
	/**
	 * removes empty values in array
	 *
	 * @param array $array
	 * @return array
	 */

	static public function removeEmptyArray($array){
		$return = array();
		$i = 0;

		foreach ($array as $key => $value) {
			if (!empty($value)) {
				$return[$key] = $value;
				$i++;
			}
		}
		return $return;
	}

//##########################################################################################################################################################
// files functions
//##########################################################################################################################################################
	/**
	 * clean backslashes from path
	 */
	static function sanitizePath($path){
		$path = trim($path);
		/* replace '//' or '/./' or '/foo/../' with '/' */
		$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		for($n=1; $n>0; $path=preg_replace($re, '/', $path, -1, $n)) {}
		return $path;


	}


	/**
	 * write a ini file from data from an array
	 */
	function writeIniFile($path,$assoc_arr=false,  $has_sections=FALSE) {
		$content = "";
		if ($has_sections) {
			foreach ($assoc_arr as $key=>$elem) {
				$content .= "[".$key."]\n";
				foreach ($elem as $key2=>$elem2) {
					if(is_array($elem2))
					{
						for($i=0;$i<count($elem2);$i++)
						{
							$content .= $key2."[] = \"".$elem2[$i]."\"\n";
						}
					}
					else if($elem2=="") $content .= $key2." = \n";
					else $content .= $key2." = \"".$elem2."\"\n";
				}
			}
		}else {
			foreach ($assoc_arr as $key=>$elem) {
				if(is_array($elem))
				{
					for($i=0;$i<count($elem);$i++)
					{
						$content .= $key."[] = \"".$elem[$i]."\"\n";
					}
				}
				else if($elem=="") $content .= $key." = \n";
				else $content .= $key." = \"".$elem."\"\n";
			}
		}

		if (!$handle = fopen(self::sanitizePath( $path), 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		return $success;
	}

	/**
	 * check if is a image type
	 */
	static function is_image($file){
		$fileExt = self::file_extension($file);

		$imgExtensions= ["jpg","jpeg","png"];

		if(in_array(strtolower($fileExt),$imgExtensions)){
			return true;
		}else{
			return false;
		}

	}




	/**
	 * return filename without extension
	 */
	static function file_basename($filename){
		if(is_string($filename)){
			return preg_replace('/\.[^.]*$/', '', $filename);
		}elseif(is_array($filename)){
			$return = array();
			foreach($filename as $file){
				$return[] = f::file_basename($file);
			}
			return $return;
		}
	}
	/**
	 * return file extension
	 */
	static function file_extension($filename){
		//returns the extension of the file
		return end(explode(".", $filename));
	}




//##########################################################################################################################################################
// url functions
//##########################################################################################################################################################
	/**
	 * clean backslashes from url
	 */
	static function sanitezeUrl($path){
		$path = trim($path);

		$protocols = ["https","http","ftp","ftps"];

		foreach ($protocols as $protocol){
			$strinLen = strlen($protocol)+3;

			if(self::stringGetFromLeft($path,$strinLen) ==  $protocol."://"){
				return $protocol."://".f::sanitizePath(self::stringDeleteFromLeft($path,$strinLen));
			}
		}

		return f::sanitizePath($path);

	}


	/**
	 * get the ID of a video based on its url, works with vimeo and youtube
	 */
	static function getVideoID($url=false){
		if($url == false){
			return fale;
		}
		$host = func::getvideoHost($url);
		switch ($host) {
			case 'youtube':
				$id=self::getYoutubeID($url);
				break;
			case 'vimeo':
				$id=self::getVimeoID($url);
				break;
			case 'selfhosted':
				$id=$url;
				break;
			default:
				return false;
				break;
		}
		if($id != false){
			return array("host"=>$host,"id"=>$id);
		}else{
			return false;
		}
	}

	/**
	 * check if video url is selfhosted, youtube or vimeo
	 */
	static function getvideoHost($url=false){
		if($url == false){
			return fale;
		}

		$parse = parse_url($url);
		$videoData["host"] = $parse["host"];

		if($videoData["host"] == $_SERVER["HTTP_HOST"]){
			return "selfhosted";
		}

		$host = explode(".",$videoData["host"]);

		if(in_array("youtube", $host) ) {
			return "youtube";
		}elseif(in_array("vimeo", $host) ){
			return "vimeo";
		}else{
			return false;
		}
	}

	/**
	 * get the ID of a youtube video
	 */
	static function getYoutubeID($url=false){
		if($url == false){
			return fale;
		}

		preg_match('~
		        # Match non-linked youtube URL in the wild. (Rev:20111012)
		        https?://         # Required scheme. Either http or https.
		        (?:[0-9A-Z-]+\.)? # Optional subdomain.
		        (?:               # Group host alternatives.
		          youtu\.be/      # Either youtu.be,
		        | youtube\.com    # or youtube.com followed by
		          \S*             # Allow anything up to VIDEO_ID,
		          [^\w\-\s]       # but char before ID is non-ID char.
		        )                 # End host alternatives.
		        ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
		        (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
		        (?!               # Assert URL is not pre-linked.
		          [?=&+%\w]*      # Allow URL (query) remainder.
		          (?:             # Group pre-linked alternatives.
		            [\'"][^<>]*>  # Either inside a start tag,
		          | </a>          # or inside <a> element text contents.
		          )               # End recognized pre-linked alts.
		        )                 # End negative lookahead assertion.
		        [?=&+%\w-]*        # Consume any URL (query) remainder.
		        ~ix', $url, $matches);

		return $matches[1];
	}

	/**
	 * get the ID of a vimeo video
	 */
	static function getVimeoID($url){
		sscanf(parse_url($url, PHP_URL_PATH), '/%d', $video_id);

		return $video_id;
	}





//##########################################################################################################################################################
// numeric functions
//##########################################################################################################################################################
	/**
	 * check if a number is between other two
	 */
	static function numberBetween($value,$min,$max){
		if(is_numeric($value) && is_numeric($min) && is_numeric($max)){
			return ($min<$value && $value<$max);
		}else{
			return FALSE;
		}
	}

	/**
	 * Will split a version string and increase the last number by one
	 */
	static function increaseMinorVersion($version=false){

		if(is_string($version)){
			$ver = explode(".",$version);
			$ver = array_reverse( $ver );
			$ver[0] = $ver[0]+1;
			$ver = array_reverse( $ver );
			$ver = implode(".",$ver);
			return $ver;

		}

	}

//##########################################################################################################################################################
// debbuging Functions
//##########################################################################################################################################################

	/**
	 * Prints content formated
	 */
	static public function p($array="this will be an empty string hopefully no one uses this ever in a program →↓←ŧ¶€ł",$title=false,$return = false){
		if($array === NULL){
			$array = "operant NULL";
		}else if($array === 0){
			$array = "integer: 0";
		}elseif($array === 1){
			$array = "integer: 1";
		}elseif($array === true){
			$array = "Bolean: True";
		}else if($array === false){
			$array = "Bolean: false";
		}else if($array === "this will be an empty string hopefully no one uses this ever in a program →↓←ŧ¶€ł"){
			$array = "Called the control function p() without a parameter<br>";
		}
		if(php_sapi_name() == "cli"){
			$print = "========| f::p() |===============\n";
			$print .= print_r($array,true);
			$print .= "\n=================================\n";
		}else{

			$id = str_replace(".","",microtime(true));
			$print = '<pre id= "'.$id.'"style="margin:10px; padding:10px; background: rgba(200, 50, 0, 0.2); border: 1px solid rgba(200, 50, 0, 0.5); overflow:auto;">';
			if($title != false){
				$print .= "<b>".$title."</b><br>";
			}
			$print .= print_r($array,true);
			$print .= "</pre>";
		}

		if($return == true){
			return $print;
		}else{
			echo $print;
		}
	}



	/**
	 * print debugging statistics, ram; time etc
	 *
	 */
	static public function getStats(){

		$return = array();
		// memory Usage
		$return["memory"]= func::humanReadBytes( memory_get_usage (TRUE ));
		$return["peakMemory"] = func::humanReadBytes(  memory_get_peak_usage (TRUE ));
		$return["maxMemory"] = func::humanReadBytes(  ini_get('memory_limit'));


		// execution Time
		$return["execution"]= f::microtimeDif();

		return $return;
	}




//##########################################################################################################################################################
// Time Functions
//##########################################################################################################################################################


	/**
	 * Return a timestamp with milliseconds
	 */
	static public function getTimestamp($dateFormat = "Y-m-d H:i:s" )
	{
		return self::formatMicrotime(microtime(),$dateFormat);
	}


	/**
	 * will return a formated mictrotime
	 */
	static public function formatMicrotime( $microTime = FALSE , $dateFormat = "Y-m-d H:i:s"){

		if($microTime != FALSE){
			$microTime = microtime();
		}


		$microtime = floatval(substr((string)$microTime, 1, 8));
		$rounded = round($microtime, 3);
		return date($dateFormat) . substr((string)$rounded, 1, strlen($rounded));
	}


	/**
	 * calculate difference wetween two microtimes
	 * or used to calculate time to run script.
	 * call function on the begining and on the end;
	 */
	static public function microtimeDif($start = false,$end = FALSE){

		if($start == false){
			if(!defined("FCLASS_EXEC_INIT_TIME")){
				$microtime = microtime();
				define("FCLASS_EXEC_INIT_TIME",$microtime);
				return $microtime;
			}elseif(defined("FCLASS_EXEC_INIT_TIME")){
				$start = FCLASS_EXEC_INIT_TIME;
			}
		}

		list ($msec, $sec) = explode(' ', $start);
		$startMicrotime = (float)$msec + (float)$sec;

		if($end == false){
			$end = microtime();
		}


		list ($msec, $sec) = explode(' ', $end);
		$endMicrotime = (float)$msec + (float)$sec;

		return  round($endMicrotime - $startMicrotime, 3);
	}



//##########################################################################################################################################################
// Size Functions
//##########################################################################################################################################################

	/**
	 * Format bites to human readable
	 *
	 */
	static function humanReadBytes($bytes) {

		//CHECK TO MAKE SURE A NUMBER WAS SENT
		if(!empty($bytes)) {

			//SET TEXT TITLES TO SHOW AT EACH LEVEL
			$s = array('bytes', 'kb', 'MB', 'GB', 'TB', 'PB');
			$e = floor(log($bytes)/log(1024));

			//CREATE COMPLETED OUTPUT
			$output = sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));

			//SEND OUTPUT TO BROWSER
			return $output;

		}
	}


	/**
	 * @param $p_sFormatted
	 * @return bool|string
	 * http://stackoverflow.com/questions/11807115/php-convert-kb-mb-gb-tb-etc-to-bytes
	 * convert human sizes to bytes
	 */
	static  function toByteSize($p_sFormatted) {
		$aUnits = array('B'=>0, 'KB'=>1, 'MB'=>2, 'GB'=>3, 'TB'=>4, 'PB'=>5, 'EB'=>6, 'ZB'=>7, 'YB'=>8);
		$sUnit = strtoupper(trim(substr($p_sFormatted, -2)));
		if (intval($sUnit) !== 0) {
			$sUnit = 'B';
		}
		if (!in_array($sUnit, array_keys($aUnits))) {
			return false;
		}
		$iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));
		if (!intval($iUnits) == $iUnits) {
			return false;
		}
		return $iUnits * pow(1024, $aUnits[$sUnit]);
	}




//##########################################################################################################################################################
// color Functions
//##########################################################################################################################################################
	static function random_color(){
		mt_srand((double)microtime()*1000000);
		$c = '';
		while(strlen($c)<6){
			$c .= sprintf("%02X", mt_rand(0, 255));
		}
		return $c;
	}





}// end of class



?>