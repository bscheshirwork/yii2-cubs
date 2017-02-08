<?php

namespace bscheshirwork\cubs\base;

/**
 * Class CubsSearchModelTrait
 * @package bscheshirwork\cubs\base
 */
trait CubsSearchModelTrait
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT], 'default', 'value' => null],
            [[static::FIELD_CREATE_AT, static::FIELD_UPDATE_AT, static::FIELD_BLOCKED_AT], 'datetime'],
            [[static::FIELD_CREATE_BY, static::FIELD_UPDATE_BY, static::FIELD_STATE], 'integer'],
        ];
    }
}