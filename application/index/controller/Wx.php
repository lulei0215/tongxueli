<?php
namespace app\index\controller;
use think\Db;
use think\Session;
use think\Request;
use think\Cookie;
use think\Captcha;
class Wx
{

//获取用户登陆信息
  public function getopenid(){
    $appid = 'wx82fd8a5ab014c63c';
    $secret = '972b7ca42c685331a6b66668f3a229f3';
    $res = $this->postget();
    $code = $res['code'];


    $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
    $res = $this->curlget($url);
    $resdata = json_decode($res,true);



    $key = $resdata['session_key'];

    $privateKey = "1234567812345678";
    $iv     = "1234898223454332";
    $openid = $resdata['openid'];

            //加密
    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $privateKey, $openid, MCRYPT_MODE_CBC, $iv);
    $openid = base64_encode($encrypted);

    $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $privateKey, $key, MCRYPT_MODE_CBC, $iv);
    $key = base64_encode($encrypted);


    $okey = $openid.$key;
            //Session::get('session_key',$okey,300);
    $arr = [
    'data'=>$okey,
    'err_code'=>'000',
    'err_msg'=>'请求成功'
    ];

    echo json_encode($arr);

  }


    //获取用户信息存入数据库
  public function getuser(){
    $res = $this->postget();

    $session_key = $this->gettocken();

    $encryptedData = $res['userinfo']['encryptedData'];
    $iv = $res['userinfo']['iv'];
    $rawdata = $res['userinfo']['rawData'];
                //dump($this->gettocken());

            //解密
    $openid = $this->getoid();

            //$unionid = $this->getunionid();

    $user=[               
    'img'=>$res['userinfo']["userInfo"]['avatarUrl'],
    'openid'=>$openid,
    'name'=>urlencode($res['userinfo']["userInfo"]['nickName']),
    'create_time'=>date('Y-m-d H:i:s')     
    ];



    $status = Db::name('user')->where('openid',$openid)->find();


            if($status){//存在openid

            }else{//不存在openid
            	
             Db::name('user')->where('openid',$openid)->insert($user);

           }

           $arr = [
           'data'=>'',
           'err_code'=>'000',
           'err_msg'=>'请求成功'
           ];     
           echo json_encode($arr);

         }
    //首页
         public function xindex(){
          $res = $this->postget(); 
          $session = $res['session']; 
          $openid = $this->getoid();      
          $user= Db::name('user')->where('openid',$openid)->find();
          $id = $user['id'];
          $nowtime = date('Y-m-d');
          $ss['name'] = ['<=',$nowtime];
          $data =   Db::name('image')->where($ss)->order('name desc')->limit(30)->select();


          $data = array_reverse($data);
          foreach ($data as $key => $value) {

           $collect =  Db::name('collect')->where('img_id',$value['id'])->where('user_id',$id)->find();

           if($collect){
            $data[$key]['collect_status'] = '1';
          }else{
            $data[$key]['collect_status'] = '0';
          }

        }
        $sign =  Db::name('sign')->where('user_id',$id)->where('datetime',$nowtime)->find();
        if($sign){
          $sign_status = '1';
        }else{
          $sign_status = '0';
        }

        $arr = [
        'data'=>['list'=>$data,'sign_status'=>$sign_status,'point'=>$user['point'],'day'=>$user['day']],
        'err_code'=>'000',
        'err_msg'=>'请求成功'
        ];     
                      // $data = array_reverse($data);
                      //   halt($data);
        echo json_encode($arr);



      }




    //签到接口sign
      public function sign(){
       $res = $this->postget(); 
       $session = $res['session']; 


       $openid = $this->getoid();
       $user= Db::name('user')->where('openid',$openid)->find();
       $id = $user['id'];
       $nowtime = date('Y-m-d');
            //签到

            //加积分
 $dd = $user['day'];//当前连续天数

 $zuo = date("Y-m-d",strtotime("-1 day"));
 $zstatus = Db::name('sign')->where('datetime',$zuo)->find();//查询昨天是否签到



 if($zstatus){
    Db::name('user')->where('id',$id)->setInc('day');//是连续签到 就+1天
  }else{
   Db::name('user')->where('id',$id)->update(['day'=>1]);//不是连续签到的话 就是1
 }


//判断签到是否连续

$days =  Db::name('user')->where('id',$id)->find(); //查询
$img = Db::name('image')->where('name',date('Y-m-d'))->find();
$imgid = $img['id'];
$imgpoint = $img['point'];
$type = $img['status'];
//img   status 状态 0 正常签到 1是自定义
$days = $days['day'];             //对应连续签到的天数
if($days < 7){
  $signd = [
  'user_id'=>$id,
  'datetime'=>date('Y-m-d'),
  'create_time'=>date('Y-m-d H:i:s'),
  'status'=>1
  ];
  Db::name('sign')->insert($signd);
  $points   = Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>'1','title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
  Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
  $signs=1;
  $day ='今天是你连续打卡的第'.$days.'天';
  $auser = Db::name('user')->where('id',$id)->find();

  $point = '你目前的总积分为'.$auser['point'];
  $pointnow = 1;
  $nowpoint = $imgpoint;
}else if($days ==7){
  $signd = [
  'user_id'=>$id,
  'datetime'=>date('Y-m-d'),
  'create_time'=>date('Y-m-d H:i:s'),
  'status'=>1
  ];
  Db::name('sign')->insert($signd);
  $typestatus=  Db::name('point')->where('user_id',$id)->where('type',1)->where('point',30)->find();
  if($typestatus){
    $points = 1;
    $signs= Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    $day ='今天是你连续打卡的第'.$days.'天';
    $auser = Db::name('user')->where('id',$id)->find();
    $point = '你目前的总积分为'.$auser['point'];
    
    $pointnow = $imgpoint;
  }else{
    $points =   Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'连续7天','user_id'=>$id,'point'=>30,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>1]);
    $signs= Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    

    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    Db::name('user')->where('id',$id)->setInc('point',30);
    $day ='今天是你连续打卡的第'.$days.'天';
    $point = '你将额外获得30积分';

    $nowpoint = $imgpoint;
  }
  
}else if($days ==30){
  $signd = [
  'user_id'=>$id,
  'datetime'=>date('Y-m-d'),
  'create_time'=>date('Y-m-d H:i:s'),
  'status'=>1
  ];
  Db::name('sign')->insert($signd);
  $typestatus=  Db::name('point')->where('user_id',$id)->where('type',2)->where('point',50)->find();
  if($typestatus){
    $points =  1;
    $signs=  Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    $auser = Db::name('user')->where('id',$id)->find();
    $point = '你目前的总积分为'.$auser['point'];
    $nowpoint = $imgpoint;
   //Db::name('point')->where('user_id')

    $day ='今天是你连续打卡的第'.$days.'天';
  }else{


    $points =    Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'连续30天','user_id'=>$id,'point'=>50,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>2]);
    $signs=  Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    Db::name('user')->where('id',$id)->setInc('point',50);
    $point = '你将额外获得50积分';
    $nowpoint = $imgpoint;
   //Db::name('point')->where('user_id')

    $day ='今天是你连续打卡的第'.$days.'天';
  }

}else if($days ==100){
  $signd = [
  'user_id'=>$id,
  'datetime'=>date('Y-m-d'),
  'create_time'=>date('Y-m-d H:i:s'),
  'status'=>1
  ];
  Db::name('sign')->insert($signd);
  $typestatus=  Db::name('point')->where('user_id',$id)->where('type',3)->where('point',100)->find();
  if($typestatus){
    $points=  1;
    $signs= Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    $auser = Db::name('user')->where('id',$id)->find();
    $point = '你目前的总积分为'.$auser['point'];
    $day ='今天是你连续打卡的第'.$days.'天';
    $nowpoint = $imgpoint;

  }else{
    $points=  Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'连续100天','user_id'=>$id,'point'=>100,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>3]);
    $signs= Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
    Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
    Db::name('user')->where('id',$id)->setInc('point',100);
    $point = '你将额外获得100积分';
    $day ='今天是你连续打卡的第'.$days.'天';
    $nowpoint = $imgpoint;
  }
}else{
  $signd = [
  'user_id'=>$id,
  'datetime'=>date('Y-m-d'),
  'create_time'=>date('Y-m-d H:i:s'),
  'status'=>1
  ];
  Db::name('sign')->insert($signd);
  $points=  1;
  $signs= Db::name('point')->insert(['img_id'=>$imgid,'point_type'=>1,'title'=>'签到积分','user_id'=>$id,'point'=>$imgpoint,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>0]);
  Db::name('user')->where('id',$id)->setInc('point',$imgpoint);
  $auser = Db::name('user')->where('id',$id)->find();

  $point = '你目前的总积分为'.$auser['point'];
  $day ='今天是你连续打卡的第'.$days.'天';
  $nowpoint = $imgpoint;
}


