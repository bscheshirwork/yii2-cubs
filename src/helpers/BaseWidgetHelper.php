<?php
namespace bscheshirwork\cubs\helpers;

use Yii;
use yii\helpers\Html;

/**
 *
 * Do not use BaseWidgetHelper. Use [[WidgetHelper]] instead.
 *
 * Class BaseWidgetHelper
 * @package bscheshirwork\cubs\helpers
 */
class BaseWidgetHelper
{
    /**
     * Return array of field for DetailView
     * <?= DetailView::widget([
     *     'model' => $model,
     *      'attributes' => \yii\helpers\ArrayHelper::merge([
     *        'id',
     *        'name',
     *        'url:ntext',
     *        'description:ntext',
     *       ], \bscheshirwork\cubs\helpers\WidgetHelper::DetailViewArray($model)),
     * ]) ?>
     * @param $model Yii\base\model
     * @return array
     */
    public static function DetailViewArray($model)
    {
        return [
            'createdAt',
            'createdBy' => [
                'label' => $model->getAttributeLabel('createdBy'),
                'value' => WidgetHelper::creatorValue(),
            ],
            'updatedAt',
            'updatedBy' => [
                'label' => $model->getAttributeLabel('updatedBy'),
                'value' => WidgetHelper::updaterValue(),
            ],
            'stateOfFlags' => [
                'label' => $model->getAttributeLabel('stateOfFlags'),
                'value' => WidgetHelper::statusValue(),
            ],
            'blockedAt',
        ];
    }

    /**
     * Return string representation of creator
     * @return \Closure
     */
    public static function creatorValue()
    {
        /**
         * @param $model \yii\base\model
         * @return string
         */
        return function ($model) {
            return $model->creator->username;
        };
    }

    /**
     * Return string representation of updater
     * @return \Closure
     */
    public static function updaterValue()
    {
        /**
         * @param $model \yii\base\model
         * @return string
         */
        return function ($model) {
            return $model->updater->username;
        };
    }

    /**
     * Return string representation of status
     * @return \Closure
     */
    public static function statusValue()
    {
        /**
         * @param $model \yii\base\model
         * @return string
         */
        return function ($model) {
            return $model->isActive() ? Yii::t('cubs', 'Active') : ($model->isEnabled() ? ' ' . Yii::t('cubs', 'On') : ' ' . Yii::t('cubs', 'Off') . $model->isBlocked() ? ' ' . Yii::t('cubs', 'Blocked') : '');
        };
    }

    /**
     * Return html representation of the button, routed ['block|unblock', 'id' => $model->id]
     * Usage in view <?= \bscheshirwork\cubs\helpers\WidgetHelper::HtmlBlockButton($model) ?>
     * @param $model
     * @param array $title array of customization the label on the button ['Unblock'=>'Block it!', 'Block' => Yii::t('someone', 'Block')]
     * @return string
     */
    public static function HtmlBlockButton($model, $title = [])
    {
        return $model->isBlocked()
            ? Html::a($title['Unblock'] ?: Yii::t('cubs', 'Unblock'), ['unblock', 'id' => $model->id], ['class' => 'btn btn-info'])
            : Html::a($title['Block'] ?: Yii::t('cubs', 'Block'), ['block', 'id' => $model->id], ['class' => 'btn btn-danger']);
    }
}