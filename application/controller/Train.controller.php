<?php

use \Glial\Synapse\Controller;
use \Glial\Cli\SetTimeLimit;
use \Glial\Cli\Color;
use \Glial\Security\Crypt\Crypt;
use \Glial\I18n\I18n;
use \Glial\Extract\Grabber;

class Train extends Controller
{

	public function index()
	{
	/*
		$ch = curl_init();

        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36';
        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,**;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: utf-8";
        $header[] = "Accept-Language: en"; // langue fr.
        $header[] = "Pragma: "; // Simule un navigateur
        //curl_setopt($ch, CURLOPT_PROXY, 'proxy.int.world.socgen:8080');
        //curl_setopt($ch, CURLOPT_PROXYUSERPWD, "aurelien.lequoy:xxxxx");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_URL, $url); 
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	        //curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	        $content = curl_exec($ch);
	        curl_close($ch);

*/

$url = "http://booking.uz.gov.ua/en/purchase/search/";
$fields = array(
	'station_id_from' => urlencode("2210700"),
	'station_id_till' => urlencode("2208001"),
	'station_from' => urlencode("Dnipropetrovsk Holovny"),
	'station_till' => urlencode("Odesa"),
	'date_dep' => urlencode("02.26.2016"),
	'time_dep' => urlencode("00:00"),
	'time_dep_till' => urlencode(""),
	'another_ec' => urlencode("0"),
	'search' => urlencode("")
);

//url-ify the data for the POST
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
$fields_string = rtrim($fields_string, '&');


print_r($fields_string);


echo "\n";

//open connection
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

	print_r($result);

	}

}