if($signs && $points){

 $arr = [
 'data'=>['point'=>$point,'day'=>$day,'nowpoint'=>$imgpoint],
 'err_code'=>'000',
 'err_msg'=>'请求成功'
 ];     
 echo json_encode($arr);
}else{
  $arr = [
  'data'=>'',
  'err_code'=>'002',
  'err_msg'=>'请求失败'
  ];     
  echo json_encode($arr);
}

}

    //收藏接口collect
public function collect(){
 $res = $this->postget(); 
 $session = $res['session'];    
 $openid = $this->getoid();
 $user= Db::name('user')->where('openid',$openid)->find();
 $id = $user['id'];
 $img_id = $res['id'];
 $data=[
 'user_id'=>$id,
 'img_id'=>$res['id'],
 'create_time'=>date('Y-m-d H:i:s')
 ];
            //首先判断用户有没有收藏
 $status = Db::name('collect')->where('user_id',$id)->where('img_id',$img_id)->find();

 if($status){

   $ss =  Db::name('collect')->where('id',$status['id'])->delete();
   $collect = Db::name('image')->where('id',$img_id)->setDec('collect');
   if($ss && $collect){
    $arr = [
    'data'=>'',
    'err_code'=>'000',
    'err_msg'=>'取消成功'
    ];     
    echo json_encode($arr);
  }else{
    $arr = [
    'data'=>'',
    'err_code'=>'002',
    'err_msg'=>'请求失败'
    ];     
    echo json_encode($arr);
  }
}else{
  $collect = Db::name('image')->where('id',$img_id)->setInc('collect');
  $collects =  Db::name('collect')->insert($data);
  if($collects && $collect){
    $arr = [
    'data'=>'',
    'err_code'=>'000',
    'err_msg'=>'添加成功'
    ];     
    echo json_encode($arr);
  }else{
    $arr = [
    'data'=>'',
    'err_code'=>'002',
    'err_msg'=>'请求失败'
    ];     
    echo json_encode($arr);
  }
}


}


    //个人中心 我的信息my
