<?php

defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "list";
$tablename = $xcmodule . "_" . "nav";
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
        $pagesize = 15;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall($tablename, $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Nav/" . $op);
        }
        break;
    case "edit":
        if (!empty($_GPC["id"])) {
            $list = pdo_get($tablename, array("id" => $_GPC["id"]));
        } else {
            $list["status"] = 1;
            $list["type"] = 1;
        }
        if (!empty($_GPC["new"]) && $_GPC["new"] == 1) {
            include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        } else {
            include $this->template("old/Nav/" . $op);
        }
        break;
    case "savemodel":
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["name"] = $_GPC["name"];
        $condition["simg"] = $_GPC["simg"];
        $condition["link"] = $_GPC["link"];
        $condition["type"] = $_GPC["type"];
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
    case "article":
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        if (!empty($_GPC["title"])) {
            $title = $_GPC["title"];
            $condition["title LIKE"] = "%" . $_GPC["title"] . "%";
            $_GET["title"] = $title;
        }
        $request = pdo_getall("xc_train_article", $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 6;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall("xc_train_article", $condition, array(), '', "createtime DESC", array($pageindex, $pagesize));
        $url = "https://" . $_SERVER["HTTP_HOST"] . "/app/index.php?i=" . $uniacid . "&c=entry&do=index&m=" . $_GPC["m"];
        if ($list) {
            foreach ($list as &$x) {
                $x["url"] = $url;
            }
        }
        include $this->template("old/Nav/article");
        break;
    case "pclass":
        $version_id = $_GPC["version_id"];
        $condition = array();
        $condition["uniacid"] = $uniacid;
        $condition["status"] = 1;
        $condition["type IN"] = array(1, 4);
        if (!empty($_GPC["xname"])) {
            $xname = $_GPC["xname"];
            $condition["name LIKE"] = "%" . $_GPC["xname"] . "%";
            $_GET["xname"] = $xname;
        }
        $request = pdo_getall("xc_train_service_class", $condition);
        $total = count($request);
        if (!isset($_GPC["page"])) {
            $pageindex = 1;
        } else {
            $pageindex = intval($_GPC["page"]);
        }
        $pagesize = 6;
        $pager = pagination($total, $pageindex, $pagesize);
        $list = pdo_getall("xc_train_service_class", $condition, array(), '', "sort DESC,createtime DESC", array($pageindex, $pagesize));
        include $this->template("old/Nav/pclass");
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
                foreach ($listmodel as &$x) {
                    $x["simg"] = tomedia($x["simg"]);
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