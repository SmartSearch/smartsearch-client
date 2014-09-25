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

use Monolog\Logger;

/**
 *
 */
class Api {

	protected $logger;
	protected $searchMethod;
	protected $eventsMethod;
    protected $url;
    protected $queryParams;  
    protected $response;
    protected $success;
    
   /**
    *
	* @param Logger logger
	* @param String url
    */
    function __construct(Logger $logger, $url) {
		$this->logger      = $logger;
		$this->url         = $url;
		
		$this->searchMethod = 'search.json';
		$this->eventsMethod = 'predefined.json';
        $this->success      = false;
    }
    
   /**
    *
    */
    public function setLogger(Logger $logger) {
        $this->logger = $logger;
    }

	/**
    *
    */
    public function setUrl($url) {
        $this->url = $url;
    }
    
   /**
    *
    */
    public function getUrl() {
        return $this->url;
    }

   /**
    *
    */
    public function getQueryParams() {
        return $this->queryParams;
    }
    
   /**
    *
    */
    public function getResponse() {
        return $this->response;
    }

   /**
    *
    */
    public function setQueryParams($queryParams) {
        $this->queryParams = $queryParams;
    }

   /**
    *
    */
    public function isSuccess() {
        return $this->success;
    }
    
   /**
    * Executes a search against the SMART Search service
	* @throws Exception
    */
    public function search() {
		$context = 'Api.search';
		$this->logger->info($context.
		    ' Trying to execute a search using the SMART Search API');
        
		$this->success = false;
        $query = http_build_query($this->getQueryParams());
        
        $completeURL = $this->url. $this->searchMethod. "?". $query;
        $this->logger->info($context.' The search we are trying to execute is {completeURL}.', array("completeURL" => $completeURL));
        
        // LOCALE API
        //$response = $this->response = @file_get_contents($completeURL);
    	$response = $this->response = file_get_contents("new_local_data.json");

        if ($response !== false) {
            $data = @json_decode($response, true);
            if ($data !== false) {
                $this->logger->info($context.' The search was successfull.');
                $this->success = true;
            }
        }

        // Optimize the search interface
        if ($query == "q="){
            $data['numResults'] = 0;
            $data['results'] = array();
            $this->success = true;
        }
	
    	// Sort by startTime
    	if(!empty($data['results'])){
    	    foreach ($data['results'] as $key => $row) {
    		$startTime[$key]  = $row['startTime'];
    	    }
    	    array_multisort($startTime, SORT_DESC, $data['results']);
    	}

        for ($i=0;$i<$data['numResults'];$i++){
            // Update geo coordenates from Twitter or FourSquare
            $data['results'][$i]['profileImageUrl'] = '';
            $data['results'][$i]['screenName'] = '';

            // Update id with the menu option id
            $option = explode("=", $query);
            $option = explode("&", $option[1]);
            $data['results'][$i]['id'] = $option[0].'_'.$data['results'][$i]['id'];

            //echo "<pre>query: ";print_r($query);echo "</pre><br>";

            if (isset($data['results'][$i]['observations']['topTweets'])){
                // Update profile image and user
                $data['results'][$i]['screenName'] = $data['results'][$i]['observations']['topTweets'][0]['user']['screen_name'];
                $check_image = @get_headers($data['results'][$i]['observations']['topTweets'][0]['user']['profile_image_url']);
                if (!strstr($check_image[0],"404"))
                    $data['results'][$i]['profileImageUrl'] = $data['results'][$i]['observations']['topTweets'][0]['user']['profile_image_url'];                

                // Update geo coordenates
                if (isset($data['results'][$i]['observations']['topTweets'][0]['geo']['type']) && 
                    $data['results'][$i]['observations']['topTweets'][0]['geo']['type'] == "Point"){
                        // Update coordinates from Twitter
                        $data['results'][$i]['lat'] = $data['results'][$i]['observations']['topTweets'][0]['geo']['coordinates'][0];
                        $data['results'][$i]['lon'] = $data['results'][$i]['observations']['topTweets'][0]['geo']['coordinates'][1];                        
                }                
            }
        }
	
    	if ($this->success) {
    		return $data;
    	} else {
    		throw new Exception($context.' The search has failed');
    	}
    }
    
}
