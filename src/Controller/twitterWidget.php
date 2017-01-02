<?php
/**
 * Created by PhpStorm.
 * User: jobsity
 * Date: 1/2/17
 * Time: 10:10 AM
 */

namespace Drupal\twitter_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

class twitterWidget extends ControllerBase
{
    public function help()
    {
        return array(
            '#theme' => 'twitter_widget_help'
        );
    }
}