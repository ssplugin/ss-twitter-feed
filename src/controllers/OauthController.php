<?php

namespace ssplugin\sstwitterfeed\controllers;

use ssplugin\sstwitterfeed\SsTwitterFeed;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use craft\helpers\UrlHelper;
use craft\helpers\App;

class OauthController extends Controller
{

	public function actionCallback( ):Response
	{
        if( UrlHelper::cpUrl() != '' ) {
            $cpUrl = UrlHelper::cpUrl();
        } else {
            $cpUrl = trim( Craft::getAlias('@web') );
        }
	    $request_token = [];
        $request_token['oauth_token'] = $_SESSION['oauth_token'];
        $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

        $oauthToken = Craft::$app->getRequest()->getParam('oauth_token');
        $oauthVerifier = Craft::$app->getRequest()->getParam('oauth_verifier');
        if(!empty($oauthToken) && !empty($oauthVerifier)){
            $baseURI = 'https://api.twitter.com/oauth/access_token';
            $nonce = time();
            $timestamp = time();
            $oauth = array('oauth_verifier' => $oauthVerifier,
                        'oauth_token' => $request_token['oauth_token'],
                        'oauth_token_secret' => $request_token['oauth_token_secret'],
                        'oauth_consumer_key' => SsTwitterFeed::$CONSUMER_KEY,
                        'oauth_nonce' => $nonce,
                        'oauth_signature_method' => 'HMAC-SHA1',
                        'oauth_timestamp' => $timestamp,
                        'oauth_version' => '1.0');


            $consumerSecret = SsTwitterFeed::$CONSUMER_SECRET;
            $baseString = SsTwitterFeed::$plugin->ssTwitterFeedService->buildBaseString( $baseURI, $oauth, "POST" );

            $compositeKey=SsTwitterFeed::$plugin->ssTwitterFeedService->getCompositeKey($consumerSecret, null);
            $oauth_signature = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true));
            $oauth['oauth_signature'] = $oauth_signature;

            $response = SsTwitterFeed::$plugin->ssTwitterFeedService->sendRequest($oauth, $baseURI);

            $responseArray = array();
            $parts = explode('&', $response);
            
            foreach($parts as $p){
                $p = explode('=', $p);
                if(!empty($p[0]) && !empty($p[1])){
                    $responseArray[$p[0]] = $p[1];
                }else{
                    $errMsg = $p[0];
                    Craft::warning($errMsg, 'ss-twitter-feed');
                }
            }
            
            if(!empty($responseArray['oauth_token'])){
                $plugin = SsTwitterFeed::getInstance();
                $settings = $plugin->getSettings();
           
                $nsettings = array(
                        'access_token'  => $responseArray['oauth_token'],
                        'access_token_secret' => $responseArray['oauth_token_secret'],                       
                    );
                Craft::$app->getPlugins()->savePluginSettings($plugin, $nsettings);
                Craft::$app->session->setNotice('Authorized successfully.');
                return $this->redirect($cpUrl.'/settings/plugins/ss-twitter-feed');
            }else{
                if(!empty($errMsg)){
                    Craft::$app->session->setError($errMsg);
                }else{
                    Craft::$app->session->setError('Authorized not successfully.');
                }
                return $this->redirect($cpUrl.'/settings/plugins/ss-twitter-feed');
            }
        }else{
            Craft::$app->session->setError('Oauth token and oauthVerifier not found.');
            Craft::warning('Oauth token and oauthVerifier not found.', 'ss-twitter-feed');
            return $this->redirect($cpUrl.'/settings/plugins/ss-twitter-feed');
        }
	}

}
?>



