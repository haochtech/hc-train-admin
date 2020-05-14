<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "service_team";
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
        if (!empty($_GPC["mark"])) {
            $mark = $_GPC["mark"];
            $condition["mark LIKE"] = "%" . $_GPC["mark"] . "%";
            $_GET["mark"] = $mark;
        }
        if (!empty($_GPC["pid"])) {
            $pid = $_GPC["pid"];
            $condition["pid"] = $_GPC["pid"];
            $_GET["pid"] = $pid;
        }
        if (!empty($_GPC["type"])) {
            $type = $_GPC["type"];
            $condition["type"] = $_GPC["type"];
            $_GET["type"] = $type;
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
        $class = pdo_getall("xc_train_service", array("uniacid" => $uniacid), array(), '', "sort DESC,createtime DESC");
        if ($list) {
            $datalist = array();
            if ($class) {
                foreach ($class as $c) {
                    $datalist[$c["id"]] = $c;
                }
            }
            foreach ($list as &$x) {
                $x["name"] = $datalist[$x["pid"]]["name"];
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Service_team/" . $op);
        }
        break;
    case "edit":
        $class = pdo_getall("xc_train_service", array("status" => 1, "uniacid" => $uniacid), array(), '', "sort DESC,createtime DESC");
        if ($class) {
            foreach ($class as &$x) {
                if (!empty($x["price"])) {
                    $x["fee"] = 1;
                } else {
                    $x["fee"] = -1;
                }
            }
        }
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
        } else {
            $list["status"] = 1;
            $list["type"] = 1;
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Service_team/" . $op);
        }
        break;
    case "savemodel":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["pid"] = $_GPC["pid"];
        $condition["mark"] = $_GPC["mark"];
        $condition["type"] = $_GPC["type"];
        $condition["start_time"] = $_GPC["start_time"];
        $condition["end_time"] = $_GPC["end_time"];
        $condition["least_member"] = $_GPC["least_member"];
        $condition["more_member"] = $_GPC["more_member"];
        if (empty($_GPC["status"])) {
            $condition["status"] = -1;
        } else {
            $condition["status"] = $_GPC["status"];
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
    case "getseachjson":
        $ararysort = ararysorts();
        $where = " WHERE uniacid=:uniacid ";
        $params = array();
        if (!empty($_GPC["mark"])) {
            $where .= " AND mark LIKE :mark ";
            $params[":mark"] = "%" . $_GPC["mark"] . "%";
        }
        if (!empty($_GPC["pid"])) {
            $where .= " AND pid=:pid ";
            $params[":pid"] = $_GPC["pid"];
        }
        if (!empty($_GPC["type"])) {
            $where .= " AND type=:type ";
            $params[":type"] = $_GPC["type"];
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
                $class = pdo_getall("xc_train_service", array("uniacid" => $uniacid), array(), '', "sort DESC,createtime DESC");
                $datalist = array();
                if ($class) {
                    foreach ($class as $c) {
                        $datalist[$c["id"]] = $c;
                    }
                }
                foreach ($listmodel as &$x) {
                    $x["name"] = $datalist[$x["pid"]]["name"];
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