<?php

namespace bscheshirwork\cubs\helpers;

/**
 * For provide cubs inside views
 *
 * To customize a core helper class (e.g. bscheshirwork\cubs\helpers\WidgetHelper), you should create a new class
 * extending from the helpers corresponding base class (e.g. bscheshirwork\cubs\helpers\BaseWidgetHelper) and name
 * your class the same as the corresponding concrete class (e.g. bscheshirwork\cubs\helpers\WidgetHelper),
 * including its namespace.
 * This class will then be set up to replace the original implementation of the framework.
 *
 * The following example shows how to customize the updaterValue() method of the bscheshirwork\cubs\helpers\WidgetHelper
 * class (the new method will be called in the detailViewArray method):
 *
 * <?php
 * namespace bscheshirwork\cubs\helpers;
 *
 * class WidgetHelper extends BaseWidgetHelper
 * {
 *     public static function updaterValue(){
 *         // your custom implementation, for example replace the username field to the email field
 *         return function($model){
 *             return $model->creator->email;
 *         };
 *     }
 * }
 *
 *
 * Class WidgetHelper
 * @package bscheshirwork\cubs\helpers
 */
class WidgetHelper extends BaseWidgetHelper
{
}
