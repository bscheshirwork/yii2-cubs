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
     * Model implement <?= StringHelper::basename($interfaceName) . "\n" ?>
     * @param $model
     * @return bool
     */
    private function checkInterface($model){
        return $model instanceof <?= StringHelper::basename($interfaceName) ?>;
    }

    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
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
