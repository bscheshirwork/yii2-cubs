<?php

namespace bscheshirwork\cubs\base;

use Yii;
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
trait CubsTrait
{

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
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_UPDATE_AT],
                ],
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_CREATE_BY, static::FIELD_UPDATE_BY],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_UPDATE_BY],
                ],
            ],
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => [static::FIELD_BLOCKED_AT],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [static::FIELD_BLOCKED_AT],
                ],
                'value' => [$this, 'getActualBlockedAt']
            ],
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
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['createdAt'], 'required'],
            [['createdAt', 'updatedAt', 'blockedAt'], 'safe'],
            [['createdBy', 'updatedBy', 'stateOfFlags'], 'integer'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'createdAt' => Yii::t('app', 'Created At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'updatedBy' => Yii::t('app', 'Updated By'),
            'stateOfFlags' => Yii::t('app', 'State Of Flags'),
            'blockedAt' => Yii::t('app', 'Blocked At'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return ArrayHelper::merge(parent::hints(), [
            'createdAt' => Yii::t('app', 'Created at'),
            'createdBy' => Yii::t('app', 'Author'),
            'updatedAt' => Yii::t('app', 'Updated at'),
            'updatedBy' => Yii::t('app', 'Updater'),
            'stateOfFlags' => Yii::t('app', 'Status of the model'),
            'blockedAt' => Yii::t('app', 'Blocked at'),
        ]);
    }

}