<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "news";
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
        if (!empty($_GPC["title"])) {
            $title = $_GPC["title"];
            $condition["title LIKE"] = "%" . $_GPC["title"] . "%";
            $_GET["title"] = $title;
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
        $class = pdo_getall("xc_train_service_class", array("uniacid" => $uniacid, "status" => 1, "type" => 4), array(), '', "sort DESC,id DESC");
        $class_list = array();
        if ($class) {
            foreach ($class as $c) {
                $class_list[$c["id"]] = $c;
            }
        }
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        if ($list) {
            foreach ($list as &$x) {
                $x["cid_name"] = $class_list[$x["cid"]]["name"];
            }
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/New/" . $op);
        }
        break;
    case "edit":
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
        } else {
            $list["status"] = 1;
            $list["index"] = -1;
        }
        $class = pdo_getall("xc_train_service_class", array("uniacid" => $uniacid, "status" => 1, "type" => 4), array(), '', "sort DESC,id DESC");
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/New/" . $op);
        }
        break;
    case "savemodel":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["title"] = $_GPC["title"];
        $condition["cid"] = $_GPC["cid"];
        $condition["simg"] = $_GPC["simg"];
        $condition["short_info"] = $_GPC["short_info"];
        $condition["link"] = $_GPC["link"];
        if (empty($_GPC["index"])) {
            $condition["index"] = -1;
        } else {
            $condition["index"] = $_GPC["index"];
        }
        if (empty($_GPC["status"])) {
            $condition["status"] = -1;
        } else {
            $condition["status"] = $_GPC["status"];
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
        $request = pdo_update($tablename, array("status" => $_GPC["status"]), array("id" => $_GPC["id"], "uniacid" => $uniacid));
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
        if (!empty($_GPC["xname"])) {
            $where .= " AND name LIKE :name ";
            $params[":name"] = "%" . $_GPC["xname"] . "%";
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
                $class = pdo_getall("xc_train_service_class", array("uniacid" => $uniacid, "status" => 1, "type" => 4), array(), '', "sort DESC,id DESC");
                $class_list = array();
                if ($class) {
                    foreach ($class as $c) {
                        $class_list[$c["id"]] = $c;
                    }
                }
                foreach ($listmodel as &$x) {
                    $x["cid_name"] = $class_list[$x["cid"]]["name"];
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