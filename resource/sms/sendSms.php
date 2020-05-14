<?php

require_once "SignatureHelper.php";
class sms
{
    public function sendSms($accessKeyId, $accessKeySecret, $sign, $code, $mobile, $templateParam)
    {
        $params = array();
        $accessKeyId = $accessKeyId;
        $accessKeySecret = $accessKeySecret;
        $params["PhoneNumbers"] = $mobile;
        $params["SignName"] = $sign;
        $params["TemplateCode"] = $code;
        $params["TemplateParam"] = $templateParam;
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $helper = new SignatureHelper();
        $content = $helper->request($accessKeyId, $accessKeySecret, "dysmsapi.aliyuncs.com", array_merge($params, array("RegionId" => "cn-hangzhou", "Action" => "SendSms", "Version" => "2017-05-25")));
        return $content;
    }
}