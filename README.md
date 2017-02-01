# yii2-cubs
Trait for AR. Include [create|update][At|By], flags and blockAt fields

##NO MORE ambigous field list!

Many similar AR classes? Already have the specific parent class? 


Need `BlameableBehavior` and `BlameableBehavior` for anyone it?

OK.

Example `migration` create table
```
<?php

use bscheshirwork\yii2-cubs\base\CubsDefaultInterface;
use bscheshirwork\yii2-cubs\db\CubsMigrationTrait;
use yii\db\Migration;

class m170130_100000_createProjectTables extends Migration implements CubsDefaultInterface
{
    use CubsMigrationTrait;

    public function up()
    {
        $this->createTable('{{%project}}', [
           'id' => $this->primaryKey(),
           'name' => $this->string(64)->notNull(),
           'url' => $this->text(),
           'description' => $this->text(),
        ]);
        $this->createIndex('{{%project_unique_name}}', '{{%project}}', 'name', true);

        $this->createTable('{{%project_form}}', [
            'id' => $this->primaryKey(),
            'projectId' => $this->integer(),
            'type' => $this->string(32)->notNull(),
            'name' => $this->string(64)->null(),
            'url' => $this->text(),
            'description' => $this->text(),
        ]);
        $this->addForeignKey('{{%fk_project_project_form}}', '{{%project_form}}', 'projectId', '{{%project}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%project_form}}');
        $this->dropTable('{{%project}}');
    }
}

```


Produced schema like this
```
CREATE TABLE `project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `url` text,
  `description` text,
  `createdAt` datetime NOT NULL,
  `createdBy` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `updatedBy` datetime DEFAULT NULL,
  `stateOfFlags` int(11) NOT NULL DEFAULT '1',
  `blockedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_unique_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

CREATE TABLE `project_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) DEFAULT NULL,
  `type` varchar(32) NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `url` text,
  `description` text,
  `createdAt` datetime NOT NULL,
  `createdBy` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `updatedBy` datetime DEFAULT NULL,
  `stateOfFlags` int(11) NOT NULL DEFAULT '1',
  `blockedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_project_project_form` (`projectId`),
  CONSTRAINT `fk_project_project_form` FOREIGN KEY (`projectId`) REFERENCES `project` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1
```



Example `gii`: 
settings `config/main-local.php`
```
...
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
        'generators' => [
            'model' => [ // generator name
                'class' => 'bscheshirwork\yii2-cubs\generators\model\Generator', // generator class
                'templates' => [
                    'cubs' => '@bscheshirwork/yii2-cubs/generators/model/cubs', // template name => path to template
                ]
            ]
        ],
    ];

...
```
Model Generator `gii/model` have new field;  
check `Cubs`, set `Cubs interface` and select `cubs` template.

Generated the `Project` model like this
```
<?php

namespace app\models;

use Yii;
use Yii\helpers\ArrayHelper;
use bscheshirwork\yii2-cubs\base\CubsTrait;

/**
 * This is the model class for table "{{%project}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $url
 * @property string $description
 * @property string $createdAt
 * @property string $createdBy
 * @property string $updatedAt
 * @property string $updatedBy
 * @property integer $stateOfFlags
 * @property string $blockedAt
 *
 * @property ProjectForm[] $projectForms
 */
class Project extends \yii\db\ActiveRecord implements \bscheshirwork\yii2-cubs\base\CubsDefaultInterface
{
    use CubsTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%project}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['name'], 'required'],
            [['url', 'description'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'url' => Yii::t('app', 'Url'),
            'description' => Yii::t('app', 'Description'),
        ]);
    }

    /**
    * @inheritdoc
    */
    public function hints()
    {
        return ArrayHelper::merge(parent::hints(), [
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectForms()
    {
        return $this->hasMany(ProjectForm::className(), ['projectId' => 'id']);
    }

    /**
     * @inheritdoc
     * @return ProjectQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProjectQuery(get_called_class());
    }
}

```

#Customize

Create your own interface and use it.
`my\base\CubsDefaultInterface.php`
```
<?php

namespace my\cubs\base;

interface CubsDefaultInterface
{
    const FIELD_CREATE_AT = 'created_at';
    const FIELD_CREATE_BY = 'created_by';
    const FIELD_UPDATE_AT = 'updated_at';
    const FIELD_UPDATE_BY = 'updated_by';
    const FIELD_BLOCKED_AT = 'blocked_at';
    const FIELD_STATE = 'status';
    const STATE_DISABLED = 0b00;
    const STATE_ENABLED = 0b01;
    const STATE_BLOCKED = 0b10;
    const STATE_PROCESS = 0b100;
    const LIST_STATE = [
        self::STATE_DISABLED => 'DISABLED',
        self::STATE_ENABLED => 'ACTIVE',
        self::STATE_BLOCKED => 'BLOCKED',
        self::STATE_PROCESS => 'PROCESS',
    ];
}
```

The migrations for the existing tables must be created manually. 


[Info](docs/cubs.md)

#Installation
Add to you `require` section `composer.json`
```
"bscheshirwork/yii2-cubs": "*",
```
