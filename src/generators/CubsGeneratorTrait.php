<?php

namespace bscheshirwork\cubs\generators;

use Yii;

trait CubsGeneratorTrait
{

    public $enableCubs;
    public $cubsFieldList = [];
    public $cubsInterface = '\bscheshirwork\cubs\base\CubsDefaultInterface';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::setAlias('@bscheshirwork/cubs', '@vendor/bscheshirwork/yii2-cubs/src');
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['enableCubs'], 'boolean'],
            [
                ['cubsInterface'],
                'match',
                'pattern' => '/^[\w\\\\]+$/',
                'message' => 'Only word characters and backslashes are allowed.',
            ],
            [['cubsInterface'], 'validateInterface'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'enableCubs' => 'Cubs',
            'cubsInterface' => 'Cubs interface',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'enableCubs' => 'This indicates whether the generator should generate labels and rules using <code>cubs</code> trait.
                Set this to <code>true</code> if you are planning to make your application so~o~o cute.',
            'cubsInterface' => 'This is the interface of the ActiveRecord class to be generated, e.g., <code>\bscheshirwork\cubs\base\CubsDefaultInterface</code>',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['enableCubs', 'cubsInterface']);
    }

    /**
     * Validates the interface name.
     *
     * @param string $attribute interface name variable.
     */
    public function validateInterface($attribute)
    {
        $value = $this->$attribute;
        if (!interface_exists($value, true)) {
            $this->addError($attribute, 'Interface must exist and must be available for autoload.');
        }
    }

    /**
     * Get cubsFieldList from interface.
     */
    public function generateCubsFieldList()
    {
        //get interface const
        try {
            $cubsConstants = (new \ReflectionClass($this->cubsInterface))->getConstants();
            $this->cubsFieldList = array_flip(array_filter($cubsConstants, function ($key) {
                return substr($key, 0, 6) == 'FIELD_';
            }, ARRAY_FILTER_USE_KEY));
        } catch (\ReflectionException $exception) {
            return false;
        }

        return true;
    }

}