<?php

namespace bscheshirwork\cubs\generators\crud;

use Yii;
use yii\gii\CodeFile;

/**
 * Generates CRUD
 *
 * @inheritdoc
 */
class Generator extends \yii\gii\generators\crud\Generator
{
    use \bscheshirwork\cubs\generators\CubsGeneratorTrait;

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return bool|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            $tableSchema = $class::getTableSchema();
            if ($this->enableCubs) {
                $this->generateCubsFieldList();
                $tableSchema->columns = array_diff_key($tableSchema->columns, $this->cubsFieldList);
            }

            return $tableSchema;
        } else {
            return false;
        }
    }
}
