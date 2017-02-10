<?php

namespace bscheshirwork\cubs\base;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class CubsQueryModelTrait
 * @package bscheshirwork\cubs\base
 */
trait CubsQueryModelTrait
{
    /**
     * @param ActiveRecord $model
     * @return $this|ActiveQuery
     */
    public function addCubsSearchCondition(ActiveRecord $model): ActiveQuery
    {
        if (static::checkInterface($model)) {
            /** @var ActiveQuery $this */
            return $this->andFilterWhere([
                $model::FIELD_CREATE_AT => $model->{$model::FIELD_CREATE_AT},
                $model::FIELD_CREATE_BY => $model->{$model::FIELD_CREATE_BY},
                $model::FIELD_UPDATE_AT => $model->{$model::FIELD_UPDATE_AT},
                $model::FIELD_UPDATE_BY => $model->{$model::FIELD_UPDATE_BY},
                $model::FIELD_STATE => $model->{$model::FIELD_STATE},
                $model::FIELD_BLOCKED_AT => $model->{$model::FIELD_BLOCKED_AT},
            ]);
        }
        return $this;
    }

    /**
     * dummy for function name constructor
     * @return $this
     */
    public function nothing()
    {
        return $this;
    }

    /**
     * Check isActive. Redefine if necessary
     * @return mixed
     */
    public function active()
    {
        return $this->andWhere(($this->modelClass)::tableName(). '.[[' . ($this->modelClass)::FIELD_STATE . ']]=1');
    }

}
