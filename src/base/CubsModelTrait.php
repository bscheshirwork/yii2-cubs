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
        return $this->isSign(static::STATE_ENABLED);
    }

    /**
     * Return if is blocked (second status bit is set)
     * @return bool
     */
    public function isBlocked()
    {
        return $this->isSign(static::STATE_BLOCKED);
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
        return $this->signOn(static::STATE_BLOCKED);
    }

    /**
     * sign off
     */
    public function unblock()
    {
        return $this->signOff(static::STATE_BLOCKED);
    }

    /**
     * sign on
     */
    public function enabled()
    {
        return $this->signOn(static::STATE_ENABLED);
    }

    /**
     * sign off
     */
    public function disabled()
    {
        return $this->signOff(static::STATE_ENABLED);
    }

    /**
     * Return is sign bits is set
     * @param $sign
     * @return bool
     */
    public function isSign($sign = self::STATE_BLOCKED)
    {
        return ($this->{static::FIELD_STATE} | $sign) == $this->{static::FIELD_STATE};
    }

    /**
     * Return is sign bits is not set
     * @param $sign
     * @return bool
     */
    public function isNotSign($sign = self::STATE_BLOCKED)
    {
        return ($this->{static::FIELD_STATE} & ~$sign) == $this->{static::FIELD_STATE};
    }

    /**
     * sign on
     * @param $sign
     * @return $this
     */
    public function signOn($sign = self::STATE_BLOCKED)
    {
        $this->{static::FIELD_STATE} |= $sign;

        return $this;
    }

    /**
     * sign off
     * @param $sign
     * @return $this
     */
    public function signOff($sign = self::STATE_BLOCKED)
    {
        $this->{static::FIELD_STATE} &= ~$sign;

        return $this;
    }

    /**
     * sign toggle
     * @param $sign
     * @return $this
     */
    public function signToggle($sign = self::STATE_BLOCKED)
    {
        $this->{static::FIELD_STATE} ^= $sign;

        return $this;
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
                'value' => [$this, 'getActualBlockedAt'],
            ],
            'cubsTimestampExpressionErase' => function () {
                $attributes = [
                    ActiveRecord::EVENT_AFTER_INSERT => [
                        static::FIELD_CREATE_AT,
                        static::FIELD_UPDATE_AT,
                        static::FIELD_BLOCKED_AT,
                    ],
                    ActiveRecord::EVENT_AFTER_UPDATE => [
                        static::FIELD_CREATE_AT,
                        static::FIELD_UPDATE_AT,
                        static::FIELD_BLOCKED_AT,
                    ],
                    ActiveRecord::EVENT_AFTER_VALIDATE => [
                        static::FIELD_CREATE_AT,
                        static::FIELD_UPDATE_AT,
                        static::FIELD_BLOCKED_AT,
                    ],
                ];

                return new class(['attributes' => $attributes]) extends Behavior
                {
                    public $attributes = [];
                    private $storedCubsAttributes = [];

                    public function events()
                    {
                        return [
                            ActiveRecord::EVENT_AFTER_INSERT => $fn = function ($event) {
                                if (!empty($this->attributes[$event->name])) {
                                    $attributes = (array)$this->attributes[$event->name];
                                    foreach ($attributes as $attribute) {
                                        if (is_string($attribute) && $this->owner->$attribute instanceof Expression && !array_key_exists($attribute,
                                                $this->owner->dirtyAttributes)) {
                                            $this->storedCubsAttributes[$attribute] = $this->owner->$attribute;
                                            $this->owner->$attribute = null;
                                        }
                                    }
                                }
                            },
                            ActiveRecord::EVENT_AFTER_UPDATE => $fn,
                            ActiveRecord::EVENT_AFTER_VALIDATE => function ($event) {
                                if (!empty($this->attributes[$event->name])) {
                                    $attributes = (array)$this->attributes[$event->name];
                                    foreach ($attributes as $attribute) {
                                        if (is_string($attribute) && array_key_exists($attribute,
                                                $this->storedCubsAttributes)) {
                                            $this->owner->$attribute = $this->storedCubsAttributes[$attribute];
                                            unset($this->storedCubsAttributes[$attribute]);
                                        }
                                    }
                                }
                            },
                        ];
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
        if ($this->isBlocked()) {
            if (empty($this->{static::FIELD_BLOCKED_AT})) {
                return $this->{static::FIELD_UPDATE_AT};
            } else {
                return $this->{static::FIELD_BLOCKED_AT};
            }
        } else {
            return null;
        }
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
            static::FIELD_CREATE_AT => Yii::t('cubs', 'Created At'),
            static::FIELD_CREATE_BY => Yii::t('cubs', 'Created By'),
            static::FIELD_UPDATE_AT => Yii::t('cubs', 'Updated At'),
            static::FIELD_UPDATE_BY => Yii::t('cubs', 'Updated By'),
            static::FIELD_STATE => Yii::t('cubs', 'State Of Flags'),
            static::FIELD_BLOCKED_AT => Yii::t('cubs', 'Blocked At'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return ArrayHelper::merge(parent::hints(), [
            static::FIELD_CREATE_AT => Yii::t('cubs', 'Created at'),
            static::FIELD_CREATE_BY => Yii::t('cubs', 'Author'),
            static::FIELD_UPDATE_AT => Yii::t('cubs', 'Updated at'),
            static::FIELD_UPDATE_BY => Yii::t('cubs', 'Updater'),
            static::FIELD_STATE => Yii::t('cubs', 'Status of the model'),
            static::FIELD_BLOCKED_AT => Yii::t('cubs', 'Blocked at'),
        ]);
    }

    /**
     * Return cubs field names. Use it in fields like this
     * public function fields()
     * {
     *     return array_diff(parent::fields(), parent::cubsFields());
     * }
     * @return array
     */
    public function cubsFields()
    {
        return [
            static::FIELD_CREATE_AT => static::FIELD_CREATE_AT,
            static::FIELD_CREATE_BY => static::FIELD_CREATE_BY,
            static::FIELD_UPDATE_AT => static::FIELD_UPDATE_AT,
            static::FIELD_UPDATE_BY => static::FIELD_UPDATE_BY,
            static::FIELD_STATE => static::FIELD_STATE,
            static::FIELD_BLOCKED_AT => static::FIELD_BLOCKED_AT,
        ];
    }
}
