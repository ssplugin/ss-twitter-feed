<?php
/**
 * SS Twitter Feed plugin for Craft CMS 3.x
 *
 * Show Recent Tweets.
 *
 * @link      http://www.systemseeders.com/
 * @copyright Copyright (c) 2019 SystemSeeders
 */

namespace ssplugin\sstwitterfeed\models;

use ssplugin\sstwitterfeed\SsTwitterFeed;

use Craft;
use craft\base\Model;

/**
 * SsTwitterFeedModel Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    SystemSeeders
 * @package   SsTwitterFeed
 * @since     1.0.0
 */
class SsTwitterFeedModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some model attribute
     *
     * @var string
     */
    public $someAttribute = 'Some Default';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['someAttribute', 'string'],
            ['someAttribute', 'default', 'value' => 'Some Default'],
        ];
    }
}
