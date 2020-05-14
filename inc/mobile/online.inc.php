<?php

global $_W, $_GPC;
$uniacid = $_W["uniacid"];
load()->func("logging");
class Online
{
    public $token, $uniacid;
    public function __construct($token, $uniacid, $appid)
    {
        $this->token = $token;
        $this->uniacid = $uniacid;
        $this->appid = $appid;
    }
    public function check_server()
    {
        if (isset($_GET["echostr"])) {
            $this->valid();
        } else {
            $this->responseMsg();
        }
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            header("content-type:text");
            echo $echoStr;
            exit;
        } else {
            echo $echoStr . "+++" . $this->token;
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr) && is_string($postStr)) {
            $postArr = json_decode($postStr, true);
            if (!empty($postArr["MsgType"]) && $postArr["MsgType"] == "text") {
                $data["uniacid"] = $this->uniacid;
                $data["openid"] = $postArr["FromUserName"];
                $data["type"] = 1;
                $data["content"] = base64_encode($postArr["Content"]);
                $this->checkOnline($data);
            } else {
                if (!empty($postArr["MsgType"]) && $postArr["MsgType"] == "image") {
                    $data["uniacid"] = $this->uniacid;
                    $data["openid"] = $postArr["FromUserName"];
                    $data["type"] = 2;
                    $data["content"] = $postArr["PicUrl"];
                    $this->checkOnline($data);
                } else {
                    if (!($postArr["MsgType"] == "event" && $postArr["Event"] == "user_enter_tempsession")) {
                    }
                }
            }
            echo "success";
            exit;
        } else {
            echo '';
            exit;
        }
    }
    public function checkOnline($data)
    {
        $online = pdo_get("xc_train_online", array("uniacid" => $data["uniacid"], "openid" => $data["openid"]));
        if ($online) {
            $data["pid"] = $online["id"];
            $request = pdo_insert("xc_train_online_log", $data);
            if ($request) {
                $sql = "UPDATE " . tablename("xc_train_online") . " SET member=member+1,updatetime=:times,type=:type,content=:content WHERE id=:id AND uniacid=:uniacid";
                pdo_query($sql, array(":times" => date("Y-m-d H:i:s"), ":id" => $online["id"], ":uniacid" => $data["uniacid"], ":type" => $data["type"], ":content" => $data["content"]));
                $prev = pdo_getall("xc_train_online_log", array("uniacid" => $data["uniacid"], "pid" => $data["pid"], "duty" => 2), array(), '', "createtime DESC,id DESC", array(1, 5));
                if ($prev) {
                    $config = pdo_get("xc_train_config", array("xkey" => "online", "uniacid" => $this->uniacid));
                    if ($config) {
                        $config["content"] = json_decode($config["content"], true);
                        if (!empty($config["content"]["jiange"])) {
                            if (time() - strtotime($prev[0]["createtime"]) >= intval($config["content"]["jiange"])) {
                                $this->sendMon($data);
                            }
                        }
                    }
                } else {
                    $this->sendMon($data);
                }
            }
        } else {
            $request = pdo_insert("xc_train_online", array("uniacid" => $data["uniacid"], "openid" => $data["openid"], "member" => 1, "updatetime" => date("Y-m-d H:i:s"), "type" => $data["type"], "content" => $data["content"]));
            if ($request) {
                $data["pid"] = pdo_insertid();
                pdo_insert("xc_train_online_log", $data);
                $this->sendMon($data);
            }
        }
    }
    public function sendMon($data)
    {
        $templateParam = array("webnamex" => "无", "nick" => "无", "content" => "无", "appid" => $this->appid, "page" => "xc_train/pages/base/base?&share=../online/index");
        $config = pdo_get("xc_train_config", array("xkey" => "online", "uniacid" => $this->uniacid));
        if ($config) {
            $config["content"] = json_decode($config["content"], true);
            if (!empty($config["content"])) {
                $web = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $data["uniacid"]));
                if ($web) {
                    $web["content"] = json_decode($web["content"], true);
                }
                $accessurl = $web["content"]["xcmessage"] . "&op=sendv2";
                $template = $config["content"]["xc"];
                $template["data"] = $config["content"]["data"];
                $openids = $config["content"]["userlist"];
                $sendopenid = array();
                foreach ($openids as $itemopenis) {
                    $sendopenid[] = $itemopenis["openid"];
                }
                $web = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $this->uniacid));
                if ($web) {
                    $web["content"] = json_decode($web["content"], true);
                    if (!empty($web["content"]) && !empty($web["content"]["title"])) {
                        $templateParam["webnamex"] = $web["content"]["title"];
                    }
                }
                $userinfo = pdo_get("xc_train_userinfo", array("uniacid" => $this->uniacid, "openid" => $data["openid"]));
                if ($userinfo && !empty($userinfo["nick"])) {
                    $templateParam["nick"] = base64_decode($userinfo["nick"]);
                }
                if ($data["type"] == 1) {
                    $templateParam["content"] = base64_decode($data["content"]);
                } else {
                    if ($data["type"] == 2) {
                        $templateParam["content"] = "图片";
                    }
                }
                momessv2($accessurl, $template, $sendopenid, $templateParam);
                if ($config["content"]["reply_status"] == 1 && !empty($config["content"]["reply_content"])) {
                    $account_api = WeAccount::create();
                    $token = $account_api->getAccessToken();
                    $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $token;
                    $data = array("touser" => $data["openid"], "msgtype" => "text", "text" => array("content" => $config["content"]["reply_content"]));
                    $postFields = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                    $pdata = curl_exec($ch);
                    curl_close($ch);
                }
            }
        }
    }
}
$token = '';
$config = pdo_get("xc_train_config", array("xkey" => "online", "uniacid" => $uniacid));
if ($config) {
    $config["content"] = json_decode($config["content"], true);
    if (!empty($config["content"]) && !empty($config["content"]["token"])) {
        $token = $config["content"]["token"];
    }
}
$online = new Online($token, $uniacid, $_W["account"]["key"]);
$online->check_server();