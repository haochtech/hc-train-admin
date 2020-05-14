<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "service";
$do = $_GPC["do"];
if (strlen($_GPC["xtitlea"]) > 0) {
    $xtitlea = urldecode($_GPC["xtitlea"]);
    $xtitleb = urldecode($_GPC["xtitleb"]);
}
switch ($op) {
    case "list":
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["cid !="] = -1;
        if (!empty($_GPC["xname"])) {
            $xname = $_GPC["xname"];
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
            $_GET["xname"] = $xname;
        }
        if (!empty($_GPC["cid"])) {
            $cid = $_GPC["cid"];
            $condition["cid"] = $_GPC["cid"];
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
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        $class = pdo_getall("xc_train_service_class", array("status" => 1, "uniacid" => $uniacid, "type" => 1), array(), '', "sort DESC,createtime DESC");
        $datalist = array();
        if ($class) {
            foreach ($class as $x) {
                $datalist[$x["id"]] = $x["name"];
            }
        }
        if ($list) {
            foreach ($list as &$y) {
                $y["cidname"] = $datalist[$y["cid"]];
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Service/" . $op);
        }
        break;
    case "edit":
        $class = pdo_getall("xc_train_service_class", array("status" => 1, "uniacid" => $uniacid, "type" => 1), array(), '', "sort DESC,createtime DESC");
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
            if ($list) {
                if (!empty($list["teacher"])) {
                    $list["teacher"] = json_decode($list["teacher"], true);
                }
            }
        } else {
            $list["status"] = 1;
            $list["content_type"] = 1;
        }
        $share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
        if ($share) {
            $share = json_decode($share["content"], true);
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Service/" . $op);
        }
        break;
    case "savemodel":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["name"] = $_GPC["name"];
        $condition["cid"] = $_GPC["cid"];
        $condition["bimg"] = $_GPC["bimg"];
        $condition["xueqi"] = $_GPC["xueqi"];
        $condition["keshi"] = $_GPC["keshi"];
        $condition["price"] = $_GPC["price"];
        $condition["content"] = $_GPC["content"];
        $condition["content_type"] = $_GPC["content_type"];
        $condition["content2"] = $_GPC["content2"];
        $condition["can_use"] = $_GPC["can_use"];
        $condition["zan"] = $_GPC["zan"];
        $condition["click"] = $_GPC["click"];
        if (!empty($_GPC["teacher"])) {
            $condition["teacher"] = htmlspecialchars_decode($_GPC["teacher"]);
        }
        $condition["share_one"] = $_GPC["share_one"];
        $condition["share_two"] = $_GPC["share_two"];
        $condition["share_three"] = $_GPC["share_three"];
        if (empty($_GPC["status"])) {
            $condition["status"] = -1;
        } else {
            $condition["status"] = $_GPC["status"];
        }
        if (empty($_GPC["index"])) {
            $condition["index"] = -1;
        } else {
            $condition["index"] = $_GPC["index"];
        }
        if (empty($_GPC["tui"])) {
            $condition["tui"] = -1;
        } else {
            $condition["tui"] = $_GPC["tui"];
        }
        if (empty($_GPC["sort"])) {
            $condition["sort"] = 0;
        } else {
            $condition["sort"] = $_GPC["sort"];
        }
        if (empty($_GPC["id"])) {
            $request = pdo_insert($tablename, $condition);
        } else {
            $request = pdo_update($tablename, $condition, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        }
        if (!empty($request)) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "statuschange":
        $request = pdo_update($tablename, array($_GPC["name"] => $_GPC["status"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "delete":
        $request = pdo_delete($tablename, array("id" => $_GPC["id"], "uniacid" => $uniacid));
        if ($request) {
            $json = array("status" => 1, "msg" => "操作成功");
            echo json_encode($json);
        } else {
            $json = array("status" => 0, "msg" => "操作失败");
            echo json_encode($json);
        }
        break;
    case "teacher":
        $tablename = "xc_train_teacher";
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
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        include $this->template("old/Service/teacher");
        break;
    case "getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        $params = array();
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
        }
        if (!empty($_GPC["cid"])) {
            $where .= " AND cid=:cid ";
            $params[":cid"] = $_GPC["cid"];
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
                $class = pdo_getall("xc_train_service_class", array("status" => 1, "uniacid" => $uniacid, "type" => 1), array(), '', "sort DESC,createtime DESC");
                $datalist = array();
                if ($class) {
                    foreach ($class as $x) {
                        $datalist[$x["id"]] = $x["name"];
                    }
                }
                foreach ($listmodel as &$y) {
                    $y["cidname"] = $datalist[$y["cid"]];
                }
            }
            $jsondate["rows"] = $listmodel;
            xc_ajax($jsondate);
        }
        break;
    case "midstatus":
        $id = intval($_GPC["id"]);
        $status = -$_GPC["status"];
        $updatestatus = array();
        $updatestatus["status"] = $status;
        $ret = pdo_update($tablename, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        exit;
        break;
    case "new_delete":
        $uniacid = $_W["uniacid"];
        $ids = $_GPC["ids"];
        $arrids = explode(",", $ids);
        if (count($arrids) < 1) {
            xc_message(-1, null);
        } else {
            $result = pdo_delete($tablename, array("id" => $arrids, "uniacid" => $uniacid));
            $status = $result > 0 ? 1 : -1;
            xc_message($status, null);
        }
        break;
    case "updatesorts":
        $id = intval($_GPC["id"]);
        $sorts = $_GPC["sorts"];
        $updatestatus = array();
        $updatestatus["sort"] = $sorts;
        $ret = pdo_update($tablename, $updatestatus, array("id" => $id, "uniacid" => $_W["uniacid"]));
        xc_message($ret, null);
        break;
}