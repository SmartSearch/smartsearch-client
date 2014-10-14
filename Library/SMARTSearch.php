<?php
/*
* SMART FP7 - Search engine for MultimediA enviRonment generated contenT
* Webpage: http://smartfp7.eu
*
* This Source Code Form is subject to the terms of the Mozilla Public
* License, v. 2.0. If a copy of the MPL was not distributed with this
* file, You can obtain one at http://mozilla.org/MPL/2.0/.
*
* The Original Code is Copyright (c) 2012-2014 PRISA Digital
* All Rights Reserved
*/
namespace smartsearch_client\Library;

use DateTime;
use Exception;
use Monolog\Logger;
use smartsearch_client\Entity\Latest;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class SMARTSearch {
    
	protected $logger;
    protected $url;
    protected $startDate;
    protected $news_query;
    protected $weather_query;
    protected $events_culture_query;
    protected $events_traffic_query;
    protected $events_sport_query;
    protected $events_commerce_query;
    protected $wheaterRequest;
    
    /**
     *   Here is where we define the API to use the SMART Search service
     *   Detailed documentation about this API is
     *   in http://opensoftware.smartfp7.eu/projects/smart/wiki/SearchApi
     *   
     * @param logger - Logger. Instance of the application's logger
     * @param url - String. URL for the SMART Search service
     * @param query_news - String. Set a place to search
     * @param query_weather - String. Set a weather condition
     * @param startDate - Date. Set init date to search
     */
    function __construct(Logger $logger, $url, $query_news=null, $query_weather=null, $startDate=null) {
        //$this->weather_query         = 'search.json?q=crowd';
        //$this->events_culture_query  = 'predefined.json?c=cult';

        $this->weather_query         = 'crowd';        
        $this->events_culture_query  = 'cult';
        $this->events_traffic_query  = 'traffic';
        $this->events_sport_query    = 'sport';
        $this->events_commerce_query = 'commerce';

        $this->logger                = $logger;
        $this->url                   = $url;

        $this->startDate = ( !is_null($startDate) ) ? $startDate : date("Y-m-d", strtotime("-15 day"));
        $this->news_query = ( !is_null($query_news) ) ? $query_news : null;
        $this->weather_query = ( !is_null($query_weather) ) ? $query_weather : null;

    }

    /**
     * Gets the Today news
	 * 
     * @return array Latest
     * @throws Exception
     */
    public function noticia() {
		$context = 'SMARTSearch.noticia';
		$this->logger->info($context.' Trying to get the Today news.');

        $api    = $this->getSearchApi();
        $api->setQueryParams(array("q" => $this->news_query, "since" => date('Y-m-d')));
        
        try {
			$result = $api->search();
            if (isset($result['rssfeeds']['results'][0]['title'])) {
                return $result['rssfeeds']['results'][0]['title'];
            } else {
				$this->logger->info($context.' No results.');
			}
        } catch(Exception $e) {
			$this->logger->error($context.' Some weird problem trying to get the news', array('error' => $e->getMessage()));
        }
        return null;
    }    
    
    /**
     * Gets the weather info for Today
	 *
	 * @returns string Weather data for Today
     */
    public function weatherToday() {
		$this->logger->info('Trying to get the Today weather');
		
        if (!is_null($this->wheaterRequest)) {
            $content = $this->wheaterRequest;
            if (!empty($content)) 
            {
                $encontrados=array();
                preg_match('/temperature":"[0-9.]{5}/',$content,$encontrados);
                if (count($encontrados)) {
                    $partes=explode('"', $encontrados[0]);
					$this->logger->info('We have been able to retrieve weather data for Today');
                    return  sprintf("%2.1f",end($partes)) ;
                }
            }
        }
		$this->logger->info('We have NOT been able to retrieve weather data for Today');
        return null;
    }
    
    /**
     *
	 * @throws Exception
     */
    public function weather() {
        $data = array();
        $params =  array('q' => $this->weather_query, "since"=>date('Y-m-d'));
        $api = $this->getSearchApi();
        $api->setQueryParams($params);
        
        try {
            $result = $api->search();
            $this->wheaterRequest = $api->getResponse();
            
            if (!$api->isSuccess()) {
                throw new Exception(' URL: '.$api->getCompleteUrl());
            } 
        } catch(Exception $e) {
           $this->logger->error($e->getMessage());
        }
        return $result;        
    }

    
    /**
     *
     * @throws Exception
     */
    public function sport() {
        $data = array();
        $params =  array('c' => $this->events_sport_query, "since" => $this->startDate);
        $api = $this->getSearchApi();
        $api->setQueryParams($params);

        try {
            $result = $api->search();
            if (!$api->isSuccess()) {
                throw new Exception(' URL: '.$api->getCompleteUrl());
            }
            if (isset($result['results'])) {
                $data = $this->getInformationResult($result);
            }
        } catch(Exception $e) {
           $this->logger->error($e->getMessage());
        }
        return $data;        
    }


    /**
     *
     * @throws Exception
     */
    public function commerce() {
        $data = array();
        $params =  array('c' => $this->events_commerce_query, "since" => $this->startDate);
        $api = $this->getSearchApi();
        $api->setQueryParams($params);

        try {
            $result = $api->search();
            if (!$api->isSuccess()) {
                throw new Exception(' URL: '.$api->getCompleteUrl());
            }
            if (isset($result['results'])) {
                $data = $this->getInformationResult($result);
            }
        } catch(Exception $e) {
           $this->logger->error($e->getMessage());
        }
        return $data;        
    }


    /**
     *
     * @throws Exception
     */
    public function culture() {
        $data = array();
        $params =  array('c' => $this->events_culture_query, "since" => $this->startDate);
        $api = $this->getSearchApi();
        $api->setQueryParams($params);

        try {
            $result = $api->search();
            if (!$api->isSuccess()) {
                throw new Exception(' URL: '.$api->getCompleteUrl());
            }
            if (isset($result['results'])) {
                $data = $this->getInformationResult($result);
            }
        } catch(Exception $e) {
           $this->logger->error($e->getMessage());
        }
        return $data;        
    }


    /**
     *
     * @throws Exception
     */
    public function traffic() {
        $data = array();
        $params =  array('c' => $this->events_traffic_query, "since" => $this->startDate);
        $api = $this->getSearchApi();
        $api->setQueryParams($params);

        try {
            $result = $api->search();
            if (!$api->isSuccess()) {
                throw new Exception(' URL: '.$api->getCompleteUrl());
            }
            if (isset($result['results'])) {
                $data = $this->getInformationResult($result);
            }
        } catch(Exception $e) {
           $this->logger->error($e->getMessage());
        }
        return $data;        
    }

   
    /**
     * 
     * @param string query
	 * @param float lat
	 * @param float lon
     * @param date since
     * @return array Latest
     * @throws Exception
     */
    public function search($query, $lat=null, $lon=null, $since=null) {
        
        $data = array();
        $params =  array("q"=>$query, "since"=>$since);

        if (!is_null($lat) && !is_null($lon)) {
            $params = array_merge($params, array('lat' => $lat, 'lon' => $lon));
        }
        $api = $this->getSearchApi();
        $api->setQueryParams($params);

		try {
			$result = $api->search();
			if (!$api->isSuccess()) {
				throw new Exception(' Fail request from WebService ');
			} 
			if (isset($result['results'])) {
				$data = $this->getInformationResult($result);
			}
		} catch(Exception $e) {
			$this->logger->error($e->getMessage());
		}
		return $data;
    }
    
    /**
     *
	 * @return Api
     */
    private function getSearchApi() {
        static $api;
        if (is_null($api)) {
            $api = new Api($this->logger, $this->url);
        }
        return $api;
    }

        /**
    * Transform result on data values
    * @param array result
    */
    public function getInformationResult($result) {
        foreach ($result['results'] as $elem) {
            $data[] = new Latest(
                    $elem['id'], 
                    new DateTime($elem['startTime']), 
                    $elem['activity'], 
                    $elem['locationId'], 
                    $elem['locationName'], 
                    $elem['locationAddress'], 
                    $elem['observations'], 
                    $elem['latestObservations'], 
                    $elem['media'], 
                    $elem['lat'], 
                    $elem['lon'], 
                    $elem['rank'], 
                    $elem['score'], 
                    $elem['description'], 
                    $elem['URI'], 
                    $elem['title'], 
                    $elem['geohash'], 
                    $elem['lorder'], 
                    $elem['triggers'],
                    $elem['profileImageUrl'],
                    $elem['screenName']
            );
        }

        return $data;
    }

}
