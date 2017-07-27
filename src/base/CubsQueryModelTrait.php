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
     * Model implement CubsDefaultInterface
     * Can be redefine to check another interface with fields definitions
     * @param $model
     * @return bool
     */
    protected function checkCubsInterface($model)
    {
        return $model instanceof CubsDefaultInterface;
    }

    /**
     * @param ActiveRecord $model
     * @param null $tablePrefix
     * @return $this|ActiveQuery
     */
    public function addCubsSearchCondition(ActiveRecord $model, $tablePrefix = null): ActiveQuery
    {
        $alias = ($tablePrefix ?? ($this->modelClass)::tableName()) . '.';
        if (static::checkCubsInterface($model)) {
            /** @var ActiveQuery $this */
            return $this->andFilterWhere([
                $alias . $model::FIELD_CREATE_AT => $model->{$model::FIELD_CREATE_AT},
                $alias . $model::FIELD_CREATE_BY => $model->{$model::FIELD_CREATE_BY},
                $alias . $model::FIELD_UPDATE_AT => $model->{$model::FIELD_UPDATE_AT},
                $alias . $model::FIELD_UPDATE_BY => $model->{$model::FIELD_UPDATE_BY},
                $alias . $model::FIELD_STATE => $model->{$model::FIELD_STATE},
                $alias . $model::FIELD_BLOCKED_AT => $model->{$model::FIELD_BLOCKED_AT},
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
            ')' .
            ' = ' . ($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]'
        );
    }

}
