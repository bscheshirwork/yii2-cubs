<?php

namespace bscheshirwork\cubs\base;

use Yii;
use yii\base\Behavior;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use Yii\helpers\ArrayHelper;

/**
 * Create Update Block and Status attribute composition
 *  DISABLED = 0
 *  ENABLED = not DISABLED
 *  ACTIVE = ENABLED + not BLOCKED
 *
 * some constant must be definite!
 * implement @see CubsDefaultInterface for define const
 *
 *
 * Class CubsMigrationTrait
 * @package bscheshirwork\cubs\db
 */
trait CubsModelTrait
{

    /**
     * Stored labels (after merge and translate)
     * if you need dynamic labels - clear it for refresh
     * @var array
     */
    protected $_attributeLabels = [];

    /**
     * Return list of status names
     * @return mixed
     */
    public function getStateList()
    {
        return static::LIST_STATE;
    }

    /**
     * Return status name
     * @param $statusId
     * @return string
     */
    public function getStateName($statusId)
    {
        return static::LIST_STATE[$statusId] ?: '';
    }

    /**
     * Return if is active (first status bit is set)
     * @return bool
     */
    public function isEnabled()
    {
        return ($this->{static::FIELD_STATE} | static::STATE_ENABLED) == $this->{static::FIELD_STATE};
    }

    /**
     * Return if is blocked (second status bit is set)
     * @return bool
     */
    public function isBlocked()
    {
        return ($this->{static::FIELD_STATE} | static::STATE_BLOCKED) == $this->{static::FIELD_STATE};
    }

    /**
     * Return if it can be used
     * return $this->isEnabled() && !$this->isBlocked();
     * @return bool
     */
    public function isActive()
    {
        return ($this->{static::FIELD_STATE} & ~static::STATE_BLOCKED | static::STATE_ENABLED) == $this->{static::FIELD_STATE};
    }

    /**
     * sign on
     */
    public function block()
    {
        $this->{static::FIELD_STATE} |= static::STATE_BLOCKED;
    }

    /**
     * sign off
     */
    public function unblock()
    {
        $this->{static::FIELD_STATE} &= ~static::STATE_BLOCKED;
    }

    /**
     * sign on
     */
    public function enabled()
    {
        $this->{static::FIELD_STATE} |= static::STATE_ENABLED;
    }

    /**
     * sign off
     */
    public function disabled()
    {
        $this->{static::FIELD_STATE} &= ~static::STATE_ENABLED;
    }

    /**
     * sign toggle
     * @param $sign
     */
    public function toggleSign($sign = self::STATE_BLOCKED)
    {
        $this->{static::FIELD_STATE} ^= $sign;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            'cubsTimestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_UPDATE_AT],
                ],
                'value' => new Expression('NOW()'),
            ],
            'cubsBlameable' => [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_CREATE_BY, static::FIELD_UPDATE_BY],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_UPDATE_BY],
                ],
            ],
            'cubsAttribute' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_BLOCKED_AT],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_BLOCKED_AT],
                ],
                'value' => [$this, 'getActualBlockedAt']
            ],
            'cubsTimestampExpressionErase' => function () {
                $attributes = [
                    ActiveRecord::EVENT_AFTER_INSERT => [static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT],
                    ActiveRecord::EVENT_AFTER_UPDATE => [static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT],
                ];
                return new class(['attributes' => $attributes]) extends AttributeBehavior {
                    public function evaluateAttributes($event)
                    {
                        if (!empty($this->attributes[$event->name])) {
                            $attributes = (array) $this->attributes[$event->name];
                            foreach ($attributes as $attribute) {
                                if (is_string($attribute) && $this->owner->$attribute instanceof Expression) {
                                    $this->owner->$attribute = null;
                                }
                            }
                        }
                    }
                };
            },
        ];
    }

    /** Call from AttributeBehavior if data changes
     * Copy value from TimestampBehavior, set same value or set null
     * @param $event
     * @return null
     */
    public function getActualBlockedAt($event)
    {
        if ($this->isBlocked())
            if (empty($this->{static::FIELD_BLOCKED_AT}))
                return $this->{static::FIELD_UPDATE_AT};
            else
                return $this->{static::FIELD_BLOCKED_AT};
        else
            return null;
    }

    /**
     * Creator relation
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(Yii::$app->get('user')->identityClass, ['id' => static::FIELD_CREATE_BY]);
    }

    /**
     * Updater relation
     * @return \yii\db\ActiveQuery
     */
    public function getUpdater()
    {
        return $this->hasOne(Yii::$app->get('user')->identityClass, ['id' => static::FIELD_UPDATE_BY]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [[static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT], 'default', 'value' => null],
            [[static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT], 'datetime'],
            [[static::FIELD_CREATE_BY, static::FIELD_UPDATE_BY, static::FIELD_STATE], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'createdAt' => Yii::t('cubs', 'Created At'),
            'createdBy' => Yii::t('cubs', 'Created By'),
            'updatedAt' => Yii::t('cubs', 'Updated At'),
            'updatedBy' => Yii::t('cubs', 'Updated By'),
            'stateOfFlags' => Yii::t('cubs', 'State Of Flags'),
            'blockedAt' => Yii::t('cubs', 'Blocked At'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return ArrayHelper::merge(parent::hints(), [
            'createdAt' => Yii::t('cubs', 'Created at'),
            'createdBy' => Yii::t('cubs', 'Author'),
            'updatedAt' => Yii::t('cubs', 'Updated at'),
            'updatedBy' => Yii::t('cubs', 'Updater'),
            'stateOfFlags' => Yii::t('cubs', 'Status of the model'),
            'blockedAt' => Yii::t('cubs', 'Blocked at'),
        ]);
    }

}