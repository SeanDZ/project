<?php
namespace app\modules\controllers;

use Yii;
use yii\web\Controller;
use app\modules\models\Admin;
use yii\data\Pagination;
class ManageController extends Controller
{
    public function actionMailchangepass()
    {
        $this->layout = false;
        $time = Yii::$app->request->get("timestamp");
        $adminuser = Yii::$app->request->get("adminuser");
        $token = Yii::$app->request->get("token");
        $model = new Admin;
        $myToken = $model->createToken($adminuser, $time);
        //对比token
        if ($token != $myToken) {
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        //设置邮件300秒内有效
        if (time() - $time > 300) {
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        //修改密码 changepass方法=>Admin.php
        /**
         * 先判断是否有post数据提交
         * 有则接收数据
         * 再验证数据reg方法在admin.php
         */
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->changePass($post)) {
                Yii::$app->session->setFlash('info', '密码修改成功');
            }
        }
        $model->adminuser = $adminuser;
        return $this->render("mailchangepass", ['model' => $model]);
    }

    public function actionManagers()
    {
        $this->layout="layouts1";
        /**
         * 分页处理
         * $managers=Admin::find()->all();
         * 配置文件params.php,配置pageSize页数
         */
        $model=Admin::find();
        $count=$model->count();
        $pageSize=Yii::$app->params['pageSize']['manage'];
        $pager=new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);
        $managers=$model->offset($pager->offset)->limit($pager->limit)->all();
        return $this->render("managers",['managers'=>$managers,'pager'=>$pager]);
    }
    public function actionReg()
    {
        $this->layout = 'layouts1';
        $model = new Admin;
        /**
         * 先判断是否有post数据提交
         * 有则接收数据
         * 再验证数据reg方法在admin.php
         */
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->reg($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            } else {
                Yii::$app->session->setFlash('info', '添加失败');
            }
        }
        $model->adminpass = '';//清除添加成功后的密码
        $model->repass = '';
        return $this->render('reg', ['model' => $model]);
    }
    /**
     * 删除
     */
    public function actionDel()
    {
        $adminid=(int)Yii::$app->request->get('adminid');
        if(empty($adminid)){
            $this->redirect(['manage/managers']);
        }
        $model = new Admin;
        if ($model->deleteAll('adminid = :id', [':id' => $adminid])) {
            Yii::$app->session->setFlash('info', '删除成功');
            $this->redirect(['manage/managers']);
        }
    }

    public function actionChangeemail()
    {
        $this->layout = 'layouts1';
        $model = Admin::find()
            ->where('adminuser = :user', [':user' => Yii::$app->session['admin']['adminuser']])
            ->one();
        /**
         * 先判断是否有post数据提交
         * 有则接收数据
         * 再验证数据reg方法在admin.php
         */
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($model->changeemail($post)) {
                Yii::$app->session->setFlash('info', '修改成功');
            }
        }
        $model->adminpass = "";
        return $this->render('changeemail', ['model' => $model]);
    }

    public function actionChangepass()
    {
        $this->layout='layouts1';
        $model=Admin::find()
            ->where('adminuser = :user', [':user' => Yii::$app->session['admin']['adminuser']])
            ->one();
//        var_dump($model);
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            if($model->changepass($post)){
                Yii::$app->session->setFlash('info','修改成功！');
            }
        }
        $model->adminpass='';
        $model->repass='';
        return $this->render('changepass',['model'=>$model]);
    }

}