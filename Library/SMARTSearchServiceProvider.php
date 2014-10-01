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
use Silex\Application;
use Silex\ServiceProviderInterface;
use smartsearch_client\Entity\Latest;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class SMARTSearchServiceProvider implements ServiceProviderInterface {
	/**
	 *
	 * @param Application app
	 */
    public function boot(Application $app) {
	}

	/**
	 *
	 * @param Application app
	 */
    public function register(Application $app) {
        if ( !isset($app['smart.url']) or empty($app['smart.url']) )
    		$app['smart.url'] = 'http://demos.terrier.org/v1/';

        $this->app = $app;
        
        $app['smart'] = $app->share(function ($name) use ($app) {
            return new SMARTSearch(
				$app['logger'], 
				$app['smart.url'],
				$app['smart.query_news'],
				$app['smart.query_weather']
                );
        });        
    }
}
