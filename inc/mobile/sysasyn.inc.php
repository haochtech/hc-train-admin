<?php

global $_W, $_GPC;
$op = $_GPC["op"];
switch ($op) {
    case "binguser":
        $savemodel = array();
        $savemodel["openid"] = safe_gpc_string($_GPC["openid"]);
        if (empty($savemodel["openid"])) {
            xc_message(-1, null, "没有获取openid");
        }
        $savemodel["nickname"] = safe_gpc_string($_GPC["nickname"]);
        $savemodel["headimgurl"] = safe_gpc_string($_GPC["headimgurl"]);
        $savemodel["uniacid"] = $_W["uniacid"];
        $savemodel["ident"] = safe_gpc_string($_GPC["ident"]);
        $savemodel["createtime"] = time();
        $savemodel["status"] = -1;
        $res = pdo_insert("xc_train_moban_user", $savemodel);
        if ($res) {
            xc_message(1, null, "操作成功");
        } else {
            xc_message(-1, null, "操作失败");
        }
}