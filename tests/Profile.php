<?php


namespace bscheshirwork\cubs\tests\unit;


use yii\db\ActiveRecord;
use bscheshirwork\cubs\base\CubsModelTrait;
use bscheshirwork\cubs\base\CubsDefaultInterface;
use yii\helpers\ArrayHelper;

class Profile extends ActiveRecord implements CubsDefaultInterface
{
    use CubsModelTrait {
        rules as rulesFromTrait;
        attributeLabels as attributeLabelsFromTrait;
        hints as hintsFromTrait;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(static::rulesFromTrait(), [
            [['description'], 'required'],
            [['description'], 'string', 'max' => 128],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->_attributeLabels ?: $this->_attributeLabels = ArrayHelper::merge(static::attributeLabelsFromTrait(),
            [
                'id' => 'ID',
                'description' => 'Description',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return ArrayHelper::merge(static::hintsFromTrait(), [
        ]);
    }
}
