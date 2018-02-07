<?php
/**
 * Curl maker
 * @package lib-curl
 * @version 0.0.1
 * @upgrade true
 */

namespace LibCurl\Library;

class Curl
{
	static function _mergeOpts($opts){
		$_opts = [
			'url'     => null,
			'method'  => 'GET',
			'query'   => [],
			'body'    => [],
			'headers' => [],
			'handler' => null
		];

		return array_replace($_opts, $opts);
	}

	static function fetch($opts){
		$opts = self::_mergeOpts($opts);
		if(!$opts['url'])
			throw new \Exception('Curl: Target url is required', 1);

		if(!filter_var($opts['url'], FILTER_VALIDATE_URL))
			throw new \Exception("Curl: Target url is not valid URL", 1);

		if(!in_array($opts['method'], ['GET', 'POST', 'PUT', 'DELETE']))
			throw new \Exception("Curl: Request method is not supported", 1);

		$url = $opts['url'];

		if($opts['query']){
			$sign = strstr($opts['url'], '?') ? '&' : '?';
			$url.= $sign . http_build_query($opts['query']);
		}
		
		$ch = curl_init($opts['url']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $opts['method']);

		// just don't verify ssl
		if(strstr($opts['url'], 'https://')){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		// body content
		if(in_array($opts['method'], ['POST', 'PUT']) && $opts['body']){
			$ctype = $opts['headers']['Content-Type'] ?? null;
			
			$data = $opts['body'];

			if($ctype == 'application/json'){
				$data = json_encode($data);
				$opts['headers']['Content-Length'] = strlen($data);
			}

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		if($opts['headers']){
			$headers = [];
			foreach($opts['headers'] as $key => $val)
				$headers[] = $key . ': ' . $val;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		$res = curl_exec($ch);
		curl_close($ch);

		if(!$opts['handler'])
			return $res;

		switch($opts['handler']){
			case 'json':
				$res = json_decode($res);
				break;
		}

		return $res;
	}
}