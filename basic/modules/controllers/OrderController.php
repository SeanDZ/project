<?php
namespace app\modules\controllers;

use app\controllers\CommonController;
use app\models\Order;
use Yii;
use yii\data\Pagination;;
class OrderController extends CommonController
{
    public function actionList()
    {
        $this->layout='layouts1';
        $model=Order::find();
        $count=$model->count();
        $pageSize=Yii::$app->params['pageSize']['order'];
        $pager=new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);
        $data=$model->offset($pager->offset)->limit($pager->limit)->all();
        $orders = Order::getDetail($data);
        return $this->render('list', ['pager' => $pager, 'orders' => $orders]);
    }
    public function actionDetail()
    {
        $this->layout='layouts1';
        $orderid=Yii::$app->request->get('orderid');
        $order=Order::find()->where('orderid=:oid',[':oid'=>$orderid])->one();;
        $data=Order::getData($order);
        return $this->render('detail',['order'=>$data]);
    }
    public function actionSend()
    {
        $this->layout='layouts1';
        $orderid=(int)Yii::$app->request->get('orderid');
        $model=Order::find()->where('orderid=:oid',[':oid'=>$orderid])->one();
        /**
         * 验证
         */
        $model->scenario='send';
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            $model->status = Order::SENDED;
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '发货成功');
            }
        }
        return $this->render('send',['model'=>$model]);
    }

}