public function my(){

  $res = $this->postget(); 
  $session = $res['session'];    
  $openid = $this->getoid();
  $user= Db::name('user')->where('openid',$openid)->find();

  $arr = [
  'data'=>$user,
  'err_code'=>'000',
  'err_msg'=>'请求成功'
  ];     
  echo json_encode($arr);



}
        //个人中心 我的收藏mycollect
public function mycollect(){

  $res = $this->postget(); 
  $session = $res['session'];    
  $openid = $this->getoid();
  $user= Db::name('user')->where('openid',$openid)->find();
  $id = $user['id'];

  $collect = Db::view('collect','*')
  ->view('image','url,name','image.id = collect.img_id')
  ->where('user_id',$id)
  ->order('name desc')
  ->select();
  $arr = [
  'data'=>$collect,
  'err_code'=>'000',
  'err_msg'=>'请求成功'
  ];     
  echo json_encode($arr);



}
        //个人中心 我的积分mypoint
public function mypoint(){

  $res = $this->postget(); 
  $session = $res['session'];    
  $openid = $this->getoid();
  $user= Db::name('user')->where('openid',$openid)->find();
  $id = $user['id'];
  $point = Db::name('point')->where('user_id',$id)->order('create_time desc')->select();
  $points = $user['point'];
 
 $arr = [
 'data'=>['list'=>$point,'points'=>$points],
 'err_code'=>'000',
 'err_msg'=>'请求成功'
 ];     
 echo json_encode($arr);


}


    //template 我的创作
