<?php

global $_W, $_GPC;
$uniacid = $_W["uniacid"];
if (!empty($_GET["id"])) {
    $list = pdo_get("xc_train_article", array("id" => $_GET["id"], "uniacid" => $uniacid));
    if ($list) {
        $list["content"] = htmlspecialchars_decode($list["content"]);
    }
}
$mobile = new MobileTemplate();
include $mobile->template("index/index", $_W, $_GPC);
class MobileTemplate
{
    private $module;
    public $modulename;
    public $weid;
    public $uniacid;
    public $__define;
    function saveSettings($settings)
    {
        global $_W;
        $pars = array("module" => $this->modulename, "uniacid" => $_W["uniacid"]);
        $row = array();
        $row["settings"] = iserializer($settings);
        cache_build_module_info($this->modulename);
        if (pdo_fetchcolumn("SELECT module FROM " . tablename("uni_account_modules") . " WHERE module = :module AND uniacid = :uniacid", array(":module" => $this->modulename, ":uniacid" => $_W["uniacid"]))) {
            return pdo_update("uni_account_modules", $row, $pars) !== false;
        } else {
            return pdo_insert("uni_account_modules", array("settings" => iserializer($settings), "module" => $this->modulename, "uniacid" => $_W["uniacid"], "enabled" => 1)) !== false;
        }
    }
    function createMobileUrl($do, $query = array(), $noredirect = true)
    {
        global $_W;
        $query["do"] = $do;
        $query["m"] = strtolower($this->modulename);
        return murl("entry", $query, $noredirect);
    }
    function createWebUrl($do, $query = array())
    {
        $query["do"] = $do;
        $query["m"] = strtolower($this->modulename);
        return wurl("site/entry", $query);
    }
    function template($filename, $_W, $_GPC)
    {
        $name = $_GPC["m"];
        $defineDir = IA_ROOT . "/addons/" . $_GPC["m"];
        $_W["template"] = "default";
        if (defined("IN_SYS")) {
            $source = IA_ROOT . "/web/themes/{$_W["template"]}/{$name}/{$filename}.html";
            $compile = IA_ROOT . "/data/tpl/web/{$_W["template"]}/{$name}/{$filename}.tpl.php";
            if (!is_file($source)) {
                $source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = $defineDir . "/template/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = IA_ROOT . "/web/themes/{$_W["template"]}/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = IA_ROOT . "/web/themes/default/{$filename}.html";
            }
        } else {
            $source = IA_ROOT . "/app/themes/{$_W["template"]}/{$name}/{$filename}.html";
            $compile = IA_ROOT . "/data/tpl/app/{$_W["template"]}/{$name}/{$filename}.tpl.php";
            if (!is_file($source)) {
                $source = IA_ROOT . "/app/themes/default/{$name}/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = $defineDir . "/template/mobile_template/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = $defineDir . "/template/webapp/{$filename}.html";
            }
            if (!is_file($source)) {
                $source = IA_ROOT . "/app/themes/{$_W["template"]}/{$filename}.html";
            }
            if (!is_file($source)) {
                if (in_array($filename, array("header", "footer", "slide", "toolbar", "message"))) {
                    $source = IA_ROOT . "/app/themes/default/common/{$filename}.html";
                } else {
                    $source = IA_ROOT . "/app/themes/default/{$filename}.html";
                }
            }
        }
        if (!is_file($source)) {
            exit("Error: template source '{$filename}' is not exist!");
        }
        $paths = pathinfo($compile);
        $compile = str_replace($paths["filename"], $_W["uniacid"] . "_" . $paths["filename"], $compile);
        if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
            template_compile($source, $compile, true);
        }
        return $compile;
    }
    function fileSave($file_string, $type = "jpg", $name = "auto")
    {
        global $_W;
        load()->func("file");
        $allow_ext = array("images" => array("gif", "jpg", "jpeg", "bmp", "png", "ico"), "audios" => array("mp3", "wma", "wav", "amr"), "videos" => array("wmv", "avi", "mpg", "mpeg", "mp4"));
        if (in_array($type, $allow_ext["images"])) {
            $type_path = "images";
        } else {
            if (in_array($type, $allow_ext["audios"])) {
                $type_path = "audios";
            } else {
                if (in_array($type, $allow_ext["videos"])) {
                    $type_path = "videos";
                }
            }
        }
        if (empty($type_path)) {
            return error(1, "禁止保存文件类型");
        }
        if (empty($name) || $name == "auto") {
            $uniacid = intval($_W["uniacid"]);
            $path = "{$type_path}/{$uniacid}/{$this->module["name"]}/" . date("Y/m/");
            mkdirs(ATTACHMENT_ROOT . "/" . $path);
            $filename = file_random_name(ATTACHMENT_ROOT . "/" . $path, $type);
        } else {
            $path = "{$type_path}/{$uniacid}/{$this->module["name"]}/";
            mkdirs(dirname(ATTACHMENT_ROOT . "/" . $path));
            $filename = $name;
            if (!strexists($filename, $type)) {
                $filename .= "." . $type;
            }
        }
        if (file_put_contents(ATTACHMENT_ROOT . $path . $filename, $file_string)) {
            file_remote_upload($path);
            return $path . $filename;
        } else {
            return false;
        }
    }
    function fileUpload($file_string, $type = "image")
    {
        $types = array("image", "video", "audio");
    }
    function getFunctionFile($name)
    {
        $module_type = str_replace("wemodule", '', strtolower(get_parent_class($this)));
        if ($module_type == "site") {
            $module_type = stripos($name, "doWeb") === 0 ? "web" : "mobile";
            $function_name = $module_type == "web" ? strtolower(substr($name, 5)) : strtolower(substr($name, 8));
        } else {
            $function_name = strtolower(substr($name, 6));
        }
        $dir = IA_ROOT . "/framework/builtin/" . $this->modulename . "/inc/" . $module_type;
        $file = "{$dir}/{$function_name}.inc.php";
        if (!file_exists($file)) {
            $file = str_replace("framework/builtin", "addons", $file);
        }
        return $file;
    }
    function __call($name, $param)
    {
        $file = $this->getFunctionFile($name);
        if (file_exists($file)) {
            require $file;
            exit;
        }
        trigger_error("模块方法" . $name . "不存在.", E_USER_WARNING);
        return false;
    }
}