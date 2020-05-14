<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "userinfo";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["nick"])) {
            $nick = $_GPC["nick"];
            $condition["nick LIKE"] = "%" . base64_encode($_GPC["nick"]) . "%";
            $_GET["nick"] = $nick;
        }
        if (!empty($_GPC["openid"])) {
            $openid = $_GPC["openid"];
            $condition["openid LIKE"] = "%" . $_GPC["openid"] . "%";
            $_GET["openid"] = $openid;
        }
        $request = pdo_getall($tablename, $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 15;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "createtime DESC", array($pageindex, $pagesize));
        if ($list) {
            $store = pdo_getall("xc_train_school", array("status" => 1, "uniacid" => $uniacid));
            $datalist = array();
            if ($store) {
                foreach ($store as $s) {
                    $datalist[$s["id"]] = $s;
                }
            }
            foreach ($list as &$x) {
                $x["nick"] = base64_decode($x["nick"]);
                $x["card_id"] = $x["createtime"];
                if ($x["shop"] == -1) {
                    $x["shop_name"] = "无权限";
                } else {
                    if ($x["shop"] == 1) {
                        $x["shop_name"] = "管理员";
                    } else {
                        if ($x["shop"] == 2) {
                            if (!empty($datalist[$x["shop_id"]])) {
                                $x["shop_name"] = $datalist[$x["shop_id"]]["name"];
                            } else {
                                $x["shop_name"] = "校区不存在";
                            }
                        }
                    }
                }
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/User/" . $op);
        }
        break;
    case "statuschange":
        $request = pdo_update($tablename, array("shop" => $_GPC["status"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "service":
        $tablename = "xc_train_school";
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["xname"])) {
            $xname = $_GPC["xname"];
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
            $_GET["xname"] = $xname;
        }
        $request = pdo_getall($tablename, $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 6;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "sort desc,createtime DESC", array($pageindex, $pagesize));
        include $this->template("old/User/service");
        break;
    case "shop":
        $condition["shop"] = $_GPC["shop"];
        if (!empty($_GPC["shop_id"])) {
            $condition["shop_id"] = $_GPC["shop_id"];
        } else {
            $condition["shop_id"] = null;
        }
        $request = pdo_update($tablename, $condition, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        $params = array();
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        if (!empty($_GPC["nick"])) {
            $where .= " AND nick LIKE :nick ";
            $params[":nick"] = "%" . base64_encode($_GPC["nick"]) . "%";
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
                $ids = array();
                foreach ($listmodel as $xx) {
                    if (!empty($xx["share"])) {
                        $ids[] = $xx["share"];
                    }
                }
                $store = pdo_getall("xc_train_school", array("status" => 1, "uniacid" => $uniacid));
                $datalist = array();
                if ($store) {
                    foreach ($store as $s) {
                        $datalist[$s["id"]] = $s;
                    }
                }
                $user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $ids));
                $user_data = array();
                if ($user) {
                    foreach ($user as $u) {
                        $u["nick"] = base64_decode($u["nick"]);
                        $user_data[$u["openid"]] = $u;
                    }
                }
                foreach ($listmodel as &$x) {
                    $x["nick"] = base64_decode($x["nick"]);
                    $x["card_id"] = $x["createtime"];
                    if ($x["shop"] == -1) {
                        $x["shop_name"] = "无权限";
                    } else {
                        if ($x["shop"] == 1) {
                            $x["shop_name"] = "管理员";
                        } else {
                            if ($x["shop"] == 2) {
                                if (!empty($datalist[$x["shop_id"]])) {
                                    $x["shop_name"] = $datalist[$x["shop_id"]]["name"];
                                } else {
                                    $x["shop_name"] = "校区不存在";
                                }
                            }
                        }
                    }
                    if (!empty($x["share"])) {
                        $x["share_nick"] = $user_data[$x["share"]]["nick"];
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "fen":
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        break;
    case "fen_getseachjson":
        $share_type = 1;
        $share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
        if ($share) {
            $share = json_decode($share["content"], true);
            if (!empty($share) && $share["apply_status"] == 1) {
                $share_type = 2;
            }
        }
        if ($share_type == 2) {
            $tablename = $xcmodule . "_apply";
        }
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        if ($share_type == 2) {
            $where .= " AND status=1 ";
        }
        $params = array();
        if (!empty($_GPC["openid"])) {
            $where .= " AND openid LIKE :openid ";
            $params[":openid"] = "%" . $_GPC["openid"] . "%";
        }
        $params = array();
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :xname ";
            $params[":xname"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["mobile"])) {
            $where .= " AND mobile LIKE :mobile ";
            $params[":mobile"] = "%" . $_GPC["mobile"] . "%";
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
                $openid = array();
                $openid_format = array();
                foreach ($listmodel as $xx) {
                    $openid[] = $xx["openid"];
                    $openid_format[] = "'" . $xx["openid"] . "'";
                }
                $user_data = array();
                if ($share_type == 2) {
                    $user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $openid));
                    if ($user) {
                        foreach ($user as $u) {
                            $user_data[$u["openid"]] = $u;
                        }
                    }
                }
                $openid_format = "(" . implode(",", $openid_format) . ")";
                $share_one_data = array();
                $sql = "SELECT sum(amount) as amount,openid FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND order_type=1 AND status=1 AND openid IN {$openid_format} GROUP BY openid ";
                $share_one = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"]));
                if ($share_one) {
                    foreach ($share_one as $so) {
                        $share_one_data[$so["openid"]] = $so;
                    }
                }
                $share_two_data = array();
                $sql = "SELECT sum(amount) as amount,openid FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND order_type=1 AND status=-1 AND openid IN {$openid_format} GROUP BY openid ";
                $share_two = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"]));
                if ($share_two) {
                    foreach ($share_two as $st) {
                        $share_two_data[$st["openid"]] = $st;
                    }
                }
                $share_three_data = array();
                $sql = "SELECT sum(amount) as amount,openid FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND order_type=1 AND status=2 AND openid IN {$openid_format} GROUP BY openid ";
                $share_three = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"]));
                if ($share_three) {
                    foreach ($share_three as $sh) {
                        $share_three_data[$sh["openid"]] = $sh;
                    }
                }
                $user_data = array();
                if ($share_type == 2) {
                    $user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $openid));
                    if ($user) {
                        foreach ($user as $u) {
                            $u["nick"] = base64_decode($u["nick"]);
                            $user_data[$u["openid"]] = $u;
                        }
                    }
                }
                foreach ($listmodel as &$x) {
                    if (!empty($x["nick"])) {
                        $x["nick"] = base64_decode($x["nick"]);
                    }
                    if (!empty($user_data[$x["openid"]])) {
                        $x["nick"] = $user_data[$x["openid"]]["nick"];
                        $x["avatar"] = $user_data[$x["openid"]]["avatar"];
                    }
                    if (!empty($user_data[$x["openid"]])) {
                        $x["share_fee"] = $user_data[$x["openid"]]["share_fee"];
                    }
                    $x["share_one"] = 0;
                    if (!empty($share_one_data[$x["openid"]])) {
                        $x["share_one"] = round(floatval($share_one_data[$x["openid"]]["amount"]), 2);
                    }
                    $x["share_two"] = 0;
                    if (!empty($share_two_data[$x["openid"]])) {
                        $x["share_two"] = round(floatval($share_two_data[$x["openid"]]["amount"]), 2);
                    }
                    $x["share_three"] = 0;
                    if (!empty($share_three_data[$x["openid"]])) {
                        $x["share_three"] = round(floatval($share_three_data[$x["openid"]]["amount"]), 2);
                    }
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
}