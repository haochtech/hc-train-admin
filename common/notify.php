<?php

define("IN_MOBILE", true);
require "../../../framework/bootstrap.inc.php";
$input = file_get_contents("php://input");
load()->func("logging");
logging_run($input);
$isxml = true;
if (!empty($input)) {
    $input = xml2array($input);
    $_W["uniacid"] = $_W["weid"] = intval($input["attach"]);
    $_W["uniaccount"] = $_W["account"] = uni_fetch($_W["uniacid"]);
    $_W["acid"] = $_W["uniaccount"]["acid"];
    $sql = "SELECT * FROM " . tablename("core_paylog") . " WHERE `uniontid`=:uniontid";
    $params = array();
    $params[":uniontid"] = $input["out_trade_no"];
    $log = pdo_fetch($sql, $params);
    if ($log && $log["card_fee"] == $input["total_fee"] / 100) {
        $_GPC["m"] = $log["module"];
        pdo_update("core_paylog", array("status" => 1), array("plid" => $log["plid"]));
        if ($log["type"] == "wxapp") {
            $site = WeUtility::createModuleWxapp($log["module"]);
        } else {
            $site = WeUtility::createModuleSite($log["module"]);
        }
        if (!is_error($site)) {
            $method = "payResult";
            if (method_exists($site, $method)) {
                $ret = array();
                $ret["weid"] = $input["attach"];
                $ret["uniacid"] = $log["uniacid"];
                $ret["acid"] = $log["acid"];
                $ret["result"] = "success";
                $ret["type"] = $log["type"];
                $ret["from"] = "notify";
                $ret["tid"] = $log["tid"];
                $ret["uniontid"] = $log["uniontid"];
                $ret["transaction_id"] = $input["transaction_id"];
                $ret["trade_type"] = $input["trade_type"];
                $ret["follow"] = $input["is_subscribe"] == "Y" ? 1 : 0;
                $ret["user"] = $log["openid"];
                $ret["fee"] = $log["fee"];
                $ret["tag"] = $log["tag"];
                $ret["is_usecard"] = $log["is_usecard"];
                $ret["card_type"] = $log["card_type"];
                $ret["card_fee"] = $log["card_fee"];
                $ret["card_id"] = $log["card_id"];
                if (!empty($input["time_end"])) {
                    $ret["paytime"] = strtotime($input["time_end"]);
                }
                $dd = $site->{$method}($ret);
                exit("success");
            }
        }
    } else {
        exit("success");
    }
}