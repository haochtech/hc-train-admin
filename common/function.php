<?php

function usercode($code, $service, $_W)
{
    $back = IA_ROOT . "/addons/xc_train/resource/images/back.jpg";
    $num = time();
    $savepath = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savepath = $savepath . "/" . date("m") . "/";
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savefile = $num;
    $saveokpath = $savepath . $savefile;
    $back_img = imagecreatefromstring(file_get_contents($back));
    list($dst_w, $dst_h, $dst_type) = getimagesize($back);
    $font = IA_ROOT . "/addons/xc_train/resource/WRYH.ttf";
    $poster = pdo_get("xc_train_config", array("xkey" => "service_poster", "uniacid" => $_W["uniacid"]));
    if ($poster) {
        $poster["content"] = json_decode($poster["content"], true);
        if (!empty($poster["content"]) && !empty($poster["content"]["list"])) {
            $poster["content"]["list"] = my_array_multisort($poster["content"]["list"], "z-index");
            foreach ($poster["content"]["list"] as $x) {
                switch ($x["type"]) {
                    case "bimg":
                        $service_bimg = $savepath . "service_" . $num . ".jpg";
                        $service["bimg"] = tomedia($service["bimg"]);
                        $re = mkThumbnail($service["bimg"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg);
                        break;
                    case "title":
                        $name = $service["name"];
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "price":
                        if (!empty($service["price"])) {
                            $name = "￥" . $service["price"];
                        } else {
                            $name = "免费";
                        }
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        if ($x["center"] == 1) {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7])) * 2, $black, $font, $name);
                        } else {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7])) * 2, $black, $font, $name);
                        }
                        break;
                    case "code":
                        $service_bimg = $savepath . "code_" . $num . ".jpg";
                        $re = mkThumbnail($code, intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg);
                        break;
                    case "text":
                        $name = $x["content"];
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "img":
                        if (!empty($x["content"])) {
                            $service_bimg = $savepath . "service_" . $num . ".jpg";
                            $service["simg"] = tomedia($service["simg"]);
                            $re = mkThumbnail($x["content"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                            $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                            list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                            $bimg_x = imagesx($bimg);
                            $bimg_y = imagesy($bimg);
                            imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                            unlink($service_bimg);
                        }
                        break;
                }
            }
        }
    }
    $exname = '';
    switch ($dst_type) {
        case 1:
            $exname = ".gif";
            imagegif($back_img, $saveokpath . ".gif");
            break;
        case 2:
            $exname = ".jpg";
            imagejpeg($back_img, $saveokpath . ".jpg");
            break;
        case 3:
            $exname = ".png";
            imagepng($back_img, $saveokpath . ".png");
            break;
        default:
    }
    $url = "https://" . $_SERVER["HTTP_HOST"] . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $savefile . '' . $exname;
    return $url;
}
function audiocode($code, $service, $_W)
{
    $back = IA_ROOT . "/addons/xc_train/resource/images/back.jpg";
    $num = time();
    $savepath = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savepath = $savepath . "/" . date("m") . "/";
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savefile = $num;
    $saveokpath = $savepath . $savefile;
    $back_img = imagecreatefromstring(file_get_contents($back));
    list($dst_w, $dst_h, $dst_type) = getimagesize($back);
    $font = IA_ROOT . "/addons/xc_train/resource/WRYH.ttf";
    $poster = pdo_get("xc_train_config", array("xkey" => "audio_poster", "uniacid" => $_W["uniacid"]));
    if ($poster) {
        $poster["content"] = json_decode($poster["content"], true);
        if (!empty($poster["content"]) && !empty($poster["content"]["list"])) {
            $poster["content"]["list"] = my_array_multisort($poster["content"]["list"], "z-index");
            foreach ($poster["content"]["list"] as $x) {
                switch ($x["type"]) {
                    case "bimg":
                        $service_bimg = $savepath . "service_" . $num . ".jpg";
                        $service["simg"] = tomedia($service["simg"]);
                        $re = mkThumbnail($service["simg"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg);
                        break;
                    case "title":
                        $name = $service["name"];
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "price":
                        if (!empty($service["price"])) {
                            $name = "￥" . $service["price"];
                        } else {
                            $name = "免费";
                        }
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        if ($x["center"] == 1) {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7])) * 2, $black, $font, $name);
                        } else {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7])) * 2, $black, $font, $name);
                        }
                        break;
                    case "code":
                        $service_bimg = $savepath . "code_" . $num . ".jpg";
                        $re = mkThumbnail($code, intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg);
                        break;
                    case "text":
                        $name = $x["content"];
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "img":
                        if (!empty($x["content"])) {
                            $service_bimg = $savepath . "service_" . $num . ".jpg";
                            $re = mkThumbnail($x["content"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                            $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                            list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                            $bimg_x = imagesx($bimg);
                            $bimg_y = imagesy($bimg);
                            imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                            unlink($service_bimg);
                        }
                        break;
                }
            }
        }
    }
    $exname = '';
    switch ($dst_type) {
        case 1:
            $exname = ".gif";
            imagegif($back_img, $saveokpath . ".gif");
            break;
        case 2:
            $exname = ".jpg";
            imagejpeg($back_img, $saveokpath . ".jpg");
            break;
        case 3:
            $exname = ".png";
            imagepng($back_img, $saveokpath . ".png");
            break;
        default:
    }
    $url = "https://" . $_SERVER["HTTP_HOST"] . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $savefile . '' . $exname;
    return $url;
}
function mkThumbnail($src, $width = null, $height = null, $filename = null)
{
    if (!isset($width) && !isset($height)) {
        return false;
    }
    if (isset($width) && $width <= 0) {
        return false;
    }
    if (isset($height) && $height <= 0) {
        return false;
    }
    $size = getimagesize($src);
    if (!$size) {
        return false;
    }
    list($src_w, $src_h, $src_type) = $size;
    $src_mime = $size["mime"];
    switch ($src_type) {
        case 1:
            $img_type = "gif";
            break;
        case 2:
            $img_type = "jpeg";
            break;
        case 3:
            $img_type = "png";
            break;
        case 15:
            $img_type = "wbmp";
            break;
        default:
            return false;
    }
    if (!isset($width)) {
        $width = $src_w * ($height / $src_h);
    }
    if (!isset($height)) {
        $height = $src_h * ($width / $src_w);
    }
    $imagecreatefunc = "imagecreatefrom" . $img_type;
    $src_img = $imagecreatefunc($src);
    $dest_img = imagecreatetruecolor($width, $height);
    imagealphablending($dest_img, false);
    imagesavealpha($dest_img, true);
    imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $width, $height, $src_w, $src_h);
    $imagefunc = "image" . $img_type;
    if ($filename) {
        $imagefunc($dest_img, $filename);
    } else {
        header("Content-Type: " . $src_mime);
        $imagefunc($dest_img);
    }
    imagedestroy($src_img);
    imagedestroy($dest_img);
    return true;
}
function my_array_multisort($data, $sort_order_field, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
{
    foreach ($data as $val) {
        $key_arrays[] = $val[$sort_order_field];
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $data);
    return $data;
}
function hex2rgb($hexColor)
{
    $color = str_replace("#", '', $hexColor);
    if (strlen($color) > 3) {
        $rgb = array("r" => hexdec(substr($color, 0, 2)), "g" => hexdec(substr($color, 2, 2)), "b" => hexdec(substr($color, 4, 2)));
    } else {
        $color = $hexColor;
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array("r" => hexdec($r), "g" => hexdec($g), "b" => hexdec($b));
    }
    return $rgb;
}
function test($url, $path = "./", $w, $h)
{
    $original_path = $url;
    $dest_path = $path . ".png";
    $src = imagecreatefromstring(file_get_contents($original_path));
    $newpic = imagecreatetruecolor($w, $h);
    imagealphablending($newpic, false);
    $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
    $r = $w / 2;
    $x = 0;
    while ($x < $w) {
        $y = 0;
        while ($y < $h) {
            $c = imagecolorat($src, $x, $y);
            $_x = $x - $w / 2;
            $_y = $y - $h / 2;
            if ($_x * $_x + $_y * $_y < $r * $r) {
                imagesetpixel($newpic, $x, $y, $c);
            } else {
                imagesetpixel($newpic, $x, $y, $transparent);
            }
            $y++;
        }
        $x++;
    }
    imagesavealpha($newpic, true);
    imagepng($newpic, $dest_path);
    imagedestroy($newpic);
    imagedestroy($src);
    unlink($url);
    return $dest_path;
}
function xc_message($status, $obj = null, $message = '', $title = '', $type = "JSON", $json_option = 0)
{
    $data = array();
    if (empty($message)) {
        if ($status == 1) {
            $data["type"] = "success";
            $message = "操作成功";
        } else {
            $data["type"] = "fail";
            $message = "操作失败";
        }
    }
    if (empty($title)) {
        if ($status == 1) {
            $title = "操作成功";
        } else {
            $data["type"] = "fail";
            $title = "操作失败";
        }
    }
    $data["status"] = $status;
    $data["message"] = $message;
    $data["obj"] = $obj;
    switch (strtoupper($type)) {
        case "JSON":
            header("Content-Type:application/json; charset=utf-8");
            exit(json_encode($data, $json_option));
        case "XML":
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($data));
        case "JSONP":
            header("Content-Type:application/json; charset=utf-8");
            $handler = isset($_GET[C("VAR_JSONP_HANDLER")]) ? $_GET[C("VAR_JSONP_HANDLER")] : C("DEFAULT_JSONP_HANDLER");
            exit($handler . "(" . json_encode($data, $json_option) . ");");
        case "EVAL":
            header("Content-Type:text/html; charset=utf-8");
            exit($data);
        default:
            exit("error");
    }
}
function momessv2($accessurl, $templateParam, $openids, $paradata)
{
    $contents = array();
    $contents["data"] = $templateParam["data"];
    $contents["url"] = $templateParam["url"];
    if (strlen($templateParam["ismin"]) > 0 && $templateParam["ismin"] != "-1" && strlen($templateParam["miniprogramappid"])) {
        $miniprogram = array();
        $miniprogram["appid"] = $templateParam["miniprogramappid"];
        $miniprogram["pagepath"] = $templateParam["miniprogrampagepath"];
        $contents["miniprogram"] = $miniprogram;
    }
    $jsoncontents = json_encode($contents, true);
    foreach ($paradata as $parakey => $paraval) {
        $jsoncontents = str_replace("{{" . $parakey . "}}", $paraval, $jsoncontents);
    }
    $contents = json_decode($jsoncontents, true);
    $contents["template_id"] = $templateParam["template_id"];
    $contents["topcolor"] = $templateParam["topcolor"];
    $postdatda = array();
    $postdatda["contents"] = $contents;
    $postdatda["openids"] = $openids;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $accessurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdatda, true));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
