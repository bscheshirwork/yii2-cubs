<?php

namespace bscheshirwork\cubs\generators\model;

use bscheshirwork\cubs\generators\CubsGeneratorTrait;
use Yii;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\base\NotSupportedException;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @inheritdoc
 */
class Generator extends \yii\gii\generators\model\Generator
{
    use CubsGeneratorTrait {
        rules as rulesFromTrait;
        attributeLabels as attributeLabelsFromTrait;
        hints as hintsFromTrait;
        stickyAttributes as stickyAttributesFromTrait;
    }

    public $enableCheckActive;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(static::rulesFromTrait(), [
            [['enableCheckActive'], 'boolean'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(static::attributeLabelsFromTrait(), [
            'enableCheckActive' => 'Check active',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return ArrayHelper::merge(static::hintsFromTrait(), [
            'enableCheckActive' => 'This indicates whether the generator should generate rules using <code>active()</code> query filter.
                Set this to <code>true</code> to add additional parameter <code>filter</code> into exist validator',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return ArrayHelper::merge(static::stickyAttributesFromTrait(), ['useTablePrefix', 'generateQuery', 'enableCheckActive']);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            // model :
            $modelClassName = $this->generateClassName($tableName);
            $queryClassName = ($this->generateQuery) ? $this->generateQueryClassName($modelClassName) : false;
            $tableRelations = isset($relations[$tableName]) ? $relations[$tableName] : [];
            array_walk($tableRelations, function (&$value){
                $value[0] = strtr($value[0], ['::className()' => '::class']);
            });
            $tableSchema = $db->getTableSchema($tableName);
            if ($this->enableCubs) {
                $this->generateCubsFieldList();
            }
            $params = [
                'tableName' => $tableName,
                'className' => $modelClassName,
                'interfaceName' => $this->cubsInterface,
                'queryClassName' => $queryClassName,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => $tableRelations,
                'relationsClassHints' => $this->generateRelationsClassHints($tableRelations, $this->generateQuery),
            ];
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $modelClassName . '.php',
                $this->render('model.php', $params)
            );

            // query :
            if ($queryClassName) {
                $params['className'] = $queryClassName;
                $params['modelClassName'] = $modelClassName;
                $files[] = new CodeFile(
                    Yii::getAlias('@' . str_replace('\\', '/', $this->queryNs)) . '/' . $queryClassName . '.php',
                    $this->render('query.php', $params)
                );
            }
        }

        return $files;
    }

    /**
     * Generates the relation class hints for the relation methods
     * @param array $relations the relation array for single table
     * @param bool $generateQuery generates ActiveQuery class (for ActiveQuery namespace available)
     * @return array
     * @since 2.0.6
     */
    public function generateRelationsClassHints($relations, $generateQuery){
        $result = [];
        foreach ($relations as $name => $relation){
            // The queryNs options available if generateQuery is active
            if ($generateQuery) {
                $queryClassRealName = '\\' . $this->queryNs . '\\' . $relation[1];
                if (class_exists($queryClassRealName, true) && is_subclass_of($queryClassRealName, '\yii\db\BaseActiveRecord')) {
                    /** @var \yii\db\ActiveQuery $activeQuery */
                    $activeQuery = $queryClassRealName::find();
                    $activeQueryClass = get_class($activeQuery);
                    if(strpos($activeQueryClass, $this->ns) === 0){
                        $activeQueryClass = StringHelper::basename($activeQueryClass);
                    }
                    $result[$name] = '\yii\db\ActiveQuery|' . $activeQueryClass;
                } else {
                    $result[$name] = '\yii\db\ActiveQuery|' . (($this->ns === $this->queryNs) ? $relation[1]: '\\' . $this->queryNs . '\\' . $relation[1]) . 'Query';
                }
            } else {
                $result[$name] = '\yii\db\ActiveQuery';
            }
        }
        return $result;
    }

    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if ($this->cubsFieldList[$column->name] ?? false) {
                continue;
            }
            if ($this->generateLabelsFromComments && !empty($column->comment)) {
                $labels[$column->name] = $column->comment;
            } elseif (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3) . ' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }


    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($this->cubsFieldList[$column->name] ?? false) {
                continue;
            }
            if ($column->autoIncrement) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case 'double': // Schema::TYPE_DOUBLE, which is available since Yii 2.0.3
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules = [];
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['" . implode("', '", $columns) . "'], 'default', 'value' => null]";
            }
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);

            if (class_exists($refClassName)) {
                $find = $refClassName::find();
                if ($find::className == 'ActiveQuery') {
                    $this->enableCheckActive = false;
                } else {
                    $refQueryClassName = $find::className;
                }
            } else {
                $refQueryClassName = $refClassName . 'Query';
            }

            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            if ($this->enableCheckActive) {
                $rules[] = "[['$attributes'], 'exist',
                'skipOnError' => true,
                'targetClass' => $refClassName::class,
                'targetAttribute' => [$targetAttributes],
                'filter' => function ($refQueryClassName \$query) {
                    \$query->active();
                }
            ]";
            } else {
                $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::class, 'targetAttribute' => [$targetAttributes]]";
            }
        }

        return $rules;
    }
}
