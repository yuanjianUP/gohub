<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 2017/2/14
 * Time: 11:10
 */


include_once("../common/BaseController.class.php");
include_once("../common/function.php");
include_once("../common/AesController.class.php");
include_once("../common/Model/ApiModel.class.php");
include_once('../common/mail/PHPMailerAutoload.php');
include_once("../common/Iplocation.class.php");
include_once("../common/Model/PayModel.class.php");
include_once("../common/Model/PayAllModel.class.php");
include_once("../common/Model/IdCardModel.class.php");
include_once("../common/redis.php");
//include_once("../common/Model/DatabaseModel.class.php");

class OnlineController extends BaseController{

    const KEY="IIugamePay12_userDaTa";

    const PHONEKEY="higamePhone123456";

    const PHONEACCOUNT='2002477';
    const PHONEPASSWORD='7hzMU/00';
    const PHONESIGN='嗨玩游戏';
    
    // 获取设备参数
    private function getDevParam(){
        // 安卓sdk设备参数 start
        $modelData['package_ver']=$this->getParam("version");
        $modelData['android_uuid']=$this->getParam("Uuid");
        $modelData['android_imei']=$this->getParam("IMEI");
        $modelData['android_ua']=$this->getParam("UA");
        $modelData['android_ver']=$this->getParam("SystemVersion");
        $modelData['android_model']=$this->getParam("SystemModel");
        $modelData['android_brand']=$this->getParam("DeviceBrand");
        // 安卓sdk设备参数 end
        return $modelData;
    }
    /**
     * 普通用户注册
     */
    public function comRegister(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏的key
        $acsPassword=$this->getParam("Password");
        $AesController = new AesController();
        $password=$AesController->decode($acsPassword);//明文密码
        $username=$this->getParam("Username");//用户名
        $type=$this->getParam("Sdktype");//sdktype 类型
        $email=$this->getParam("Email");//邮箱
        $idfa=$this->getParam("idfa");
        $apiModel=ApiModel::getInstance();
        $apiModel->comRegister($gameId,$key,$password,$username,$email,$type,$idfa,$modelData);
    }


    /**
     * 新版--用户注册登录
     * @param int Ugameid 游戏ID
     * @param string Ugamekey 游戏的key
     * @param int Phone 手机号
     * @param string Password 密码
     * @param string Code 手机验证码
     */
    public function newRegister(){
        $modelData     = $this->getDevParam();// 获取设备参数
        $gameId        = $this->getParam("Ugameid");//游戏ID
        $key           = $this->getParam("Ugamekey");//游戏的key
        $phone         = $this->getParam("Phone");//手机号
        $code          = $this->getParam("Code");//验证码
        $acsPassword   = $this->getParam("Password");
        $AesController = new AesController();
        $password      = $AesController->decode($acsPassword);//明文密码

        $type          = $this->getParam("Sdktype",0);//sdktype 类型
        $idfa          = $this->getParam("idfa");
        $verify        = $this->read($phone);//验证码

        $params = [
            'gameId'    => $gameId,
            'gameKey'   => $key,
            'phone'     => $phone,
            'queryCode' => $code,
            'password'  => $password,
            'type'      => $type,
            'idfa'      => $idfa,
            'code'      => $verify,
            'modelData' => $modelData,
        ];

        // var_dump($params);die;

        $apiModel = ApiModel::getInstance();
        $apiModel->newRegister($params);
    }



    /**
     * 新版--用户登陆接口
     * @param int Ugameid 游戏ID
     * @param string Ugamekey 游戏的key
     * @param string Username 用户名
     * @param string Password 密码
     */
    public function newUserLogin(){
        $modelData   = $this->getDevParam();// 获取设备参数
        $gameId      = $this->getParam("Ugameid");//游戏ID
        $key         = $this->getParam("Ugamekey");//游戏key
        $acsPassword = $this->getParam("Password");//aes 加密密文
        $username    = $this->getParam("Username");//用户名
        $idfa        = $this->getParam("idfa");

        $password = (new AesController())->decode($acsPassword);//明文密码
        $apiModel = ApiModel::getInstance();

        $params = [
            'gameId'    => $gameId,
            'gameKey'   => $key,
            'username'  => $username,
            'password'  => $password,
            'type'      => $type,
            'idfa'      => $idfa,
            'modelData' => $modelData,
        ];

        $apiModel->newUserLogin($params);
    }

    
    /**
     * 用户登陆接口
     */

