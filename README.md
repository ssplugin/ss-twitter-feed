# SS Twitter Feed plugin for Craft CMS 3.x

Show Recent twitter timeline on your site. 


## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require ssplugin/ss-twitter-feed

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for SS Twitter Feed.

## SS Twitter Feed Overview

A plugin for Craft CMS that allows you to retrive your Twitter timeline.

## Configuring SS Twitter Feed

Once you’ve installed the SS Twitter Feed plugin. 
Go to plugin settings to connect to twitter.
Just click on button get your Twitter Access Token and Twitter Secret.

## Using SS Twitter Feed

-You can directly access multidimensional array of your Twitter posts using following method.
Each post has Following components you can get to:
<ul>
   <li> <strong>name</strong> ( the name of the Twitter account )</li>
   <li> <strong>screen_name</strong> ( the screen name of the Twitter account )</li>
   <li> <strong>text</strong> ( Twitter text )</li>
   <li> <strong>profile_image_url</strong>( Profile picture of your Twitter )</li>
   <li> <strong>url</strong> ( the Twitter url )</li>
   <li> <strong>image_url</strong> ( Twitter media image url )</li>
   <li> <strong>retweet_count</strong>  (Total number of retweet count  )</li>
   <li> <strong>favorite_count</strong> (Total number of likes the post recieved )</li>
   <li> <strong>created_at</strong>     ( The tweet post time )</li>
   <li> <strong>retweet_link</strong> (The retweet link on site )</li>
   <li> <strong>favorite_link</strong> (  favorite link )</li>
</ul>

Example:
```
{% for tweet in craft.ssTwitterFeed.displayPost( '5' ) %}
	{{ tweet.url }}
	{{ tweet.screen_name }}
	{{ tweet.text }}
	{{ tweet.created_at }}
	{{ tweet.retweet_count }}
	{{ tweet.favorite_count }}
{% endfor %}
```
## SS Twitter Feed Roadmap

**BENEFITS:**
<ul>
    <li> Hassle-free and Simple setup( No need to create Twitter App).</li>
    <li> Limit number of tweets to show.</li>
    <li> Increment social commitment among you and your clients.</li>
    <li> Show your Twitter content your way to perfectly match your website style.</li>
    <li> The Plugib is updated consistently with new features, bug-fixes and Twitter API changes.</li>
    <li> Support is speedy, effective and powerful.</li>
</ul>

### License

This DOMPDF Wrapper for Laravel is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Release it

Brought to you by [SystemSeeders](http://www.systemseeders.com/)
