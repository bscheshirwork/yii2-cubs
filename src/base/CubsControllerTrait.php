<?php

namespace bscheshirwork\cubs\base;

/**
 * Create Update Block and Status attribute composition
 *
 * Class CubsControllerTrait
 * @package bscheshirwork\cubs\base
 */
trait CubsControllerTrait
{
    /**
     * Blocked an existing model.
     * If block is successful, the browser will be redirected to the 'view' page.
     * If block is fail, the browser will be render to the 'update' page.
     * @param integer $id
     * @return mixed
     */
    public function actionBlock($id)
    {
        $model = $this->findModel($id);
        $model->block();
        if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Unblocked an existing model.
     * If unblock is successful, the browser will be redirected to the 'view' page.
     * If unblock is fail, the browser will be render to the 'update' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUnblock($id)
    {
        $model = $this->findModel($id);
        $model->unblock();
        if ($model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

}