public function template(){
 $res = $this->postget(); 
 $session = $res['session'];    
 $openid = $this->getoid();
 $user= Db::name('user')->where('openid',$openid)->find();
 $template = Db::name('contribute')->where('user_id',$user['id'])->order('create_time desc')->select();
 $arr = [
 'data'=>$template,
 'err_code'=>'000',
 'err_msg'=>'请求成功'
 ];     
 echo json_encode($arr);

}
//进入投稿页面
public function jrcontribute(){
 $res = $this->postget(); 
 $session = $res['session'];    
 $openid = $this->getoid();
 $user= Db::name('user')->where('openid',$openid)->find();
 $da = date('Y-m-d');
 $day = $this->get_days($da);
 if(strlen($day) ==2){
  $day = '0'.$day;
}else if(strlen($day) ==1){
  $day = '00'.$day;
}else{

}
$mon = date('F');
$mons = date('m');
$days = date('d');
$template = Db::name('template')->where('id',1)->find();
$year = date('Y');
$data=['nickname'=>$user['name'],'touxiang'=>$user['img'],'tian'=>$day,'year'=>$year,'mon'=>$mons,'eg_mon'=>$mon,'day'=>$days,'mimg'=>$template['content']];
$arr = [
'data'=>$data,
'err_code'=>'000',
'err_msg'=>'请求成功'
];     
echo json_encode($arr);


}


function resize_img($url,$path='./'){
  $imgname = $path.uniqid().'.jpg';
  $file = $url;
    list($width, $height) = getimagesize($file); //获取原图尺寸
    $percent = (110/$width);
    //缩放尺寸
    $newwidth = $width * $percent;
    $newheight = $height * $percent;
    $src_im = imagecreatefromjpeg($file);
    $dst_im = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    imagejpeg($dst_im, $imgname); //输出压缩后的图片
    imagedestroy($dst_im);
    imagedestroy($src_im);
    return $imgname;
  }

  function test($url,$path='./'){  
        $w = 110;  $h=110; // original size  
        $original_path= $url;  
        $dest_path = $path.uniqid().'.png';  
        $src = imagecreatefromstring(file_get_contents($original_path));  
        $newpic = imagecreatetruecolor($w,$h);  
        imagealphablending($newpic,false);  
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);  
        $r=$w/2;  
        for($x=0;$x<$w;$x++)  
          for($y=0;$y<$h;$y++){  
            $c = imagecolorat($src,$x,$y);  
            $_x = $x - $w/2;  
            $_y = $y - $h/2;  
            if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){  
              imagesetpixel($newpic,$x,$y,$c);  
            }else{  
              imagesetpixel($newpic,$x,$y,$transparent);  
            }  
          }  
          imagesavealpha($newpic, true);  
          imagepng($newpic, $dest_path);  
          imagedestroy($newpic);  
          imagedestroy($src);  
       // unlink($url);  
          return $dest_path;  
        }  


