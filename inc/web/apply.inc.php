<?php

global $_W, $_GPC, $xcmodule, $xcconfig;
$xtitlea = '';
$xtitleb = '';
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
}
if (strlen($_GPC["xtitleb"]) > 0) {
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
$table = $xcmodule . "_" . "apply";
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$do = $_GPC["do"];
switch ($op) {
    case "share":
        include $this->template("mymanage/" . $do . "/" . $op);
        break;
    case "share_getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        $params = array();
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile=:mobile ";
            $params[":mobile"] = $_GPC["mobile"];
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($table);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "share_change":
        $request = pdo_update($table, array("status" => $_GPC["status"], "applytime" => date("Y-m-d H:i:s")), array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
        if ($request) {
            xc_message(1, null);
        } else {
            xc_message(-1, null);
        }
        break;
    case "withdraw":
        $tablename = $xcmodule . "_share_withdraw";
        $condition = array();
        $condition["uniacid"] = $_W["uniacid"];
        if (!empty($_GPC["openid"])) {
            $openid = $_GPC["openid"];
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
            $_GET["openid"] = $openid;
        }
        if (!empty($_GPC["out_trade_no"])) {
            $out_trade_no = $_GPC["out_trade_no"];
            $condition["out_trade_no LIKE"] = "%" . $_GPC["out_trade_no"] . "%";
            $_GET["out_trade_no"] = $out_trade_no;
        }
        if (!empty($_GPC["status"])) {
            $status = $_GPC["status"];
            $condition["status"] = $_GPC["status"];
            $_GET["status"] = $status;
        }
        $total = 0;
        $request = pdo_count($tablename, $condition);
        if ($request) {
            $total = $request;
        }
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 15;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "id DESC", array($pageindex, $pagesize));
        $times = array("start" => date("Y-m-d") . " 00:00:00", "end" => date("Y-m-d") . " 23:59:59");
        $export_field = array("openid" => "用户id", "out_trade_no" => "订单号", "type_name" => "提现方式", "amount" => "提现金额", "username" => "账号", "name" => "姓名", "mobile" => "手机号", "code" => "收款码", "createtime" => "申请时间", "status_name" => "状态");
        $export_url = $this->createWebUrl($_GPC["do"], array("op" => "share_withdraw_excel"));
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Order/" . $op);
        }
        break;
    case "share_withdraw_getseachjson":
        $tablename = $xcmodule . "_share_withdraw";
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND order_type=1 ";
        $params = array();
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["status"])) {
            $where .= " AND status=:status ";
            $params[":status"] = $_GPC["status"];
        }
        if (!empty($_GPC["times"])) {
            $where .= " AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<=:end_time ";
            $params[":start_time"] = strtotime($_GPC["times"]["start"]);
            $params[":end_time"] = strtotime($_GPC["times"]["end"]);
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                foreach ($listmodel as &$x) {
                    if (!empty($x["code"])) {
                        $x["code"] = tomedia($x["code"]);
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "share_withdraw_status":
        $request = pdo_update($xcmodule . "_share_withdraw", array("status" => $_GPC["status"], "applytime" => date("Y-m-d H:i:s")), array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
        if ($request) {
            if ($_GPC["status"] == 2) {
                $order = pdo_get($xcmodule . "_share_withdraw", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
                if ($order) {
                    $amount = $order["amount"];
                    if (!empty($order["fee"])) {
                        $amount = round(floatval($amount) + floatval($order["fee"]), 2);
                    }
                    $condition = array();
                    if ($order["order_type"] == 1) {
                        $condition["share_fee +="] = $amount;
                    } else {
                        if ($order["order_type"] == 2) {
                            $condition["team_fee +="] = $amount;
                        }
                    }
                    pdo_update($xcmodule . "_userinfo", $condition, array("uniacid" => $_W["uniacid"], "openid" => $order["openid"]));
                }
            }
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "share_withdraw_excel":
        $table = $xcmodule . "_share_withdraw";
        if (!empty($_GPC["xc"])) {
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            $where = " WHERE uniacid=:uniacid AND order_type=1 ";
            $params = array();
            if (!empty($_GPC["openid"])) {
                $where .= " AND openid LIKE :openid ";
                $params[":openid"] = "%" . $_GPC["openid"] . "%";
            }
            if (!empty($_GPC["out_trade_no"])) {
                $where .= " AND out_trade_no LIKE :out_trade_no ";
                $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
            }
            if (!empty($_GPC["status"])) {
                $where .= " AND status=:status ";
                $params[":status"] = $_GPC["status"];
            }
            $params["uniacid"] = $_W["uniacid"];
            $fulltable = tablename($table);
            $sql = "SELECT * FROM   " . $fulltable . $where;
            $list = pdo_fetchall($sql, $params);
            if ($list) {
                header("Content-type: application/vnd.ms-excel; charset=utf8");
                header("Content-Disposition: attachment; filename=order.xls");
                $data = "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        $data .= "<th>" . $x["name"] . "</th>";
                    }
                }
                $data .= "</tr>";
                foreach ($list as &$v) {
                    if ($v["type"] == 1) {
                        $v["type_name"] = "微信";
                    } else {
                        if ($v["type"] == 2) {
                            $v["type_name"] = "支付宝";
                        } else {
                            if ($v["type"] == 3) {
                                $v["type_name"] = "银行卡";
                            }
                        }
                    }
                    if ($v["status"] == -1) {
                        $v["status_name"] = "待处理";
                    } else {
                        if ($v["status"] == 1) {
                            $v["status_name"] = "成功";
                        } else {
                            if ($v["status"] == 2) {
                                $v["status_name"] = "失败";
                            }
                        }
                    }
                    if (!empty($v["code"])) {
                        $v["code"] = tomedia($v["code"]);
                    }
                    $data = $data . "<tr>";
                    foreach ($xc as $x) {
                        if ($x["status"] == 1) {
                            $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                        }
                    }
                    $data = $data . "</tr>";
                }
                $data = "<table border='1'>" . $data . "</table>";
                echo iconv("UTF-8", "GBK//TRANSLIT", $data . "\t");
                exit;
            }
        }
        break;
    case "team":
        $times = array("start" => date("Y-m-d") . " 00:00:00", "end" => date("Y-m-d") . " 23:59:59");
        $export_field = array("openid" => "用户id", "out_trade_no" => "订单号", "type_name" => "提现方式", "amount" => "提现金额", "username" => "账号", "name" => "姓名", "mobile" => "手机号", "code" => "收款码", "createtime" => "申请时间", "status_name" => "状态");
        $export_url = $this->createWebUrl($_GPC["do"], array("op" => "team_excel"));
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Order/" . $op);
        }
        break;
    case "team_getseachjson":
        $tablename = $xcmodule . "_share_withdraw";
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid AND order_type=2 ";
        $params = array();
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["out_trade_no"])) {
            $where .= " AND out_trade_no LIKE :out_trade_no ";
            $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
        }
        if (!empty($_GPC["status"])) {
            $where .= " AND status=:status ";
            $params[":status"] = $_GPC["status"];
        }
        if (!empty($_GPC["times"])) {
            $where .= " AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<=:end_time ";
            $params[":start_time"] = strtotime($_GPC["times"]["start"]);
            $params[":end_time"] = strtotime($_GPC["times"]["end"]);
        }
        $params["uniacid"] = $_W["uniacid"];
        $fulltable = tablename($tablename);
        $sql = "SELECT COUNT(*) FROM   " . $fulltable . $where;
        $total = pdo_fetchcolumn($sql, $params);
        $jsondate = array();
        $jsondate["total"] = pdo_fetchcolumn($sql, $params);
        if (empty($jsondate["total"])) {
            $jsondate["rows"] = array();
            xc_ajax($jsondate);
        } else {
            $sql = "SELECT * FROM  {$fulltable}   {$where} ORDER BY " . $ararysort["order"] . " LIMIT " . $ararysort["offset"] . "," . $ararysort["limit"];
            $listmodel = pdo_fetchall($sql, $params);
            if ($listmodel) {
                foreach ($listmodel as &$x) {
                    if (!empty($x["code"])) {
                        $x["code"] = tomedia($x["code"]);
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "team_excel":
        $table = $xcmodule . "_share_withdraw";
        if (!empty($_GPC["xc"])) {
            $xc = htmlspecialchars_decode($_GPC["xc"]);
            $xc = json_decode($xc, true);
            $where = " WHERE uniacid=:uniacid AND order_type=2 ";
            $params = array();
            if (!empty($_GPC["openid"])) {
                $where .= " AND openid LIKE :openid ";
                $params[":openid"] = "%" . $_GPC["openid"] . "%";
            }
            if (!empty($_GPC["out_trade_no"])) {
                $where .= " AND out_trade_no LIKE :out_trade_no ";
                $params[":out_trade_no"] = "%" . $_GPC["out_trade_no"] . "%";
            }
            if (!empty($_GPC["status"])) {
                $where .= " AND status=:status ";
                $params[":status"] = $_GPC["status"];
            }
            $params["uniacid"] = $_W["uniacid"];
            $fulltable = tablename($table);
            $sql = "SELECT * FROM   " . $fulltable . $where;
            $list = pdo_fetchall($sql, $params);
            if ($list) {
                header("Content-type: application/vnd.ms-excel; charset=utf8");
                header("Content-Disposition: attachment; filename=order.xls");
                $data = "<tr>";
                foreach ($xc as $x) {
                    if ($x["status"] == 1) {
                        $data .= "<th>" . $x["name"] . "</th>";
                    }
                }
                $data .= "</tr>";
                foreach ($list as &$v) {
                    if ($v["type"] == 1) {
                        $v["type_name"] = "微信";
                    } else {
                        if ($v["type"] == 2) {
                            $v["type_name"] = "支付宝";
                        } else {
                            if ($v["type"] == 3) {
                                $v["type_name"] = "银行卡";
                            }
                        }
                    }
                    if ($v["status"] == -1) {
                        $v["status_name"] = "待处理";
                    } else {
                        if ($v["status"] == 1) {
                            $v["status_name"] = "成功";
                        } else {
                            if ($v["status"] == 2) {
                                $v["status_name"] = "失败";
                            }
                        }
                    }
                    if (!empty($v["code"])) {
                        $v["code"] = tomedia($v["code"]);
                    }
                    $data = $data . "<tr>";
                    foreach ($xc as $x) {
                        if ($x["status"] == 1) {
                            $data .= "<td style='vnd.ms-excel.numberformat:@'>" . $v[$x["key"]] . "</td>";
                        }
                    }
                    $data = $data . "</tr>";
                }
                $data = "<table border='1'>" . $data . "</table>";
                echo iconv("UTF-8", "GBK//TRANSLIT", $data . "\t");
                exit;
            }
        }
        break;
}
exit;