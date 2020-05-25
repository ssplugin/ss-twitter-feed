<?php

namespace ssplugin\sstwitterfeed\controllers;

use ssplugin\sstwitterfeed\SsTwitterFeed;

use Craft;
use craft\web\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use yii\web\Response;
use craft\helpers\UrlHelper;

class OauthController extends Controller
{

	public function actionCallback( ):Response
	{
		$request_token = [];
		$request_token['oauth_token'] = $_SESSION['oauth_token'];
		$request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

		$oauthToken = Craft::$app->getRequest()->getParam('oauth_token');
        $oauthVerifier = Craft::$app->getRequest()->getParam('oauth_verifier');

        $connection = new TwitterOAuth( SsTwitterFeed::$CONSUMER_KEY, SsTwitterFeed::$CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

        $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $oauthVerifier]);

        //save token in DB       		
		$plugin = SsTwitterFeed::getInstance();
        $settings = $plugin->getSettings();
       
        $nsettings = array(
                        'access_token'  => $access_token['oauth_token'],
                        'access_token_secret' => $access_token['oauth_token_secret'],                       
                    );

        Craft::$app->getPlugins()->savePluginSettings($plugin, $nsettings);
    	return $this->redirect(UrlHelper::cpUrl('settings/plugins/ss-twitter-feed'));
	}

}
?>



