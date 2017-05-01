<?php
namespace app\modules\controllers;

use app\models\Category;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
class CategoryController extends Controller
{
    public function actionList()
    {
        $this->layout='layouts1';
        $data=Category::find();
        $count=$data->count();
        $pageSize = Yii::$app->params['pageSize']['category'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $all = $data->offset($pager->offset)->limit($pager->limit)->asArray()->all();
        $model=new Category;
        $cates=$model->getTreeList();
        return $this->render('cates',['cates'=>$cates,'pager'=>$pager]);
    }
    public function actionAdd()
    {
        $this->layout='layouts1';
        $model=new Category();
        $list=$model->getOptions();//获取下拉菜单
        $list[0]='添加顶级分类';
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            if($model->add($post)){
                Yii::$app->session->setFlash('info','添加成功！');
            }
        }
        return $this->render('add',['list'=>$list,'model'=>$model]);
    }

    /**
     * 编辑
     */
    public  function actionMod()
    {
        $this->layout='layouts1';
        $cateid=Yii::$app->request->get('cateid');
        $model=Category::find()->where('cateid=:id',[':id'=>$cateid])->one();
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            if($model->load($post) && $model->save()){
                Yii::$app->session->setFlash('info','修改成功！');
            }
        }
        $list=$model->getOptions();//获取下拉菜单
        return $this->render('add',['model'=>$model,'list'=>$list]);
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        try {
            $cateid = Yii::$app->request->get('cateid');
            if (empty($cateid)) {
                throw new \Exception('参数错误');
            }
            $data = Category::find()->where('parentid=:pid', [':pid' => $cateid])->one();
            if ($data) {
                throw new \Exception('有子分类 不能删除！');
            }
            if(!Category::deleteAll('cateid=:id',[':id'=>$cateid])){
                throw new \Exception('删除失败！');
            }
        }catch (\Exception $e){
            Yii::$app->session->setFlash('info',$e->getMessage());
        }
        return $this->redirect(['category/list']);
    }

}