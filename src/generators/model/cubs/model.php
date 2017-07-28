<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator bscheshirwork\cubs\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $interfaceName string interface name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use Yii\helpers\ArrayHelper;
use bscheshirwork\cubs\base\CubsModelTrait;
use <?= ltrim($interfaceName, '\\') ?>;

/**
 * This is the model class for table "<?= $generator->generateTableName($tableName) ?>".
 *
<?php foreach ($tableSchema->columns as $column): ?>
 * @property <?= "{$column->phpType} \${$column->name}\n" ?>
<?php endforeach; ?>
<?php if (!empty($relations)): ?>
 *
<?php foreach ($relations as $name => $relation): ?>
 * @property <?= $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n" ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?= $className ?> extends <?= '\\' . ltrim($generator->baseClass, '\\') . ' implements ' . StringHelper::basename($interfaceName) . "\n" ?>
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
        return '<?= $generator->generateTableName($tableName) ?>';
    }
<?php if ($generator->db !== 'db'): ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?= $generator->db ?>');
    }
<?php endif; ?>

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(static::rulesFromTrait(), [<?= empty($rules) ? '' : ("\n            " . implode(",\n            ", $rules) . ",\n        ") ?>]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->_attributeLabels ?: $this->_attributeLabels = ArrayHelper::merge(static::attributeLabelsFromTrait(), [
<?php foreach ($labels as $name => $label): ?>
            <?= "'$name' => " . $generator->generateString($label) . ",\n" ?>
<?php endforeach; ?>
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
<?php foreach ($relations as $name => $relation): ?>

    /**
     * @return <?= ($queryClassName ? $relation[1] . 'Query' : '\yii\db\ActiveQuery') . "\n" ?>
     */
    public function get<?= $name ?>()
    {
        <?= $relation[0] . "\n" ?>
    }
<?php endforeach; ?>
<?php if ($queryClassName): ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * @inheritdoc
     * @return <?= $queryClassFullName ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?= $queryClassFullName ?>(get_called_class());
    }
<?php endif; ?>
}
