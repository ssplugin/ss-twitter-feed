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
    public function displayPost( $limit = '10', $exclude_retweets  = 'false' )
    {
        $include_rts = 'true';
        if( $exclude_retweets == 'true' ) {
            $include_rts = 'false';
        }
        $settings = craft::$app->plugins->getPlugin('ss-twitter-feed')->getSettings();

        if( empty($settings->access_token) || empty( $settings->access_token_secret )) {
            echo 'Please connect to Twitter via SS Twitter Feed Plugin for get Access token and Secret.';
        } else {
            //Retrive tweet data
            $param =  array( 'count' => $limit, 'exclude_replies' => 'true', 'tweet_mode' => 'extended', 'include_rts'=> $include_rts );
            $tweets_info = $this->buidTweetJson($settings, $param);
            
            if( !empty($tweets_info)){
                $tweets_data = (array) $tweets_info;
                if( isset($tweets_data['errors'][0]) ){
                    $errmsg = '';
                    foreach ((array) $tweets_data['errors'][0] as $k => $value) {
                        $errmsg .= $value;
                    }
                    echo $errmsg;
                    return true;
                } else {
                    if(!empty($tweets_data)){
                        $tweets = array();
                        foreach ($tweets_data as $row) {
                            if( empty( $row->entities->urls )  &&  empty( $row->entities->media[0]->url ) ) {
                                $url = isset( $row->retweeted_status->entities->urls[0]->url ) ? $row->retweeted_status->entities->urls[0]->url : null;
                            } else {                   
                                $url = isset( $row->entities->urls[0]->url ) ? $row->entities->urls[0]->url : $row->entities->media[0]->url;
                            }
                            if( empty( $row->extended_entities ) && empty( $row->retweeted_status->extended_entities ) ) {
                                $images = isset( $row->quoted_status->extended_entities->media )?$row->quoted_status->extended_entities->media:null;                        
                            } else {
                                $images = isset($row->extended_entities->media)?$row->extended_entities->media:$row->retweeted_status->extended_entities->media;
                            }
                            $ss_tweet = $this->parseTweet( $row->full_text );

                            $tweets[] = array(
                                'name' => isset( $row->user->name ) ? $row->user->name:null,
                                'screen_name' => isset( $row->user->screen_name ) ? $row->user->screen_name:null,
                                'text' => isset(  $row->full_text ) ? $row->full_text:null,
                                'text_html' => isset(  $ss_tweet ) ? $ss_tweet:null,
                                'profile_image_url' => isset( $row->user->profile_image_url )?$row->user->profile_image_url:null,
                                'url' => isset( $url )? $url: null,
                                'image_url' => isset( $row->entities->media[0]->media_url ) ? $row->entities->media[0]->media_url:null,
                                'images'   => isset( $images )?$images:null,
                                'retweet_count'  => isset( $row->retweet_count ) ? $row->retweet_count:null,
                                'favorite_count' => isset( $row->favorite_count ) ? $row->favorite_count:null,                     
                                'created_at'     => $this->time_ago( $row->created_at ),
                                'retweet_link'  => 'https://twitter.com/intent/retweet?tweet_id='.$row->id_str,
                                'favorite_link'  => 'https://twitter.com/intent/like?tweet_id='.$row->id_str,
                            );
                        }
                        return $tweets;
                    }else{
                        echo 'There are not found any tweets yet.';
                        return true;
                    }
                }
            }
            echo 'There are not found any tweets yet.';
            return true;
        }
    }

    public function buidTweetJson($settings, $param) {
        //Build oauth_hash
        $oauth_hash = '';
        $oauth_hash .= 'count='.$param['count'].'&';
        $oauth_hash .= 'exclude_replies='.$param['exclude_replies'].'&';
        $oauth_hash .= 'include_rts='.$param['include_rts'].'&';
        $oauth_hash .= 'oauth_consumer_key='.SsTwitterFeed::$CONSUMER_KEY.'&';
        $oauth_hash .= 'oauth_nonce=' . time() . '&';
        $oauth_hash .= 'oauth_signature_method=HMAC-SHA1&';
        $oauth_hash .= 'oauth_timestamp=' . time() . '&';
        $oauth_hash .= 'oauth_token='.$settings->access_token.'&';
        $oauth_hash .= 'oauth_version=1.0&';
        $oauth_hash .= 'tweet_mode=extended';

        //Build signature
        $base = '';
        $base .= 'GET';
        $base .= '&';
        $base .= rawurlencode('https://api.twitter.com/1.1/statuses/user_timeline.json');
        $base .= '&';
        $base .= rawurlencode($oauth_hash);
        $key = '';
        $key .= rawurlencode(SsTwitterFeed::$CONSUMER_SECRET);
        $key .= '&';
        $key .= rawurlencode($settings->access_token_secret);
        $signature = base64_encode(hash_hmac('sha1', $base, $key, true));
        $signature = rawurlencode($signature);

        //Build oauth_header
        $oauth_header = '';
        $oauth_header .= 'count="'.$param['count'].'", ';
        $oauth_header .= 'exclude_replies="'.$param['exclude_replies'].'", ';
        $oauth_header .= 'include_rts="'.$param['include_rts'].'", ';
        $oauth_header .= 'oauth_consumer_key="'.SsTwitterFeed::$CONSUMER_KEY.'", ';
        $oauth_header .= 'oauth_nonce="' . time() . '", ';
        $oauth_header .= 'oauth_signature="' . $signature . '", ';
        $oauth_header .= 'oauth_signature_method="HMAC-SHA1", ';
        $oauth_header .= 'oauth_timestamp="' . time() . '", ';
        $oauth_header .= 'oauth_token='.$settings->access_token.', ';
        $oauth_header .= 'oauth_version="1.0", ';
        $oauth_header .= 'tweet_mode="extended",' ;
        $curl_header = array("Authorization: OAuth {$oauth_header}", 'Expect:');
        
        //Curl request
        $curl_request = curl_init();
        curl_setopt($curl_request, CURLOPT_HTTPHEADER, $curl_header);
        curl_setopt($curl_request, CURLOPT_HEADER, false);
        curl_setopt($curl_request, CURLOPT_URL, 'https://api.twitter.com/1.1/statuses/user_timeline.json?count='.$param['count'].'&exclude_replies='.$param['exclude_replies'].'&tweet_mode='.$param['tweet_mode'].'&include_rts='.$param['include_rts']);
        curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);
        $json = curl_exec($curl_request);
        curl_close($curl_request);

        $tweets_data = json_decode($json);
        return $tweets_data;
    }

    public function makeClickableLinks( $text ) {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        if(preg_match($reg_exUrl, $text, $url)) {
            return preg_replace($reg_exUrl, "<a href='{$url[0]}' target='_blank'>{$url[0]}</a>", $text);
        } else {
            return $text;
        }
    }

    public function parseTweet( $text ) {

      $text = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
      $text = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
      $text = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $text);
      $text = preg_replace("/#(\w+)/", "<a href=\"http://twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $text);

      return $text;
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