function emoji($str, $m)
{
    $emoji = array("/::)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/01.gif", "/::~" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/02.gif", "/::B" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/03.gif", "/::|" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/04.gif", "/:8-)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/05.gif", "/::<" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/06.gif", "/::\$" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/07.gif", "/::X" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/08.gif", "/::Z" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/09.gif", "/::'(" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/10.gif", "/::-|" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/11.gif", "/::@" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/12.gif", "/::P" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/13.gif", "/::D" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/14.gif", "/::O" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/15.gif", "/::(" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/16.gif", "/::([囧]" => "[囧]", "/::Q" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/17.gif", "/::T" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/18.gif", "/:,@P" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/19.gif", "/:,@-D" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/21.gif", "/::d" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/22.gif", "/:,@o" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/23.gif", "/:|-)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/24.gif", "/::!" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/25.gif", "/::L" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/26.gif", "/::>" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/27.gif", "/::,@" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/28.gif", "/:,@f" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/29.gif", "/::-S" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/30.gif", "/:?" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/31.gif", "/:,@x" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/32.gif", "/:,@@" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/33.gif", "/:,@!" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/34.gif", "/:!!!" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/35.gif", "/:xx" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/36.gif", "/:bye" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/37.gif", "/:wipe" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/38.gif", "/:dig" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/39.gif", "/:handclap" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/40.gif", "/:B-)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/41.gif", "/:<@" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/42.gif", "/:@>" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/43.gif", "/::-O" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/44.gif", "/:>-|" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/45.gif", "/:P-(" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/46.gif", "/::'|" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/47.gif", "/:X-)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/48.gif", "/::*" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/49.gif", "/:8*" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/50.gif", "/:pd" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/51.gif", "/:<W>" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/52.gif", "/:beer" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/53.gif", "/:coffee" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/54.gif", "/:pig" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/55.gif", "/:rose" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/56.gif", "/:fade" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/57.gif", "/:showlove" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/58.gif", "/:heart" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/59.gif", "/:break" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/60.gif", "/:cake" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/61.gif", "/:bome" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/62.gif", "/:shit" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/63.gif", "/:moon" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/64.gif", "/:sun" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/65.gif", "/:hug" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/66.gif", "/:strong" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/67.gif", "/:weak" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/68.gif", "/:share" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/69.gif", "/:v" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/70.gif", "/:@)" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/71.gif", "/:jj" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/72.gif", "/:@@" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/73.gif", "/:ok" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/74.gif", "/:jump" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/75.gif", "/:shake" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/76.gif", "/:<O>" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/77.gif", "/:circle" => "https://" . $_SERVER["HTTP_HOST"] . "/addons/" . $m . "/resource/emoji/78.gif", "[Hey]" => "[嘿哈]", "[Facepalm]" => "[捂脸]", "[Smirk]" => "[奸笑]", "[Smart]" => "[机智]", "[Concerned]" => "[皱眉]", "[Yeah!]" => "[耶]", "[Packet]" => "[红包]", "[發]" => "[發]", "[小狗]" => "[小狗]");
    $data = array();
    foreach ($emoji as $key => $x) {
        $str = str_replace($key, $m . "_cut" . $x . $m . "_cut", $str);
    }
    $str = explode($m . "_cut", $str);
    foreach ($str as $s) {
        if (strpos($s, "/addons/" . $m . "/resource/emoji/") !== false) {
            $data[] = array("type" => 2, "content" => $s);
        } else {
            $data[] = array("type" => 1, "content" => $s);
        }
    }
    return $data;
}
function array2url($params)
{
    $str = '';
    $ignore = array("coupon_refund_fee", "coupon_refund_count");
    foreach ($params as $key => $val) {
        if (!((empty($val) || is_array($val)) && !in_array($key, $ignore))) {
            $str .= "{$key}={$val}&";
        }
    }
    $str = trim($str, "&");
    return $str;
}
function sub_pay($order, $_GPC, $_W)
{
    load()->model("payment");
    load()->model("account");
    $moduels = uni_modules();
    if (empty($order) || !array_key_exists($_GPC["m"], $moduels)) {
        return error(1, "模块不存在");
    }
    $moduleid = empty($_GPC["i"]) ? "000000" : sprintf("%06d", $_GPC["i"]);
    $uniontid = date("YmdHis") . $moduleid . random(8, 1);
    $wxapp_uniacid = intval($_W["account"]["uniacid"]);
    $paylog = pdo_get("core_paylog", array("uniacid" => $_W["uniacid"], "module" => $_GPC["m"], "tid" => $order["tid"]));
    if (empty($paylog)) {
        $paylog = array("uniacid" => $_W["uniacid"], "acid" => $_W["acid"], "openid" => $_W["openid"], "module" => $_GPC["m"], "tid" => $order["tid"], "uniontid" => $uniontid, "fee" => floatval($order["fee"]), "card_fee" => floatval($order["fee"]), "status" => "0", "is_usecard" => "0", "tag" => iserializer(array("acid" => $_W["acid"], "uid" => $_W["member"]["uid"])), "type" => "wxapp");
        pdo_insert("core_paylog", $paylog);
        $paylog["plid"] = pdo_insertid();
    }
    if (!empty($paylog) && $paylog["status"] != "0") {
        return error(1, "这个订单已经支付成功, 不需要重复支付.");
    }
    if (!empty($paylog) && empty($paylog["uniontid"])) {
        pdo_update("core_paylog", array("uniontid" => $uniontid), array("plid" => $paylog["plid"]));
        $paylog["uniontid"] = $uniontid;
    }
    $_W["openid"] = $paylog["openid"];
    $params = array("tid" => $paylog["tid"], "fee" => $paylog["card_fee"], "user" => $paylog["openid"], "uniontid" => $paylog["uniontid"], "title" => $order["title"]);
    $setting = uni_setting($wxapp_uniacid, array("payment"));
    $wechat_payment = array("appid" => $_W["account"]["key"], "signkey" => $setting["payment"]["wechat"]["signkey"], "mchid" => $setting["payment"]["wechat"]["mchid"], "version" => 2);
    load()->func("communication");
    $wOpt = array();
    if (!empty($params["user"]) && is_numeric($params["user"])) {
        $params["user"] = mc_uid2openid($params["user"]);
    }
    $package = array();
    $package["appid"] = $order["sub_appid"];
    $package["mch_id"] = $order["sub_mch_id"];
    $package["sub_appid"] = $wechat_payment["appid"];
    $package["sub_mch_id"] = $wechat_payment["mchid"];
    $package["nonce_str"] = random(8);
    $package["body"] = cutstr($params["title"], 26);
    $package["attach"] = $_W["uniacid"];
    $package["out_trade_no"] = $params["uniontid"];
    $package["total_fee"] = $params["fee"] * 100;
    $package["spbill_create_ip"] = CLIENT_IP;
    $package["time_start"] = date("YmdHis", TIMESTAMP);
    $package["time_expire"] = date("YmdHis", TIMESTAMP + 600);
    $package["notify_url"] = $_W["siteroot"] . "addons/" . $_GPC["m"] . "/common/notify.php";
    $package["trade_type"] = "JSAPI";
    $package["sub_openid"] = empty($params["user"]) ? $_W["fans"]["from_user"] : $params["user"];
    ksort($package, SORT_STRING);
    $string1 = '';
    foreach ($package as $key => $v) {
        if (!empty($v)) {
            $string1 .= "{$key}={$v}&";
        }
    }
    $string1 .= "key={$order["sub_key"]}";
    $package["sign"] = strtoupper(md5($string1));
    $dat = array2xml($package);
    $response = ihttp_request("https://api.mch.weixin.qq.com/pay/unifiedorder", $dat);
    if (is_error($response)) {
        return $response;
    }
    $xml = @isimplexml_load_string($response["content"], "SimpleXMLElement", LIBXML_NOCDATA);
    if (strval($xml->return_code) == "FAIL") {
        return error(-1, strval($xml->return_msg));
    }
    if (strval($xml->result_code) == "FAIL") {
        return error(-1, strval($xml->err_code) . ": " . strval($xml->err_code_des));
    }
    $prepayid = $xml->prepay_id;
    $wOpt["appId"] = $wechat_payment["appid"];
    $wOpt["timeStamp"] = strval(TIMESTAMP);
    $wOpt["nonceStr"] = random(8);
    $wOpt["package"] = "prepay_id=" . $prepayid;
    $wOpt["signType"] = "MD5";
    if ($xml->trade_type == "NATIVE") {
        $code_url = $xml->code_url;
        $wOpt["code_url"] = strval($code_url);
    }
    ksort($wOpt, SORT_STRING);
    $string = '';
    foreach ($wOpt as $key => $v) {
        $string .= "{$key}={$v}&";
    }
    $string .= "key={$order["sub_key"]}";
    $wOpt["paySign"] = strtoupper(md5($string));
    return $wOpt;
}
function ararysorts($sorts = "id", $offset = 0, $limit = 20, $order = " desc")
{
    $temsort = array();
    $xsorts = $sorts;
    $xorder = $order;
    if (isset($_REQUEST["sort"]) && !empty($_REQUEST["sort"])) {
        $xsorts = $_REQUEST["sort"];
    }
    if (isset($_REQUEST["order"]) && !empty($_REQUEST["order"])) {
        $xorder = $_REQUEST["order"];
    }
    $temsort["order"] = $xsorts . " " . $xorder;
    $temsort["offset"] = isset($_REQUEST["offset"]) && !empty($_REQUEST["offset"]) ? $_REQUEST["offset"] : $offset;
    $temsort["limit"] = isset($_REQUEST["limit"]) && !empty($_REQUEST["limit"]) ? $_REQUEST["limit"] : $limit;
    return $temsort;
}
function xc_ajax($data, $json_option = 0)
{
    header("Content-Type:application/json; charset=utf-8");
    exit(json_encode($data));
}
function arrayToXml($arr)
{
    $xml = "<root>";
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
    }
    $xml .= "</root>";
    return $xml;
}
function xmlToArray($xml)
{
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA)), true);
    return $values;
}
function juhecurl($url, $params = false, $ispost = 0)
{
    $httpInfo = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        return false;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);
    return $response;
}
function push($requestInfo, $url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Expect:"));
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $requestInfo);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $tmpInfo = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "Errno" . curl_error($curl);
    }
    curl_close($curl);
    return $tmpInfo;
}
function wp_print($printer_sn, $orderInfo, $times)
{
    $content = array("user" => USER, "stime" => STIME, "sig" => SIG, "apiname" => "Open_printMsg", "sn" => $printer_sn, "content" => $orderInfo, "times" => $times);
    $client = new HttpClient(IP, PORT);
    if (!$client->post(PATH, $content)) {
        return "error";
    } else {
        return $client->getContent();
    }
}
function xc_txvideoUrl($url, $type = 1)
{
    if (strpos($url, "http") !== false) {
        $vid = substr($url, 24, 11);
    } else {
        $vid = $url;
    }
    load()->func("communication");
    $response = ihttp_request("http://vv.video.qq.com/getinfo?vids=" . $vid . "&platform=101001&charge=0&otype=json");
    $response = strstr($response["content"], "{");
    $response = substr($response, 0, strlen($response) - 1);
    $response = json_decode($response, true);
    $url = $response["vl"]["vi"][0]["ul"]["ui"][0]["url"];
    if ($type == 1) {
        $fn = $response["vl"]["vi"][0]["fn"];
        $vkey = $response["vl"]["vi"][0]["fvkey"];
        $trueurl = $url . $fn . "?vkey=" . $vkey;
        return $trueurl;
    } else {
        if ($type == 2) {
            $response_2 = ihttp_request("http://vv.video.qq.com/getkey?format=2&otype=json&vt=150&vid=" . $vid . "&ran=0%2E9477521511726081&charge=0&filename=" . $vid . ".mp4&platform=11");
            $response_2 = strstr($response_2["content"], "{");
            $response_2 = substr($response_2, 0, strlen($response_2) - 1);
            $response_2 = json_decode($response_2, true);
            $vkey_2 = $response_2["key"];
            $fn_2 = $response_2["filename"];
            $trueurl = $url . $fn_2 . "?vkey=" . $vkey_2;
            return $trueurl;
        } else {
            return $url;
        }
    }
}
function setcode()
{
    global $_GPC, $_W;
    $uniacid = $_W["uniacid"];
    $request = pdo_get("xc_train_config", array("xkey" => "code", "uniacid" => $uniacid));
    if (!$request) {
        $request["content"] = "000000";
        pdo_insert("xc_train_config", array("uniacid" => $uniacid, "xkey" => "code", "content" => $request["content"]));
    }
    $code = intval($request["content"]) + 1;
    $code3 = '';
    $i = 0;
    while ($i < 6 - strlen($code)) {
        $code3 = $code3 . "0";
        $i++;
    }
    $code3 = $code3 . $code;
    pdo_update("xc_train_config", array("content" => $code3), array("xkey" => "code", "uniacid" => $uniacid));
    $vipcode = $uniacid . date("Ymd") . $code3;
    return $vipcode;
}
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000;
    $lat1 = $lat1 * pi() / 180;
    $lng1 = $lng1 * pi() / 180;
    $lat2 = $lat2 * pi() / 180;
    $lng2 = $lng2 * pi() / 180;
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    return round($calculatedDistance);
}
function sharecode($code, $service, $_W)
{
    $back = IA_ROOT . "/addons/xc_train/resource/images/back.jpg";
    $num = time();
    $savepath = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savepath = $savepath . "/" . date("m") . "/";
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savefile = $num;
    $saveokpath = $savepath . $savefile;
    $back_img = imagecreatefromstring(file_get_contents($back));
    list($dst_w, $dst_h, $dst_type) = getimagesize($back);
    $font = IA_ROOT . "/addons/xc_black/resource/WRYH.ttf";
    $poster = pdo_get("xc_train_config", array("xkey" => "share_poster", "uniacid" => $_W["uniacid"]));
    if ($poster) {
        $poster["content"] = json_decode($poster["content"], true);
        if (!empty($poster["content"]) && !empty($poster["content"]["list"])) {
            $poster["content"]["list"] = my_array_multisort($poster["content"]["list"], "z-index");
            foreach ($poster["content"]["list"] as $x) {
                switch ($x["type"]) {
                    case "bimg":
                        $service_bimg = $savepath . "logo_" . $num . ".jpg";
                        $re = mkThumbnail($service["avatar"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $service_bimg2 = test($service_bimg, $savepath . "logo2_" . $num, intval($x["width"]) * 2, intval($x["height"]) * 2);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg2));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg2);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg2);
                        break;
                    case "nick":
                        $name = base64_decode($service["nick"], true);
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                                if ($x["bold"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2 + 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "code":
                        $service_bimg = $savepath . "code_" . $num . ".jpg";
                        $re = mkThumbnail($code, intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                        $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                        list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                        $bimg_x = imagesx($bimg);
                        $bimg_y = imagesy($bimg);
                        imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                        unlink($service_bimg);
                        break;
                    case "text":
                        $name = $x["content"];
                        $width = intval(intval($x["width"]) / intval($x["px"]));
                        $height = intval(intval($x["height"]) / intval($x["px"]));
                        $line = intval(mb_strlen($name, "UTF-8") / $width) + 1;
                        $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $name);
                        $color = hex2rgb($x["color"]);
                        $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                        $i = 0;
                        while ($i < $line) {
                            $text = mb_substr($name, $i * $width, $width, "UTF-8");
                            if ($i + 1 <= $height && !empty($text)) {
                                if ($x["center"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                } else {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                                if ($x["bold"] == 1) {
                                    imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2 + 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($name, $i * $width, $width, "UTF-8"));
                                }
                            }
                            $i++;
                        }
                        break;
                    case "img":
                        if (!empty($x["content"])) {
                            $service_bimg = $savepath . "service_" . $num . ".jpg";
                            $service["simg"] = tomedia($service["simg"]);
                            $re = mkThumbnail($x["content"], intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                            $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                            list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                            $bimg_x = imagesx($bimg);
                            $bimg_y = imagesy($bimg);
                            imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                            unlink($service_bimg);
                        }
                        break;
                }
            }
        }
    }
    $exname = '';
    switch ($dst_type) {
        case 1:
            $exname = ".gif";
            imagegif($back_img, $saveokpath . ".gif");
            break;
        case 2:
            $exname = ".jpg";
            imagejpeg($back_img, $saveokpath . ".jpg");
            break;
        case 3:
            $exname = ".png";
            imagepng($back_img, $saveokpath . ".png");
            break;
        default:
    }
    $url = "https://" . $_SERVER["HTTP_HOST"] . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $savefile . '' . $exname;
    return $url;
}
function movecode($data, $post_data, $_W)
{
    $back = IA_ROOT . "/addons/xc_train/resource/images/back.jpg";
    $num = time();
    $savepath = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savepath = $savepath . "/" . date("m") . "/";
    if (!is_dir($savepath)) {
        mkdir($savepath, 0700, true);
    }
    $savefile = $num;
    $saveokpath = $savepath . $savefile;
    $back_img = imagecreatefromstring(file_get_contents($back));
    list($dst_w, $dst_h, $dst_type) = getimagesize($back);
    $font = IA_ROOT . "/addons/xc_black/resource/WRYH.ttf";
    $list = my_array_multisort($data["list"], "z-index");
    foreach ($list as $x) {
        if ($x["type"] == "code" || $x["type"] == "img" || $x["type"] == "bimg") {
            $content = '';
            if ($x["type"] == "img") {
                $content = $x["content"];
            } else {
                $content = $post_data[$x["type"]];
            }
            if (!empty($content)) {
                $service_bimg = $savepath . "move_" . $num . ".jpg";
                $re = mkThumbnail($content, intval($x["width"]) * 2, intval($x["height"]) * 2, $service_bimg);
                $bimg = imagecreatefromstring(file_get_contents($service_bimg));
                list($bimg_w, $bimg_h, $bimg_type) = getimagesize($service_bimg);
                $bimg_x = imagesx($bimg);
                $bimg_y = imagesy($bimg);
                imagecopy($back_img, $bimg, intval($x["left"]) * 2, intval($x["top"]) * 2, 0, 0, $bimg_x, $bimg_y);
                unlink($service_bimg);
            }
        } else {
            $content = '';
            if ($x["type"] == "text") {
                $content = $x["content"];
            } else {
                $content = $post_data[$x["type"]];
            }
            if (!empty($content)) {
                $width = intval(intval($x["width"]) / intval($x["px"]));
                $height = intval(intval($x["height"]) / intval($x["px"]));
                $line = intval(mb_strlen($content, "UTF-8") / $width) + 1;
                $fontBox = imagettfbbox((intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, $font, $content);
                $color = hex2rgb($x["color"]);
                $black = imagecolorallocate($back_img, $color["r"], $color["g"], $color["b"]);
                $i = 0;
                while ($i < $line) {
                    $text = mb_substr($content, $i * $width, $width, "UTF-8");
                    if ($i + 1 <= $height && !empty($text)) {
                        if ($x["center"] == 1) {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, (intval($x["left"]) + (intval($x["width"]) - ($fontBox[2] - $fontBox[0]) / 2) / 2) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($content, $i * $width, $width, "UTF-8"));
                        } else {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($content, $i * $width, $width, "UTF-8"));
                        }
                        if ($x["bold"] == 1) {
                            imagefttext($back_img, (intval($x["px"]) - (intval(intval($x["px"]) / 7) + 3)) * 2, 0, intval($x["left"]) * 2 + 2, (intval($x["top"]) + ($fontBox[1] - $fontBox[7]) * ($i + 1)) * 2, $black, $font, mb_substr($content, $i * $width, $width, "UTF-8"));
                        }
                    }
                    $i++;
                }
            }
        }
    }
    $exname = '';
    switch ($dst_type) {
        case 1:
            $exname = ".gif";
            imagegif($back_img, $saveokpath . ".gif");
            break;
        case 2:
            $exname = ".jpg";
            imagejpeg($back_img, $saveokpath . ".jpg");
            break;
        case 3:
            $exname = ".png";
            imagepng($back_img, $saveokpath . ".png");
            break;
        default:
    }
    $url = "https://" . $_SERVER["HTTP_HOST"] . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $savefile . '' . $exname;
    return $url;
}