<?php

namespace app\modules\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $this->layout="layouts1";
        return $this->render('index');
    }
}
