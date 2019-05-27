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
use Abraham\TwitterOAuth\TwitterOAuth;
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
        $connection     = new TwitterOAuth( SsTwitterFeed::$CONSUMER_KEY, SsTwitterFeed::$CONSUMER_SECRET);
        $site_url       = trim( Craft::getAlias('@web') );
        
        $request_token  = $connection->oauth('oauth/request_token', array('oauth_callback' => SsTwitterFeed::$OAUTH_PROCESSOR_URL.$site_url.SsTwitterFeed::$OAUTH_RET_URL ));
    
        $_SESSION['oauth_token']        = $request_token['oauth_token'];
        $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $url = SsTwitterFeed::$OAUTH_PROCESSOR_URL.$site_url.SsTwitterFeed::$OAUTH_RET_URL.'&toauth_token='.$request_token['oauth_token'];

        return $url;
    }
}
