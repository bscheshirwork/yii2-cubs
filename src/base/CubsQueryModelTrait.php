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
     * Check isActive. Redefine if necessary.
     * Can be use in chain and multiply dependencies:
     * public function active($tablePrefix = null)
     * {
     *     return $this->andWhere(($this->modelClass)::tableName().'.[[' . ($this->modelClass)::FIELD_STATE . ']]=1')
     *         ->joinWith([
     *             'firstRelation' => function(\common\models\FirstRelationQuery $query){
     *                 $query->active();
     *             },
     *             'secondRelation' => function(\common\models\SecondRelationQuery $query){
     *                 $query->active('secondRelation');
     *             },
     *         ]);
     * }
     * @param null $tablePrefix the table name or the alias of table
     * (set alias if you use multiply join to same table in chain)
     * @return $this
     */
    public function active($tablePrefix = null)
    {
        /** @var ActiveQuery $this */
        return $this->andWhere(
            '(' . ($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]' .
            ' & ~' . ($this->modelClass)::STATE_BLOCKED .
            ' | ' . ($this->modelClass)::STATE_ENABLED .
            ')'.
            ' = ' . ($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]'
        );
    }

}