//获取当前是年的第几天
        public function get_days($date)
        {
          $date_start= strtotime(date('Ymd',strtotime($date)));
          $year_start = strtotime(date('Y0101',strtotime($date)));
          return ( $date_start- $year_start )/86400 + 1;
        }
    //投稿
        public function contribute(){

         $file = request()->file('pic');
         $post = request()->post();

         $info = $file->validate(['size' => 156780000000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/uploads');

         $pic= 'http://'.$_SERVER['HTTP_HOST'].'/tongxueli/public/uploads/'.$info->getSaveName();
         $pic =  str_replace("\\","/",$pic);



         $session = $post['seccion'];    

         $openid=substr($session , 0 , 44);

            //解密
         $encryptedData = base64_decode($openid);
         $privateKey = "1234567812345678";
         $iv     = "1234898223454332";
         $openid = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);
         $openid =  trim($openid);

         $user= Db::name('user')->where('openid',$openid)->find();





         $data =[
         'user_id'=>$user['id'],
         'url'=>$pic,
         'create_time'=>date('Y-m-d H:i:s'),
         'status'=>0,
         'user_name'=>$user['name']
         ];
         $ids =   Db::name('contribute')->insertGetid($data);
         if($ids){

           echo json_encode($ids);
         }else{
           $arr = [
           'data'=>'',
           'err_code'=>'002',
           'err_msg'=>'请求失败'
           ];     
           echo json_encode($arr);
         }


       }
//一键投稿
       public function ycontribute(){

        $res = $this->postget(); 
        $session = $res['session'];    
        $openid = $this->getoid();
        $user= Db::name('user')->where('openid',$openid)->find();
        $id = $user['id'];
   //$pic = $res['pic'];
        $fid = $res['formId'];
        $pid = substr($res['id'],1,4);
        $contribute =   Db::name('contribute')->where('id',$pid)->update(['status'=>1,'formid'=>$fid]);
        if($contribute){
         $arr = [
         'data'=>'',
         'err_code'=>'000',
         'err_msg'=>'请求成功'
         ];     
         echo json_encode($arr);
       }else{
         $arr = [
         'data'=>'',
         'err_code'=>'002',
         'err_msg'=>'请求失败'
         ];     
         echo json_encode($arr);
       }





     }
//我的创作
     public function creation(){

      $res = $this->postget(); 
      $session = $res['session'];    
      $openid = $this->getoid();
      $user= Db::name('user')->where('openid',$openid)->find();
      $id = $user['id'];

      $contribute =   Db::name('contribute')->where('user_id',$id)->order('create_time desc')->select();

      $arr = [
      'data'=>$contribute,
      'err_code'=>'000',
      'err_msg'=>'请求成功'
      ];     
      echo json_encode($arr);
    }
//商品表
    public function goods(){

      $res = $this->postget(); 
      $session = $res['session'];    
      $openid = $this->getoid();
      $goods= Db::name('goods')->select();

      $arr = [
      'data'=>$goods,
      'err_code'=>'000',
      'err_msg'=>'请求成功'
      ];     
      echo json_encode($arr);
    }
    public function exchange(){
     $res = $this->postget(); 
     $session = $res['session'];    
     $openid = $this->getoid();
     $user= Db::name('user')->where('openid',$openid)->find();

     $id = $res['id'];




   }
//订单中心
   public function orders(){

     $res = $this->postget(); 
     $session = $res['session'];    
     $openid = $this->getoid();
     $user= Db::name('user')->where('openid',$openid)->find();
 //$user= Db::name('user')->where('id',3)->find();

     $orders = Db::view('order','*')
     ->view('goods','*','goods.id = order.goods_id')
     ->where('order.user_id',$user['id'])
     ->order('order.create_time desc')
     ->select();
     $arr = [
     'data'=>$orders,
     'err_code'=>'000',
     'err_msg'=>'请求成功'
     ];     
     echo json_encode($arr);


   }
//点击兑换 商品详情
   public function clickdh(){
     $res = $this->postget(); 
     $session = $res['session'];    
     $openid = $this->getoid();
     $user= Db::name('user')->where('openid',$openid)->find();

     $goodsid = $res['id'];
     $goods = Db::name('goods')->where('id',$goodsid)->find();
     $order = Db::name('order')->field('id,dress,phone,name')->where('user_id',$user['id'])->find();

     if($order){
      $goods['addressinfo']=['address'=>$order['dress'],'phone'=>$order['phone'],'name'=>$order['name']];
    }else{
      $goods['addressinfo']='';
    }
    $arr = [
    'data'=>$goods, 
    'err_code'=>'000',
    'err_msg'=>'请求成功'
    ];     
    echo json_encode($arr);

  }

//最后兑换
  public function order(){
    $res = $this->postget(); 
    $session = $res['session'];    
    $openid = $this->getoid();
    $goodsid = $res['id'];

    $goods= Db::name('goods')->where('id',$goodsid)->find();
    $user= Db::name('user')->where('openid',$openid)->find();
    $id = $user['id'];

    $goodspoint = $goods['point'] * $res['num'];

    if($goodspoint > $user['point']){
      $arr = [
      'data'=>[],
      'err_code'=>'002',
      'err_msg'=>'积分不足'
      ];     
      echo json_encode($arr);
      die();
    }

    $orderid = date('ymdhi', time()) . rand(100,999).substr(microtime(true),11,15);
    $data = [
    'orderid'=>$orderid,
    'user_id'=>$user['id'],
    'goods_id'=>$goodsid,
    'num'=>$res['num'],
    'point'=>$goods['point'],
    'dress'=>$res['addressinfo']['address'],
    'name'=>$res['addressinfo']['name'],
    'phone'=>$res['addressinfo']['phone'],
    'create_time'=>date('Y-m-d H:i:s')
    ];
    $pp =[
    'create_time'=>date('Y-m-d H:i:s'),
    'point_type'=>0,
    'point'=>$goodspoint,
    'title'=>'兑换礼品',
    'type'=>6,
    'user_id'=>$user['id']
    ];
    $points = Db::name('user')->where('id',$id)->setDec('point',$goodspoint);
    $pstatus = Db::name('point')->insert($pp);
    $order = Db::name('order')->insert($data);
    if($order && $points && $pstatus){
     $arr = [
     'data'=>[],
     'err_code'=>'000',
     'err_msg'=>'请求成功'
     ];     
     echo json_encode($arr);
   }else{
     $arr = [
     'data'=>[],
     'err_code'=>'002',
     'err_msg'=>'请求失败'
     ];     
     echo json_encode($arr);


   }

 }

//投稿



	/**
 * GET方式获取远程url数据
 * @param $url 远程地址
 * @param $data GET数据
 * @return url返回数据
 */
  function curlget($url,$data=""){
    $ch=curl_init();
    $header[]="Accept-Charset: utf-8";
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"GET");
    curl_setopt($ch,CURLOPT_TIMEOUT,500);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (compatible; MSIE 5.01;Window NT 5.0)');
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch,CURLOPT_AUTOREFERER,1);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HEADER,0);
    $temp=curl_exec($ch);
    curl_close($ch);
    return $temp;
  }

   //获取小程序发送数据
  public function postget(){
    $res = json_decode(file_get_contents("php://input"),true);
    return $res;
  }

  public function gettocken(){
    $res = $this->postget(); 
    $session = $res['session']; 

    $tocken=substr($session , 44 , 44);
            //解密
    $encryptedData = base64_decode($tocken);
    $privateKey = "1234567812345678";
    $iv     = "1234898223454332";
    $tocken = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);
    return trim($tocken);
  }

  public function getoid(){
    $res = $this->postget(); 
    $session = $res['session']; 

    $openid=substr($session , 0 , 44);
            //解密
    $encryptedData = base64_decode($openid);
    $privateKey = "1234567812345678";
    $iv     = "1234898223454332";
    $openid = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);
    return trim($openid);
  }





}