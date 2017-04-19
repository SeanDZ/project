<?php
namespace app\modules\controllers;
use app\models\Profile;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use app\models\User;
class UserController extends Controller
{
    public function actionUsers()
    {
        /**
         * 链表 user profile取数据
         * 分页
         */
        $model=User::find()->joinWith('profile');
        $count=$model->count();
        $pageSize=Yii::$app->params['pageSize']['user'];
        $pager=new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);
        $users=$model->offset($pager->offset)->limit($pager->limit)->all();
        $this->layout='layouts1';
        return $this->render('users',['users'=>$users,'pager'=>$pager]);
    }
    public function actionReg()
    {
        $this->layout='layouts1';
        $model=new User;
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            if($model->reg($post)){
                Yii::$app->session->setFlash('info','添加成功！');
            }
        }
        $model->userpass='';
        $model->repass='';
        return $this->render('reg',['model'=>$model]);
    }
    /**
     * 删除需要表user profile数据同时删除
     * 此处事物操作 保持完整性
     */
    public function actionDel()
    {
        try{
            $userid=(int)Yii::$app->request->get('userid');
            if(empty($userid)){
                throw new \Exception();
            }
            $trans=Yii::$app->db->beginTransaction();
            if($obj=Profile::find()->where('userid=:id',[':id'=>$userid])->one()){
                $res=Profile::deleteAll('userid=:id',[':id'=>$userid]);
                if(empty($res)){
                    throw new \Exception();
                }
            }
            if(!User::deleteAll('userid=:id',[':id'=>$userid])){
                throw new \Exception();
            }
            $trans->commit();
        }catch (\Exception $e){
            if(Yii::$app->db->getTransaction()){
                $trans->rollBack();
            }
        }
        $this->redirect(['user/users']);
    }

}