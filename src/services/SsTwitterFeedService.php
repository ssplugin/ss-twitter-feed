<?php
/**
 * SS Twitter Feed plugin for Craft CMS 3.x
 *
 * Show Recent Tweets.
 *
 * @link      http://www.systemseeders.com/
 * @copyright Copyright (c) 2019 SystemSeeders
 */

namespace ssplugin\sstwitterfeed\services;

use ssplugin\sstwitterfeed\SsTwitterFeed;
use craft\helpers\Template as TemplateHelper;
use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 * SsTwitterFeedService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    SystemSeeders
 * @package   SsTwitterFeed
 * @since     1.0.0
 */
class SsTwitterFeedService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     SsTwitterFeed::$plugin->ssTwitterFeedService->exampleService()
     *
     * @return mixed
     */
    public function getUrl()
    {
        $baseURI = 'https://api.twitter.com/oauth/request_token';
        $nonce = time();
        $timestamp = time();
        $site_url  = trim( Craft::getAlias('@web') );
        $oauth = array('oauth_callback' => SsTwitterFeed::$OAUTH_PROCESSOR_URL.$site_url.SsTwitterFeed::$OAUTH_RET_URL,
                      'oauth_consumer_key' => SsTwitterFeed::$CONSUMER_KEY,
                      'oauth_nonce' => $nonce,
                      'oauth_signature_method' => 'HMAC-SHA1',
                      'oauth_timestamp' => $timestamp,
                      'oauth_version' => '1.0');
        $consumerSecret = SsTwitterFeed::$CONSUMER_SECRET; 
        $baseString = $this->buildBaseString($baseURI, $oauth, 'POST');
        $compositeKey = $this->getCompositeKey($consumerSecret, null);
        $oauth_signature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true));

        $oauth['oauth_signature'] = $oauth_signature;
        try {
            $response = $this->sendRequest($oauth, $baseURI);
            $responseArray = array();
            $parts = explode('&', $response);
            
            foreach($parts as $p){

                $p = explode('=', $p);
                if(!empty($p[0]) && !empty($p[1])){
                    $responseArray[$p[0]] = $p[1]; 
                }   
            }
            if(!empty($responseArray)){
                $oauth_token = $responseArray['oauth_token'];
                $oauth_token_secret = $responseArray['oauth_token_secret'];
                
                $_SESSION['oauth_token']        = $oauth_token;
                $_SESSION['oauth_token_secret'] = $oauth_token_secret;
                $url = 'http://api.twitter.com/oauth/authorize?oauth_token='.$oauth_token;
                return $url;
            }else{
                return 'Could not authenticate.'; 
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function buildBaseString($baseURI, $params, $method){
        $r = array();
        ksort($params);
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value);
        }
        return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }


    public function getCompositeKey($consumerSecret, $requestToken){
        return rawurlencode($consumerSecret) . '&' . rawurlencode($requestToken);
    }


    public function buildAuthorizationHeader($oauth){
        $r = 'Authorization: OAuth ';

        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"".rawurlencode($value)."\"";

        $r .= implode(', ', $values);
        return $r;
    }

    public function sendRequest($oauth, $baseURI){
        try {
            $header = array( $this->buildAuthorizationHeader($oauth), 'Expect:'); 
            $options = array(CURLOPT_HTTPHEADER => $header,
                               CURLOPT_HEADER => false, 
                               CURLOPT_URL => $baseURI, 
                               CURLOPT_POST => true, 
                               CURLOPT_RETURNTRANSFER => true,
                               CURLOPT_SSL_VERIFYPEER => false); 

            $ch = curl_init(); 
            curl_setopt_array($ch, $options); 
            $response = curl_exec($ch); 
            curl_close($ch); 
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
