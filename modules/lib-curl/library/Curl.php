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
			'handler' => null,
			'agent'   => null
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
		
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $opts['method']);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		if($opts['agent'])
			curl_setopt($ch, CURLOPT_USERAGENT, $opts['agent']);

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

		if(\Phun::$dispatcher->config->libCurl['log']){
			$fname = gmdate('Y-m-d-H-i-s-') . uniqid();
            $dir = BASEPATH . '/etc/log/lib-curl/' . gmdate('Y/m/d/H');
            if(!is_dir($dir))
                mkdir($dir, 0777, true);
			$f = fopen($dir . '/' . $fname, 'w');

			fwrite($f, json_encode($opts, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL);
			fwrite($f, $res);

			fclose($f);
		}

		if(!$opts['handler'])
			return $res;

		$ret = $res;
		switch($opts['handler']){
			case 'json':
				$ret = json_decode($res);
				if(json_last_error()){
					$fn = BASEPATH . '/etc/log/lib-curl/error-' . gmdate('YmdHis');
					$f = fopen($fn, 'w');
					fwrite($f, json_encode($opts, JSON_PRETTY_PRINT |  JSON_UNESCAPED_SLASHES) . PHP_EOL . PHP_EOL);
					fwrite($f, $res);
					fclose($f);
				}
				break;
		}

		return $ret;
	}
}