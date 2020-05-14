<?php

global $_W, $_GPC, $xcmodule, $xcurl;
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "index";
$do = $_GPC["do"];
switch ($op) {
    case "index":
        include $this->template("mymanage/" . strtolower($_GPC["do"]) . "/" . $op);
        exit;
}
exit;