<?php
namespace app\models;

use yii\db\ActiveRecord;
class Product extends ActiveRecord
{
    const AK = 'LraX1ayfSVzHhYsgru4H7oG4BwdrBWA0Vjd_yTXy';
    const SK = 'l1RDWGN3xDr2ziWlvu4AWmOFD8rho-pU131LP9Q_';
    const DOMAIN = 'on3bxe73e.bkt.clouddn.com';
    const BUCKET = 'myshop';
    public $cate;
    public function rules()
    {
        return [
            ['title','required','message'=>'标题不能为空！'],
            ['descr','required','message'=>'描述不能为空！'],
            ['cateid','required','message'=>'分类不能为空！'],
            ['price','required','message'=>'价格不能为空！'],
            ['num','integer','min'=>0,'message'=>'库存必须是数字！'],
            [['issale','ishot','pics','istui'],'safe'],
            [['cover'],'required'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'cateid' => '分类名称',
            'title'  => '商品名称',
            'descr'  => '商品描述',
            'price'  => '商品价格',
            'ishot'  => '是否热卖',
            'issale' => '是否促销',
            'saleprice' => '促销价格',
            'num'    => '库存',
            'cover'  => '图片封面',
            'pics'   => '商品图片',
            'ison'   => '是否上架',
            'istui'   => '是否推荐',
        ];
    }

    public static function tableName()
    {
        return "{{%product}}";
    }
    public function add($data)
    {
        if ($this->load($data) && $this->save()) {
            return true;
        }
        return false;
    }
}