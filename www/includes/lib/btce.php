<?php
/*
 BTC-E ticker feed
 author: lavajumper <joe@connexsmart.com>
 date  : 2013-05-01
 
*/

class btce_ticker
{
	/**
	 * @var username to use for authentication against the API
	 */
	protected $user;

	/**
	 * @var password to use for authentication against the API
	 */
	protected $pass;

	/**
	 * Current btc-e fee in percent (unused)
	 */
	const BTCE_FEE = 0.0065;
	/**
	 * btc-e generic endpoint
	 */
	const ENDPOINT = 'https://btc-e.com/api/2/ltc_usd/'; 
	/**
	 * btc-e endpoint for the TickerAPI
	 */
	const ENDPOINT_USD = 'ticker';

	/**
	 * A timeout to control how long to wait for the API to respond in seconds
	 */
	const TIMEOUT = 3;

	/**
	 * User agent string which is sent which all requests
	 */
	const USERAGENT = 'UKD1 BTCE Client';

	/**
	 * Sell Order type
	 */
	const SELL_ORDER = 1;

	/**
	 * Buy order type
	 */
	const BUY_ORDER = 2;

	/**
	 * Order status ACTIVE
	 */
	const STATUS_ACTIVE = 1;

	/**
	 * Order status INACTIVE (insufficent funds)
	 */
	const STATUS_INSUFFICENT_FUNDS = 2;

	/**
	 * Do a HTTP POST to the specified URI
	 *
	 * @param string $uri uri to post to (appended to the endpoint)
	 * @param array $data array of post fields to pass
	 * @return bool|array
	 */
	protected function _post ($uri, $data = array())
	{
		$data['name'] = $this->user;
		$data['pass'] = $this->pass;

		$r = $this->_http('POST', $uri, $data);

		if ($r['http_code'] === 200)
		{
			return json_decode($r['result'], true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Perform an HTTP GET
	 *
	 * @param  $uri URI to get, appended to the endpoint url
	 * @param array $data get parameters
	 * @return bool|array
	 */
	protected function _get ($uri, $data = array())
	{
		if(count($data)==0)
			$data=null;
		$r = $this->_http('GET', $uri, $data);
		//var_dump($r);
		if ($r['http_code'] === 200)
		{
			return json_decode($r['result'], true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * perform a HTTP request
	 *
	 * @param string $method HTTP method to use, currently supports GET|POST
	 * @param string $uri URI to append to the endppint
	 * @param array $data single dimensional key / value pairs of data to pass
	 * @return array
	 */
	protected function _http ($method, $uri, $data)
	{
		if(!isset($data)){ $data=array(); }
		$url = self::ENDPOINT . $uri;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);


		switch ($method)
		{
			case 'POST':
				$post_fields = array();
				foreach ($data as $k=>$v) {
					array_push($post_fields, "$k=$v");
				}
				$post_fields = implode('&', $post_fields);

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
				break;

			case 'GET':
			default:
				$get_fields = array();
				foreach ($data as $k=>$v) {
					array_push($get_fields, "$k=$v");
				}
				$url .= '?' . implode('&', $get_fields);

				curl_setopt($ch, CURLOPT_URL, $url);
		}

		$result = curl_exec ($ch);

		$tmp = curl_getinfo($ch);
		$tmp['result'] = $result;
		curl_close ($ch);

		return $tmp;
	}
	
	public function getCurrentUSDPrice(){
		$data=$this->_get(self::ENDPOINT_USD);
		//var_dump($data);
		echo($data['ticker']['last']."\n");
		return($data['ticker']['last']);

	}
	
	public function getCurrentBTCPrice(){
		$data=$this->_get(ENDPOINT_BTC,"");
		return($data['last']);
	}

}



?>