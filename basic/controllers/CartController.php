<?php
namespace app\controllers;

use app\models\Product;
use app\controllers\CommonController;
use Yii;
use app\models\User;
use app\models\Cart;
class CartController extends CommonController
{
    public function actionIndex()
    {
        /**
         * 遍历购物车
         */
        if(Yii::$app->session['isLogin'] != 1){
            return $this->redirect(['member/auth']);
        }
        $userid=User::find()
                ->where('username=:name',[':name'=>Yii::$app->session['loginname']])
                ->one();
        $cart=Cart::find()->where('userid=:uid',[':uid'=>$userid])->asArray()->all();
        $data=[];
        foreach($cart as $k=>$pro){
            $product=Product::find()->where('productid=:pid',[':pid'=>$pro['productid']])->one();
            $data[$k]['cover']=$product->cover;
            $data[$k]['title']=$product->title;
            $data[$k]['productnum']=$pro['productnum'];
            $data[$k]['price']=$pro['price'];
            $data[$k]['productid']=$pro['productid'];
            $data[$k]['cartid']=$pro['cartid'];
        }
        $this->layout="layouts1";
        return $this->render('index',['data'=>$data]);
    }
    public function actionAdd()
    {
        /**
         * 商品id 商品数量 单价 用户id
         * 判断用户是否登录
         */
        //登录不完整 不能插入cart数据表
        if(Yii::$app->session['isLogin'] != 1){
            return $this->redirect(['member/auth']);
        }
        $userid=User::find()
            ->where('username=:name',[':name'=>Yii::$app->session['loginname']])
            ->one();
        if(Yii::$app->request->isPost){
            $post=Yii::$app->request->post();
            $num=Yii::$app->request->post()['productnum'];
            $data['Cart']=$post;
            $data['Cart']['userid']=$userid;
        }
        if(Yii::$app->request->isGet){
            $productid=Yii::$app->request->get('productid');
            $model=Product::find()
                ->where('productid=:pid',[':pid'=>$productid])
                ->one();
            $price=$model->issale?$model->saleprice:$model->price;
            $num=1;
            $data['Cart']=['productid'=>$productid,'productnum'=>$num,'price'=>$price,'userid'=>$userid];
        }
        /**
         * 判断购物车是否有该商品  有及更新数量 没有插入
         */
        if (!$model = Cart::find()->where('productid = :pid and userid = :uid', [':pid' => $data['Cart']['productid'], ':uid' => $data['Cart']['userid']])->one()) {
            $model = new Cart;
        } else {
            $data['Cart']['productnum'] = $model->productnum + $num;
        }
        $data['Cart']['createtime'] = time();
        $model->load($data);
        $model->save();
        return $this->redirect(['cart/index']);
    }

    public function actionMod()
    {
        $cartid = Yii::$app->request->get("cartid");
        $productnum = Yii::$app->request->get("productnum");
        Cart::updateAll(['productnum' => $productnum], 'cartid = :cid', [':cid' => $cartid]);
    }
    public function actionDel()
    {
        $cartid = Yii::$app->request->get("cartid");
        Cart::deleteAll('cartid = :cid', [':cid' => $cartid]);
        return $this->redirect(['cart/index']);
    }
}