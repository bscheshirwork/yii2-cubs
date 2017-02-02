<?php

namespace bscheshirwork\cubs\db;

use Yii\helpers\ArrayHelper;

/**
 * Create Update Block and Status field composition
 *
 * Class CubsMigrationTrait
 * @package bscheshirwork\cubs\db
 */
trait CubsMigrationTrait
{

    /**
     * @inheritdoc
     * @param $table
     * @param $columns
     * @param null $options
     */
    public function createTable($table, $columns, $options = null)
    {
        $fields = [
            static::FIELD_CREATE_AT => $this->datetime()->notNull(),
            static::FIELD_CREATE_BY => $this->integer()->null(),
            static::FIELD_UPDATE_AT => $this->datetime()->null(),
            static::FIELD_UPDATE_BY => $this->integer()->null(),
            static::FIELD_STATE => $this->integer()->notNull()->defaultValue(static::STATE_ENABLED),
            static::FIELD_BLOCKED_AT => $this->datetime()->null(),
        ];
        // reorder and merge default (can use UnsetArrayValue)
        $without = array_diff_key($columns, $fields);
        $with = array_intersect_key($columns, $fields);
        $columns = ArrayHelper::merge($without + $fields, $with);

        parent::createTable($table, $columns, $options);
    }

}