    public function userLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏key
        $acsPassword=$this->getParam("Password");//aes 加密密文
        $username=$this->getParam("Username");//用户名
        $idfa=$this->getParam("idfa");

        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0,"msg"=>"gameid参数为空")));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0,"msg"=>"用户名参数为空")));
        }elseif(empty($acsPassword)){
            die (json_encode(array("Code"=>102,"Status"=>0,"msg"=>"密码参数为空")));
        }

        $AesController=new AesController();
        $password=$AesController->decode($acsPassword);//明文密码
        $apiModel=ApiModel::getInstance();
        $apiModel->userLogin($gameId,$key,$username,$password,$idfa,$modelData);
    }

    //角色账号绑定登录 用于对接第三方账号登录的SDK
    public function roleLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏key
        $username=$this->getParam("Username");//用户名
        $type=$this->getParam("Type",'0');
        $uuid=$this->getParam("Uuid");

        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0,"msg"=>"gameid参数为空")));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0,"msg"=>"用户名参数为空")));
        }
        $apiModel=ApiModel::getInstance();
        $apiModel->roleLogin($gameId,$key,$username,$type,$uuid,$modelData);
    }


    /**
     *  fb登陆和注册
     */
    public function facebook_check(){
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏的key
        $fbuid=$this->getParam("Fbuid");//fbuid 用户ID
        $token=$this->getParam("Fbtoken");//fbtoken
        $fbappid=$this->getParam("Fbappid");//fbappid
        $type=$this->getParam("Sdktype");//设备类型

        if(empty($gameId)){
            die (json_encode(array("Ugameid"=>'',"Code"=>101,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }elseif(empty($fbuid)){
            die (json_encode(array("Ugameid"=>$gameId ,"Code"=>113,"Sdkuid"=>"","Status"=>0,"Isnew"=>"" ,"msg"=>"fbuid 参数不存在")));
        }elseif(empty($token)){
            die (json_encode(array("Ugameid"=>$gameId ,"Code"=>114,"Sdkuid"=>"","Status"=>0,"Isnew"=>"","msg"=>"token 参数不存在")));
        }elseif(empty($fbappid)){
            die (json_encode(array("Ugameid"=>$gameId ,"Code"=>115,"Sdkuid"=>"","Status"=>0,"Isnew"=>"","msg"=>"fbappid 不存在")));
        }elseif($type==''){
            die (json_encode(array("Ugameid"=>$gameId ,"Code"=>112,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->facebook_check($gameId,$key,$fbuid,$token,$fbappid,$type);
    }

    /**
     * 收集facebook 资料
     */
    public function collection(){
        $data=array();
        $gameId=$this->getParam("Ugameid");//游戏Id
        $type=$this->getParam('Sdktype');
        $data['gender']=$this->getParam("gender");
        $data['fbemail']=$this->getParam("email");//邮箱
        $data['id']=$this->getParam("id");//fbuid
        $data['link']=$this->getParam("link");//头像
        $data['locale']=$this->getParam("locale");
        $data['name']=$this->getParam("name");
        $data['verified']=$this->getParam("verified");

        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }elseif(empty($gameId)){
            die(json_encode(array( "Code"=>101,"Status"=>0)));
        }elseif($data['id']==''){
            die(json_encode(array( "Code"=>113,"Status"=>0)));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->collectionFb($gameId,$data);
    }

    /**
     * facebook 活动分享
     */
    public function facebookShare(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");//游戏ID
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID

        if(empty($gameId)){
            die(json_encode(array('Status'=>0,'Code'=>101,'Subtitle'=>'','Comtent'=>'','id'=>'','isend'=>'','shareExplain'=>'','shareLink'=>'','shareImage'=>'','activeImage'=>'','title'=>'','msg'=>"gameId 为空")));
        }elseif($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>122,'Subtitle'=>'','Comtent'=>'','id'=>'','isend'=>'','shareExplain'=>'','shareLink'=>'','shareImage'=>'','activeImage'=>'','title'=>'',"msg"=>"角色ID为空")));
        }elseif($serverId==''){
            die(json_encode(array('Status'=>0,'Code'=>123,'Subtitle'=>'','Comtent'=>'','id'=>'','isend'=>'','shareExplain'=>'','shareLink'=>'','shareImage'=>'','activeImage'=>'','title'=>'',"msg"=>"服务器ID为空")));
        }elseif(empty($uid)){
            die(json_encode(array('Status'=>0,'Code'=>124,'Subtitle'=>'','Comtent'=>'','id'=>'','isend'=>'','shareExplain'=>'','shareLink'=>'','shareImage'=>'','activeImage'=>'','title'=>'','msg'=>"用户ID为空")));
        }

        $apiModel=ApiModel::getInstance();

       // $apiModel->gets
       $apiModel->share($gameId,$roleId,$serverId,$key,$uid);

    }


    /**
     * facebook分享礼包接口
     */
    public function shareGift(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");//游戏ID
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID
        $shareId=$this->getParam("Shareid");//活动ID


        if(empty($gameId)){
            die(json_encode(array("Code"=>101,'Status'=>0)));
        }elseif($roleId==''){
            die(json_encode(array("Code"=>122,'Status'=>0)));
        }elseif($serverId==''){
            die(json_encode(array("Code"=>123,'Status'=>0)));
        }elseif(empty($uid)){
            die(json_encode(array("Code"=>124,'Status'=>0)));
        }elseif(empty($shareId)){
            die(json_encode(array("Code"=>130,'Status'=>0)));
        }

        $apiModel=ApiModel::getInstance();

        $apiModel->sendShareGift($gameId,$uid,$serverId,$key,$roleId,$shareId);
    }


    /**
     * facebook 邀请礼包列表
     */
    public function inviteList(){
        $key=$this->getParam("Ugamekey");//key
        $uid=$this->getParam("Uid");//用户ID
        $gameId=$this->getParam("Ugameid");//游戏ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $roleId=$this->getParam("Roleid");//角色ID

        if(empty($uid)){
            die(json_encode(array('Status'=>0,'Code'=>124,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"",'msg'=>"gameid 为空")));
        }elseif(empty($gameId)){
            die(json_encode(array('Status'=>0,'Code'=>101,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif($serverId==''){
            die(json_encode(array('Status'=>0,'Code'=>123,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>122,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->inviteList($gameId,$serverId,$roleId,$uid,$key);
    }

    /**
     * facebook 邀请并且发送礼包
     */
    public function inviteGift(){
        $key=$this->getParam("Ugamekey");
        $gameId=$this->getParam("Ugameid");//游戏ID
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID，
        $Invitefbid=$this->getParam("Invitefbid");
        $activityId=$this->getParam("Activeid");

        if(empty($gameId)){
            die(json_encode(array('Status'=>0,'Code'=>101,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>122,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif(empty($uid)){
            die(json_encode(array('Status'=>0,'Code'=>124,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif($serverId==''){
            die(json_encode(array('Status'=>0,'Code'=>123,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif(empty($activityId)){
            die(json_encode(array('Status'=>0,'Code'=>132,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }elseif(empty($Invitefbid)){
            die(json_encode(array('Status'=>0,'Code'=>131,'Data'=>array("0"=>array("complete" => "","packcomtent"=>"","targetlogo"=>"","targetnum"=>"")),"explain"=>"","id"=>"","invitelang"=>"","invitenum"=>"","maxinvitenum"=>"")));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->addInvite($gameId,$serverId,$roleId,$activityId,$uid,$Invitefbid,$key);
    }

    /**
     *
     * fb点赞活动,并且发送礼包
     */
    public function like(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");//gameid
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID
       // print_r($_POST);
        if(empty($uid)){
            die(json_encode(array('Status'=>0,'Code'=>124,'Data'=>array("0"=>array("complete" => "","comtent"=>"","id"=>"","logo"=>"","likenumber"=>"")),"explain"=>"","id"=>"","invite"=>"","likelink"=>"","maxlikelink"=>"","share"=>"")));
        }elseif(empty($gameId)){
            die(json_encode(array('Status'=>0,'Code'=>101,'Data'=>array("0"=>array("complete" => "","comtent"=>"","id"=>"","logo"=>"","likenumber"=>"")),"explain"=>"","id"=>"","invite"=>"","likelink"=>"","maxlikelink"=>"","share"=>"")));
        }elseif($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>122,'Data'=>array("0"=>array("complete" => "","comtent"=>"","id"=>"","logo"=>"","likenumber"=>"")),"explain"=>"","id"=>"","invite"=>"","likelink"=>"","maxlikelink"=>"","share"=>"")));
        }elseif($serverId==''){
            die(json_encode(array('Status'=>0,'Code'=>123,'Data'=>array("0"=>array("complete" => "","comtent"=>"","id"=>"","logo"=>"","likenumber"=>"")),"explain"=>"","id"=>"","invite"=>"","likelink"=>"","maxlikelink"=>"","share"=>"")));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->likelist($gameId,$key,$serverId,$uid,$roleId);
    }

    /**
     * 修改密码
     */
    public function resetPwd(){
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏key
        $oldPwd=$this->getParam("Odpasswd");//旧的密码
        $newPwd=$this->getParam("Newpasswd");//新的密码
        $username=$this->getParam("Username");//用户名

        $aesController=new AesController();
        //加密之前的秘密
        $beforeOldPwd=$aesController->decode($oldPwd);
        $beforeNewPwd=$aesController->decode($newPwd);

        if(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0,"msg"=>"参数为空")));
        }

        if(strlen($beforeNewPwd)<6||strlen($beforeNewPwd)>20){
            die (json_encode(array("Code"=>160,"Status"=>0)));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->resetPwd($gameId,$key,$username,$beforeOldPwd,$beforeNewPwd);
    }



    /**
     * 个人中心页面修改密码
     */
    public function resetNewPwd(){
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏key
        $oldPwd=$this->getParam("Odpasswd");//旧的密码
        $newPwd=$this->getParam("Newpasswd");//新的密码
        $username=$this->getParam("userId");//用户ID

        $aesController=new AesController();
        //加密之前的秘密
        $beforeOldPwd=$aesController->decode($oldPwd);
        $beforeNewPwd=$aesController->decode($newPwd);

        if(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0,"msg"=>"参数为空")));
        }

        if(strlen($beforeNewPwd)<6||strlen($beforeNewPwd)>20){
            die (json_encode(array("Code"=>160,"Status"=>0)));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->resetNewPwd($gameId,$key,$username,$beforeOldPwd,$beforeNewPwd);
    }


    /**
     * 游客登陆接口
     */
    public function visitorLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏id
        $key=$this->getParam("Ugamekey");//游戏key
        $uuid=$this->getParam("Uuid");//mac 地址
        $type=$this->getParam("Sdktype");//1 ios 2 android;
        $version=$this->getParam("version");
        $idfa=$this->getParam("idfa");
        if(empty($gameId)){
            die (json_encode(array("Ugameid"=>'',"Code"=>101,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }elseif(empty($uuid)){
            die (json_encode(array("Ugameid"=>'',"Code"=>124,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }elseif($type==''){
            die (json_encode(array("Ugameid"=>'',"Code"=>112,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));

        }
        $apiModel=ApiModel::getInstance();
        $apiModel->visitorLogin($gameId,$key,$uuid,$type,$version,$idfa,$modelData);

    }
    

    /**
     * 一键登陆接口
     */
    public function oneClickLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏id
        $key=$this->getParam("Ugamekey");//游戏key
        $uuid=$this->getParam("Uuid");//mac 地址
        $type=$this->getParam("Sdktype");//1 ios 2 android;
        $version=$this->getParam("version");
        $idfa=$this->getParam("idfa");
        if(empty($gameId)){
            die (json_encode(array("Ugameid"=>'',"Code"=>101,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }elseif(empty($uuid)){
            die (json_encode(array("Ugameid"=>'',"Code"=>124,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));
        }elseif($type==''){
            die (json_encode(array("Ugameid"=>'',"Code"=>112,"Sdkuid"=>"","Status"=>0,"Isnew"=>"")));

        }
        $apiModel=ApiModel::getInstance();
        $apiModel->oneClickLogin($gameId,$key,$uuid,$type,$version,$idfa,$modelData);

    }


    /*
    * 个人中心页面绑定手机号码
    */
    public function bindNewPhone(){

        $phone=$this->getParam("phone");//手机号码
        $code=$this->getParam("code");//手机验证码
        // $uuid=$this->getParam("Uuid");//设备ID
        $username=$this->getParam("userId");//用户名
        $key=$this->getParam("Ugamekey");//游戏ID
        $gameId=$this->getParam("Ugameid");//游戏ID
        // $password=$this->getParam("Password");//密码

        $isPhone=isPhone($phone);
        if(!$isPhone){
            die(json_encode(array('Status'=>0,'Code'=>168,"msg"=>"手机号码格式不对")));
        }

        if(empty($code)){
            $msg=array("Ugameid"=>$gameId, "Code"=>173, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码为空",);
            exit(json_encode($msg));//返回json 数据
        }

        $verify=$this->read($phone);

        if(empty($verify)){
            $msg=array("Ugameid"=>$gameId, "Code"=>171, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码失效",);
            exit(json_encode($msg));//返回json 数据
        }

        if($code!=$verify){
            unlink("./code/{$phone}.log");
            $msg=array("Ugameid"=>$gameId, "Code"=>170, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码不正确",);
            exit(json_encode($msg));//返回json 数据
        }



        $apiModel=ApiModel::getInstance();
        //$apiModel->phoneBind($gameId,$key,$username,$beforePwd,$uuid);
        $apiModel->phoneNewBind($gameId,$key,$username,'',$phone);
    }



    /*
     * 手机号码账号绑定
     */
    public function phoneBind(){

        $phone=$this->getParam("phone");//手机号码
        $code=$this->getParam("code");//手机验证码
        $uuid=$this->getParam("Uuid");//设备ID
        $username=$this->getParam("Username");//用户名
        $key=$this->getParam("Ugamekey");//游戏ID
        $gameId=$this->getParam("Ugameid");//游戏ID
        $password=$this->getParam("Password");//密码

        $isPhone=isPhone($phone);
        if(!$isPhone){
            die(json_encode(array('Status'=>0,'Code'=>168,"msg"=>"手机号码格式不对")));
        }

        if(empty($code)){
            $msg=array("Ugameid"=>$gameId, "Code"=>173, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码为空",);
            exit(json_encode($msg));//返回json 数据
        }

        $verify=$this->read($phone);

        if(empty($verify)){
            $msg=array("Ugameid"=>$gameId, "Code"=>171, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码失效",);
            exit(json_encode($msg));//返回json 数据
        }

        if($code!=$verify){
            unlink("./code/{$phone}.log");
            $msg=array("Ugameid"=>$gameId, "Code"=>170, "Sdkuid"=>'', "Status"=>0, "msg"=>"验证码不正确",);
            exit(json_encode($msg));//返回json 数据
        }

        $beforePwd='';
        if(!empty($password)){
            $aesController=new AesController();
            $beforePwd=$aesController->decode($password);
            if(strlen($beforePwd)<6||strlen($beforePwd)>20){
                die (json_encode(array("Code"=>160,"Status"=>0)));
            }
        }

        $apiModel=ApiModel::getInstance();
        //$apiModel->phoneBind($gameId,$key,$username,$beforePwd,$uuid);
        $apiModel->phoneBind($gameId,$key,$username,$beforePwd,$phone,$uuid);
    }

    /**
     * 游客绑定个人信息
     */
    public function vistorBind(){
        $key=$this->getParam("Ugamekey");//游戏key
        $gameId=$this->getParam("Ugameid");//游戏IDUser
        $uuid=$this->getParam("Uuid");//mac 地址
        $username=$this->getParam("Username");
        $pwd=$this->getParam("Password");//密码
        $email=$this->getParam("Email");
        $version=$this->getParam("version");
        $aesController=new AesController();
        //加密之前的秘密
        $beforePwd=$aesController->decode($pwd);
        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0)));
        }elseif(empty($uuid)){
            die (json_encode(array("Code"=>124,"Status"=>0)));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0)));
        }elseif(empty($pwd)){
            die (json_encode(array("Code"=>102,"Status"=>0)));
        }elseif(strlen($beforePwd) < 6 || strlen($beforePwd) > 20){
            die (json_encode(array("Code"=>160,"Status"=>0,"msg"=>"密码不在长度范围")));
        }
        $apiModel=ApiModel::getInstance();
        $apiModel->vistorBind($gameId,$key,$uuid,$username,$beforePwd,$email,$version);
    }

    /**
     * 邮箱绑定
     */
    public function emailBind(){
        $key=$this->getParam("Ugamekey");//游戏key
        $gameId=$this->getParam("Ugameid");//游戏IDUser

        $username=$this->getParam("Username");
        $pwd=$this->getParam("Password");//密码
        $email=$this->getParam("Email");
        $aesController=new AesController();
        $beforePwd=$aesController->decode($pwd);
        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0)));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0)));
        }elseif(empty($beforePwd)){
            die (json_encode(array("Code"=>102,"Status"=>0)));
        }elseif(empty($email)){
            die (json_encode(array("Code"=>154,"Status"=>0)));
        }
        $apiModel=ApiModel::getInstance();

        // $bool=isPhone($email);
        // if($bool){

        //     $apiModel->phoneBind($gameId,$key,$username,$beforePwd,$email);
        // }else{
            $apiModel->emailBind($gameId,$key,$username,$beforePwd,$email);
        // }
    }

    /**
     * 找回密码
    */
    public function findPassword(){
        $key=$this->getParam("Ugamekey");//游戏key
        $gameId=$this->getParam("Ugameid");//游戏IDUser
        $username=$this->getParam("Username");
        $email=$this->getParam("Email");
        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0)));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0)));
        }elseif(empty($email)){
            die (json_encode(array("Code"=>154,"Status"=>0)));
        }

        $apiModel=ApiModel::getInstance();

        $bool=isPhone($email);
        $pwd='';
        if($bool){


            $userData=$apiModel->getUserByName($username);

            if(!empty($userData)){

                if($userData['phone']!=$email){
                    $msg=array("Code"=>111,"Status"=>0,"msg"=>"手机号码不一致");//用户不存在
                    exit(json_encode($msg));
                }
            }else{
                $msg=array("Code"=>111,"Status"=>0);//用户不存在
                exit(json_encode($msg));
            }
            $pwd=random();
            $content="您已经通过手机号码找回游戏平台账号密码,这是您新的密码{$pwd},请妥善保管";
             $isSend= $this->getSendCode($email,$pwd,2);
            if($isSend){
                $apiModel->findPasswordByPhone($gameId,$key,$email,$username,$pwd);
            }else{
                $msg=array("Code"=>158,"Status"=>1,"msg"=>"短信发送失败");
                exit(json_encode($msg));
            }
        }else{
            $apiModel->findPassword($gameId,$key,$email,$username);
        }

    }


    /**
     * facebook 账号绑定
     */
    public function facebookBind(){
        $key=$this->getParam("Ugamekey");//游戏key
        $gameId=$this->getParam("Ugameid");//游戏IDUser
        $username=$this->getParam("Username");
        $pwd=$this->getParam("Password");//加密的密码
        $email=$this->getParam("Email");
        $token=$this->getParam("Fbtoken");//token
        $fbappid=$this->getParam("Fbappid");//fbappid
        $fbuid=$this->getParam("Fbuid");//fbuid
        $aesController=new AesController();
        $beforePwd=$aesController->decode($pwd);
        if(empty($gameId)){
            die (json_encode(array("Code"=>101,"Status"=>0)));
        }elseif(empty($username)){
            die (json_encode(array("Code"=>103,"Status"=>0)));
        }elseif(empty($beforePwd)){
            die (json_encode(array("Code"=>102,"Status"=>0)));
        }elseif(empty($fbuid)){
            die (json_encode(array("Code"=>113,"Status"=>0)));
        }elseif(empty($token)){
            die (json_encode(array("Code"=>114,"Status"=>0)));
        }elseif(empty($fbappid)){
            die (json_encode(array("Code"=>115,"Status"=>0)));
        }elseif(strlen($beforePwd) < 6 || strlen($beforePwd) > 20){
            die (json_encode(array("Code"=>160,"Status"=>0)));
        }elseif(strlen($username) < 4 || strlen($username) > 20){
            die (json_encode(array("Code"=>162,"Status"=>0)));
        }
        $apiModel=ApiModel::getInstance();
        $apiModel->facebookBind($gameId,$key,$token,$username,$email,$beforePwd);
    }

    /**
     * sdk设备数据采集
     */
    public function sdkCollection(){

        $data['gameId']=$this->getParam('Ugameid');//游戏ID
        $data['type']=$this->getParam("Sdktype");//设备类型 0 安卓 1 IOS
        $data['google_advertising_id']=$this->getParam("google_advertising_id");
        $data['android_id']=$this->getParam("android_id");
        $data['imei']=$this->getParam("imei");
        $data['devicetype']=$this->getParam("devicetype");
        $data['osversion']=$this->getParam("osversion");
        $data['idfv']=$this->getParam("idfv");
        $data['idfa']=$this->getParam("idfa");
        $data['verified']=$this->getParam("verified");
        $data['android_version_devicetype']=$this->getParam("android_version_devicetype");
        $data['time']=time();
        $curentIdfa=$this->getParam("currentIdfa");//当前的idfa

        if($data['gameId']==''){
            die(json_encode(array( "Code"=>101,"Status"=>0)));
        }elseif($data['type']==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }

        $apiModel=ApiModel::getInstance();
        if(!empty($curentIdfa)&&strlen($curentIdfa)>5){

            //第一次过来
            if($curentIdfa==$data['idfa']){
                $apiModel->sdkCollection($data);
            }else{
                $insertData=array(
                    "idfa"=>$data['idfa'],
                    "newIdfa"=>$curentIdfa,
                    "ip"=>get_client_ip(),
                    "gameId"=>$data['gameId'],
                    "ctime"=>time(),
                );
                $apiModel->table("all_idfa")->add($insertData);
            }
            die(json_encode(array( "Code"=>100,"Status"=>1)));
        }

        $apiModel->sdkCollection($data);
    }

    /**
     * 初始化接口
     * 版本更新接口
     */
    public function sdkUpdate(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $currentbundleId =$this->getParam("currentbundleId");//当前的版本号
        $gameId=$this->getParam("Ugameid");//游戏ID
        $type=$this->getParam("isios");
        $version=$this->getParam("version");
        if($gameId == '')
        {
            die (json_encode(array("Code"=>101,"Status"=>0,'isUpdate'=>'','switch'=>'','updateDesc'=>'','urlbundleid'=>'')));
        }

        if($type == '')
        {
            die (json_encode(array("Code"=>119,"Status"=>0,'isUpdate'=>'','switch'=>'','updateDesc'=>'','urlbundleid'=>'')));

        }

        if($version == '')
        {
            die (json_encode(array("Code"=>120,"Status"=>0,'isUpdate'=>'','switch'=>'','updateDesc'=>'','urlbundleid'=>'')));

        }

        if($currentbundleId == '')
        {
            die (json_encode(array("Code"=>121,"Status"=>0,'isUpdate'=>'','switch'=>'','updateDesc'=>'','urlbundleid'=>'')));

        }
        $apiModel=ApiModel::getInstance();
        $apiModel->sdkUpdate($gameId,$type,$currentbundleId,$version,$modelData['android_uuid'],$modelData);
    }

    /**
     * 活动功能开启
     */
    public function btnStart(){

        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");
        $serverId=$this->getParam("Serverid");//服务器ID
        $type=$this->getParam("Sdktype");
        $version=$this->getParam("Version");
        $Uid=$this->getParam("Uid");//用户ID
        $roleId=$this->getParam("Roleid");//角色ID
        $packname=$this->getParam("currentbundleId");//包名
        if($gameId == ''){
            die (json_encode(array("Code"=>101,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($version == ''){
            die (json_encode(array("Code"=>120,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($type == ''){
            die (json_encode(array("Code"=>112,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($serverId == ''){
            die (json_encode(array("Code"=>123,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }
        // $model=ApiModel::getInstance();
        // $gameConfig=$model->getConfigByGameId($gameId);
        // if(empty($gameConfig)){
        //     die (json_encode(array("Ugameid"=>$gameId, "Code"=>105, "Sdkuid"=>'', "Status"=>0, "msg"=>"该游戏还没有配置")));
        // }elseif($key!=$gameConfig['app_client_secret']){
        //     die (json_encode(array("Ugameid"=>$gameId, "Code"=>109, "Sdkuid"=>'', "Status"=>0, "msg"=>"密钥不对",)));
        // }
        $config=include("../common/config.inc.log.php");
        $apiModel=ApiModel::getInstance($config);
        $apiModel->loginLog($Uid,$gameId,$serverId,$roleId,$type);
        $model=ApiModel::getInstance();
        $model->btnStart($gameId,$type,$version,$key,$serverId,$packname);

    }



    /**
     * google 支付
     */
    public function google_pay(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");//游戏ID
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID
        $Cp_orderid=$this->getParam("Cp_orderid");//cp订单号
        $Receive_data=$this->getParam("Receive_data");//google 的票据
        $version=$this->getParam("Version");//版本号
        $sku=$this->getParam("Sku");//sku 商品号
        $Sign=$this->getParam("Sign");//签名
        $Ctext=$this->getParam("Ctext");

        $apiModel=ApiModel::getInstance();

        $payParam=$Receive_data;
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key

      //  print_r($gameConfigData);exit;
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }elseif($roleId == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>122,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Roleid 空")));
        }elseif($serverId == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>123,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Serverid 空")));
        }elseif($uid== '')
        {
            die(json_encode(array('Status'=>0,'Code'=>124,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Uid 空")));
        }elseif($Cp_orderid == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>133,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Cp_orderid 空")));
        }elseif($sku == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }elseif($Receive_data == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>135,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Receive_data 空")));
        }elseif($version == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>136,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Version 空")));
        }elseif($Sign== '') {
            die(json_encode(array('Status' => 0, 'Code' => 139, 'out_orderid' => "", "cp_orderid" => "", "amount" => "", "msg" => "Sign 空")));
        }

        $log=new LogController();
      //  $log->writeLog("google.log",var_export($_POST,true));

            //票据解码
        $acsController=new AesController();
        $Receive_data=$acsController->decode($Receive_data);
        //解码之后的json数据
        $order_json=json_decode($Receive_data);

        $sdk_orderid=isset($order_json->orderId)?$order_json->orderId:'';//sdk 的订单号

        $public_key=$gameConfigData['google_key'];
        $public_key = "-----BEGIN PUBLIC KEY-----\n" .chunk_split($public_key, 64, "\n") . "-----END PUBLIC KEY-----";

        $public_key_handle =openssl_pkey_get_public($public_key);
        $googleResult = openssl_verify($Receive_data, base64_decode($_POST['Sign']), $public_key_handle,OPENSSL_ALGO_SHA1);//google 的结果

        $orderNo=$this->createOrderNo($gameId,$uid,"0");//自己的订单号 google 订单号

        //查询sku对应的价格和货币类型
        $priceData=$apiModel->getPriceBySku($gameId,0,$sku);

        //获取是不是沙箱环境
        $payVersion=$apiModel->getVersionByGameIdAndType($version,0,$gameId);
        $sanbox=0;//默认是正式环境

        if(!empty($payVersion)&&$payVersion['isSanbox']==1){
            $sanbox=1;//沙箱环境
        }

        //获取白名单列表数据
        $payWhiteList=$apiModel->getPayWhiteListByGameIdAndType($gameId,0);//ios
        if(!empty($payWhiteList)){
            $uidArr=json_decode($payWhiteList['white_list'],true);
            if(in_array($uid,$uidArr)){
                $sanbox=1;//沙箱
            }
        }
        $insertData=array(
            "gameId"=>$gameId,
            "type"=>0,
            "cp_orderid"=>$Cp_orderid,//cp的订单号
            "sdk_orderid"=>$sdk_orderid,//sdk订单号
            "orderid"=>$orderNo,//自己的订单号,
            "ctime"=>time(),
            "uid"=>$uid,
            "roleId"=>$roleId,
            "pay_param"=>$payParam,//支付参数
            "sku"=>$sku,
            "price"=>$priceData['amount'],
            "serverid"=>$serverId,
            "currency"=>$priceData['currency'],
            'game_price'=>$priceData['price'],
            "is_sandbox"=>$sanbox,
            'cText'=>$Ctext,
        );

        $cpOrderData=$apiModel->getCpOrderByGameIdAndType($gameId,0,$Cp_orderid);

        $param=$insertData;
        $param['status']=$googleResult;
        $param['Receive_data']=$Receive_data;
        $log->writeLog("google.log",date("Y-m-d H:i:s").var_export($param,true));
        if($googleResult != 1)
        {
            //校验不通过订单
                if(empty($cpOrderData)){
                    //数据入库
                    $insertData['isFinsh']=3;//订单通不过验证
                    $apiModel->table("pay_log")->add($insertData);
                }
            //数据入库
            die(json_encode(array('Status'=>0,'Code'=>402,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单验证不通过")));
        }else{
            //通过验证
            $sdkOrderData=$apiModel->getOrderDataBysdkOrder($gameId,0,$sdk_orderid);
            if($sdk_orderid && $sdk_orderid != '' && !empty($sdkOrderData))
            {
                die(json_encode(array('Status'=>0,'Code'=>142,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单验存在")));
            }
            //商品不存在
            if($order_json->productId !=$sku){
                if(empty($cpOrderData)){
                    $insertData['isFinsh']=2;//订单通不过验证
                //数据入库
                    $apiModel->table("pay_log")->add($insertData);
                }
                die(json_encode(array('Status'=>0,'Code'=>164,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"商品不存在")));
            }

            if(($sdk_orderid && $sdk_orderid != '') ||($version==$payVersion['version']&&$sdk_orderid && $sdk_orderid != '')){
                //发货
                //数据入库
                $insertData['isFinsh']=0;
                $insertData['is_sandbox']=0;
               $insertId=  $apiModel->table("pay_log")->add($insertData);
                    //正式环境
                $payAllModel=PayAllModel::getInstance();
                $payAllModel->addPay($insertData['uid'],$insertData['roleId'],$insertData['serverid'],$insertData['gameId'],$insertData['price'],"USD",$insertData['orderid'],"google");
                $payModel=PayModel::getInstance();
                $cpStatus=$payModel->sdkPay($insertData);
                if($cpStatus){
                    $apiModel->table("pay_log")->where(array("id"=>$insertId))->save(array("isFinsh"=>1));
                    //更新订单状态
                    die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"成功")));
                }else{
                    die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"发货失败")));
                }
            }else{
                //不发货
                //测试环境
                $insertData['isFinsh']=0;
                $insertId=$apiModel->table("pay_log")->add($insertData);
                //发货
                if($sanbox==1){
                    //沙箱环境
                    $payModel=PayModel::getInstance();
                    $cpStatus=$payModel->sdkPay($insertData);
                    if($cpStatus){
                        //更新发货状态
                        $apiModel->table("pay_log")->where(array("id"=>$insertId))->save(array("isFinsh"=>1));//发货成功
                        die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"成功")));
                    }else{
                        die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"发货失败")));
                    }
                }else{
                    die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"发货失败")));
                }
            }
        }
    }


    /**
     * ios 支付
     */
    public function ios_pay(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");//游戏ID
        $roleId=$this->getParam("Roleid");//角色ID
        $serverId=$this->getParam("Serverid");//服务器ID
        $uid=$this->getParam("Uid");//用户ID
        $Cp_orderid=$this->getParam("Cp_orderid");//cp订单号
        $Receive_data=$this->getParam("Receive_data");//google 的票据
        $version=$this->getParam("Version");//版本号
        $sku=$this->getParam("Sku");//sku 商品号
        $Ctext=$this->getParam("Ctext");
        $apiModel=ApiModel::getInstance();
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key

        $paramStr=$_POST;
        $log=new LogController();
      //  $log->writeLog("iospay.log",date("Y-m-d H:i:s")."_".var_export($paramStr,true));
        //  print_r($gameConfigData);exit;
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }elseif($roleId == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>122,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Roleid 空")));
        }elseif($serverId == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>123,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Serverid 空")));
        }elseif($uid== '')
        {
            die(json_encode(array('Status'=>0,'Code'=>124,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Uid 空")));
        }elseif($Cp_orderid == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>133,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Cp_orderid 空")));
        }elseif($sku == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }elseif($Receive_data == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>135,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Receive_data 空")));
        }elseif($version == '')
        {
            die(json_encode(array('Status'=>0,'Code'=>136,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Version 空")));
        }


       // $log->writeLog("iospay.log",date("Y-m-d H:i:s")."_".var_export($_POST,true));
        $is_test_a=$is_test_b=0;
        $orderNo=$this->createOrderNo($gameId,$uid,1);//自己的订单号
        $apiModel=ApiModel::getInstance();
        //查询sku对应的价格和货币类型
        $priceData=$apiModel->getPriceBySku($gameId,1,$sku);
        $iosUrl="https://buy.itunes.apple.com/verifyReceipt";//正式地址
        $is_sanbox=0;//默认不是沙箱
        //查看是否是白名单或者是沙箱环境
        $payVersion=$apiModel->getVersionByGameIdAndType($version,1,$gameId);
        //获取白名单列表数据
        $payWhiteList=$apiModel->getPayWhiteListByGameIdAndType($gameId,1);//ios
        if(!empty($payWhiteList)){
            $uidArr=json_decode($payWhiteList['white_list'],true);
            if(in_array($uid,$uidArr)){
                $is_test_a=1;//沙箱
            }
        }
        //如果当前的版本是沙箱环境
        if(!empty($payVersion)){
            if($payVersion['isSanbox']==1){
                //沙箱环境
                $is_test_b=1;
            }
        }
        if($is_test_a==1||$is_test_b==1){
            $is_sanbox=1;//变成沙箱环境
            $iosUrl="https://sandbox.itunes.apple.com/verifyReceipt";//测试地址
        }
        $jsonData = json_encode(array("receipt-data"=>$Receive_data));
        $insertData=array(
            "gameId"=>$gameId,
            "type"=>1,//ios 支付
            "cp_orderid"=>$Cp_orderid,//cp的订单号
          //  "sdk_orderid"=>$sdk_orderid,//sdk订单号
            "orderid"=>$orderNo,//自己的订单号,
            "ctime"=>time(),
            "uid"=>$uid,
            "roleId"=>$roleId,
            "pay_param"=>'',//支付参数
            "sku"=>$sku,
            "price"=>isset($priceData['amount'])?$priceData['amount']:0,
            "serverid"=>$serverId,
            "currency"=>isset($priceData['currency'])?$priceData['currency']:'',
            'game_price'=>isset($priceData['price'])?$priceData['price']:0,
            "is_sandbox"=>$is_sanbox,
            'cText'=>$Ctext,
        );
        //查看cp订单存不存在
        $cpOrderData=$apiModel->getCpOrderByGameIdAndType($gameId,1,$Cp_orderid);
        $response = $this->http_post_data($iosUrl,$jsonData);

        $param=$_POST;
        $param['status']=$response->{'status'};

        //兼容实现,如果21008的话,切换到正式环境
        if($param['status']==21008){
            unset($response);
            unset($param['status']);
            $response = $this->http_post_data("https://buy.itunes.apple.com/verifyReceipt",$jsonData);
            $param['status']=$response->{'status'};
        }

        $log->writeLog("iospay.log",date("Y-m-d H:i:s")."_".var_export($param,true));
        if($response == '' && !isset($response->{'status'})){
            die(json_encode(array('Status'=>0,'Code'=>145,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"请求失败 ".$response->{'status'})));
        }elseif($response->{'status'} != 0){
            //订单验证不通过
            if(empty($cpOrderData)){
                //cp订单不存在,数据入库
                $insertData['isFinsh']=3;
                $apiModel->table("pay_log")->add($insertData);//插入数据
            }
           // $log->writeLog("error.log",date("Y-m-d H:i:s").$response->{'status'}."__".$orderNo);

            die(json_encode(array('Status'=>0,'Code'=>402,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单验证不通过 ".$response->{'status'})));
        }else{
            //验证通过的数据
            if(empty($priceData)){
                //sku对应的商品不存在
                if(empty($cpOrderData)){
                    //数据入库
                    $insertData['isFinsh']=2;
                    $apiModel->table("pay_log")->add($insertData);
                }
                die(json_encode(array('Status'=>0,'Code'=>137,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"商品不存在")));
            }else{
                //对应的商品
                //解析苹果返回的数据
                $ios_pay_comtent = $response->{'receipt'}->{'in_app'};
                // unset($ios_pay_comtent[0]);
                if(count($ios_pay_comtent) == 1) {
                    //----------------------------收据里面只有一个订单号 ------------------------------------------------------//
                    foreach ($ios_pay_comtent as $key => $value) {
                        $ios_orderid=$value->{'transaction_id'};
                        //查询sdkorder 是不是存在
                        $sdkData=$apiModel->getOrderDataBysdkOrder($gameId,1,$ios_orderid);
                        if($sku != $value->{'product_id'}) {
                            //检查收据中的商品id是否跟前端传过来的一直 三个参数必须同时满足 sku product_id
                            if(empty($sdkData)){
                                //数据入库
                                $insertData['sdk_orderid']=$ios_orderid;
                                $insertData['isFinsh']=2;
                                $apiModel->table("pay_log")->add($insertData);
                            }
                            die(json_encode(array('Status'=>0,'Code'=>140,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"商品不存在")));
                        }else{
                        //    $pay_logs=mysql_query("SELECT  * FROM `pay_log` WHERE `app_id`='".$_POST['Ugameid']."' and sdk_orderid = '".$ios_orderid."' and sdk_type = 1  limit 1");
                            if(empty($sdkData)) {
                                //数据入库
                                $insertData['ctime']=time();
                                $insertData['isFinsh']=0;
                                $insertData['sdk_orderid']=$ios_orderid;
                                $insertId=$apiModel->table("pay_log")->add($insertData);

                                if($insertData['is_sandbox']==0){
                                    //正式环境
                                    $payAllModel=PayAllModel::getInstance();
                                    $payAllModel->addPay($insertData['uid'],$insertData['roleId'],$insertData['serverid'],$insertData['gameId'],$insertData['price'],"RMB",$insertData['orderid'],"ios");
                                }

                                $payModel=PayModel::getInstance();
                                $cp_state=$payModel->sdkPay($insertData);
                                if($cp_state) //cp返回成功
                                {//更多状态
                                    $apiModel->table("pay_log")->where(array("id"=>$insertId))->save(array("isFinsh"=>1));
                                    die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"成功")));

                                } else {

                                    die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"cp发货失败")));
                                }
                            }else {
                                die(json_encode(array('Status'=>0,'Code'=>142,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单存在")));//订单已经存在
                            }
                        }
                    }//----------------------------收据里面只有一个订单号 ------------------------------------------------------//
                }else{
                    //多个商品处理
                    //判断多个订单号中是否存在多个未发货的订单
                    $ios_orderid_array=$ios_orderid_array_string=array();
                    foreach ($ios_pay_comtent as $key => $value) {
                        $transaction_id=$value->{'transaction_id'};
                        $ios_orderid_array_string[$key]="'".$transaction_id."'";
                        $ios_orderid_array[$key]=$transaction_id;
                    }
                    $sql="SELECT  DISTINCT `sdk_orderid` FROM `iiu_pay_log` WHERE `gameId`='".$gameId."' and sdk_orderid in (".implode(",", $ios_orderid_array_string).") and type = 1 ";

                    $result=$apiModel->query($sql);
                  //  $pay_logs       = mysql_query("SELECT  distinct `sdk_orderid` FROM `pay_log` WHERE `app_id`='".$_POST['Ugameid']."' and sdk_orderid in (".implode(",", $ios_orderid_array).") and sdk_type = 1 ");
                    $count_num      = count($result);
                    $ios_orderid_num= count($ios_pay_comtent);
                    $ret_num        = $ios_orderid_num - $count_num ;
                    if($ret_num > 1) {
                        foreach ($ios_pay_comtent as $key => $values) {
                            $transaction_ids=$values->{'transaction_id'};
                            $_pay_log=$apiModel->getOrderDataBysdkOrder($gameId,1,$transaction_ids);
                            if(empty($_pay_log)){
                                //数据入库
                                $insertData['ctime']=time();
                                $insertData['isFinsh']=3;
                                $insertData['sdk_orderid']=$transaction_ids;
                                $apiModel->table("pay_log")->add($insertData);
                                unset($insertData['ctime']);
                                unset($insertData['isFinsh']);
                                unset($insertData['sdk_orderid']);
                            }
                        }
                        die(json_encode(array('Status'=>0,'Code'=>141,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单异常")));
                    }else if( $ret_num ==1) {
                        $ret_arr=array();
                        $_ios_orderids='';
                        foreach($result as $key=> $v){
                            $ret_arr[$key]=$v['sdk_orderid'];
                        }
                        if(count($ios_orderid_array) > count($ret_arr))
                        {
                            $_ios_orderid_arr=array_diff($ios_orderid_array,$ret_arr);
                        }else {
                            $_ios_orderid_arr=array_diff($ret_arr,$ios_orderid_array);
                        }
                        foreach ($_ios_orderid_arr as $key => $value) {
                            $_ios_orderids=$value;
                        }
                        $pay_logs=$apiModel->getOrderDataBysdkOrder($gameId,1,$_ios_orderids);
                        if(empty($pay_logs)){
                            //订单入库
                            $insertData['ctime']=time();
                            $insertData['isFinsh']=0;
                            $insertData['sdk_orderid']=$_ios_orderids;
                            $insertId=$apiModel->table("pay_log")->add($insertData);
                        }else{
                            $insertId=$pay_logs['id'];
                        }

                        if($insertData['is_sandbox']==0){
                            //正式环境
                            $payAllModel=PayAllModel::getInstance();
                            $payAllModel->addPay($insertData['uid'],$insertData['roleId'],$insertData['serverid'],$insertData['gameId'],$insertData['price'],"RMB",$insertData['orderid'],"ios");
                        }

                        $payModel=PayModel::getInstance();
                        $cp_status=$payModel->sdkPay($insertData);//发货成功
                        if($cp_status){
                            //更新订单状态
                            $apiModel->table("pay_log")->where(array("id"=>$insertId))->save(array("isFinsh"=>1));
                            die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"成功")));
                        }else{
                            //发货失败
                            die(json_encode(array('Status'=>1,'Code'=>100,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>$priceData['amount'],"msg"=>"cp发货失败")));
                        }
                    }else{
                        foreach ($ios_pay_comtent as $key => $values) {
                            $transaction_ids=$values->{'transaction_id'};
                            $pay_logs=$apiModel->getOrderDataBysdkOrder($gameId,1,$transaction_ids);
                            if(empty($pay_logs)){
                                $insertData['ctime']=time();
                                $insertData['isFinsh']=3;
                                $insertData['sdk_orderid']=$transaction_ids;
                                $apiModel->table("pay_log")->add($insertData);
                                unset($insertData['ctime']);
                                unset($insertData['isFinsh']);
                                unset($insertData['sdk_orderid']);
                            }
                        }
                        die(json_encode(array('Status'=>0,'Code'=>142,'out_orderid'=>$orderNo,"cp_orderid"=>$Cp_orderid,"amount"=>"","msg"=>"订单存在")));
                    }
                }
            }
        }

    }

    /**
     * 获取12分钟支付的数据
     */
    public function getPayData(){

        $gameId=$this->getParam("gameid");//游戏ID
        $isios=$this->getParam("isios");//设备类型
        $sign=$this->getParam("sign");//签名
        $userId=$this->getParam("userid");//用户ID
        if(empty($gameId)){
            exit(json_encode(array("Code"=>101,"Status"=>0,"Userid"=>$userId,"msg"=>"gameid不能为空")));
        }elseif($isios==''){
            exit(json_encode(array("Code"=>119,"Status"=>0,"Userid"=>$userId,"msg"=>"isios不能为空")));
        }elseif(empty($sign)){
            exit(json_encode(array("Code"=>139,"Status"=>0,"Userid"=>$userId,"msg"=>"签名不能为空")));
        }elseif(empty($userId)){
            exit(json_encode(array("Code"=>124,"Status"=>0,"Userid"=>$userId,"msg"=>"用户ID不能为空")));
        }

        $param=$_POST;
        $createSign=$this->createSign($param,self::KEY);
        if($sign==$createSign){
            $apiModel=ApiModel::getInstance();
            $apiModel->getAllPayByUid($userId,$gameId,$isios);
        }else{
            //签名不对
            exit(json_encode(array("Code"=>202,"Status"=>0,"Userid"=>$userId,"msg"=>"签名不对")));
        }
    }

    /**
     * 生成签名的办法
     */
    public function createSign($data,$key){
        ksort($data);
        $str='';
        foreach($data as $k=> $v){
           if($k!="sign"){
               $str.=$v;
           }
        }
        $str.=$key;
        return md5($str);
    }

    //手动补单
    public function hand_operation(){
        $gameId=$this->getParam("gameId");//游戏ID
        $sign=$this->getParam("sign");//签名
        $userId=$this->getParam("userId");//用户ID
        $roleId=$this->getParam("roleId");//角色ID
        $serverId=$this->getParam("serverId");//服务器ID
        $orderId=$this->getParam("orderId");//订单号
        $price=$this->getParam("amount");//支付金额
        $mapAmount=$this->getParam("mapAmount");//游戏币数量
        $currencyCode=$this->getParam("currencyCode");
        $cText=$this->getParam("cText");
        $param=$_POST;
        $createSign=$this->createSign($param,self::KEY);
        if($createSign!=$sign){
            //签名不对
            $msg=array("status"=>0,"msg"=>"签名不对");
            exit(json_encode($msg));

        }
        if($gameId==''){
            exit(json_encode(array("msg"=>"gameId为空","status"=>0)));
        }elseif($userId=='') {
            exit(json_encode(array("msg" => "用户ID为空", "status" => 0)));
        }elseif($roleId==''){
            exit(json_encode(array("msg" => "角色ID为空", "status" => 0)));
        }elseif($serverId==''){
            exit(json_encode(array("msg" => "服务器ID为空", "status" => 0)));
        }elseif($orderId==''){
            exit(json_encode(array("msg" => "订单号为空", "status" => 0)));
        }elseif($price==''){
            //支付价格不能为空
            exit(json_encode(array("msg" => "支付金额为空", "status" => 0)));
        }elseif($currencyCode==''){
            //货币类型不能为空
            exit(json_encode(array("msg" => "货币类型为空", "status" => 0)));
        }

        $payModel=PayModel::getInstance();
        $status=$payModel->hand_operation($gameId,$userId,$roleId,$serverId,$orderId,$price,$mapAmount,$currencyCode,$cText);
        if($status){
            //发送成功
            exit(json_encode(array("msg"=>"发送成功","status"=>1)));
        }else{
            //发送失败
            exit(json_encode(array("msg"=>"发送失败","status"=>0)));
        }
    }


    //第三方发送礼包接口
    public function thirdSendGift(){
        $gameId=$this->getParam("gameId");//游戏ID
        $serverId=$this->getParam("serevrId");//服务器ID
        $roleId=$this->getParam("roleId");//角色ID
        $giftID=$this->getParam("giftID");//礼包编号
        $title=$this->getParam("title");//邮件标题
        $uid=$this->getParam("userId");
        $content=$this->getParam("content");//邮件内容
        $sign=$this->getParam("sign");//签名
        $postData=$_POST;
        $createSign=$this->createSign($postData,self::KEY);
        if($gameId==''){
            exit(json_encode(array("status"=>0,"msg"=>"游戏ID不能为空")));
        }elseif($serverId==''){
            exit(json_encode(array("status"=>0,"msg"=>"服务器ID不能为空")));
        }elseif($roleId==''){
            exit(json_encode(array("status"=>0,"msg"=>"角色ID不能为空")));
        }elseif($giftID==''){
            exit(json_encode(array("status"=>0,"msg"=>"礼包编号不能为空")));
        }elseif($title==''){
            exit(json_encode(array("status"=>0,"msg"=>"邮件标题不能为空")));
        }elseif($content==''){
            exit(json_encode(array("status"=>0,"msg"=>"邮件内容不能为空")));
        }
        if($sign!=$createSign){
            exit(json_encode(array("status"=>0,"msg"=>"签名错误")));
        }else{

            //发送礼包
           $bool=sendGift($gameId,$serverId,$roleId,$uid,$giftID,$title,$content,0);

            if($bool){
                exit(json_encode(array("status"=>1,"msg"=>"礼包发送成功")));
            }else{
                exit(json_encode(array("status"=>0,"msg"=>"发送失败")));
            }
        }
    }

    //支付宝支付
    public function mayun(){
       $this->alipay();

    }


    public function webPay(){
        $key=$this->getParam("ugamekey");//key
        $gameId=$this->getParam("gameId");
        $serverId=$this->getParam("serverId");//服务器ID
        $type=$this->getParam("type",0);
        $Uid=$this->getParam("userId");//用户ID
        $roleId=$this->getParam("roleId");//角色ID
        $sku=$this->getParam("sku");//sku配置
        $ctext=stripslashes($this->getParam("cText"));//透传参数
        $cp_orderId=$this->getParam("cp_orderId");//cp订单号
        if($gameId == ''){
            die (json_encode(array("reCode"=>101,"reStatus"=>0,)));
        }elseif($type == ''){
            die (json_encode(array("reCode"=>112,"reStatus"=>0,)));
        }elseif($serverId == ''){
            die (json_encode(array("reCode"=>123,"reStatus"=>0,)));
        }

        $insetData=[
            "userId"=>$Uid,
            "roleId"=>$roleId,
            "serverId"=>$serverId,
            "cp_orderId"=>$cp_orderId,
            "ctext"=>$ctext,
            "sku"=>$sku,
            "gameId"=>$gameId,
            "ctime"=>time(),//时间戳
        ];
        $model=ApiModel::getInstance();
        $priceData=$model->getPriceBySku($gameId,$type,$sku);
        if(!empty($priceData)){
            $insetData['price']=$priceData['price'];
            $insetData['amount']=$priceData['amount'];
        }

        $model->table("pay_data")->add($insetData);
        $url="http://gameapi.viphigame.com/level/index.html?order=".$cp_orderId;
        header("Location:{$url}");
    }

    /*
     * 支付宝支付
     */
    public function alipay(){
        $sku=$this->getParam("Sku");//金额
        $userId=$this->getParam("userId");//用户ID
        $roleId=$this->getParam("roleId");//角色ID
        $serverId=$this->getParam("serverId");//服务器ID
        $gameId=$this->getParam("gameId");//游戏ID
        $type=$this->getParam("type");//设备类型 0 android 1 ios
        $currencyCode=$this->getParam("currencyCode","RMB");//获取类型
        $cp_orderId=$this->getParam("cp_orderId");//cp订单
        $cText=stripslashes($this->getParam("cText"));//
        $key=$this->getParam("Ugamekey");//game key
        $apiModel=ApiModel::getInstance();
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key

        if($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>138,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"roleId 空")));
        }
        if($userId==''){
            die(json_encode(array('Status'=>0,'Code'=>139,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"userId 空")));
        }
        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }
        if($sku==''){
            //sku不能为空
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }

        $priceData=$apiModel->getPriceBySku($gameId,$type,$sku);
        if(empty($priceData)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品不存在")));
        }
        $amount=$priceData['amount'];

        // 获取优惠信息
        // if($gameId == 2001){
            $info = $this->getCacheDiscountInfo([
                'userId'   => $userId,
                'gameId'   => $gameId,
                'shop_id'  => $priceData['id'],
                'roleId'   => $roleId,
                'serverId' => $serverId,
            ]);
            $percent = $info ? bcdiv($info['percent'],100,2) : 1;
            $amount  = $amount * $percent;
        // }

        // 这里判断防沉迷开关
        $AntiAddiction = $apiModel->AntiAddiction($gameId,$userId);
        if( $AntiAddiction['if_auth'] == 1){
            $idCard=IdCardModel::getInstance();
            $idCard_result=$idCard->getRealNameAndAge($userId);
            if($idCard_result['isRealName'] == 1){
                $payAllModel=PayAllModel::getInstance();
                $stime = date("Y-m").'-01 00:00:00';
                $etime = date('Y-m-d', strtotime("$stime +1 month -1 day")).' 23:59:59';
                $stime = strtotime($stime);
                $etime = strtotime($etime);
                $pay_info = $payAllModel->table("pay_all")->where(['gameId'=>$gameId,'userId'=>$userId,'time'=>['between',[$stime,$etime]]])->field('userId,ifnull(sum(amount),0) amount')->find();
                if(empty($pay_info)){
                    $paid_amount = 0;
                }else{
                    $paid_amount = $pay_info['amount'];
                }
                //已实名处理
                //剩余付费额度
                if($idCard_result['age'] < 8 ){
                    // 8岁以下禁止付费
                    $paid_amount=0;
                    if($amount > 0){
                        $result['Status']=3;
                        $result['Code']=100;
                        $result['Msg']='未满8周岁用户禁止充值';
                        exit(json_encode($result));
                    }
                }else if($idCard_result['age'] >= 8 && $idCard_result['age'] < 16){
                    $paid_amount=bcsub(200,$paid_amount,2);
                    if($amount > 50){
                        $result['Status']=3;
                        $result['Code']=101;
                        $result['Msg']='未满16周岁用户单次充值不超过50元';
                        exit(json_encode($result));
                    }
                }else if($idCard_result['age'] >= 16 && $idCard_result['age'] < 18){
                    $paid_amount=bcsub(400,$paid_amount,2);
                    if($amount > 100){
                        $result['Status']=3;
                        $result['Code']=102;
                        $result['Msg']='未满18周岁用户单次充值不超过100元';
                        exit(json_encode($result));
                    }
                }else{
                    $paid_amount='无限制';
                }
                if($paid_amount != '无限制' && $paid_amount <= 0){
                    $result['Status']=3;
                    $result['Code']=103;
                    $result['Msg']='未成年玩家当月可充值额度已用光';
                    exit(json_encode($result));
                }
                if($paid_amount != '无限制' && bcsub($paid_amount,$amount,2) < 0){
                    $result['Status']=3;
                    $result['Code']=104;
                    $result['Msg']='您本月充值额度不足，剩余额度为'.$paid_amount.'元';
                    exit(json_encode($result));
                }
            }else{
                $result['Code']=105;
                $result['Status']=2;
                $result['Msg']='未实名认证用户禁止充值,请您先实名认证';
                exit(json_encode($result));
            }
        }


        $payData    =   [
            'userId'        =>  $userId,
            'roleId'        =>  $roleId,
            'serverId'      =>  $serverId,
            'cp_orderId'    =>  $cp_orderId,
            'ctime'         =>  time(),
            'ctext'         =>  $cText,
            'sku'           =>  $sku,
            'price'         =>  $priceData['price'],
            'amount'        =>  $amount,
            'gameId'        =>  $gameId,
        ];
        $apiModel->table('pay_data')->add($payData);

        $sendData=array(
            "gameId"=>$gameId,
            "userId"=>$userId,
            "roleId"=>$roleId,
            "serverId"=>$serverId,
            "amount"=>$amount,
            "type"=>$type,
            "cp_orderId"=>$cp_orderId,
            "cText"=>$cText,
            "price"=>$priceData['price'],
            "ip"=>get_client_ip(),
            "sku_amount"=>$priceData['amount'],
        );

        if($gameId==2391){
            $data=curlPost("http://pay.higame.cn/walipay/huifu.php?a=index",$sendData);
            exit($data);
        }else{
            $data=curlPost("http://pay.higame.cn/walipay/online.php?a=index",$sendData);
            exit($data);
        }



    }


    /**
     * 每个充值档位开启
     */
    public function btnStartFlag(){
        $key=$this->getParam("Ugamekey");//key
        $gameId=$this->getParam("Ugameid");
        $serverId=$this->getParam("Serverid");//服务器ID
        $type=$this->getParam("Sdktype");
        $version=$this->getParam("Version");
        $Uid=$this->getParam("Uid");//用户ID
        $roleId=$this->getParam("Roleid");//角色ID
        $packname=$this->getParam("currentbundleId");//包名
        $sku=$this->getParam("Sku");//sku配置
        if($gameId == ''){
            die (json_encode(array("Code"=>101,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($version == ''){
            die (json_encode(array("Code"=>120,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($type == ''){
            die (json_encode(array("Code"=>112,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }elseif($serverId == ''){
            die (json_encode(array("Code"=>123,"Status"=>0,'fbflag'=>0,'5starflag'=>0,'paymentflag'=>0)));
        }

        $model=ApiModel::getInstance();
        $model->btnStartFlag($gameId,$type,$version,$key,$serverId,$packname,$sku,$Uid);
    }


    // /**
    //  * 跳转地址
    // */
    // public function redirect(){
    //     $orderId=$this->getParam("orderId");

    //     $payModel=PayModel::getInstance();

    //     $data=$payModel->table("kuai")->where(array("orderId"=>$orderId))->find();
    //     if(!empty($data)){

    //         $base64Data=base64_decode($data['url']);

    //         echo "<script>window.location.href='{$base64Data}';</script>";
    //     }else{
    //         echo "no data";
    //     }
    // }


    /**
     * 微信支付
     */
    public function mahuateng(){

        $this->weixin();
    }


    /**
     * 获取支付优惠详情
     */
    public function getDiscountInfo(){
        $sku      = $this->getParam("Sku");//档次
        $userId   = $this->getParam("userId");//用户ID
        $roleId   = $this->getParam("roleId");//角色ID
        $serverId = $this->getParam("serverId");//服务器ID
        $gameId   = $this->getParam("gameId");//游戏ID
        $type     = $this->getParam("type",0);//设备类型 0 android 1 ios
        $key      = $this->getParam("Ugamekey");//game key
        $packname = $this->getParam("name","嗨玩游戏");//运用名称
        $packId   = $this->getParam("packname");//包名
        

        if(empty($roleId)){
            die(json_encode(array('Status'=>0,'Code'=>138,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"roleId 空")));
        }
        if(empty($userId)){
            die(json_encode(array('Status'=>0,'Code'=>139,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"userId 空")));
        }
        if($type == ''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }

        if(empty($sku)){//sku不能为空
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }

        $apiModel = ApiModel::getInstance();

        $gameConfigData = $apiModel->getConfigByGameId($gameId);//获取google 的key
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }

        $priceData=$apiModel->getPriceBySku($gameId,$type,$sku);
        if(empty($priceData)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品不存在")));
        }
        $amount = $priceData['amount'];

        // 获取优惠信息
        $info = $this->getCacheDiscountInfo([
            'userId'   => $userId,
            'gameId'   => $gameId,
            'shop_id'  => $priceData['id'],
            'roleId'   => $roleId,
            'serverId' => $serverId,
        ]);
        $percent = $info ? bcdiv($info['percent'],100,2) : 1;

        $money = ($amount * $percent) ? ($amount * $percent) :$amount;

        $pointsConfig = $this->getPointsConfig($gameId);//获取积分配置
        $points = $this->getUserPoints(['gameId'=>$gameId,'userId'=>$userId]);//获取用户总积分

        if($pointsConfig && ($points !== false)){
            $totalPoints = $points;
        }

        // 只有异次元主公参与, 比例1:10
        /*if($gameId == 2561){
            $aliPoints = $apiModel->table('alipay')->field('SUM(amount) amount')->where([
                'userId'  => $userId,
                'gameId'  => $gameId,
                'ctime'   => ['ge',strtotime('2022-05-26')],
                'isFinsh' => 1
            ])->find()['amount'];
            

            $wxPoints = $apiModel->table('weixin_pay')->field('SUM(amount) amount')->where([
                'userId'  => $userId,
                'gameId'  => $gameId,
                'ctime'   => ['ge',strtotime('2022-05-26')],
                'isFinsh' => 1
            ])->find()['amount'];

            $allPoints = intval($aliPoints) + intval($wxPoints);            

            // 获取用户已使用的积分
            $usePoints = $apiModel->table('points_details')->field('SUM(amount) amount')->where([
                'userId'  => $userId,
                'isFinsh' => 1
            ])->find()['amount'];

            // var_dump($usePoints);

            $totalPoints = $allPoints - intval($usePoints*10);
        }*/

        $res = [
            'amount'    => bcsub($amount,0,2),//原始金额
            'percent'   => $percent ? $percent : 1,
            'money'     => bcsub($money,0,2),//折扣金额
            'diff'      => bcsub($amount,$money,2),
            'goodsName' => $priceData['tradeName'],
        ];
        if(isset($totalPoints)){
            $res['points'] = $totalPoints;
            $res['tips'] = "每充值1元可获{$pointsConfig['multiple']}积分, 每10积分可以抵扣1元";
        }

        exit(json_encode($res));
    }

    /**
     * 获取用户积分
     * @param int gameId
     * @return bool|int [false|points]
     */
    public function getUserPoints($params){
        $gameId = $params['gameId'];
        $userId = $params['userId'];
        $key    = "POINTS_LIST_{$gameId}_{$userId}";
        $redis  = new RedisCache();
        $redis  = $redis->hander;
        $info   = $redis->exists($key);

        $res = false;
        $points = 0;

        $apiModel = ApiModel::getInstance();
        if($info){
            $res = $redis->get($key);
        }else{

            $info = $apiModel->table('points_list')->where([
                'gameId' => $gameId,
                'userId' => $userId,
            ])->find();

            if($info){
                $res = $info['points'];
                $redis->set($key,$res);
                $redis->expire($key,1800);//半小时一次
            }else{
                $data = ['userId'=>$userId,'gameId'=>$gameId];
                $add = $apiModel->table('points_list')->add($data);
                if($add === false){
                    sendMail("新增积分数据失败",'jinxu@higame.cn',json_encode($data));
                }
                $res = $points;
                $redis->set($key,$res);
                $redis->expire($key,1800);//半小时一次
            }
        }

        /*if($info){
            $add = $apiModel->table('points_detail')->where([
                'userId'        => $userId,
                'gameId'        => $gameId,
                'type'          => 1,
                'isSend'        => ['ge','0'],
                'expired_time'  => ['ge',time()],
            ])->field('IFNULL(SUM(points),0) AS points')->find()['points'];


            // if($gameId == 2001){
            //     var_dump("add[{$add}]---sql".$apiModel->getlastSql());
            // }

            // if($gameId == 2561 && $userId == 2485605){
            //     $log = new LogController();
            //     $str = "sql:".$apiModel->getlastSql();
            //     $log->writeLog('addPointsInfo.log',$str);
            // }

            $sub = $apiModel->table('points_detail')->where([
                'userId'        => $userId,
                'gameId'        => $gameId,
                'type'          => 2,
                'isSend'        => ['ge','0'],
                'expired_time'  => ['ge',time()],
            ])->field('IFNULL(SUM(points),0) AS points')->find()['points'];
            
            // if($userId == 2268895){
            //     var_dump("redis[{$add}--{$sub}]---sql".$apiModel->getlastSql());
            // }
            // if($gameId == 2561 && $userId == 2485605){
            //     // var_dump("add[{$add}]--sub[{$sub}]");
            //     $log = new LogController();
            //     $str = "sql:".$apiModel->getlastSql();
            //     $log->writeLog('addPointsInfo.log',$str);
            // }

            $res = bcsub($add,$sub);
        }else{

            $info = $apiModel->table('points_config')->where([
                'game_id'      => $gameId,
                'switch_start' => ['le',time()],
                'switch_end'   => ['ge',time()],
            ])->order('id desc')->find();

            // if($gameId == 2001){
            //     var_dump("info-sql:".$apiModel->getlastSql());
            // }

            if($info){
                $add = $apiModel->table('points_detail')->where([
                    'userId'        => $userId,
                    'gameId'        => $gameId,
                    'type'          => 1,
                    'isSend'        => ['ge','0'],
                    'expired_time'  => ['ge',time()],
                ])->field('IFNULL(SUM(points),0) AS points')->find()['points'];
    
                // if($gameId == 2001){
                //     var_dump("add-sql:".$apiModel->getlastSql());
                // }

                $sub = $apiModel->table('points_detail')->where([
                    'userId'        => $userId,
                    'gameId'        => $gameId,
                    'type'          => 2,
                    'isSend'        => ['ge','0'],
                    'expired_time'  => ['ge',time()],
                ])->field('IFNULL(SUM(points),0) AS points')->find()['points'];

                // if($gameId == 2001){
                //     var_dump("sub-sql:".$apiModel->getlastSql());
                //     var_dump("数据库".bcsub($add,$sub));die;
                // }

                // if($userId == 2268895){
                //     var_dump("数据库[{$add}--{$sub}]---sql".$apiModel->getlastSql());
                // }

                $res = bcsub($add,$sub);
            }
        }*/

        // if($gameId == 2561 && $userId == 2485605){
        $log = new LogController();
        $log->writeLog(date('Y-m-d').'addPointsInfo.log',"[".date('Y-m-d H:i:s')."] getUserPoints---uid[{$userId}]---gameId[{$gameId}]---points[{$res}]");
        // }

        return $res;
    }

    /**获取积分配置
     * 
     * @param int gameId
     * @return bool|int [false|points]
     */
    public function getPointsConfig($gameId){
        $key   = "POINTS_CONFIG_{$gameId}";
        $redis = new RedisCache();
        $info  = $redis->exists($key);
        $time  = time();

        $res = [];
        if($info){

            $info = $redis->get($key);
            foreach($info as $val){
                if(($time >= $val['switch_start']) && ($time <= $val['switch_end'])){
                    $res = $val;
                    break;
                }
            }
        }else{
            $apiModel = ApiModel::getInstance();
            $res = $apiModel->table('points_config')->where([
                'game_id'      => $gameId,
                'switch_start' => ['le',$time],
                'switch_end'   => ['ge',$time],
            ])->order('id desc')->find();
        }

        return $res;
    }

    /**
     * @param $data 发货的数据
     * @param $payment 那种支付方式
     * @return bool
     * 积分抵扣发货接口--异次元
     */
    public function sendPointPay333(){
        die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"抱歉，积分兑换系统正在升级，请稍后再进行兑换")));

        $sku          = $this->getParam("Sku");//金额
        $userId       = $this->getParam("userId");//用户ID
        $roleId       = $this->getParam("roleId");//角色ID
        $serverId     = $this->getParam("serverId");//服务器ID
        $gameId       = $this->getParam("gameId");//游戏ID
        $type         = $this->getParam("type");//设备类型 0 android 1 ios
        $currencyCode = $this->getParam("currencyCode","RMB");//获取类型
        $cp_orderId   = $this->getParam("cp_orderId");//cp订单
        $cText        = stripslashes($this->getParam("cText"));//
        $key          = $this->getParam("Ugamekey");//game key
        $payment      = $this->getParam('payment','points');

        if($gameId == 2001){
            return $this->sendPointPay2([
                'sku'          => $sku,
                'userId'       => $userId,
                'roleId'       => $roleId,
                'serverId'     => $serverId,
                'gameId'       => $gameId,
                'type'         => $type,
                'currencyCode' => $currencyCode,
                'cp_orderId'   => $cp_orderId,
                'cText'        => $cText,
                'key'          => $key,
                'payment'      => $payment,
            ]);
        }

        $apiModel=ApiModel::getInstance();
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key

        if(!in_array($gameId,[2001,2561])){
            die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"哪凉快哪去")));
        }

        if($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>138,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"roleId 空")));
        }
        if($userId==''){
            die(json_encode(array('Status'=>0,'Code'=>139,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"userId 空")));
        }
        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }
        if($sku==''){
            //sku不能为空
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }

        $priceData=$apiModel->getPriceBySku($gameId,$type,$sku);
        if(empty($priceData)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品不存在")));
        }
        $amount = $priceData['amount'];

        // 获取用户总金额
        $aliPoints = $apiModel->table('alipay')->field('SUM(amount) amount')->where([
            'userId'  => $userId,
            'gameId'  => $gameId,
            'ctime'   => ['ge',strtotime('2022-05-26')],
            'isFinsh' => 1
        ])->find()['amount'];

        $wxPoints = $apiModel->table('weixin_pay')->field('SUM(amount) amount')->where([
            'userId'  => $userId,
            'gameId'  => $gameId,
            'ctime'   => ['ge',strtotime('2022-05-26')],
            'isFinsh' => 1
        ])->find()['amount'];

        $allPoints = intval($aliPoints) + intval($wxPoints);            

        // 获取用户已使用的积分
        $usePoints = $apiModel->table('points_detail')->field('SUM(amount) amount')->where([
            'userId'  => $userId,
            'isFinsh' => 1
        ])->find()['amount'];

        $totalPoints = $allPoints - intval($usePoints*10);
        $totalPoints = $totalPoints ? $totalPoints :0;
        
        $diffPoint = $totalPoints - ($amount*10);

        if($diffPoint < 0){
            die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"积分不足")));
        }

        // 获取优惠信息
        $info = $this->getCacheDiscountInfo([
            'userId'   => $userId,
            'gameId'   => $gameId,
            'shop_id'  => $priceData['id'],
            'roleId'   => $roleId,
            'serverId' => $serverId,
        ]);
        $percent = $info ? bcdiv($info['percent'],100,2) : 1;
        $amount  = $amount * $percent;

        $gameConfigData = $apiModel->getConfigByGameId($gameId);

        $orderNo = $this->createOrderNo($gameId,$userId);//创建订单号

        // 给研发必要参数
        $sendData=array(
            "Ugameid"       => $gameId,//游戏名称
            "Uid"           => $userId,//用户id
            "Roleid"        => $roleId,//角色Id
            "Serverid"      => $serverId,//服务器ID
            "Orderid"       => $orderNo,//平台订单号
            "Time"          => time(),//时间戳
            "Pay_channel"   => 1,//1 google ios 2 第三方
            "Price"         => $amount,//交易金额
            "Currency_type" => $currencyCode,//货币类型
            "Amount"        => $priceData['price'],//游戏币数量
            "Cp_orderid"    => $cp_orderId,//cp_orderId
            "Ctext"         => $cText,//cText
        );

        // 判断 是否需要显示SkuPrice
        if($gameConfigData['if_pay_sku_amount'] == 1){
            $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$gameId,'cp_orderId'=>$cp_orderId])->find();
            $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$gameId,'sku'=>$pay_data_info['sku']])->find();
            $sendData["SkuPrice"] = $sku_info['amount'];
        }

        // 判断 支付回调将实际金额替换成sku金额开关
        if($gameConfigData['if_real_sku_amount'] == 1){
            $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$gameId,'cp_orderId'=>$cp_orderId])->find();
            $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$gameId,'sku'=>$pay_data_info['sku']])->find();
            $sendData["Price"] = $sku_info['amount'];
        }

        $sign = $this->createSign($sendData,$gameConfigData['app_server_secret']);
        $sendData['Sign'] = $sign;
        $flag = false;

        // 通知研发发货
        $log = new LogController();
        $filename = $sendData['Ugameid']."SendPay.log";
        for($i=0;$i<3;$i++){
            $result=curlPost(trim($gameConfigData['ios_pay']),$sendData);
            $codeData=json_decode($result,true);
            $writeData=array(
                // "orderId"   => $data['Orderid'],
                // "time"      => date("Y-m-d H:i:s",$data['Time']),
                "stime"     => date("Y-m-d H:i:s"),//触发发货时间
                "msg"       => $codeData,
                "param"     => $sendData,
                "payment"   => $payment,//支付方式对应的表名
                "gameId"    => $sendData['Ugameid'],
                "url"       => trim($gameConfigData['ios_pay']),
                "resultMsg" => $result,
            );
            $log->writeLog($filename,var_export($writeData,true));

            //发货成功
            if($codeData['code'] == 1){
                $flag=true;
                break;
            }
        }

        // 更新积分消费
        $points = intval($amount*10);
        $insertData = [
            "userId"        => $userId,
            "gameId"        => $gameId,
            "serverId"      => $serverId,//服务器ID
            "roleId"        => $roleId,//角色Id
            "amount"        => $amount,//游戏币数量
            "payType"       => 'POINT',
            "orderId"       => $orderNo,//平台订单号
            "ctime"         => time(),//时间戳
            "utime"         => time(),//时间戳
            "cpType"        => 1,//优惠类型（1积分抵扣）
            "isFinsh"       => 1,
            "type"          => 0,
            "currencyCode"  => $currencyCode,
            "price"         => $priceData['price'],//交易金额
            "cp_orderId"    => $cp_orderId,
            "points"        => $points,
        ];


        $res = $apiModel->table('points_details')->add($insertData);

        $incRes = $apiModel->execute("UPDATE iiu_points_list SET `points` = `points`-{$points} WHERE `userId` = {$userId} AND `gameId` = {$gameId} AND `points` > 0");
        if(!$res || $incRes === false){
            sendMail("更新积分消费记录失败",'jinxu@higame.cn',json_encode($insertData));
        }
        
        if(!$flag && $sendData['Ugameid']!="10000"){
            //发货失败,数据入库
            $insertData=array(
                "param"=>str_replace("\\", "\\\\", serialize($sendData)),
                "num"=>0,
                "ctime"=>time(),
                "utime"=>time(),
                "msg"=>serialize($codeData),
                "payment"=>$payment,
                "gameId"=>$sendData['Ugameid'],

            );
            $apiModel->table("queue")->add($insertData);
            // $body="掉单信息是订单号是:{$sendData['Orderid']},支付方式是:{$payment}";
            // if($sendData['Ugameid']!="10000"){
            //     // sendMail("掉单",'403345073@qq.com',$body);
            // }
        }

        if($flag){
            $apiModel->table('points_details')->where(['orderId'=>$orderNo])->save(['isSend'=>1]);
            die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"ok")));
        }

        die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"ok.")));
    }

    /**
     * @param $data 发货的数据
     * @param $payment 那种支付方式
     * @return bool
     * 积分抵扣发货接口---新版
     */
    public function sendPointPay(){

        $sku          = $this->getParam("Sku");//金额
        $userId       = $this->getParam("userId");//用户ID
        $roleId       = $this->getParam("roleId");//角色ID
        $serverId     = $this->getParam("serverId");//服务器ID
        $gameId       = $this->getParam("gameId");//游戏ID
        $type         = $this->getParam("type");//设备类型 0 android 1 ios
        $currencyCode = $this->getParam("currencyCode","RMB");//获取类型
        $cp_orderId   = $this->getParam("cp_orderId");//cp订单
        $cText        = stripslashes($this->getParam("cText"));//
        $key          = $this->getParam("Ugamekey");//game key
        $payment      = $this->getParam('payment','points');

        // if(!in_array($gameId,[2001,2561])){
        //     die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"哪凉快哪去")));
        // }

        // if(!in_array($userId,[2089321])){
        //     die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"抱歉，积分兑换系统正在升级，请稍后再进行兑换")));
        // }


        $apiModel = ApiModel::getInstance();
        $gameConfigData = $apiModel->getConfigByGameId($gameId);//获取google 的key


        if($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>138,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"roleId 空")));
        }
        if($userId==''){
            die(json_encode(array('Status'=>0,'Code'=>139,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"userId 空")));
        }
        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }
        if($sku==''){
            //sku不能为空
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }

        $priceData=$apiModel->getPriceBySku($gameId,$type,$sku);
        if(empty($priceData)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品不存在")));
        }
        $amount = $priceData['amount'];

        $log = new LogController();

        // 锁3s
        $redis  = new RedisCache();
        $redis  = $redis->hander;
        $poKey  = "sendPointPay_{$gameId}_{$roleId}_{$userId}_{$amount}";
        $addRes = $redis->incr($poKey);
        if($addRes == 1){
            $res=$redis->expire($poKey,3);
            $log->writeLog(date('Y-m-d').'addPointsInfo.log',"[".date('Y-m-d H:i:s')."] sendPointPay redis---gameId[{$gameId}]---uid[{$userId}]--key[{$poKey}]---amount[{$amount}]");
        }

        if($addRes > 1){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"请勿频繁操作")));
        }

        // 获取积分配置
        $pointsConfig = $this->getPointsConfig($gameId);

        if(empty($pointsConfig)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"活动已过期")));
        }
        
        // 获取用户可用积分
        $userPoints = $this->getUserPoints(['gameId'=>$gameId,'userId'=>$userId]);
        if($userPoints === false){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品已过期")));
        }

        $points = $amount*10;//需要兑换的积分

        if($gameId == 2001){
            $points = ceil($amount*10);//需要兑换的积分
        }

        // if($userId == 2268895){
        //     var_dump("userPoints[{$userPoints}]---amount[{$amount}]");die;
        // }

        $diffPoints = intval($userPoints - $points);//获取积分差值
        if($userPoints < 1 || $diffPoints < 0){
            die(json_encode(array('Status'=>0,'Code'=>138,"msg"=>"积分不足")));
        }

        // 获取游戏配置
        $gameConfigData = $apiModel->getConfigByGameId($gameId);

        $orderNo = $this->createOrderNo($gameId,$userId);//创建订单号

        // 给研发必要参数
        $sendData=array(
            "Ugameid"       => $gameId,//游戏名称
            "Uid"           => $userId,//用户id
            "Roleid"        => $roleId,//角色Id
            "Serverid"      => $serverId,//服务器ID
            "Orderid"       => $orderNo,//平台订单号
            "Time"          => time(),//时间戳
            "Pay_channel"   => 1,//1 google ios 2 第三方
            "Price"         => $amount,//交易金额
            "Currency_type" => $currencyCode,//货币类型
            "Amount"        => $priceData['price'],//游戏币数量
            "Cp_orderid"    => $cp_orderId,//cp_orderId
            "Ctext"         => $cText,//cText
        );

        // 判断 是否需要显示SkuPrice
        if($gameConfigData['if_pay_sku_amount'] == 1){
            $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$gameId,'cp_orderId'=>$cp_orderId])->find();
            $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$gameId,'sku'=>$pay_data_info['sku']])->find();
            $sendData["SkuPrice"] = $sku_info['amount'];
        }

        // 判断 支付回调将实际金额替换成sku金额开关
        if($gameConfigData['if_real_sku_amount'] == 1){
            $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$gameId,'cp_orderId'=>$cp_orderId])->find();
            $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$gameId,'sku'=>$pay_data_info['sku']])->find();
            $sendData["Price"] = $sku_info['amount'];
        }

        $sign = $this->createSign($sendData,$gameConfigData['app_server_secret']);
        $sendData['Sign'] = $sign;
        $flag = false;


        // 通知研发发货
        $log = new LogController();
        $filename = $sendData['Ugameid']."SendPay.log";
        for($i=0;$i<3;$i++){
            $result=curlPost(trim($gameConfigData['ios_pay']),$sendData);
            $codeData=json_decode($result,true);
            $writeData=array(
                // "orderId"   => $data['Orderid'],
                // "time"      => date("Y-m-d H:i:s",$data['Time']),
                "stime"     => date("Y-m-d H:i:s"),//触发发货时间
                "msg"       => $codeData,
                "param"     => $sendData,
                "payment"   => $payment,//支付方式对应的表名
                "gameId"    => $sendData['Ugameid'],
                "url"       => trim($gameConfigData['ios_pay']),
                "resultMsg" => $result,
            );
            $log->writeLog($filename,var_export($writeData,true));

            //发货成功
            if($codeData['code'] == 1){
                $flag=true;
                break;
            }
        }


        // 更新积分消费
        $insertData = [
            "userId"       => $userId,
            "gameId"       => $gameId,
            "serverId"     => $serverId,//服务器ID
            "roleId"       => $roleId,//角色Id
            "amount"       => $priceData['amount'],//交易金额
            "payType"      => 'POINT',
            "orderId"      => $orderNo,//平台订单号
            "ctime"        => time(),//时间戳
            "utime"        => time(),//时间戳
            "isSend"       => 0,
            "type"         => 2,//积分类型 2消费
            "cp_orderId"   => $cp_orderId,
            "cText"        => $cText,
            "points"       => $points,
            "pconfigId"    => $pointsConfig['id'],
            'expired_time' => isset($pointsConfig['expired_end']) ? $pointsConfig['expired_end'] : strtotime('2222-12-31 23:59:59'),
            'multiple'     => isset($pointsConfig['multiple']) ? $pointsConfig['multiple'] : 1,
        ];

        $res = $apiModel->table('points_detail')->add($insertData);

        // 更新总积分
        $incRes = $apiModel->execute("UPDATE iiu_points_list SET `points` = `points`-{$points} WHERE `userId` = {$userId} AND `gameId` = {$gameId} AND `points` > 0");
        $redisInfo = $apiModel->table('points_list')->where(['gameId' => $gameId,'userId' => $userId])->find();
        $redis->set("POINTS_LIST_{$gameId}_{$userId}",$redisInfo['points']);

        // 失败发通知
        if(!$res || $incRes === false){
            sendMail("更新积分消费记录失败",'jinxu@higame.cn',json_encode($insertData));
        }
        
        if(!$flag && $sendData['Ugameid']!="10000"){
            //发货失败,数据入库
            $insertData=array(
                "param"=>str_replace("\\", "\\\\", serialize($sendData)),
                "num"=>0,
                "ctime"=>time(),
                "utime"=>time(),
                "msg"=>serialize($codeData),
                "payment"=>$payment,
                "gameId"=>$sendData['Ugameid'],

            );
            $apiModel->table("queue")->add($insertData);
            sendMail($sendData['Ugameid']."积分兑换失败",'jinxu@higame.cn',json_encode($sendData));
            // $body="掉单信息是订单号是:{$sendData['Orderid']},支付方式是:{$payment}";
            // sendMail("积分兑换掉单",'jinxu@higame.cn',json_encode($body));
        }

        // 发货成功更新数据
        if($flag){
            $apiModel->table('points_detail')->where(['orderId'=>$orderNo])->save(['isSend'=>1]);
            
            $log->writeLog(date('Y-m-d').'addPointsInfo.log',"[".date('Y-m-d H:i:s')."] sendPointPay success---gameId[{$gameId}]---uid[{$userId}]".var_export($insertData,true));

            die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"ok")));
        }

        die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"ok.")));
    }

    
    /**
     * 获取缓存中的优惠信息
     * @param int $gameId
     * @param int $amount
     * @return int $perent
     */
    private function getCacheDiscountInfo($params){
        $userId   = $params['userId'];
        $gameId   = $params['gameId'];
        $roleId   = $params['roleId'];
        $shop_id  = $params['shop_id'];
        $serverId = $params['serverId'];

        $redis    = new RedisCache();
        $apiModel = ApiModel::getInstance();
        $key      = "iiu_discount_list_".$gameId; 
        $percent  = 100;
        $info = [];
        $list = $redis->exists($key);

        if($list){
            $list = $redis->get($key);
            
            foreach($list as $val){
                if($shop_id == $val['shop_id'] && ($val['start'] <= time()) && ($val['end'] >= time())){
                    $info[] = $val;
                }
            }

        }else{

            $info = $apiModel->table('discount_list')->where([
                'gameId'  => $gameId,
                'shop_id' => $shop_id,
                'start'   => ['le',time()],
                'end'     => ['ge',time()]
            ])->select();

        }


        $info = $this->formatTmpCp([
            'list'     => $info,
            'userId'   => $userId,
            'gameId'   => $gameId,
            'roleId'   => $roleId,
            'serverId' => $serverId,
        ]);
        
        $percent = $info['percent'];

        if($gameId == 2550){
            $fillable = [2409446,2089321,2622558,2546641,2370128,2257820,2370128,2653221];//白名单 前面2个测试号，后面玩家号
            if(!in_array($userId,$fillable)){
                $percent = 100;
            }
            // var_dump($percent,$info);die;
        }

        return ['percent'=>$percent,'type'=>$info['type']];
    }


    /**
     * 判断是否满足折扣条件
     * @param array list
     * @return array info
     */
    private function formatTmpCp($params){
        $list     = $params['list'];
        $userId   = $params['userId'];
        $gameId   = $params['gameId'];
        $roleId   = $params['roleId'];
        $serverId = $params['serverId'];

        $return_arr = [
            'id'      => 0,
            'percent' => 100,
            'type'    => 1,
        ];
        $arr = [];

        foreach($list as $v){
            $arr[$v['type']][$v['id']] = $v;
        }

        //优先级
        $if_ok = false;//是否处理完毕


        // if($userId == 1720242){
        //     var_dump($arr);
        // }

        // 新用户充值
        if(!$if_ok && isset($arr[3])){
            $list_arr = $arr[3];
            krsort($list_arr);//倒序
            
            // 获取用户相对应游戏的总充值金额
            $payAllModel = PayAllModel::getInstance();
            $userMoney   = $payAllModel->table('pay_all')->field('SUM(amount) amount')->where(['userId'=>$userId,'gameId'=>$gameId,'roleId'=>$roleId,'serverId'=>$serverId])->find()['amount'];

            if((float)$userMoney == 0){
                $return_arr = current($list_arr);
                $if_ok = true;
            }

            // if($userId == 1720242){
            //     var_dump(111,$return_arr);
            // }
        }

        // 累充折扣
        if(!$if_ok && isset($arr[2])){
            $list_arr = $arr[2];
            krsort($list_arr);//倒序
            
            // 获取用户相对应游戏的总充值金额
            $payAllModel = PayAllModel::getInstance();
            $userMoney   = $payAllModel->table('pay_all')->field('SUM(amount) amount')->where(['userId'=>$userId,'gameId'=>$gameId])->find()['amount'];
            $userMoney = floatval($userMoney);

            // if($userId == 1720242){
            //     var_dump("钱：$userMoney");
            // }

            foreach($list_arr as $v){
                // 符合累充范围
                $scope = explode(',',$v['scope']);
                if(count($scope) == 2){
                    list($start,$end) = $scope;
                    if(($userMoney >= $start) && ($userMoney <= $end)){
                        $return_arr = $v;
                        $if_ok = true;
                        break;
                    }
                }
            }
            // if($userId == 1720242){
            //     var_dump(222,$return_arr);
            // }
        }



        // 普通折扣
        if( !$if_ok && isset($arr[1])){
            $list_arr = $arr[1];
            krsort($list_arr);
            $return_arr = current($list_arr);
            $if_ok = true;
        }
        // if($userId == 1720242){
        //     var_dump(333,$return_arr);
        // }
        return $return_arr;
    }

    /*
     * 微信支付
     */
    public function weixin(){
        $sku=$this->getParam("Sku");//金额
        $userId=$this->getParam("userId");//用户ID
        $roleId=$this->getParam("roleId");//角色ID
        $serverId=$this->getParam("serverId");//服务器ID
        $gameId=$this->getParam("gameId");//游戏ID
        $type=$this->getParam("type",0);//设备类型 0 android 1 ios
        $currencyCode=$this->getParam("currencyCode","RMB");//获取类型
        $cp_orderId=$this->getParam("cp_orderId");//cp订单
        $cText=stripslashes($this->getParam("cText"));//
        //$spcText=$this->getParam("sPcText");//渠道参数
        $key=$this->getParam("Ugamekey");//game key
        $packname=$this->getParam("name","嗨玩游戏");//运用名称
        $packId=$this->getParam("packname");//包名
        $apiModel=ApiModel::getInstance();

        // if($gameId == '2001'){
        //     // exit(stripslashes($cText));
        //     var_dump($cText);
        // }
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key

        if($roleId==''){
            die(json_encode(array('Status'=>0,'Code'=>138,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"roleId 空")));
        }
        if($userId==''){
            die(json_encode(array('Status'=>0,'Code'=>139,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"userId 空")));
        }
        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }
        if($sku==''){
            //sku不能为空
            die(json_encode(array('Status'=>0,'Code'=>134,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Sku 空")));
        }

        $priceData=$apiModel->getPriceBySku($gameId,$type,$sku);
        if(empty($priceData)){
            die(json_encode(array('Status'=>0,'Code'=>137,"msg"=>"商品不存在")));
        }
        $amount=$priceData['amount'];


        // 获取优惠信息
        // if($gameId == 2001){
            $info = $this->getCacheDiscountInfo([
                'userId'   => $userId,
                'gameId'   => $gameId,
                'shop_id'  => $priceData['id'],
                'roleId'   => $roleId,
                'serverId' => $serverId,
            ]);
            $percent = $info ? bcdiv($info['percent'],100,2) : 1;
            $amount  = $amount * $percent;
        // }
        

        // 这里判断防沉迷开关
        $AntiAddiction = $apiModel->AntiAddiction($gameId,$userId);
        if( $AntiAddiction['if_auth'] == 1){
            $idCard=IdCardModel::getInstance();
            $idCard_result=$idCard->getRealNameAndAge($userId);
            if($idCard_result['isRealName'] == 1){
                $payAllModel=PayAllModel::getInstance();
                $stime = date("Y-m").'-01 00:00:00';
                $etime = date('Y-m-d', strtotime("$stime +1 month -1 day")).' 23:59:59';
                $stime = strtotime($stime);
                $etime = strtotime($etime);
                $pay_info = $payAllModel->table("pay_all")->where(['gameId'=>$gameId,'userId'=>$userId,'time'=>['between',[$stime,$etime]]])->field('userId,ifnull(sum(amount),0) amount')->find();
                if(empty($pay_info)){
                    $paid_amount = 0;
                }else{
                    $paid_amount = $pay_info['amount'];
                }
                //已实名处理
                //剩余付费额度
                if($idCard_result['age'] < 8 ){
                    // 8岁以下禁止付费
                    $paid_amount=0;
                    if($amount > 0){
                        $result['Status']=3;
                        $result['Code']=100;
                        $result['Msg']='未满8周岁用户禁止充值';
                        exit(json_encode($result));
                    }
                }else if($idCard_result['age'] >= 8 && $idCard_result['age'] < 16){
                    $paid_amount=bcsub(200,$paid_amount,2);
                    if($amount > 50){
                        $result['Status']=3;
                        $result['Code']=101;
                        $result['Msg']='未满16周岁用户单次充值不超过50元';
                        exit(json_encode($result));
                    }
                }else if($idCard_result['age'] >= 16 && $idCard_result['age'] < 18){
                    $paid_amount=bcsub(400,$paid_amount,2);
                    if($amount > 100){
                        $result['Status']=3;
                        $result['Code']=102;
                        $result['Msg']='未满18周岁用户单次充值不超过100元';
                        exit(json_encode($result));
                    }
                }else{
                    $paid_amount='无限制';
                }
                if($paid_amount != '无限制' && $paid_amount <= 0){
                    $result['Status']=3;
                    $result['Code']=103;
                    $result['Msg']='未成年玩家当月可充值额度已用光';
                    exit(json_encode($result));
                }
                if($paid_amount != '无限制' && bcsub($paid_amount,$amount,2) < 0){
                    $result['Status']=3;
                    $result['Code']=104;
                    $result['Msg']='您本月充值额度不足，剩余额度为'.$paid_amount.'元';
                    exit(json_encode($result));
                }
            }else{
                $result['Code']=105;
                $result['Status']=2;
                $result['Msg']='未实名认证用户禁止充值,请您先实名认证';
                exit(json_encode($result));
            }
        }

        if(empty($packname)){
            $packname=$packId;
        }
        
        $payData    =   [
            'userId'        =>  $userId,
            'roleId'        =>  $roleId,
            'serverId'      =>  $serverId,
            'cp_orderId'    =>  $cp_orderId,
            'ctime'         =>  time(),
            'ctext'         =>  $cText,
            'sku'           =>  $sku,
            'price'         =>  $priceData['price'],
            'amount'        =>  $amount,
            'gameId'        =>  $gameId,
        ];
        $apiModel->table('pay_data')->add($payData);

        $sendData=array(
            "gameId"=>$gameId,
            "userId"=>$userId,
            "roleId"=>$roleId,
            "serverId"=>$serverId,
            "amount"=>$amount,
            "sku_amount"=>$priceData['amount'],
            "type"=>$type,
            "cp_orderId"=>$cp_orderId,
            "cText"=>$cText,
            // "sPcText"=>$spcText,
            "currencyCode"=>$currencyCode,
            // "payType"=>"APP",
            "price"=>$priceData['price'],
            "packname"=>$packname,
            "packId"=>$packId,

        );


		$data=curlPost("http://pay.higame.cn/wweixin/online.php?a=index",$sendData);
        exit($data);
        
        if($gameId==2391){
            $data=curlPost("http://pay.higame.cn/wweixin/huifu.php?a=index",$sendData);
            exit($data);
        }else{
            $data=curlPost("http://pay.higame.cn/wweixin/online.php?a=index",$sendData);
            exit($data);
        }

    }

    /*
     * 订单查询接口
     */
    public function queryOrder(){
        $gameId=$this->getParam("gameId");//游戏ID
        $key=$this->getParam("Ugamekey");//game key
        $payment=$this->getParam("payment","weixin");//支付方式
        $orderId=$this->getParam("orderId");//订单号
        $apiModel=ApiModel::getInstance();
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key


        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }

        if($orderId==''){
            die(json_encode(array('Status'=>0,'Code'=>120,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"订单号不存在")));
        }
         $sendData=array(
            "orderId"=>$orderId,
             "payment"=>$payment,
         );
        $data=curlPost("http://pay.higame.cn/wweixin/online.php?a=checkOrder",$sendData);
        exit($data);
    }

    /*
     * 爱贝云支付
     */
    public function iapppay(){

        $waresid=$this->getParam("waresid");//商品ID
        $userId=$this->getParam("userId");//用户ID
        $gameId=$this->getParam("gameId");//游戏ID
        $roleId=$this->getParam("roleId");//角色ID
        $serverId=$this->getParam("serverId");//服务器ID
        $price=$this->getParam("amount");//价格
        $currencyCode=$this->getParam("currencyCode","RMB");//货币类型
        $cp_orderId=$this->getParam("cp_orderd");//cp订单号
        $cText=$this->getParam("cText");//传参数
        $type=$this->getParam("type");//设备类型 1 ios 0 android
        $key=$this->getParam("Ugamekey");//game key
        $apiModel=ApiModel::getInstance();
        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key
        if(empty($gameId)){
            $msg=array("msg"=>"缺少游戏ID","status"=>0,"Code"=>401);
            exit(json_encode($msg));
        }elseif(empty($userId)){
            $msg=array("msg"=>"缺少用户ID","status"=>0,"Code"=>405);
            exit(json_encode($msg));
        }elseif(empty($roleId)){
            $msg=array('msg'=>"缺少角色ID","status"=>0,"Code"=>403);
            exit(json_encode($msg));
        }elseif(empty($serverId)){
            $msg=array('msg'=>"缺少服务器ID","status"=>0,"Code"=>402);
            exit(json_encode($msg));
        }elseif(empty($price)){
            $msg=array('msg'=>"缺少金额","status"=>0,"Code"=>407);
            exit(json_encode($msg));
        }
        if(empty($cp_orderId)){
            $msg=array('msg'=>"缺少cp订单号","status"=>0,"Code"=>406);
                exit(json_encode($msg));
        }
        if($type==''){
            die(json_encode(array( "Code"=>112,"Status"=>0)));
        }
        if($gameConfigData['app_client_secret'] != $key)
        {
            die(json_encode(array('Status'=>0,'Code'=>109,'out_orderid'=>"","cp_orderid"=>"","amount"=>"","msg"=>"Ugamekey 错误")));
        }

        $sendData=array(
            "waresid"=>$waresid,
            "userId"=>$userId,
            "roleId"=>$roleId,
            "gameId"=>$gameId,
            "serverId"=>$serverId,
            "amount"=>$price,
            "cp_orderid"=>$cp_orderId,
            "cText"=>$cText,
            "type"=>$type,
        );

        $result=curlPost("https://pay.higame.cn/iapppay/online.php?a=index",$sendData);
        exit($result);
    }

    public function http_post_data($url, $data_string) {
        $curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL, $url);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle,CURLOPT_HEADER, 0);
        curl_setopt($curl_handle,CURLOPT_POST, true);
        curl_setopt($curl_handle,CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, 0);
        $response_json =curl_exec($curl_handle);
        $response =json_decode($response_json);
        curl_close($curl_handle);
        return $response;
    }



    /**
     * 新版---获取手机验证码
     * @param int phone
     * @param string sign
     */
    public function getVerify(){
        $phone=$this->getParam("phone");//手机号码
        $sign=$this->getParam("sign");//签名

        $isPhone=isPhone($phone);
        if(!$isPhone){
            die(json_encode(array('Status'=>0,'Code'=>168,"msg"=>"手机号码格式不对")));
        }

        $bool=$this->verifySign($sign,$phone);
        if(!$bool){
            die(json_encode(array('Status'=>0,'Code'=>172,"msg"=>"签名不正确")));
        }

        $apiModel = ApiModel::getInstance();
        $codeInfo = $apiModel->table('phone_code')->where(['phone' => $phone,'ctime' => ['ge',strtotime('today')]])->order('ctime desc')->select();

        $codeNums = count($codeInfo);

        $log = new LogController();
        $log->writeLog(date('Y-m-d').'sendPhoneCode.log',"[".date('Y-m-d H:i:s')."] phone[{$phone}]---nums[{$codeNums}]");

        if($codeInfo){
            $codeTime = reset($codeInfo)['ctime'];
            $codeTimes = end($codeInfo)['ctime'];

            $oneMin  = $codeTime + 60;
            $oneHour = $codeTimes + (60*60);
            $oneDay  = strtotime('today 23:59:59') - ($codeTimes + (60*60*24));

            // 1分钟内1条
            if($oneMin > time()){
                die(json_encode(['Status'=>0,'Code'=>175,"msg"=>"验证码请求太频繁"],320));
            }

            // 1小时内10条
            if(($oneHour > time()) && ($codeNums >= 10)){
                die(json_encode(['Status'=>0,'Code'=>175,"msg"=>"验证码请求太频繁"],320));
            }

            // 今天内20条
            if((strtotime('today 23:59:59') < ($codeTime + (60*60*24))) && ($codeNums >= 20)){
                die(json_encode(['Status'=>0,'Code'=>175,"msg"=>"验证码请求太频繁"],320));
            }
        }


        $rand     = random();
        $content  = "您本次验证码为{$rand},如果不是本人操作请忽略";
        $sendBool = $this->getSendCode($phone,$rand);
        if($sendBool){
            $this->write($phone,$rand);
            $apiModel->table('phone_code')->add(['phone'=>$phone,'code'=>$rand,'ctime'=>time()]);
            $log->writeLog(date('Y-m-d').'sendPhoneCode.log',"[".date('Y-m-d H:i:s')."] phone[{$phone}]---nums[{$codeNums}]---code[{$rand}]");
            die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"验证码发送成功")));
        }else{
            die(json_encode(array('Status'=>0,'Code'=>169,"msg"=>"验证码发送失败")));
        }
    }

    /*
     * 旧版---获取手机验证码
     */
    public function oldgetVerify(){
        $phone=$this->getParam("phone");//手机号码
        $sign=$this->getParam("sign");//签名

        $isPhone=isPhone($phone);

        // if(in_array($phone,['15070439391','13059313498'])){
            // return $this->newgetVerify($phone,$sign);
        // }

        if(!$isPhone){
            die(json_encode(array('Status'=>0,'Code'=>168,"msg"=>"手机号码格式不对")));
        }

        $bool=$this->verifySign($sign,$phone);
        if(!$bool){
            die(json_encode(array('Status'=>0,'Code'=>172,"msg"=>"签名不正确")));
        }

        $str=$this->readAll($phone);
        if(!empty($str)){
            $codeArr=explode("_",$str);

            // 1分钟内1条
            if(time()-60<end($codeArr)){
                // die(json_encode(array('Status'=>0,'Code'=>175,"msg"=>"验证码请求太频繁")));
            }


        }
        
        $rand=random();
        $content="您本次验证码为{$rand},如果不是本人操作请忽略";
        $sendBool=$this->getSendCode($phone,$rand);
        if($sendBool){
            $this->write($phone,$rand);
            die(json_encode(array('Status'=>1,'Code'=>100,"msg"=>"验证码发送成功")));
        }else{
            die(json_encode(array('Status'=>0,'Code'=>169,"msg"=>"验证码发送失败")));
        }
    }


    /*
     * 带有验证码的登录注册接口
     */
    public function phoneLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏的key
        $code=$this->getParam("code");//验证码
        $phone=$this->getParam("phone");//手机密码
        $type=$this->getParam("Sdktype",'0');//sdktype 类型

        $idfa=$this->getParam("idfa");
        $apiModel=ApiModel::getInstance();

        $verify=$this->read($phone);

        $apiModel->phoneLogin($gameId,$key,$phone,$code,$type,$verify,$idfa,$modelData);

    }


    /**
     * taptap的登录注册接口
     * @param int Ugameid 游戏ID
     * @param string Ugamekey 游戏key
     * @param string uname taptap用户名
     * @param int Sdktype 类型
     * @param int idfa 
     * @param string openId taptap返回的openid
     */
    public function tapTapLogin(){
        $modelData = $this->getDevParam();// 获取设备参数
        $gameId    = $this->getParam("Ugameid");//游戏ID
        $key       = $this->getParam("Ugamekey");//游戏的key
        $type      = $this->getParam("Sdktype",'0');//sdktype 类型
        $uname     = $this->getParam("username");//taptap用户名
        $openId    = $this->getParam("openId");
        $idfa      = $this->getParam("idfa");
        $apiModel  = ApiModel::getInstance();

        $openId = str_replace(' ','+',$openId);//替换空格为+

        $apiModel->tapTapLogin($gameId,$key,$uname,$openId,$type,$idfa,$modelData);
    }

    /*
     * 带有签名登录
     */
    public function signLogin(){
        // 获取设备参数 start
        $modelData = $this->getDevParam();
        // 获取设备参数 end
        $gameId=$this->getParam("Ugameid");//游戏ID
        $key=$this->getParam("Ugamekey");//游戏的key
        $sign=$this->getParam("sign");//签名登录
        $phone=$this->getParam("phone");//手机密码
        $type=$this->getParam("Sdktype",'0');//sdktype 类型
        $idfa=$this->getParam("idfa");

        $bool=$this->verifySign($sign,$phone);
        if(!$bool){
            die(json_encode(array('Status'=>0,'Code'=>172,"msg"=>"签名不正确")));
        }

        $apiModel=ApiModel::getInstance();
        $apiModel->signLogin($gameId,$key,$phone,$idfa,$type,$modelData);
    }


    /*
    * 获取验证码
    */
    public function getSendCode($phone,$content,$model = '1'){

        $url="https://api.mix2.zthysms.com/v2/sendSms";

        $time=time();
        $password=md5(md5("Hig@me110!").$time);
        switch($model){
            case '1':
                $content="【嗨玩游戏中心】验证码".$content."，用于手机登录，10分钟内有效。验证码请勿随意告知他人，谨防被骗。";
                break;
            case '2':
                $content="【嗨玩游戏中心】您已经通过手机号码找回游戏平台账号密码,这是您新的密码".$content.",请妥善保管";
                break;
            default:
                $content="【嗨玩游戏中心】验证码".$content."，用于手机登录，10分钟内有效。验证码请勿随意告知他人，谨防被骗。";
                break;
        }
        // $content="【木子时代】验证码".$content."，用于手机登录，10分钟内有效。验证码请勿随意告知他人，谨防被骗。";
        
        $data=[
            "username"=>"muzihy",
            "tKey"=>$time,
            "mobile"=>$phone,
            "content"=>$content,
            "password"=>$password,
        ];
        $header=array(
            // "Authorization:Bearer".$this->getParam("token"),
            "Content-Type:application/json;charset=utf-8",
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($header)&&is_array($header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        //是否https请求
        $https = substr($url, 0, 8) == "https://" ? true : false;
        if($https){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
        }
        if (is_array($data) && 0 < count($data)) {
            // $postBodyString ='{"appId":'.$data['appId'].","."outTradeNo:".$data['outTradeNo'].",sign:".$data['sign'].",subject:".$data['subject'].",totalAmount:".$data['totalAmount'].",notifyUrl:".$data['notifyUrl']."}";

            $postBodyString=json_encode($data);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postBodyString);
        }
        $res = curl_exec($ch);

        // var_dump($res);
        $charset=array();
        $charset[0]=substr($res,0,1);
        $charset[1]=substr($res,1,1);
        $charset[2]=substr($res,2,1);
        if(ord($charset[0])==239&&ord($charset[1])==187&&ord($charset[2])==191){
            $res=substr($res,3);

        }
        $result=json_decode($res,true);
        if(isset($result['code'])&&$result['code']==200){
            return true;
        }else{
            return false;
        }

    }


    /*
     * 获取短信验证码签名
     */
    public function verifySign($sign,$phone){
        $generateSign=md5($phone.self::PHONEKEY);

        if($sign==$generateSign){

            return true;
        }else{
            return false;
        }
    }


    /*
     * 写进文件
     */
    public function write($phone,$random){
        $logPath="./code/{$phone}.log";
        $data=$random."_".time();
        file_put_contents($logPath,$data);
    }


    /*
     * 获取该文件的数据
     */
    public function readAll($phone){
        $logPath="./code/{$phone}.log";
        if(file_exists($logPath)){
            return file_get_contents($logPath);
        }else{
            return false;
        }
    }

    /**
     * 新版---读数据
     * @param int phone
     */
    public function newRead($phone){
        $apiModel = ApiModel::getInstance();
        $info = $apiModel->table('phone_code')->where(['phone' => $phone,'ctime' => ['ge',strtotime('today')]])->order('ctime desc')->find();

        if($info){
            if(time()-1800 > end($info['ctime'])){

                return false;
            }else{
                return $info['code'];
            }
        }else{
            return false;
        }
    }

    /*
     * 读数据
     */
    public function read($phone){
        $logPath="./code/{$phone}.log";
        if(file_exists($logPath)){

            $str=file_get_contents($logPath);

            $data=explode("_",$str);
            if(time()-1800>end($data)){

                return false;
            }else{
                return $data[0];
            }
        }else{
            return false;
        }
    }


    public function test(){

        // $this->getSendCode("13760040664","test");

        // $payModel=PayAllModel::getInstance();
        // $payModel->execute("selkkk");

        // $apiModel=ApiModel::getInstance();
        // $data=$apiModel->functionStart(2148,1);

        // var_dump($data);
    }
    /*************************************************************/
    /***********************统计idfa的数据************************/
    /*************************************************************/

    /**
     * sdk排重入库实现
     */
    public function contronList(){

        $ip=get_client_ip();

        $ipList=array("134.175.174.151","120.79.188.249","47.92.24.80");
        if(in_array($ip,$ipList)){

            $apiModel=ApiModel::getInstance();
            $result=$apiModel->contronInsertData();

            file_put_contents("./Log/log.log",date("Y-m-d H:i:s").$result."\r\n",FILE_APPEND);

        }else{
        }
    }

    /*
     * 排重接口实现
     */
    public function adRepeat(){

        $idfa=$this->getParam("idfa");
        $appId=$this->getParam("appId");//appID
        $apiModel=ApiModel::getInstance();

        $appData=$apiModel->getByAppId($appId);

        $msg=array(
            $idfa=>0,
        );
        if(!empty($appData)){
            $idfaData=$apiModel->getByGameAndIdfa($appData['gameId'],$idfa);
            if($idfaData){
                $msg[$idfa]=0;
            }else{
                $msg[$idfa]=1;
            }
        }else{

            $msg[$idfa]=0;
        }

        exit(json_encode($msg));
    }


    /*
     * 米桃排重接口
     */
    /*
    * 排重接口实现
    */
    public function miAdRepeat(){

        $idfa=$this->getParam("idfa");
        $appId=$this->getParam("appId");//appID
        $apiModel=ApiModel::getInstance();

        $appData=$apiModel->getByAppId($appId);

        $msg=array(
            "code"=>1,
            "msg"=>"未知",
            "data"=>array("idfa"=>1),

        );
        if(!empty($appData)){
            $idfaData=$apiModel->getByGameAndIdfa($appData['gameId'],$idfa);
            if(!$idfaData){
                $msg['data']=array("idfa"=>0);
                $msg['code']=0;
            }
        }else{

            $msg["code"]=2;
            $msg['msg']="appid不存在";
        }

        exit(json_encode($msg));
    }


    /*
   * 米桃赚赚接口
   */
    public function miadSubmit(){
        $appId=$this->getParam("appid");//appId的数据
        $idfa=$this->getParam("idfa");//idfa的数据
        $ip=$this->getParam("ip");//ip的地址
        $channel=$this->getParam("channel",2);//1默认 2米桃

        $msg=array("code"=>1,"msg"=>"未知错误","data"=>array("status"=>false));
        if(empty($appId)){
            $msg['msg']="appid为空";
            exit(json_encode($msg));
        }

        if(empty($idfa)){
            $msg['msg']="idfa为空";
            exit(json_encode($msg));
        }

        if(empty($ip)){
            $msg['msg']="ip地址为空";
        }

        $apiModel=ApiModel::getInstance();

        $appData=$apiModel->getByAppId($appId);
        if(!empty($appData)){

            $insertData=array(
                "idfa"=>$idfa,
                "gameId"=>$appData['gameId'],
                "ctime"=>time(),
                "utime"=>time(),
                "ip"=>$ip,
                "channel"=>$channel,
            );

            $apiModel->insertStaticData($insertData);

            // $status=$apiModel->table("statics_idfa")->add($insertData);
            $idfaData=$apiModel->getByGameAndIdfa($appData['gameId'],$idfa);
            if($idfaData){
                $msg['code']=0;
                $msg['msg']="ok";
                $msg['data']=array("status"=>true);
                exit(json_encode($msg));
            }else{
                $msg['msg']="激活失败";
                $msg['data']=array("status"=>false);
                exit(json_encode($msg));
            }
            // if(is_array($idfaData)&&!empty($idfaData)){

            //     $apiModel->table("sdk_collection")->where(array("id"=>$idfaData['id']))->save(array("channel"=>$channel));
            //     $msg['code']=0;
            //     $msg['msg']="ok";
            //     $msg['data']=array("status"=>true);
            //     exit(json_encode($msg));
            // }elseif($idfaData>=0){
            //     $apiModel->activeData($idfaData,$channel);
            // } else{
            //         $msg['msg']="激活失败";
            //     $msg['data']=array("status"=>false);
            //     exit(json_encode($msg));
            // }
        }else{
            $msg['msg']="appId的信息不存在";
            exit(json_encode($msg));
        }
    }

    /*
   * 风暴对接上报
   */

    public function fengAdSubmit(){

        $_GET['channel']=3;
        $this->adSubmit();
    }

    /*
     * 热葫芦对接上报
     */
    public function reAdSubmit(){

        $_GET['channel']=2;
        $this->adSubmit();
    }

    //统计激活的数据
    public function adSubmit(){
        $appId=$this->getParam("appid");//appId的数据
        $idfa=$this->getParam("idfa");//idfa的数据
        $ip=$this->getParam("ip");//ip的地址
        $channel=$this->getParam("channel",1);//1默认 2米桃
        $msg=array("code"=>1,"result"=>"未知错误");
        if(empty($appId)){
            $msg['result']="appid为空";
            exit(json_encode($msg));
        }

        if(empty($idfa)){
            $msg['result']="idfa有空";
            exit(json_encode($msg));
        }

        if(empty($ip)){
            $msg['result']="ip地址为空";
        }

        $apiModel=ApiModel::getInstance();

        $appData=$apiModel->getByAppId($appId);
        if(!empty($appData)){

            $insertData=array(
                "idfa"=>$idfa,
                "gameId"=>$appData['gameId'],
                "ctime"=>time(),
                "utime"=>time(),
                "ip"=>$ip,
                "channel"=>$channel,
            );

            $apiModel->insertStaticData($insertData);
            $msg['code']=0;
            $msg['result']="ok";
            exit(json_encode($msg));
            $idfaData=$apiModel->getByGameAndIdfa($appData['gameId'],$idfa);
            if(!empty($idfaData)){
                $apiModel->table("sdk_collection")->where(array("id"=>$idfaData['id']))->save(array("channel"=>$channel));

                $msg['code']=0;
                $msg['result']="ok";
                exit(json_encode($msg));
            }else{
                //添加数据
                $msg['code']=1;
                $msg['result']="激活失败";
                exit(json_encode($msg));
            }
        }else{
            $msg['result']="appId的信息不存在";
            exit(json_encode($msg));
        }

    }
    /*
   * 风暴激活
   */
    public function fengClickDo(){
        $_GET['channel']=3;//re葫芦
        $this->clickDo();
    }


    /*
     * 热葫芦的点击数据
     */
    public function reClickDo(){
        $_GET['channel']=2;//re葫芦
        $this->clickDo();
    }


    /*
     * 爱盈利的点击数据
     */
    public function clickDo(){
        $appId=$this->getParam("appid");//appid的数据
        $channel=$this->getParam("channel",1);//渠道的数据
        $idfa=$this->getParam("idfa");
        $ip=$this->getParam("ip");//ip地址
        $timestamp=$this->getParam("timestamp");//时间
        $sign=$this->getParam("sign");//签名数据
        $callback=$this->getParam("callback");//callback的地址
        $apiModel=ApiModel::getInstance();
        $appData=$apiModel->getByAppId($appId);
        $msg=array("code"=>1,"result"=>"错误");
        if(!empty($appData)){

            $verifySign=md5($timestamp."81dc9bdb52d04dc20036dbd8313ed055");
            if($verifySign!=$sign){
                $msg['result']="签名错误";
                exit(json_encode($msg));
            }
            //格式  gameId_idfa_时间_是否执行_执行结果_渠道_ip_回调地址
            $str=$appData['gameId']."_".$idfa."_".$timestamp."_0_0_".$channel."_".$ip."_".urldecode($callback);

            $apiModel->clickDo($str);
            $msg['code']=0;
            $msg['result']="ok";
            exit(json_encode($msg));
        }else{
            $msg['result']="appid不存在";
        }

        exit(json_encode($msg));
    }

    /**
     * 实名验证的接口
     */
    public function realNameAction(){

        $AppClientUserId=$this->getParam("userId");//平台的用户ID
        $gameId=$this->getParam("Ugameid",'');//游戏Id
        $idCard=$this->getParam("idCard");//身份证号码,aes加密之后的数据
        $aes=new AesController();
        $idCard=$aes->decode($idCard);
        $name=$this->getParam("name");//玩家身份证的名字,aes加密
        $idCardModel=IdCardModel::getInstance();

        $redis  = new RedisCache();
        $redis  = $redis->hander;
        $poKey  = "REALNAME_{$gameId}_{$AppClientUserId}_{$idCard}";
        $addRes = $redis->incr($poKey);
        if($addRes == 1){
            $redis->expire($poKey,5);
            $idCardModel->doUserRealName($AppClientUserId,$idCard,$name,$gameId);
        }else{
            $msg=array( "Code"=>199,"Status"=>0,"msg"=>"请勿频繁实名",'error'=>'2');
            exit(json_encode($msg));
        }
    }


    /**
     * 获取实名验证的结果
     */
    public function getRealNameInfo(){
        $userId=$this->getParam("userId");//用户ID

        $idCard=IdCardModel::getInstance();
        $result=$idCard->getRealNameAndAge($userId);

        $result['Code']=100;
        $result['Status']=1;
        exit(json_encode($result));

    }

    /* 
    提交游戏时长数据
    */
    public function updatePlayGameTime(){
        $userId=$this->getParam("Uid");//用户ID
        $gameId=$this->getParam("Ugameid");//游戏ID
        $uuid=$this->getParam("Uuid");//设备ID.
        $randKey=$this->getParam("RandKey");//随机字符串 用来判断是不是同一个登录
        $check=$this->getParam("Check");//查询 1：只是查询，不增加时间.
        $day = date("Y-m-d");
        $last_day = date("Y-m-d",strtotime("-1 day"));
        $timestamp = time();
        $apiModel=ApiModel::getInstance();
        $payAllModel=PayAllModel::getInstance();
        // $gameConfigInfo = $apiModel->getConfigByGameId($gameId);

        $AntiAddiction = $apiModel->AntiAddiction($gameId,$userId);
        //获取当前游戏时长
        $play_time_info = $payAllModel->table('game_play_time')->where(['uid'=>$userId,'game_id'=>$gameId,'day'=>$day])->find();
        if($check != '1'){
            if(empty($play_time_info)){
                //获取前天游戏时长，进行判断是不是连续在线
                $play_last_time_info = $payAllModel->table('game_play_time')->where(['uid'=>$userId,'game_id'=>$gameId,'day'=>$last_day,'state'=>'1'])->find();
                if(!empty($play_last_time_info)){
                    // if(bcsub($timestamp,$play_last_time_info['utime']) > 600){

                        //今日内退出游戏超过10分钟之后的重新登录
                    if($play_last_time_info['key'] != $randKey){
                        
                        //首次不增加时间
                        $insetData  =   [
                            'uid'       =>  $userId,
                            'game_id'   =>  $gameId,
                            'uuid'      =>  $uuid,
                            'day'       =>  $day,
                            'minute'    =>  0,
                            'key'       =>  $randKey,
                            'state'     =>  1,
                            'ctime'     =>  $timestamp,
                            'utime'     =>  $timestamp,
                        ];
                        // 防沉迷开关
                        if( $AntiAddiction['if_auth'] == 1){
                            //上线上报信息
                            $this->updataLoginOut([$userId],[$uuid],1,$gameId);
                        }

                    }else{
                        //一直在线
                        //增加1分钟时长
                        $insetData  =   [
                            'uid'       =>  $userId,
                            'game_id'   =>  $gameId,
                            'uuid'      =>  $uuid,
                            'day'       =>  $day,
                            'minute'    =>  1,
                            'key'       =>  $randKey,
                            'state'     =>  1,
                            'ctime'     =>  $timestamp,
                            'utime'     =>  $timestamp,
                        ];
                    }
                    //游戏跨天的时候，及时下线隔天的记录
                    $payAllModel->table('game_play_time')->where(['uid'=>$userId,'game_id'=>$gameId,'day'=>$last_day,'state'=>'1'])->save(['state'=>'0']);
                }else{
                    //默认0分钟开始
                    $insetData  =   [
                        'uid'       =>  $userId,
                        'game_id'   =>  $gameId,
                        'uuid'      =>  $uuid,
                        'day'       =>  $day,
                        'minute'    =>  0,
                        'key'       =>  $randKey,
                        'state'     =>  1,
                        'ctime'     =>  $timestamp,
                        'utime'     =>  $timestamp,
                    ];
                }
                $payAllModel->table('game_play_time')->add($insetData);
                $play_minute    =   $insetData['minute'];
                // 防沉迷开关
                if( $AntiAddiction['if_auth'] == 1){
                    //上线上报信息
                    $this->updataLoginOut([$userId],[$uuid],1,$gameId);
                }
            }else{
                // // 判断是否请求频繁防止刷数据，因为5分钟请求一次，怕有误差。设置了40秒
                // if(bcsub($timestamp,$play_time_info['utime']) < 40){
                //     $result['Code']=101;
                //     $result['Status']=0;
                //     $result['Type']=0;
                //     $result['Age']=0;
                //     $result['Amount']=0;
                //     $result['Minute']=0;
                //     $result['Msg']='非法请求';
                //     exit(json_encode($result));
                // }
    
                // if(bcsub($timestamp,$play_time_info['utime']) > 600){
                    //今日内退出游戏超过10分钟之后的重新登录
                if($play_time_info['key'] != $randKey){
                    //首次不增加时间
                    $updateData     =   [
                        'uuid'      =>  $uuid,
                        'key'       =>  $randKey,
                        'state'     =>  1,
                        'utime'     =>  $timestamp,
                    ];
                    $play_minute    =   $play_time_info['minute'];
                    // 防沉迷开关
                    if( $AntiAddiction['if_auth'] == 1){
                        //上线上报信息
                        $this->updataLoginOut([$userId],[$uuid],1,$gameId);
                    }
                }else{
                    //一直在线
                    //增加1分钟时长
                    $updateData     =   [
                        'uuid'      =>  $uuid,
                        'minute'    =>  bcadd($play_time_info['minute'],1),
                        'key'       =>  $randKey,
                        'state'     =>  1,
                        'utime'     =>  $timestamp,
                    ];
                    $play_minute    =   $updateData['minute'];
                }
                
                $payAllModel->table('game_play_time')->where(['uid'=>$userId,'game_id'=>$gameId,'day'=>$day])->save($updateData);
                
            }
        }else{
            $play_minute    =   empty($play_time_info['minute']) ? 0 : $play_time_info['minute'];
        }
        
        
        
        
        $payAllModel=PayAllModel::getInstance();
        $stime = date("Y-m").'-01 00:00:00';
        $etime = date('Y-m-d', strtotime("$stime +1 month -1 day")).' 23:59:59';
        $stime = strtotime($stime);
        $etime = strtotime($etime);
        $pay_info = $payAllModel->table("pay_all")->where(['gameId'=>$gameId,'userId'=>$userId,'time'=>['between',[$stime,$etime]]])->field('userId,ifnull(sum(amount),0) amount')->find();
        if(empty($pay_info)){
            $amount = 0;
        }else{
            $amount = $pay_info['amount'];
        }
        $idCard=IdCardModel::getInstance();
        $idCard_result=$idCard->getRealNameAndAge($userId);
        $result['IsRealName'] = $idCard_result['isRealName'];
        if($idCard_result['isRealName'] == 1){
            //已实名处理
            //剩余付费额度 剩余时间
            if($this->checkCanPlayGame() != 2){
                $result['Minute']=bcsub(60,$play_minute);//未成年60分钟
            }else{
                $result['Minute']=bcsub(0,$play_minute);//非规定日期里面
            }

            if($idCard_result['age'] < 8 ){
                // 8岁以下禁止付费
                $result['Amount']=0;
                $result['MaxAmount']=0; //单次最大充值额度
            }else if($idCard_result['age'] >= 8 && $idCard_result['age'] < 16){
                $result['Amount']=bcsub(200,$amount,2);
                $result['MaxAmount']=50; //单次最大充值额度
            }else if($idCard_result['age'] >= 16 && $idCard_result['age'] < 18){
                $result['Amount']=bcsub(400,$amount,2);
                $result['MaxAmount']=100; //单次最大充值额度
            }else{
                $result['Minute']='无限制';
                $result['Amount']='无限制';
                $result['MaxAmount']='无限制'; //单次最大充值额度
            }
            // 判断剩余时间是否为负数
            if($result['Minute']!='无限制' && $result['Minute'] < 0){
                $result['Minute']=0;
            }
            $result['Age']=$idCard_result['age'];

//		 $tmpssss = [2409446,2821549,2821583];
//		 if(in_array($userId,$tmpssss)){
	           $log = new LogController();
            	$log->writeLog(date('Y-m-d').'updatePlayGameTime.log',"[".date('Y-m-d H:i:s')."] 实名信息 :".json_encode($idCard_result,320).'---result data:'.json_encode($result,320));
//		 }



            // 防沉迷开关
            if( $AntiAddiction['if_auth'] == 1){
                // 未满18岁处理
                if($idCard_result['age'] < 18){
                    $state = $this->checkCanPlayGame();
                    $result['Code']=100;
                    $result['Status']=1;
                    $result['Type']=1;
                    if($state != 0){
                        // $result['Msg']='未成年用户每天只能游戏1.5小时';
                        $result['Msg']='亲，根据《网络游戏未成年防沉迷系统》要求，未成年人仅可在周五、周六、周日以及法定节假日每日的20时至21时登录游戏';
                        exit(json_encode($result));
                    }else if($play_minute >= 60){
                        $result['Msg']='亲，根据《网络游戏未成年防沉迷系统》要求，您今天的在线时长已超过防沉迷系统规定时间，请您明天再来';
                        exit(json_encode($result));
                    }
                }
                

                // //剩余时长判断
                // if($idCard_result['age'] < 18 && $play_minute >= 60){
                //     $result['Code']=100;
                //     $result['Status']=1;
                //     $result['Type']=1;
                //     // $result['Msg']='未成年用户每天只能游戏1.5小时';
                //     $result['Msg']='亲，根据《网络游戏未成年防沉迷系统》要求，您今天的在线时长已超过防沉迷系统规定时间，请您明天再来';
                //     exit(json_encode($result));
                // }
            }

        }else{
            $result['Age']=0;//实名年龄
            $result['Amount']=0;//当月剩余额度
            $result['Minute']=0;//当天剩余时间
            $result['MaxAmount']=0; //单次最大充值额度
            // $result['Minute']=bcsub(60,$play_minute);
            // 判断剩余时间是否为负数
            if($result['Minute']!='无限制' && $result['Minute'] < 0){
                $result['Minute']=0;
            }
            // 防沉迷开关
            if( $AntiAddiction['if_auth'] == 1){
                //未实名处理
                if($play_minute >= 0){
                    $result['Code']=100;
                    $result['Status']=1;
                    $result['Type']=1;
                    // $result['Msg']='未实名认证用户每天只能游戏1小时';
                    $result['Msg']='亲，根据《网络游戏未成年防沉迷系统》要求，您游戏体验时间已用完，请您实名认证后继续游戏';
                    exit(json_encode($result));
                }
            }
        }
        
        
        $result['Code']=100;
        $result['Status']=1;
        $result['Type']=0;
        $result['Msg']='OK';
        exit(json_encode($result));
    }

    //未成年是否可以游戏 -- 判断是否法定节假日 以及周五、六、日 20:00--21:00
    private function checkCanPlayGame(){
        $timeStamp = time();
        $time = date("Y-m-d H:i:s",$timeStamp);
        $date = date('Y-m-d',$timeStamp);
        $stratTime = strtotime($date." 20:00:00");
        $endTime = strtotime($date." 21:00:00");
        
        $return = [];

        
        if(in_array(date('w',$timeStamp),['0','5','6'])){
            if($timeStamp >= $stratTime && $timeStamp <= $endTime){
                return 0;
            }else{
                //不在规定的时间里面
                return 1;
            }
        }else{
            //不在周五、六、日
            $payAllModel=PayAllModel::getInstance();
            $status = $payAllModel->table('legal_holidays')->where(['date'=>$date])->find();
            if(empty($status)){
                //不在规定的日期里面
                return 2;
            }else{
                if($timeStamp >= $stratTime && $timeStamp <= $endTime){
                    return 0;
                }else{
                    //不在规定的时间里面
                    return 1;
                }
            }
        }


        

    }



    //防沉迷上报下线数据方法
    public function updateAuthenticationLoginOut(){
        $timeStamp = time();
        $timeStamp_last = bcsub($timeStamp,180);
        $apiModel=ApiModel::getInstance();
        $payAllModel=PayAllModel::getInstance();
        $game_play_info = $payAllModel->table('game_play_time')->where(['state'=>'1','utime'=>['lt',$timeStamp_last]])->field('id,uid,game_id,uuid')->limit(128)->select();

        //下线
        
        $id_arr  = array_column(json_decode(json_encode($game_play_info),true), 'id');
        
        if(!empty($id_arr)){
            $payAllModel->table('game_play_time')->where(['id'=>['in',implode(",",$id_arr)]])->save(['state'=>'0']);
        }
        
        $login_out_info = [];
        foreach($game_play_info as $k => $v){
            if(isset($login_out_info[$v['game_id']])){
                array_push($login_out_info[$v['game_id']],['uid'=>$v['uid'],'uuid'=>$v['uuid']]);
            }else{
                $login_out_info[$v['game_id']][] = ['uid'=>$v['uid'],'uuid'=>$v['uuid']];
            }
        }
        foreach( $login_out_info as $k => $v){
            $AntiAddiction = $apiModel->AntiAddiction($k,$v['uid']);
            // 防沉迷开关
            if( $AntiAddiction['if_auth'] == 1){
                $uid_arr  = array_column($login_out_info[$k], 'uid');
                $uuid_arr  = array_column($login_out_info[$k], 'uuid');
                //上线上报信息
                print_r($this->updataLoginOut($uid_arr,$uuid_arr,0,$k));
                echo "<br>";
            }
        }
    }


   
    //上传数据到中宣部
    private function updataLoginOut($uid_arr = [],$uuid_arr  = [],$type,$gameId = ''){
        $apiModel=ApiModel::getInstance();
        if(!empty($gameId)){
            $game_config = $apiModel->table("game_config")->where(['gameId'=>$gameId])->find();
        }else{
            $game_config    =   [
                'auth_appid'        =>  '',
                'auth_secret_key'   =>  '',
                'auth_bizid'        =>  '',
            ];
        }
        //请求数据
        $authentication = new Authentication($game_config['auth_appid'],$game_config['auth_secret_key'],$game_config['auth_bizid']);
        
        // $uid_arr_info = array_merge($uid_arr);



        if(count($uid_arr)>0){
            $realname_user_info = $apiModel->table("realname_user")->where(['userid'=>['in',implode(",",$uid_arr)]])->field('userId,pi')->select();
        }else{
            $realname_user_info = [];
        }
        

        //  查询用户实名信息
        for($i=0;$i<count($realname_user_info);$i++){
            $realname[$realname_user_info[$i]['userId']] = $realname_user_info[$i]['pi'];
        }

        for($i=0;$i<count($uid_arr);$i++){
            $data[] = [
                //可行 游客
                'no' => bcadd($i,1),//序号
                'si' => md5($gameId.'_'.$uid_arr[$i]),//内部对应的用户ID
                'bt' => $type,// 上线/下线
                'ot' => $type == 0 ? bcsub(time(),160) : time() ,//时间戳
                'ct' => isset($realname[$uid_arr[$i]]) && !empty($realname[$uid_arr[$i]]) ? 0 : 2,// 游客/用户
                'di' => $uuid_arr[$i],//uuid
                'pi' => isset($realname[$uid_arr[$i]]) && !empty($realname[$uid_arr[$i]]) ? $realname[$uid_arr[$i]] : '',//PI值
            ];
        }
        // print_r($data);
        $state = $authentication->loginout($data);
        return $state;
    }


    private function getNextMonthDays($date){
        $timestamp=strtotime($date);
        $arr=getdate($timestamp);
        if($arr['mon'] == 12){
            $year=$arr['year'] +1;
            $month=$arr['mon'] -11;
            $firstday=$year.'-0'.$month.'-01';
            $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        }else{
            $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)+1).'-01'));
            $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        }
        return array($firstday,$lastday);
    }

    /**
     * 获取广告相关的配置信息
     */
    public function getAdvConfig(){

        $gameId=$this->getParam("gameId");//游戏ID
        $version=$this->getParam("vesion","0");//版本号

        $apiModel=ApiModel::getInstance();//游戏ID

        $gameConfig=$apiModel->getConfigByGameId($gameId);
        if(!empty($gameConfig)){


            $configData=$apiModel->table("adv_config")->where(array("gameId"=>$gameId,"version"=>$version))->find();
            if(empty($configData)){
                $configData=$apiModel->table("adv_config")->where(array("gameId"=>$gameId,"version"=>"0"))->find();
            }

            if(!empty($configData)){

                $jsonData=[
                    "Code"=>100,
                    "Status"=>1,
                    "appID"=>$configData['appID'],//google的appID
                    "unitID"=>$configData['unitID'],//google的单元ID
                    "placementID"=>$configData['placementID'],//facebook的广告ID
                    "msg"=>"数据获取成功",
                ];
                if($configData['AdChannel']==1){
                    $jsonData['AdChannel']="Google";
                }else{
                    $jsonData['AdChannel']="Facebook";//facebook的广告
                }

                exit(json_encode($jsonData));
            }else{

                $msg=array("Ugameid"=>$gameId, "Code"=>103, "Sdkuid"=>'', "Status"=>0, "msg"=>"还没有配置相关的数据",);
                exit(json_encode($msg));
            }

        }else{

            $msg=array("Ugameid"=>$gameId, "Code"=>105, "Sdkuid"=>'', "Status"=>0, "msg"=>"该游戏还没有配置",);
            exit(json_encode($msg));
        }

    }

    /**
     *
     * 广告google支付
     */

    public function advGooglePay(){

        $gameId=$this->getParam("gameId");//游戏ID

        // $Cp_orderid=$this->getParam("Cp_orderid");//cp订单号
        $Receive_data=$this->getParam("data");//google 的票据
        $apiModel=ApiModel::getInstance();

        $gameConfigData=$apiModel->getConfigByGameId($gameId);//获取google 的key
        $log=new LogController();
        //  $log->writeLog("google.log",var_export($_POST,true));

        //票据解
        $Receive_data=base64_decode($Receive_data);
        //解码之后的json数据
        $order_json=json_decode($Receive_data);

        $public_key=$gameConfigData['google_key'];
        $public_key = "-----BEGIN PUBLIC KEY-----\n" .chunk_split($public_key, 64, "\n") . "-----END PUBLIC KEY-----";

        $public_key_handle =openssl_pkey_get_public($public_key);
        $googleResult = openssl_verify($Receive_data, base64_decode($_POST['Sign']), $public_key_handle,OPENSSL_ALGO_SHA1);//google 的结果


        $params=[];
        $param['status']=$googleResult;
        $param['Receive_data']=$Receive_data;
        $log->writeLog("google.log",date("Y-m-d H:i:s").var_export($param,true));
        if($googleResult != 1)
        {
            //校验不通过订单

            //数据入库
            die(json_encode(array('Status'=>0,'Code'=>402,)));
        }else{
            //通过验证

            $insertData=[
                "google_orderNo"=>$order_json->orderId,//google订单号
                "sku"=>$order_json->productId,//google订单号
                "ctime"=>time(),
            ];

            $apiModel->table("adv_googlepay")->add($insertData);


            die(json_encode(array('Status'=>1,'Code'=>100,)));
        }
    }


    /*
     *客户端直接登录对接
     */
    public function loginGameUrl(){

        $uid=$this->getParam("Uid");//用户ID
        // $uid=str_replace(" ","+",$uid);
        $gameId=$this->getParam("Ugameid");//用户ID
        $Sdktype=$this->getParam("Sdktype");//sdk类型
        $Ugamekey=$this->getParam("Ugamekey");//游戏的key
        $version=$this->getParam("Version");//版本号
        // $isWeb=$this->getParam("isWeb");//如果是就加载web页面游戏
        $token=$this->getParam("token");
        $aes=new AesController();

        $playUid=$this->getParam("playUid");
        // $log=new LogController();
        // $log->writeLog("test.log",$uid);
        $uid=!empty($aes->decode($uid))?$aes->decode($uid):$playUid;

        // echo "<script>alert($uid);</script>";
        if(empty($uid)){
            echo "<script>alert('服务器在维护....');</script>";
            return;
        }

        // if($aes->decode($uid) != $playUid){
        //     echo "<script>alert('非法访问....');</script>";
        //     return;
        // }

        $apiModel=ApiModel::getInstance();
        $gameConfig=$apiModel->getConfigByGameId($gameId);
        if(empty($gameConfig)){

            exit("没有对应的游戏");
        }
        if($gameConfig['app_client_secret']!=$Ugamekey){

            exit("游戏的key不一致");
        }

        // $url="http://higame.lcws.iossh.rydth5.com/higame/login/login";//测试服
        // $apiModel=ApiModel::getInstance();

        $data=$apiModel->table("url_verify")->where(array("gameId"=>$gameId))->find();
        if(!empty($data)){
            if($data['version']==$version&&$data['flag']==1){
                $url=$data['testurl'];
            }else{
                $url=$data['checkurl'];
            }
        }

        // $payModel=PayModel::getInstance();

        $param=[
            "Ugameid"=>$gameId,
            "Uid"=>$uid,
            "Time"=>time(),
        ];

        $title= $gameConfig['gameName'];

        switch($gameId){
            case 2317:
                $title="一刀传世";
                break;
        }
        $sign=$this->createSign($param,$Ugamekey);
        $param['Sign']=$sign;
        $playUrl=$this->connectUrl($url,$param);
        $content=$this->getByFile("playclient");

        $replace=array(
                '$url'=>$playUrl,//游戏地址
                '$gameId'=>$gameId,//游戏ID
                '$Sdktype'=>$Sdktype,//设备类型
                '$Uid'=>$uid,//用户ID
                '$Time'=>$param['Time'],
                '$Sign'=>$sign,
                '$Ugamekey'=>$Ugamekey,
                '$token'=>$token,
                '$title'=>$title,
                '$version'=>str_replace('.','',$version),
        );

        if($gameId == '2568'){
             //$replace['qqgroupkey'] = 'wUdddHnNdEHyUe9qL-wVm5s1Nq8lMa7p';
            $replace['qqgroupkey'] = 'nCgqMQUb116G_1TbI0f2gbqAPZE-n8P4';
        }
        
        // if($gameId == 2568){
        //     var_dump($content,$replace);die;
        // }
        echo strtr($content,$replace);
    }

    /*
    * 根据url地址判断有没有?进行拼接
    */
    public function connectUrl($url,$param){

        $paramStr=http_build_query($param);
        $returnUrl='';
        if(strpos($url,"?")!==false){
            $returnUrl=$url."&".$paramStr;
        }else{
            $returnUrl=$url."?".$paramStr;
        }

        return $returnUrl;
    }

    public function getByFile($filename){
        $path="./h5/{$filename}.html";

        return file_get_contents($path);
    }






    public function budandemo2(){
        try{
            $apiModel = ApiModel::getInstance();

            $list = $apiModel->query("SELECT gameId,userId,serverId,roleId,amount,isFinsh,isSend,cp_orderId,orderId,cText,price,'alipay' as 'paytypes' FROM `iiu_alipay` where  ctime >= 1659956400 and isSend = 0 and isFinsh = 1 
            union 
            SELECT gameId,userId,serverId,roleId,amount,isFinsh,isSend,cp_orderId,orderId,cText,price,'weixin' as 'paytypes' FROM `iiu_weixin_pay` where ctime >= 1659956400 and isSend = 0 and isFinsh = 1");

//             echo '<pre>';
//             var_dump($list);die;

            foreach($list as $val){
//                $gameConfigData = $apiModel->getConfigByGameId($val['gameId']);

//                echo '<pre>';
//                var_dump($gameConfigData);
//
//                // 给研发必要参数
//                $sendData = [
//                    "Ugameid"       => $val['gameId'],//游戏名称
//                    "Uid"           => $val['userId'],//用户id
//                    "Roleid"        => $val['roleId'],//角色Id
//                    "Serverid"      => $val['serverId'],//服务器ID
//                    "Orderid"       => $val['orderId'],//平台订单号
//                    "Time"          => time(),//时间戳
//                    "Pay_channel"   => 1,//1 google ios 2 第三方
//                    "Price"         => $val['amount'],//交易金额
//                    "Currency_type" => 'RMB',//货币类型
//                    "Amount"        => $val['price'],//游戏币数量
//                    "Cp_orderid"    => $val['cp_orderId'],//cp_orderId
//                    "Ctext"         => $val['cText'],//cText
//                ];
//
//                // 判断 是否需要显示SkuPrice
//                if($gameConfigData['if_pay_sku_amount'] == 1){
//                    $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$val['gameId'],'cp_orderId'=>$val['cp_orderId']])->find();
//                    $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$val['gameId'],'sku'=>$pay_data_info['sku']])->find();
//                    $sendData["SkuPrice"] = $sku_info['amount'];
//                }
//
//                // 判断 支付回调将实际金额替换成sku金额开关
//                if($gameConfigData['if_real_sku_amount'] == 1){
//                    $pay_data_info = $apiModel->table('pay_data')->where(['gameId'=>$val['gameId'],'cp_orderId'=>$val['cp_orderId']])->find();
//                    $sku_info = $apiModel->table('map_amount')->where(['gameId'=>$val['gameId'],'sku'=>$pay_data_info['sku']])->find();
//                    $sendData["Price"] = $sku_info['amount'];
//                }
//
//                $sign = $this->createSign($sendData,$gameConfigData['app_server_secret']);
//                $sendData['Sign'] = $sign;
//
//                echo '<pre>';
//                var_dump($sendData);
//
//                // 通知研发发货
//                
//                $sendRes = [];
//                $aaa=false;
//                $result=curlPost(trim($gameConfigData['ios_pay']),$sendData);
//                $codeData = json_decode($result,true);

//			echo '<pre>';
//               var_dump($result);

                //发货成功
//                if($codeData['code'] == 1){
//                    $sendRes = $codeData;
//                    $aaa = true;
//                    var_dump($sendRes);
//                    //break;
//                }
                
                // if($aaa === true){
//                	echo '<pre>';
//                    var_dump($sendRes);
//                    echo '\r\n';
                    $payAllModel = PayAllModel::getInstance();
                    $payAllModel->addPay2($val['userId'],$val['roleId'],$val['serevrId'],$val['gameId'],$val['amount'],'RMB',$val['orderId'],$val['paytypes']);
                // }
            }


            var_dump('ok');
        }catch(\Throwable $th){
            var_dump($th);
        }
    }


















    public function budandemo(){

       $apiModel = ApiModel::getInstance();

       $list = $apiModel->query("SELECT gameId,userId,serverId,roleId,amount,isFinsh,isSend,cp_orderId,orderId,price,ctime,'alipay' as 'paytypes',cText FROM `iiu_alipay` where  ctime BETWEEN 1659956400 AND 1659970800 AND isSend = 0 AND isFinsh = 1 
       union 
       SELECT gameId,userId,serverId,roleId,amount,isFinsh,isSend,cp_orderId,orderId,price,ctime,'weixin' as 'paytypes',cText FROM `iiu_weixin_pay` where ctime BETWEEN 1659956400 AND 1659970800 AND isSend = 0 AND isFinsh = 1");

       // echo '<pre>';
       // var_dump($list);die;

       foreach($list as $val){
           $payAllModel = PayAllModel::getInstance();
           $payAllModel->addPay2($val['userId'],$val['roleId'],$val['serverId'],$val['gameId'],$val['amount'],'RMB',$val['orderId'],$val['paytypes'],$val['ctime']);
       }

       var_dump('ok');

    }








}

$online =new OnlineController();
$online->execute();