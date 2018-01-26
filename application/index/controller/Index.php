<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Request;
use think\Cookie;
use think\Captcha;
class Index extends Controller
{

  public function logout(){
    Session::clear();
    $this->redirect('index/Index/login');
  }
  public function admin(){

    $data = Db::name('admin')->select();
    return view('admin',['data'=>$data]);
  }
  public function admins(){

    $data = Db::name('user')->select();
    foreach ($data as $key => $value) {
      $data[$key]['name'] = urldecode($value['name']);
    }
    return view('admins',['data'=>$data]);
  }
  public function adminsedit(){
    $post = request()->post();
    $get = request()->get();
    if(request()->iSpost()){
      $id = $post['id'];
      $point = $post['point'];
      $stat = Db::name('user')->where('id',$id)->setInc('point',$point);
      $points   = Db::name('point')->insert(['img_id'=>0,'point_type'=>'666','point_type'=>1,'title'=>'系统添加','user_id'=>$id,'point'=>$point,'datetime'=>date('Y-m-d'),'create_time'=>date('Y-m-d H:i:s'),'type'=>6]);
      if($stat && $points){

        $this->redirect('index/Index/admins');

      }else{

        $this->redirect('index/Index/admins');
      }
    }else{
      $id= $get['id'];
      $data = Db::name('user')->where('id',$id)->find();
      return view('adminsedit',['data'=>$data]);
    }
  }
  public function adminedit(){
    $get = request()->get();
    $post = request()->post();
    if(request()->iSpost()){
      $password = substr(md5($post['password']), 8, 16);

      $ustatus = Db::name('admin')->where('username','admin')->update(['password'=>$password,'create_time'=>date('Y-m-d H:i:s')]);
      if($ustatus){
        return $this->redirect('index/Index/admins');
      }else{
        return $this->redirect('index/Index/adminedit');
      }
    }else{
      $data = Db::name('admin')->where('id',$get['id'])->find();
      return view('adminedit',['data'=>$data]);
    }


  }

  public function login(){
    if(Session::has('username')){
      $username = Session::get('username');
      return view('index',['username'=>$username]);
    }
    if(request()->isPost()){
      $post = request()->post();
      $captcha = new \think\captcha\Captcha;
      $code=$post['captcha'];
      $captcha = new \think\captcha\Captcha();
      $a = $captcha->check($code);
      if($a == $code){
        $data = Db::name('admin')->where('username',$post['username'])->find();  
        if($data){
          $sta = ($data['password'] == substr(md5($post['password']), 8, 16));
          if($sta){
            Session::set('username',$post['username']);
            return view('index',['username'=>$post['username']]);
          }else{
                                //密码错误
            $data=['status'=>2];
            return view('oky',['data'=>$data]);
          }
        }else{
                 //用户名错误
          $data=['status'=>2];
          return view('oky',['data'=>$data]);
        }

      }else{
              //验证码错误
        $data=['status'=>3];
        return view('oky',['data'=>$data]);
      }

    }else{

      return view('login');

      
    }

  }
  //主页欢迎页
  public function welcome(){

   $num =  Db::name('sign')->where('datetime',date('Y-m-d'))->select();
   $num = count($num);
   return view('welcome',['num'=>$num]);
 }
    //图片列表
 public function imglist(){
  $data =   Db::name('image')->order('name desc')->select();
  return view('picture-list',['data'=>$data]);
}
    //图片添加
public function imgadd(){

  return view('picture-add');
}



