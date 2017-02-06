<?php

namespace bscheshirwork\cubs\base;

/**
 * Class CubsQueryModelTrait
 * @package bscheshirwork\cubs\base
 */
trait CubsQueryModelTrait
{
    public function addCubsSearchCondition(){
        /** @var \yii\db\ActiveQuery $this */
        return $this->andFilterWhere([
            static::FIELD_CREATE_AT => $this->{static::FIELD_CREATE_AT},
            static::FIELD_CREATE_BY => $this->{static::FIELD_CREATE_BY},
            static::FIELD_UPDATE_AT => $this->{static::FIELD_UPDATE_AT},
            static::FIELD_UPDATE_BY => $this->{static::FIELD_UPDATE_BY},
            static::FIELD_STATE => $this->{static::FIELD_STATE},
            static::FIELD_BLOCKED_AT => $this->{static::FIELD_BLOCKED_AT},
        ]);
    }
}
