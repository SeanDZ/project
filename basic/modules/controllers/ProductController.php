<?php
namespace app\modules\controllers;

use app\models\Category;
use app\models\Product;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use crazyfd\qiniu\Qiniu;
class ProductController extends Controller
{
    public function actionList()
    {
        /**
         * 分页显示商品列表
         */
        $model = Product::find();
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['product'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $products = $model->offset($pager->offset)->limit($pager->limit)->all();
        $this->layout = "layouts1";
        return $this->render("products", ['pager' => $pager, 'products' => $products]);
    }

    public function actionAdd()
    {
        $this->layout = "layouts1";
        $model = new Product;
        $cate = new Category;
        $list = $cate->getOptions();
        unset($list[0]);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
//            $pics = $this->upload();
            $pics=[];
            if (!$pics) {
                $model->addError('cover', '封面不能为空');
            } else {
                $post['Product']['cover'] = $pics['cover'];
                $post['Product']['pics'] = $pics['pics'];
            }
            if ($pics && $model->add($post)) {
                Yii::$app->session->setFlash('info', '添加成功');
            } else {
                Yii::$app->session->setFlash('info', '添加失败');
            }
        }
        return $this->render("add", ['list' => $list, 'model' => $model]);
    }
/**
    private function upload()
    {
        if ($_FILES['Product']['error']['cover'] > 0) {
            return false;
        }
        $qiniu = new Qiniu(Product::AK, Product::SK, Product::DOMAIN, Product::BUCKET);
        $key = uniqid();
        $qiniu->uploadFile($_FILES['Product']['tmp_name']['cover'], $key);
        $cover = $qiniu->getLink($key);
        $pics = [];
        foreach ($_FILES['Product']['tmp_name']['pics'] as $k => $file) {
            if ($_FILES['Product']['error']['pics'][$k] > 0) {
                continue;
            }
            $key = uniqid();
            $qiniu->uploadFile($file, $key);
            $pics[$key] = $qiniu->getLink($key);
        }
        return ['cover' => $cover, 'pics' => json_encode($pics)];
    }
 * **/

    /**
     * 编辑
     * 七牛图片省略
     */
    public function actionMod()
    {
        $this->layout='layouts1';
        $cate=new Category;
        $list=$cate->getOptions();
        unset($list[0]);
        $productid=Yii::$app->request->get('productid');
        $model=Product::find()->where('productid=:id',[':id'=>$productid])->one();
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            //图片省略
            if($model->load($post) && $model->save()){
                Yii::$app->session->setFlash('info','修改成功1');
            }
        }
        return $this->render('add',['model'=>$model,'list'=>$list]);
    }
    public function actionDel()
    {
        $productid=Yii::$app->request->get('productid');
        $model=Product::find()->where('productid=:id',[':id'=>$productid])->one();
        /**
         * 七牛图片不删除
         */
        Product::deleteAll('productid=:pid',[':pid'=>$productid]);
        return $this->redirect(['product/list']);
    }


    public function actionOn()
    {
        $productid = Yii::$app->request->get("productid");
        Product::updateAll(['ison' => '1'], 'productid = :pid', [':pid' => $productid]);
        return $this->redirect(['product/list']);
    }
    public function actionOff()
    {
        $productid = Yii::$app->request->get("productid");
        Product::updateAll(['ison' => '0'], 'productid = :pid', [':pid' => $productid]);
        return $this->redirect(['product/list']);
    }
}