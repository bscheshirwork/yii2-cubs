#CUBS

Для создания однотипных групп полей таблиц был создан набор инструментов:

1.Интерфейс `bscheshirwork\cubs\base\CubsDefaultInterface` с определением набора полей. 
Данный или аналогичный интерфейс должны реализовать все базовые классы, которые будут использовать данный набор инструментов.

2.Трейт `bscheshirwork\cubs\base\CubsModelTrait` реализующий весь необходимый функционал доступа, изменения, установки поведений, 
основных функций модели `ActiveRecord`

3.Трейт `bscheshirwork\cubs\db\CubsMigrationTrait` для использования в миграциях. Добавляет в команду создания таблицы набор полей.

4.Шаблон генератора `model`
```
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'generators' => [
            'model' => [ // generator name
                'class' => 'bscheshirwork\cubs\generators\crud\Generator',
                'templates' => [
                    'cubs' => '@bscheshirwork/cubs/generators/model/cubs', // template name => path to template
                ]
            ]
        ],
    ];
```

5.Перевод `i18n`, ключ 'cubs': `Yii::t('cubs', 'Message')`. 

6.Хелпер для использования в виджетах `\bscheshirwork\cubs\helpers\WidgetHelper`, содержащий набор декораторов для работы с полями cubs

7.Трейт для конструктора запросов `bscheshirwork\cubs\base\CubsQueryModelTrait`, содержащий проверку интерфейса и 
реализацию проверки "активно". Проверка может быть вызвана в цепочке, а также для нескольких влияющих моделей.
```
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
        return $this->andWhere(($tablePrefix ?: ($this->modelClass)::tableName()) . '.[[' . ($this->modelClass)::FIELD_STATE . ']]=1');
    }

```
