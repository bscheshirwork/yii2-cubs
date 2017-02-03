#CUBS

Для создания однотипных групп полей таблиц был создан набор инструментов:

1.Интерфейс `bscheshirwork\cubs\base\CubsDefaultInterface` с определением набора полей. 
Данный или аналогичный интерфейс должны реализовать все базовые классы, которые будут использовать данный набор инструментов.

2.Трейт `bscheshirwork\cubs\base\CubsTrait` реализующий весь необходимый функционал доступа, изменения, установки поведений, 
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