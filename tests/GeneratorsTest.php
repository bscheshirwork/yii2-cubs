<?php

namespace bscheshirwork\cubs\tests\unit;


/**
 * GeneratorsTest checks that Gii generators aren't throwing any errors during generation
 * @group gii
 */
class GeneratorsTest extends GiiTestCase
{

    public function testModelGenerator()
    {
        $generator = new \bscheshirwork\cubs\generators\model\Generator();
        $generator->templates = [
            // https://github.com/yiisoft/yii2-gii/issues/295
            // template name => alias + path to template
            'default' => \Yii::getAlias('@bscheshirwork/cubs/generators/model/cubs'),
        ];
        $generator->template = 'default';
        $generator->tableName = 'profile';
        $generator->modelClass = 'Profile';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $files = $generator->generate();
        $modelCode = $files[0]->content;

        $this->assertTrue(strpos($modelCode, "'id' => 'ID'") !== false, "ID label should be there:\n" . $modelCode);
        $this->assertTrue(strpos($modelCode, "'description' => 'Description',") !== false,
            "Description label should be there:\n" . $modelCode);
    }

    public function testCRUDGenerator()
    {
        $generator = new \bscheshirwork\cubs\generators\crud\Generator();
        $generator->templates = [
            'default' => \Yii::getAlias('@bscheshirwork/cubs/generators/crud/cubs'),
        ];
        $generator->template = 'default';
        $generator->modelClass = 'bscheshirwork\cubs\tests\unit\Profile';
        $generator->controllerClass = 'app\TestController';

        $valid = $generator->validate();
        $this->assertTrue($valid, 'Validation failed: ' . print_r($generator->getErrors(), true));

        $this->assertNotEmpty($generator->generate());
    }
}