    //图片修改
public function imgedit(){
 if(request()->isPost()){
  $post = request()->post();
  $file = request()->file('image');
  $id = $post['id'];


  if($file){
    $info = $file->validate(['size' => 156780000000, 'ext' => 'jpg'])->move(ROOT_PATH . 'public/uploads');


    if($info){
      $url = 'http://'.$_SERVER['HTTP_HOST'].'/public/uploads' . "/".$info->getSaveName();
      dump($url);
      
      $sa= ['url'=>$url];

      $sa['point']=$post['point'];
      $stat = Db::name('image')->where('id',$id)->update($sa);
      if($stat){

        $this->redirect('index/Index/imglist');

      }else{

        $this->redirect('index/Index/imglist');
      }

    }else{
      return false;
                                    // 上传失败获取错误信息
                                    //echo $file->getError();
    }
  }else{

   $this->redirect('index/Index/imglist');
 }
}else{
  $get = request()->get();

  $id = $get['id'];
  $data = Db::name('image')->where('id',$id)->find();
  return view('picture-edit',['data'=>$data]);
}
}
    //图片删除
public function imgdelete(){

  if(request()->isPost()){
    $post = request()->post();
    $id = $_POST['id'];
    $data = Db::name('image')->where('id',$id)->delete();
    if($data){
      return 1;
    }else{
      return 0;
    }
  }else{
    return 0;
  }

}



public function upload(){
            // 获取表单上传文件 例如上传了001.jpg
  $file = request()->file('file'); 
  $savename = $file->getInfo()['name'];
  $infos = $file->getInfo()['name'];
  $name = explode('.',$infos)[0];
        // 移动到框架应用根目录/public/uploads/ 目录下
  $info = $file->validate(['size' => 156780000000, 'ext' => 'jpg'])->move(ROOT_PATH . 'public/uploads');
  dump($file->getInfo());

  if($info){
      // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
     // $path =  $info->getExtension();
            // 成功上传后 返回上传信息
        //$info =   json_decode($info,true);
    $year = explode('-',$name)[0];
    $month = explode('-',$name)[1];
    $day = explode('-',$name)[2];
    if(strlen($year)==4){}else{return false;}
    if(strlen($month)==2){}else{return false;}
    if(strlen($day)==2){}else{return false;}
    $dd = checkdate($month, $day, $year);
    if($dd){


      $savename = $file->getInfo()['name'];
      $name = explode('.',$savename)[0];
      $sa['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/public/uploads' . "/".$info->getSaveName();
      $sa['name'] = $name = explode('.',$savename)[0];
      $sa['create_time'] = date('Y-m-d H:i:s');
      dump($sa);
      $status = Db::name('image')->where('name',$name)->find();
      if($status){
        return false;
      }else{
        $img = Db::name('image')->insert($sa);
        if($img){
          return 1;
        }else{
          return 0;
        }
      }
    }else{
      return '图片名称错误';
    }
        //return json(array('state'=>0,'errmsg'=>'上传失败'));
      //return json(array('state'=>1,'path'=>$path));
  }else{

            // 上传失败返回错误信息
    return false;
  }
}
public function index()
{
  if(Session::has('username')){
    return view('index');
  }else{
    $this->redirect('index/Index/login');
  }
}

//投稿管理
public function contribute(){
  $post = request()->post();
  if(request()->iSpost()){

    if($post['status'] ==1){

      $id = $post['id'];
      $user_id = $post['user_id'];
      $status = Db::name('contribute')->where('id',$id)->update(['status'=>2]);
      $formid = Db::name('contribute')->where('id',$id)->find();
      $create_time = $formid['create_time'];
      $formid = $formid['formid'];
      $openid = Db::name('user',$user_id)->find();
      $openid = $openid['openid'];
      $now = date('Y-m-d H:i:s');
                //别忘记还要加积分
      if($status){
        $template = '{
          "touser": "'.$openid.'",  
          "template_id": "fQe-S5VVTzt_IdByjlLGLxAaKLTXo1u6UkBziC7_wBU", 
          "page": "index",          
          "form_id": "'.$formid.'",         
          "data": {
            "keyword1": {
              "value": "'.$create_time.'", 
              "color": "#173177"
            }, 
            "keyword2": {
              "value": "'.$now.'", 
              "color": "#173177"
            }, 
            "keyword3": {
              "value": "投稿通过", 
              "color": "#173177"
            }
          },
          "emphasis_keyword": "keyword1.DATA" 
        }';

      }

  $status = $this->fasong($template);
  if($status){

  }else{

  }

    }else{

    }
  }else{

    $data =   Db::name('contribute')->where('status',1)->order('create_time desc')->select();
    return view('contribute',['data'=>$data]);


  }





}
public function goodsadd(){
  if(request()->isPost()){
    $post = request()->post();
    $file = request()->file('image');
    if($file){
      $info = $file->validate(['size' => 156780000000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/uploads');
      if($info){
      $url = 'http://'.$_SERVER['HTTP_HOST'].'/public/uploads' . "/".$info->getSaveName();
        
        
        $sa =[
        'img'=>$url,
        'point'=>$post['point'],
        'name'=>$post['name'],
        'create_time'=>date('Y-m-d H:i:s')
        ];

        $stat = Db::name('goods')->insert($sa);
        if($stat){

          $this->redirect('index/Index/goods');

        }else{

          $this->redirect('index/Index/goods');
        }

      }else{

        return false; 
                                    // 上传失败获取错误信息
                                    //echo $file->getError();
      }
    }else{

     $this->redirect('index/Index/goods');
   }



 }else{


  return view('goodsadd');
}
}
//礼品管理
public function goods(){
  $post = request()->post();

  if(request()->iSpost()){




  }else{

    $data = Db::name('goods')->select();
    return view('goods',['data'=>$data]);
  }

}

public function goodsdelete(){

  if(request()->isPost()){
    $post = request()->post();
    $id = $_POST['id'];
    $data = Db::name('goods')->where('id',$id)->delete();
    if($data){
      return 1;
    }else{
      return 0;
    }
  }else{
    return 0;
  }

}

public function goodsedit(){


  $post = request()->post();
  $get = request()->get();

  if(request()->iSpost()){
    $file = request()->file('image');
    $id = $post['id'];

 
    if($file){
      $info = $file->validate(['size' => 156780000000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public/uploads');
      if($info){
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/public/uploads' . "/".$info->getSaveName();
        

        $sa =[
        'img'=>$url,
        'point'=>$post['point'],
        'name'=>$post['name'],
        'create_time'=>date('Y-m-d H:i:s')
        ];

        $stat = Db::name('goods')->where('id',$id)->update($sa);
        if($stat){

          $this->redirect('index/Index/goods');

        }else{

          $this->redirect('index/Index/goods');
        }

      }else{

                                    // 上传失败获取错误信息
                                    //echo $file->getError();
      }
    }else{
      $sa =[
      'point'=>$post['point'],
      'name'=>$post['name'],
      'create_time'=>date('Y-m-d H:i:s')
      ];
      $stat = Db::name('goods')->where('id',$id)->update($sa);
      if($stat){

        $this->redirect('index/Index/goods');

      }else{

        $this->redirect('index/Index/goods');
      }
    }



  }else{
    $id= $get['id'];
    $data = Db::name('goods')->where('id',$id)->find();
    return view('goodsedit',['data'=>$data]);
  }





}
public function order(){

  $data  = Db::name('order')->order('create_time desc')->select();
  return view('order',['data'=>$data]);
}
//模版消息发送
public function fasong($template){
//$openid 用户微信的openid   type是类型 1咨询 2 活动 3课程对应的id
$nowtime = time();//当前时间戳
$tocken = Db::name('tocken')->where('id',1)->find();
$atime = $tocken['time'] + 7000;

  if($atime < $nowtime){//过期了
    dump('guoqi');
    $appid =  "wx82fd8a5ab014c63c";
    $secret = "972b7ca42c685331a6b66668f3a229f3";
    $asurl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret; 
    $data = $this->http_request($asurl);    
    $access_token =json_decode($data,true)['access_token'];
    if($access_token){
      $data = ['tocken'=>$access_token,'status'=>1,'time'=>time(),'create_time'=>date('Y-m-d H:i:s')];
      Db::name('tocken')->where('id',1)->update($data);
    }
  }else{
    dump('wei');
    $access_token =$tocken['tocken'];
  }
// $url ='https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN ';
// $unicode = $this->http_request($url);
// $ucode = json_decode($unicode,true)['unionid'];
  $urls = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token;

  $urlss = $this->http_request($urls,$template);
  $msg1 = json_decode($urlss);
  dump($msg1);
  if($msg1->errmsg =='ok'){
    return 'ok';
  }else{
    return 'no';
  } 
}
}
