<?php
/**
 * This is the template for generating the ActiveQuery class.
 */
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator bscheshirwork\cubs\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $className string class name */
/* @var $modelClassName string related model class name */

$modelFullClassName = $modelClassName;
if ($generator->ns !== $generator->queryNs) {
    $modelFullClassName = '\\' . $generator->ns . '\\' . $modelFullClassName;
}

echo "<?php\n";
?>

namespace <?= $generator->queryNs ?>;

use bscheshirwork\cubs\base\CubsQueryModelTrait;
use <?= ltrim($interfaceName, '\\') ?>;

/**
 * This is the ActiveQuery class for [[<?= $modelFullClassName ?>]].
 *
 * @see <?= $modelFullClassName . "\n" ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->queryBaseClass, '\\') . "\n" ?>
{
    use CubsQueryModelTrait;

    /**
     * return ($this->{static::FIELD_STATE} & ~static::STATE_BLOCKED | static::STATE_ENABLED) == $this->{static::FIELD_STATE};
     * @param null $tablePrefix
     * @return $this
     */
    /*public function active($tablePrefix = null)
    {
        return $this->andWhere(
            '(' . ($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]' .
            ' & ~' . ($this->modelClass)::STATE_BLOCKED .
            ' | ' . ($this->modelClass)::STATE_ENABLED .
            ')'.
            ' = ' . ($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]'
        );
    }*/

    /**
     * @inheritdoc
     * @return <?= $modelFullClassName ?>[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return <?= $modelFullClassName ?>|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
