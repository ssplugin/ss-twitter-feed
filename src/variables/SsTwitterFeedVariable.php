<?php
/**
 * SS Twitter Feed plugin for Craft CMS 3.x
 *
 * Show Recent Tweets.
 *
 * @link      http://www.systemseeders.com/
 * @copyright Copyright (c) 2019 SystemSeeders
 */

namespace ssplugin\sstwitterfeed\variables;

use ssplugin\sstwitterfeed\SsTwitterFeed;
use Abraham\TwitterOAuth\TwitterOAuth;
use Craft;

/**
 * SS Twitter Feed Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.ssTwitterFeed }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    SystemSeeders
 * @package   SsTwitterFeed
 * @since     1.0.0
 */
class SsTwitterFeedVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.ssTwitterFeed.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.ssTwitterFeed.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function displayPost( $limit = '10' )
    {
        $settings = craft::$app->plugins->getPlugin('ss-twitter-feed')->getSettings();
        if( empty($settings->access_token) || empty( $settings->access_token_secret )) {
            echo 'Please connect to Twitter via SS Twitter Feed Plugin for get Access token and Secret.';
        } else { 
          
          $conn = new TwitterOAuth( SsTwitterFeed::CONSUMER_KEY, SsTwitterFeed::CONSUMER_SECRET, $settings->access_token, $settings->access_token_secret );
          $tweets_info = $conn->get( "statuses/user_timeline", array('count' => $limit, 'exclude_replies' => true));
          
          $tweets = array();
          foreach ($tweets_info as $row) {

              if( empty( $row->entities->urls )  &&  empty( $row->entities->media[0]->url ) ) {
                
                $url = isset( $row->retweeted_status->entities->urls[0]->url )? $row->retweeted_status->entities->urls[0]->url:null;
              } else {
               
                $url = isset( $row->entities->urls[0]->url ) ? $row->entities->urls[0]->url : $row->entities->media[0]->url;
              }
              $tweets[] = array(
                  'name' => isset( $row->user->name ) ? $row->user->name:null,
                  'screen_name' => isset( $row->user->screen_name ) ? $row->user->screen_name:null,
                  'text' => isset( $row->text )?$row->text:null,
                  'profile_image_url' => isset( $row->user->profile_image_url )?$row->user->profile_image_url:null,
                  'url' => $url,
                  'image_url' => isset( $row->entities->media[0]->media_url ) ? $row->entities->media[0]->media_url:null,
                  'retweet_count'  => isset( $row->retweet_count ) ? $row->retweet_count:null,
                  'favorite_count' => isset( $row->favorite_count ) ? $row->favorite_count:null,                     
                  'created_at'     => $this->time_ago( $row->created_at ),
                  'retweet_link'  => 'https://twitter.com/intent/retweet?tweet_id='.$row->id_str,
                  'favorite_link'  => 'https://twitter.com/intent/like?tweet_id='.$row->id_str,
              );
          }
          
          return $tweets;
        }      
    }

    public function getUrl()
    {        
       return SsTwitterFeed::$plugin->ssTwitterFeedService->getUrl( );
    }
    
    public function time_ago($timestamp){
  
        $time_ago        = strtotime($timestamp);
        $current_time    = time();
        $time_difference = $current_time - $time_ago;
        $seconds         = $time_difference;
      
        $minutes = round($seconds / 60); // value 60 is seconds  
        $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec  
        $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;  
        $weeks   = round($seconds / 604800); // 7*24*60*60;  
        $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60  
        $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60
                    
        if ($seconds <= 60){
            return "Just Now";
        } else if ($minutes <= 60){      
            return "$minutes minutes ago";      
        } else if ($hours <= 24){      
            return "$hours hrs ago";    
        } else if ($days <= 7){       
            return "$days days ago";   
        } else {        
            return date('d M', $time_ago);
        }
    }
}
