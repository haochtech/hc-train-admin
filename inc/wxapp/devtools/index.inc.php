<?php

//decode by http://www.zx-xcx.com/
defined("IN_IA") or exit("Access Denied");
global $_GPC, $_W, $xcmodule;
$uniacid = $_W["uniacid"];
$op = strlen($_GPC["op"]) > 1 ? $_GPC["op"] : "base";
require_once IA_ROOT . "/addons/" . $_GPC["m"] . "/resource/qrcode/phpqrcode.php";
switch ($op) {
	case "userinfo":
		if (!empty($_W["openid"])) {
			$userinfo = pdo_get("xc_train_userinfo", array("openid" => $_W["openid"], "uniacid" => $uniacid));
			if ($userinfo) {
				$condition = array();
				if (!empty($_GPC["avatarUrl"])) {
					$condition["avatar"] = $_GPC["avatarUrl"];
				}
				if (!empty($_GPC["nickName"])) {
					$condition["nick"] = base64_encode($_GPC["nickName"]);
				}
				if (!empty($condition)) {
					pdo_update("xc_train_userinfo", $condition, array("openid" => $_W["openid"], "uniacid" => $uniacid));
				}
			} else {
				if (!empty($_GPC["avatarUrl"])) {
					$condition["avatar"] = $_GPC["avatarUrl"];
				}
				if (!empty($_GPC["nickName"])) {
					$condition["nick"] = base64_encode($_GPC["nickName"]);
				}
				$condition["uniacid"] = $uniacid;
				$condition["openid"] = $_W["openid"];
				if (!empty($_GPC["share"])) {
					$share_config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
					if ($share_config) {
						$share_config = json_decode($share_config["content"], true);
						if ($share_config && $share_config["status"] == 1) {
							$share_user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "id" => $_GPC["share"]));
							if ($share_user && $share_user["openid"] != $_W["openid"]) {
								$condition["share"] = $share_user["openid"];
							}
						}
					}
				}
				pdo_insert("xc_train_userinfo", $condition);
			}
			$request = pdo_get("xc_train_userinfo", array("status" => 1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
			if ($request) {
				$request["nick"] = base64_decode($request["nick"]);
				$request["login"] = -1;
				$config = pdo_get("xc_train_config", array("xkey" => "open_ad", "uniacid" => $uniacid));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if ($config["content"]["status"] == 1) {
						if ($config["content"]["type"] == 1) {
							$log = pdo_get("xc_train_login_log", array("openid" => $_W["openid"]));
							if ($log) {
								$request["login"] = -1;
							} else {
								$request["login"] = 1;
							}
						} elseif ($config["content"]["type"] == 2) {
							$log = pdo_get("xc_train_login_log", array("openid" => $_W["openid"], "plan_date" => date("Y-m-d")));
							if ($log) {
								$request["login"] = -1;
							} else {
								$request["login"] = 1;
							}
						}
					}
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(1, "请先授权");
			}
		}
		break;
	case "base":
		$request = array();
		$request["xc_img"] = array("step01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/step01.jpg", "step02" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/step02.jpg", "apply01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/apply01.png", "apply02" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/apply02.png", "class01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/class02.png", "class02" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/class002.png", "cur01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/cur01.png", "fen01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/fen_nav01.png", "fen02" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/fen_nav02.png", "fen03" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/fen_nav03.png", "fen04" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/fen_nav04.png", "fen05" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/fen01.png", "gua" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/gua.png", "move01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/move01.png", "online" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/online.png", "score01" => $_W["siteroot"] . "addons/" . $_GPC["m"] . "/resource/wxapp/score01.png");
		$config = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "web"));
		if ($config) {
			$config["content"] = json_decode($config["content"], true);
			if (!empty($config["content"]["footer"])) {
				foreach ($config["content"]["footer"] as &$x) {
					$x["icon"] = tomedia($x["icon"]);
					$x["select"] = tomedia($x["select"]);
				}
			}
			if (!empty($config["content"]["password"])) {
				$config["content"]["password"] = md5($config["content"]["password"]);
			}
			if (!empty($config["content"]["online_simg"])) {
				$config["content"]["online_simg"] = tomedia($config["content"]["online_simg"]);
			}
			if (!empty($config["content"]["sign_bimg"])) {
				$config["content"]["sign_bimg"] = tomedia($config["content"]["sign_bimg"]);
			}
			if (!empty($config["content"]["g_class"])) {
				$config["content"]["g_class"] = tomedia($config["content"]["g_class"]);
			}
			if (!empty($config["content"]["x_class"])) {
				$config["content"]["x_class"] = tomedia($config["content"]["x_class"]);
			}
			if (!empty($config["content"]["cut_bimg"])) {
				$config["content"]["cut_bimg"] = explode(",", $config["content"]["cut_bimg"]);
				foreach ($config["content"]["cut_bimg"] as &$cb) {
					$cb = tomedia($cb);
				}
			}
			if (!empty($config["content"]["news_icon"])) {
				$config["content"]["news_icon"] = tomedia($config["content"]["news_icon"]);
			}
			if (empty($config["content"]["service_list5"])) {
				$config["content"]["service_list5"] = "预约试听";
			}
			if (!empty($config["content"]["group_bimg"])) {
				foreach ($config["content"]["group_bimg"] as &$gb) {
					$gb = tomedia($gb);
				}
			}
			if (empty($config["content"]["mall_pei_type"])) {
				$config["content"]["mall_pei_type"] = -1;
			}
			$request["config"] = $config;
		}
		$theme = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "theme"));
		if ($theme) {
			$theme["content"] = json_decode($theme["content"], true);
			if (!empty($theme["content"]["icon"])) {
				foreach ($theme["content"]["icon"] as &$x) {
					$x = tomedia($x);
				}
			}
			$request["theme"] = $theme;
		}
		$closed = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "closed"));
		if ($closed) {
			$closed["content"] = json_decode($closed["content"], true);
			if (!empty($closed["content"]) && $closed["content"]["status"] == 1) {
				$request["closed"] = $closed["content"];
			}
		}
		$text = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "text"));
		if ($text) {
			$request["text"] = json_decode($text["content"], true);
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "index":
		$request = array();
		$banner = pdo_getall("xc_train_banner", array("status" => 1, "uniacid" => $uniacid), array(), '', "sort DESC,createtime DESC");
		if ($banner) {
			foreach ($banner as &$x) {
				$x["bimg"] = tomedia($x["bimg"]);
			}
			$request["banner"] = $banner;
		}
		$ad = pdo_get("xc_train_config", array("xkey" => "ad", "uniacid" => $uniacid));
		if ($ad) {
			$ad["content"] = json_decode($ad["content"], true);
			if ($ad["content"]["status"] == 1 && !empty($ad["content"]["list"])) {
				$request["ad"] = $ad["content"]["list"];
				if (!empty($ad["content"]["color"])) {
					$request["ad_color"] = $ad["content"]["color"];
				}
			}
		}
		$nav = pdo_getall("xc_train_nav", array("status" => 1, "uniacid" => $uniacid), array(), '', "sort DESC,id DESC");
		if ($nav) {
			foreach ($nav as &$n) {
				$n["simg"] = tomedia($n["simg"]);
			}
			$request["nav"] = $nav;
		}
		$list = pdo_getall("xc_train_service", array("uniacid" => $uniacid, "status" => 1, "index" => 1), array(), '', "sort DESC,createtime DESC");
		if ($list) {
			foreach ($list as &$l) {
				$l["bimg"] = tomedia($l["bimg"]);
			}
			$request["list"] = $list;
		}
		$mall = pdo_getall("xc_train_mall", array("uniacid" => $uniacid, "status" => 1, "index" => 1), array(), '', "sort DESC,id DESC");
		if ($mall) {
			foreach ($mall as &$m) {
				$m["simg"] = tomedia($m["simg"]);
			}
			$request["mall"] = $mall;
		}
		$news = pdo_getall("xc_train_news", array("uniacid" => $uniacid, "status" => 1, "index" => 1), array(), '', "sort DESC,id DESC");
		if ($news) {
			foreach ($news as &$n) {
				$n["simg"] = tomedia($n["simg"]);
			}
			$request["news"] = $news;
		}
		$open_ad = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "open_ad"));
		if ($open_ad) {
			$open_ad["content"] = json_decode($open_ad["content"], true);
			if (!empty($open_ad["content"]["bimg"])) {
				$open_ad["content"]["bimg"] = tomedia($open_ad["content"]["bimg"]);
			}
			if ($open_ad["content"]["status"] == 1) {
				if ($open_ad["content"]["type"] == 1) {
					$log = pdo_get("xc_train_login_log", array("openid" => $_W["openid"]));
					if ($log) {
						$open_ad["login"] = -1;
					} else {
						$open_ad["login"] = 1;
					}
				} elseif ($open_ad["content"]["type"] == 2) {
					$log = pdo_get("xc_train_login_log", array("openid" => $_W["openid"], "plan_date" => date("Y-m-d")));
					if ($log) {
						$open_ad["login"] = -1;
					} else {
						$open_ad["login"] = 1;
					}
				}
			}
			$request["open_ad"] = $open_ad;
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "coupon":
		$coupon = pdo_getall("xc_train_coupon", array("status" => 1, "uniacid" => $uniacid));
		if ($coupon) {
			$coupon_id = array();
			$datalist = array();
			foreach ($coupon as $c) {
				$datalist[$c["id"]] = $c;
				$coupon_id[] = $c["id"];
			}
			$user_coupon = pdo_getall("xc_train_user_coupon", array("status" => -1, "uniacid" => $uniacid, "openid" => $_W["openid"]));
			$user_id = array();
			if ($user_coupon) {
				foreach ($user_coupon as $u) {
					$user_id[] = $u["cid"];
				}
			}
			if ($_GPC["curr"] == 1) {
				$condition["status"] = 1;
				$condition["uniacid"] = $uniacid;
				if (!empty($user_id)) {
					$condition["id !="] = $user_id;
				}
				$request = pdo_getall("xc_train_coupon", $condition, array(), '', "sort DESC,createtime DESC");
				foreach ($request as $key => $x) {
					$x["times"] = json_decode($x["times"], true);
					if (strtotime($x["times"]["start"]) < time() && time() < strtotime($x["times"]["end"])) {
						$request[$key]["fail"] = date("Y/m/d", strtotime($x["times"]["end"]));
					} else {
						unset($request[$key]);
					}
				}
			} elseif ($_GPC["curr"] == 2) {
				$condition["status"] = -1;
				$condition["uniacid"] = $uniacid;
				$condition["cid IN"] = $coupon_id;
				$condition["openid"] = $_W["openid"];
				$request = pdo_getall("xc_train_user_coupon", $condition, array(), '', "createtime DESC");
				if ($request) {
					foreach ($request as $key => $x) {
						$x["times"] = $datalist[$x["cid"]]["times"];
						$x["times"] = json_decode($x["times"], true);
						if (strtotime($x["times"]["start"]) < time() && time() < strtotime($x["times"]["end"])) {
							$request[$key]["fail"] = date("Y/m/d", strtotime($x["times"]["end"]));
							$request[$key]["name"] = $datalist[$x["cid"]]["name"];
							$request[$key]["condition"] = $datalist[$x["cid"]]["condition"];
						} else {
							unset($request[$key]);
						}
					}
				}
			} elseif ($_GPC["curr"] == 3) {
				$condition["status"] = 1;
				$condition["uniacid"] = $uniacid;
				$condition["cid IN"] = $coupon_id;
				$condition["openid"] = $_W["openid"];
				$request = pdo_getall("xc_train_user_coupon", $condition, array(), '', "createtime DESC");
				if ($request) {
					foreach ($request as $key => $x) {
						$x["times"] = $datalist[$x["cid"]]["times"];
						$x["times"] = json_decode($x["times"], true);
						$request[$key]["fail"] = date("Y/m/d", strtotime($x["times"]["end"]));
						$request[$key]["name"] = $datalist[$x["cid"]]["name"];
						$request[$key]["condition"] = $datalist[$x["cid"]]["condition"];
					}
				}
			} elseif ($_GPC["curr"] == 4) {
				$condition["status"] = -1;
				$condition["uniacid"] = $uniacid;
				$condition["cid IN"] = $coupon_id;
				$condition["openid"] = $_W["openid"];
				$request = pdo_getall("xc_train_user_coupon", $condition, array(), '', "createtime DESC");
				if ($request) {
					foreach ($request as $key => $x) {
						$x["times"] = $datalist[$x["cid"]]["times"];
						$x["times"] = json_decode($x["times"], true);
						if (strtotime($x["times"]["start"]) < time() && time() < strtotime($x["times"]["end"])) {
							unset($request[$key]);
						} else {
							unset($request[$key]);
							$request[$key]["fail"] = date("Y/m/d", strtotime($x["times"]["end"]));
							$request[$key]["name"] = $datalist[$x["cid"]]["name"];
							$request[$key]["condition"] = $datalist[$x["cid"]]["condition"];
						}
					}
				}
			}
			if (!empty($request)) {
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(0, "操作失败");
			}
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "school":
		if (!empty($_GPC["longitude"]) && !empty($_GPC["latitude"])) {
			$page = intval($_GPC["page"] - 1) * intval($_GPC["pagesize"]);
			$pageisze = $_GPC["pagesize"];
			$sql = "SELECT *,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((:xlatitude*PI()/180-latitude*PI()/180)/2),2)+COS(:xlatitude*PI()/180)*COS(latitude*PI()/180)*POW(SIN((:xlongitude*PI()/180-longitude*PI()/180)/2),2))),2) AS juli FROM " . tablename("xc_train_school") . " WHERE status=1 AND uniacid=:uniacid ORDER BY juli,sort DESC,createtime DESC LIMIT {$page},{$pageisze}";
			$request = pdo_fetchall($sql, array(":xlatitude" => $_GPC["latitude"], ":xlongitude" => $_GPC["longitude"], ":uniacid" => $uniacid));
		} else {
			$request = pdo_getall("xc_train_school", array("uniacid" => $uniacid, "status" => 1), array(), '', "sort DESC,createtime DESC", array($_GPC["page"], $_GPC["pagesize"]));
		}
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "school_detail":
		$request = pdo_get("xc_train_school", array("status" => 1, "id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$request["simg"] = tomedia($request["simg"]);
			$request["content"] = json_decode($request["content"], true);
			$request["total"] = 0;
			if (!empty($request["teacher"])) {
				$request["teacher"] = json_decode($request["teacher"], true);
				$request["total"] = count($request["teacher"]);
				$teacher = pdo_getall("xc_train_teacher", array("status" => 1, "uniacid" => $uniacid));
				$datalist = array();
				if ($teacher) {
					foreach ($teacher as $t) {
						$datalist[$t["id"]] = $t;
					}
				}
				foreach ($request["teacher"] as &$x) {
					$x["simg"] = tomedia($datalist[$x["id"]]["simg"]);
					$x["name"] = $datalist[$x["id"]]["name"];
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "login_log":
		$request = pdo_insert("xc_train_login_log", array("openid" => $_W["openid"], "uniacid" => $uniacid, "plan_date" => date("Y-m-d")));
		break;
	case "article":
		$request = pdo_get("xc_train_article", array("id" => $_GPC["id"], "uniacid" => $uniacid));
		if ($request) {
			$request["content"] = htmlspecialchars_decode($request["content"]);
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "closed":
		$request = pdo_get("xc_train_config", array("uniacid" => $uniacid, "xkey" => "closed"));
		if ($request) {
			$request["content"] = json_decode($request["content"], true);
			if (!empty($request["content"]) && !empty($request["content"]["simg"])) {
				$request["content"]["simg"] = tomedia($request["content"]["simg"]);
			}
			return $this->result(0, "操作成功", $request["content"]);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "sms_code":
		$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($config) {
			$config["content"] = json_decode($config["content"], true);
			if ($config["content"]["apply_code"] == 1) {
				$code = random(6, true);
				if ($config["content"]["code_type"] == 1) {
					require_once MODULE_ROOT . "/resource/sms/sendSms.php";
					$send = new sms();
					$templateParam = array("code" => $code);
					$result = $send->sendSms($config["content"]["accesskeyid"], $config["content"]["accesskeysecret"], $config["content"]["sign"], $config["content"]["code"], $_GPC["mobile"], $templateParam);
					if (strtolower($result->Code) == "ok") {
						cache_write($_W["uniacid"] . "_" . $_W["openid"] . "_code", $code, 5 * 60);
						return $this->result(0, "发送成功", $result);
					} else {
						return $this->result(1, $result->Message, $result);
					}
				} elseif ($config["content"]["code_type"] == 2) {
					$sendUrl = "http://v.juhe.cn/sms/send";
					$tpl_value = array("#code#=" . $code);
					$smsConf = array("key" => $config["content"]["appkey"], "mobile" => $_GPC["mobile"], "tpl_id" => $config["content"]["tpl_id"], "tpl_value" => join("&", $tpl_value));
					$content = juhecurl($sendUrl, $smsConf, 1);
					if ($content) {
						$result = json_decode($content, true);
						if ($result["error_code"] == 0) {
							cache_write($_W["uniacid"] . "_" . $_W["openid"] . "_code", $code, 5 * 60);
							return $this->result(0, "发送成功", $result);
						} else {
							return $this->result(1, $result["reason"]);
						}
					} else {
						return $this->result(1, "其它错误");
					}
				}
			} else {
				return $this->result(1, "短信未开启");
			}
		} else {
			return $this->result(1, "短信未配置");
		}
		break;
	case "share_apply":
		$apply = pdo_get($xcmodule . "_apply", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => -1));
		if ($apply) {
			return $this->result(1, "已申请");
		} else {
			if (!empty($_GPC["code"])) {
				$code = cache_load($_W["uniacid"] . "_" . $_W["openid"] . "_code");
				if (empty($code) || $code != $_GPC["code"]) {
					return $this->result(1, "验证码错误");
				}
			}
			$condition["uniacid"] = $_W["uniacid"];
			$condition["openid"] = $_W["openid"];
			$condition["name"] = $_GPC["name"];
			$condition["mobile"] = $_GPC["mobile"];
			$condition["status"] = -1;
			$condition["createtime"] = date("Y-m-d H:i:s");
			$request = pdo_insert($xcmodule . "_apply", $condition);
			if ($request) {
				return $this->result(0, "操作成功", array("status" => 1));
			} else {
				return $this->result(1, "操作失败");
			}
		}
		break;
	case "share_change":
		$request = pdo_update($xcmodule . "_apply", array("status" => 3), array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 2));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "share_index":
		$request = array();
		$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($user) {
			$user["nick"] = base64_decode($user["nick"]);
			$user["createtime"] = date("Y-m-d", strtotime($user["createtime"]));
			if (!empty($user["share"])) {
				$share_user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $user["share"]));
				if ($share_user) {
					$user["share_name"] = base64_decode($share_user["nick"]);
				}
			}
			$request["user"] = $user;
		}
		$status = -1;
		$share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($share) {
			$share = json_decode($share["content"], true);
			if (!empty($share) && $share["status"] == 1) {
				$status = 1;
				if ($share["apply_status"] == 1) {
					$apply = pdo_get($xcmodule . "_apply", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1));
					if ($apply) {
						$status = 1;
					} else {
						$status = -1;
					}
				}
				$request["share"] = $share;
			}
		}
		if ($status == -1) {
			return $this->result(1, "没有权限", array("redirect" => "../index/index"));
		}
		$poster = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share_poster"));
		if ($poster) {
			$poster = json_decode($poster["content"], true);
			if (!empty($poster) && $poster["status"] == 1) {
				$request["poster"] = $poster;
			}
		}
		$team_one = 0;
		$team_two = 0;
		$team_three = 0;
		if ($share["type"] >= 1) {
			$one_user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share" => $_W["openid"]));
			$one_ids = array();
			if ($one_user) {
				$team_one = count($one_user);
				foreach ($one_user as $ou) {
					$one_ids[] = $ou["openid"];
				}
			}
			if ($share["type"] >= 2 && !empty($one_ids)) {
				$two_user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share IN" => $one_ids));
				$two_ids = array();
				if ($two_user) {
					$team_two = count($two_user);
					foreach ($two_user as $tu) {
						$two_ids[] = $tu["openid"];
					}
				}
				if ($share["type"] == 3 && !empty($two_ids)) {
					$three_user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share IN" => $two_ids));
					if ($three_user) {
						$team_three = count($three_user);
					}
				}
			}
		}
		$request["team"] = intval($team_one) + intval($team_two) + intval($team_three);
		$request["withdraw"] = 0;
		$sql = "SELECT sum(amount) FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND order_type=1 AND openid=:openid AND status=1";
		$withdraw = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
		if ($withdraw) {
			$request["withdraw"] = round(floatval($withdraw), 2);
		}
		$request["order"] = 0;
		$order = pdo_count($xcmodule . "_share_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status IN" => array(-1, 1)));
		if ($order) {
			$request["order"] = $order;
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "share_code":
		$poster = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share_poster"));
		if ($poster) {
			$poster = json_decode($poster["content"], true);
			if (!empty($poster) && $poster["status"] == 1) {
				$code = '';
				$user = pdo_get("xc_train_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if ($user && !empty($user["share_code"])) {
					$code = tomedia($user["share_code"]);
				}
				if (empty($code) || !file_get_contents($code, 0, null, 0, 1)) {
					$account_api = WeAccount::create();
					$token = $account_api->getAccessToken();
					if (is_array($token) || empty($token)) {
						return $this->result(1, $token["errno"] . ":" . $token["message"], $token);
					} else {
						$filename = "code_" . $_W["openid"] . ".jpg";
						$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/";
						if (!file_exists($fileurl)) {
							mkdir($fileurl, 0777, true);
						}
						$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
						$post_data = array("page" => "xc_train/pages/base/base", "scene" => "share_" . $user["id"]);
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
						$output = curl_exec($ch);
						curl_close($ch);
						$request = json_decode($output, true);
						if (is_array($request) && !empty($request)) {
							return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
						} else {
							header("Content-Type: text/plain; charset=utf-8");
							header("Content-type:image/jpeg");
							$jpg = $output;
							$file = fopen($fileurl . $filename, "w");
							$reee = fwrite($file, $jpg);
							fclose($file);
							$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
							pdo_update($xcmodule . "_userinfo", array("share_code" => $code), array("openid" => $_W["openid"], "uniacid" => $uniacid));
							$code = tomedia($code);
						}
					}
				}
				$share_code = sharecode($code, $user, $_W);
				return $this->result(0, "操作成功", array("code" => $share_code));
			} else {
				return $this->result(1, "海报未开启");
			}
		} else {
			return $this->result(1, "海报未开启");
		}
		break;
	case "share_config":
		$request = array();
		$status = -1;
		$share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($share) {
			$share = json_decode($share["content"], true);
			if (!empty($share) && $share["status"] == 1) {
				$status = 1;
				if ($share["apply_status"] == 1) {
					$apply = pdo_get($xcmodule . "_apply", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1));
					if ($apply) {
						$status = 1;
					} else {
						$status = -1;
					}
				}
			}
			$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			if ($user) {
				$share["user"] = $user;
			}
			$request = $share;
		}
		if ($status == -1) {
			return $this->result(1, "没有权限", array("redirect" => "../index/index"));
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "share_team":
		$curr = intval($_GPC["curr"]);
		$curr_name = '';
		$request = array();
		if ($curr == 1) {
			$curr_name = "一级";
			$request = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share" => $_W["openid"]), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		} elseif ($curr == 2) {
			$curr_name = "二级";
			$one = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share" => $_W["openid"]), array("openid"));
			if ($one) {
				$ids = array();
				foreach ($one as $o) {
					$ids[] = $o["openid"];
				}
				$request = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share IN" => $ids), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
			}
		} elseif ($curr == 3) {
			$curr_name = "三级";
			$one = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share" => $_W["openid"]), array("openid"));
			if ($one) {
				$ids = array();
				foreach ($one as $o) {
					$ids[] = $o["openid"];
				}
				$two = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share IN" => $ids), array("openid"));
				if ($two) {
					$ids = array();
					foreach ($two as $o) {
						$ids[] = $o["openid"];
					}
					$request = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "share IN" => $ids), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
				}
			}
		}
		if ($request) {
			$openids = array();
			foreach ($request as $o) {
				$openids[] = "'" . $o["openid"] . "'";
			}
			$openids = "(" . implode(",", $openids) . ")";
			$sql = "SELECT count(*) as order_count,share FROM " . tablename($xcmodule . "_share_order") . " WHERE uniacid=:uniacid AND `type`=:curr AND status=1 AND share IN " . $openids . "  GROUP BY share ";
			$order_count = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":curr" => $_GPC["curr"]));
			$order_count_data = array();
			if ($order_count) {
				foreach ($order_count as $oc) {
					$order_count_data[$oc["share"]] = $oc["order_count"];
				}
			}
			$sql = "SELECT sum(amount) as order_sum,share FROM " . tablename($xcmodule . "_share_order") . " WHERE uniacid=:uniacid AND `type`=:curr AND status=1 AND share IN " . $openids . " GROUP BY share";
			$order_sum = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":curr" => $_GPC["curr"]));
			$order_sum_data = array();
			if ($order_sum) {
				foreach ($order_sum as $oc) {
					$order_sum_data[$oc["share"]] = $oc["order_sum"];
				}
			}
			foreach ($request as &$x) {
				$x["nick"] = base64_decode($x["nick"]);
				$share = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $x["share"]));
				if ($share) {
					$x["share_name"] = base64_decode($share["nick"]);
				}
				$x["order_count"] = 0;
				if (!empty($order_count_data[$x["openid"]])) {
					$x["order_count"] = $order_count_data[$x["share"]];
				}
				$x["order_sum"] = 0;
				if (!empty($order_sum_data[$x["openid"]])) {
					$x["order_sum"] = round(floatval($order_sum_data[$x["openid"]]), 2);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "share_withdraw":
		$share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($share) {
			$share = json_decode($share["content"], true);
			if (!empty($share) && $share["status"] == 1 && $share["withdraw"] == 1) {
				$xc = $_GPC["xc"];
				$amount = $xc["amount"];
				if (!empty($share["withdraw_limit"]) && floatval($share["withdraw_limit"]) > floatval($xc["amount"])) {
					return $this->result(1, "金额错误");
				}
				$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if (floatval($amount) > floatval($user["share_fee"])) {
					return $this->result(1, "余额不足");
				}
				$fee = 0;
				if ($share["fee_type"] == 2 && !empty($share["fee_price"])) {
					$fee = $share["fee_price"];
				} else {
					if ($share["fee_type"] == 3 && !empty($share["fee_pro"])) {
						$fee = round(floatval($amount) * floatval($share["fee_pro"]) / 100, 2);
					}
				}
				$xc["amount"] = round(floatval($amount) - floatval($fee), 2);
				if (floatval($xc["amount"]) < 0) {
					return $this->result(1, "提现金额错误");
				}
				$xc["uniacid"] = $_W["uniacid"];
				$xc["openid"] = $_W["openid"];
				$xc["out_trade_no"] = setcode();
				$xc["fee"] = $fee;
				$xc["order_type"] = 1;
				$xc["status"] = -1;
				$xc["createtime"] = date("Y-m-d H:i:s");
				$request = pdo_insert($xcmodule . "_share_withdraw", $xc);
				if ($request) {
					pdo_update($xcmodule . "_userinfo", array("share_fee -=" => floatval($amount)), array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
					return $this->result(0, "操作成功", array("status" => 1));
				} else {
					return $this->result(1, "操作失败");
				}
			} else {
				return $this->result(1, "没有权限");
			}
		} else {
			return $this->result(1, "没有权限");
		}
		break;
	case "share_record":
		$condition["uniacid"] = $_W["uniacid"];
		$condition["openid"] = $_W["openid"];
		$condition["status"] = $_GPC["curr"];
		$condition["order_type"] = 1;
		if (!empty($_GPC["plan_date"])) {
			$start = $_GPC["plan_date"] . "-01 00:00:00";
			$end = date("Y-m-d", strtotime($start . " +1 months ")) . " 00:00:00";
			$condition["createtime >="] = $start;
			$condition["createtime <"] = $end;
		}
		$request = pdo_getall($xcmodule . "_share_withdraw", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "share_record_count":
		$request = array("amount" => 0);
		$where = " uniacid=:uniacid AND openid=:openid AND status=1 AND order_type=1 ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":openid"] = $_W["openid"];
		if (!empty($_GPC["plan_date"])) {
			$start = $_GPC["plan_date"] . "-01 00:00:00";
			$end = date("Y-m-d", strtotime($start . " +1 months ")) . " 00:00:00";
			$where .= " AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start);
			$condition[":end_time"] = strtotime($end);
		}
		$sql = "SELECT sum(amount) FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE " . $where;
		$withdraw = pdo_fetchcolumn($sql, $condition);
		if ($withdraw) {
			$request["amount"] = round(floatval($withdraw), 2);
		}
		return $this->result(0, "操作成功", $request);
		break;
	case "share_order":
		$condition["uniacid"] = $_W["uniacid"];
		$condition["openid"] = $_W["openid"];
		if ($_GPC["curr"] == 1) {
			$condition["status"] = -1;
		} elseif ($_GPC["curr"] == 2) {
			$condition["status"] = 1;
		}
		$request = pdo_getall($xcmodule . "_share_order", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "share_count":
		$where = " uniacid=:uniacid AND openid=:openid AND status=1 ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":openid"] = $_W["openid"];
		if ($_GPC["curr"] == 1) {
			$plan_date = date("Y-m", strtotime($_GPC["plan_date"]));
			$start = $plan_date . "-01 00:00:00";
			$end = date("Y-m-d", strtotime($start . " +1 months ")) . " 00:00:00";
			$where .= " AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start);
			$condition[":end_time"] = strtotime($end);
		} elseif ($_GPC["curr"] == 2) {
			$plan_date = date("Y-m-d", strtotime($_GPC["plan_date"]));
			$start = $plan_date . " 00:00:00";
			$end = $plan_date . " 23:59:59";
			$where .= " AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<=:end_time ";
			$condition[":start_time"] = strtotime($start);
			$condition[":end_time"] = strtotime($end);
		}
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT * FROM " . tablename($xcmodule . "_share_order") . " WHERE " . $where . " ORDER BY id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, $condition);
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "line_class":
		$request = pdo_getall($xcmodule . "_service_class", array("uniacid" => $_W["uniacid"], "status" => 1, "type" => 5), array(), '', "sort DESC,id DESC");
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "line":
		$where = " uniacid=:uniacid AND status=1 AND unix_timestamp(start_time)<:times AND unix_timestamp(end_time)>:times ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":times"] = time();
		if (!empty($_GPC["cid"])) {
			$where .= " AND cid=:cid ";
			$condition[":cid"] = $_GPC["cid"];
		}
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT * FROM " . tablename($xcmodule . "_line") . " WHERE " . $where . " ORDER BY sort DESC,id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, $condition);
		if ($request) {
			foreach ($request as &$x) {
				$x["end"] = date("Y-m-d", strtotime($x["end_time"]));
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "line_detail":
		pdo_update($xcmodule . "_line", array("click +=" => 1), array("uniacid" => $_W["uniacid"], "status" => 1));
		$where = " uniacid=:uniacid AND id=:id AND status=1 AND unix_timestamp(start_time)<:times AND unix_timestamp(end_time)>:times ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":id"] = $_GPC["id"];
		$condition[":times"] = time();
		$sql = "SELECT * FROM " . tablename($xcmodule . "_line") . " WHERE " . $where;
		$request = pdo_fetch($sql, $condition);
		if ($request) {
			$request["simg"] = tomedia($request["simg"]);
			$zan = pdo_get($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "cid" => $_GPC["id"], "status" => 1, "type" => 2));
			if ($zan) {
				$request["zan_user"] = 1;
			} else {
				$request["zan_user"] = -1;
			}
			$request["zan"] = 0;
			$zan = pdo_count($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "cid" => $_GPC["id"], "status" => 1, "type" => 2));
			if ($zan) {
				$request["zan"] = $zan;
			}
			$request["discuss"] = 0;
			$discuss = pdo_count($xcmodule . "_discuss", array("uniacid" => $_W["uniacid"], "pid" => $_GPC["id"], "status" => 1, "type" => 4));
			if ($discuss) {
				$request["discuss"] = $discuss;
			}
			$request["order"] = -1;
			$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "pid" => $_GPC["id"], "order_type" => 6, "status" => 1));
			if ($order) {
				$request["order"] = 1;
			}
			$request["discuss_on"] = -1;
			$discuss_on = pdo_get($xcmodule . "_discuss", array("uniacid" => $_W["uniacid"], "pid" => $_GPC["id"], "type" => 4, "openid" => $_W["openid"]));
			if ($discuss_on) {
				$request["discuss_on"] = 1;
			}
			if (!empty($request["video"])) {
				$request["video"] = json_decode($request["video"], true);
				$video_ids = array();
				foreach ($request["video"] as $v) {
					$video_ids[] = $v["id"];
				}
				$video = pdo_getall($xcmodule . "_video", array("uniacid" => $_W["uniacid"], "status" => 1, "id IN" => $video_ids), array(), '', "sort DESC,id DESC");
				if ($video) {
					foreach ($video as &$vv) {
						$vv["bimg"] = tomedia($vv["bimg"]);
					}
					$request["video_data"] = $video;
				}
			}
			if (!empty($request["audio"])) {
				$request["audio"] = json_decode($request["audio"], true);
				$audio_ids = array();
				foreach ($request["audio"] as $a) {
					$audio_ids[] = $a["id"];
				}
				$audio = pdo_getall($xcmodule . "_audio", array("uniacid" => $_W["uniacid"], "status" => 1, "id IN" => $audio_ids), array(), '', "sort DESC,id DESC");
				if ($audio) {
					foreach ($audio as &$aa) {
						$aa["simg"] = tomedia($aa["simg"]);
					}
					$request["audio_data"] = $audio;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "礼包已下架");
		}
		break;
	case "line_zan":
		$request = pdo_insert($xcmodule . "_zan", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "cid" => $_GPC["id"], "status" => 1, "type" => 2, "createtime" => date("Y-m-d H:i:s")));
		if ($request) {
			return $this->result(0, "操作成功", array("status" => 1));
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "score_index":
		$request = array();
		$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($user) {
			$request["user"] = $user;
		}
		$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "score"));
		if ($config) {
			$request["config"] = json_decode($config["content"], true);
			if ($request["config"]["check_status"] == 1 && !empty($request["config"]["check_list"])) {
				$check_list = $request["config"]["check_list"];
				$check_list = my_array_multisort($check_list, "day");
				$check_status = -1;
				$check_time = 1;
				$check_user = pdo_get($xcmodule . "_score_check", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if ($check_user) {
					if ($check_user["plan_date"] == date("Y-m-d")) {
						$check_status = 1;
						$check_time = $check_user["times"];
					} elseif ($check_user["plan_date"] == date("Y-m-d", strtotime("-1 days"))) {
						$check_time = $check_user["times"] + 1;
					}
				}
				$check_data = array(array("name" => "昨天", "plan_date" => date("Y-m-d", strtotime("-1 days")), "score" => 0), array("name" => "今天", "plan_date" => date("Y-m-d"), "score" => 0, "times" => $check_time), array("name" => date("m-d", strtotime("+1 days")), "plan_date" => date("Y-m-d", strtotime("+1 days")), "score" => 0, "times" => $check_time + 1), array("name" => date("m-d", strtotime("+2 days")), "plan_date" => date("Y-m-d", strtotime("+2 days")), "score" => 0, "times" => $check_time + 2), array("name" => date("m-d", strtotime("+3 days")), "plan_date" => date("Y-m-d", strtotime("+3 days")), "score" => 0, "times" => $check_time + 3), array("name" => date("m-d", strtotime("+4 days")), "plan_date" => date("Y-m-d", strtotime("+4 days")), "score" => 0, "times" => $check_time + 4), array("name" => date("m-d", strtotime("+5 days")), "plan_date" => date("Y-m-d", strtotime("+5 days")), "score" => 0, "times" => $check_time + 5), array("name" => date("m-d", strtotime("+6 days")), "plan_date" => date("Y-m-d", strtotime("+6 days")), "score" => 0, "times" => $check_time + 6));
				foreach ($check_data as &$cd) {
					if (!empty($cd["times"])) {
						foreach ($check_list as $cl) {
							if (!empty($cl["day"]) && !empty($cl["score"])) {
								if ($cd["times"] >= $cl["day"]) {
									$cd["score"] = $cl["score"];
								}
							}
						}
					}
				}
				$prev = pdo_get($xcmodule . "_score_record", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "type" => 1, "pid" => $check_data[0]["plan_date"]));
				if ($prev) {
					$check_data[0]["score"] = $prev["score"];
				}
				$request["check_data"] = array("check_status" => $check_status, "check_time" => $check_time, "check_data" => $check_data);
			}
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "score_mall":
		$request = pdo_getall($xcmodule . "_score_mall", array("uniacid" => $_W["uniacid"], "status" => 1, "kucun >" => 0), array(), '', "sort DESC,id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["pro"] = round(intval($x["sold"]) / (intval($x["sold"]) + intval($x["kucun"])) * 100, 2);
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "score_detail":
		$request = pdo_get($xcmodule . "_score_mall", array("uniacid" => $_W["uniacid"], "status" => 1, "kucun >" => 0, "id" => $_GPC["id"]));
		if ($request) {
			if (!empty($request["bimg"])) {
				$request["bimg"] = explode(",", $request["bimg"]);
				foreach ($request["bimg"] as &$b) {
					$b = tomedia($b);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "商品已下架");
		}
		break;
	case "score_check":
		$score = 0;
		$order = pdo_get($xcmodule . "_score_check", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($order && $order["plan_date"] == date("Y-m-d")) {
			return $this->result(1, "已签到");
		}
		$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "score"));
		$check_time = 1;
		if ($config) {
			$config = json_decode($config["content"], true);
			if ($config["check_status"] == 1 && !empty($config["check_list"])) {
				$check_list = $config["check_list"];
				$check_list = my_array_multisort($check_list, "day");
				$check_status = -1;
				$check_user = pdo_get($xcmodule . "_score_check", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if ($check_user) {
					if ($check_user["plan_date"] == date("Y-m-d")) {
						$check_status = 1;
						$check_time = $check_user["times"];
					} elseif ($check_user["plan_date"] == date("Y-m-d", strtotime("-1 days"))) {
						$check_time = $check_user["times"] + 1;
					}
				}
				foreach ($check_list as $cl) {
					if (!empty($cl["day"]) && !empty($cl["score"])) {
						if ($check_time >= $cl["day"]) {
							$score = $cl["score"];
						}
					}
				}
			}
		}
		$request = array();
		if ($order) {
			$request = pdo_update($xcmodule . "_score_check", array("plan_date" => date("Y-m-d"), "times" => $check_time), array("id" => $order["id"]));
		} else {
			$request = pdo_insert($xcmodule . "_score_check", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "plan_date" => date("Y-m-d"), "times" => 1, "createtime" => date("Y-m-d H:i:s")));
		}
		if ($request) {
			if (!empty($score)) {
				pdo_insert($xcmodule . "_score_record", array("uniacid" => $_W["uniacid"], "name" => "签到", "openid" => $_W["openid"], "pid" => date("Y-m-d"), "type" => 1, "score" => $score, "createtime" => date("Y-m-d H:i:s")));
				pdo_update($xcmodule . "_userinfo", array("score +=" => $score), array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
			}
			return $this->result(0, "操作成功", array("score" => $score));
		} else {
			return $this->result(1, "签到失败");
		}
		break;
	case "score_record":
		$request = pdo_getall($xcmodule . "_score_record", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "score_pay":
		$request = array();
		$mall = pdo_get($xcmodule . "_score_mall", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
		if ($mall) {
			$request["service"] = $mall;
		}
		$map = pdo_get($xcmodule . "_address", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "status" => 1));
		if ($map) {
			$request["map"] = $map;
		}
		$store = array();
		if (!empty($_GPC["longitude"]) && !empty($_GPC["latitude"])) {
			$sql = "SELECT *,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((:xlatitude*PI()/180-latitude*PI()/180)/2),2)+COS(:xlatitude*PI()/180)*COS(latitude*PI()/180)*POW(SIN((:xlongitude*PI()/180-longitude*PI()/180)/2),2))),2) AS juli FROM " . tablename("xc_train_school") . " WHERE status=1 AND uniacid=:uniacid ORDER BY juli,sort DESC,createtime DESC";
			$store = pdo_fetchall($sql, array(":xlatitude" => $_GPC["latitude"], ":xlongitude" => $_GPC["longitude"], ":uniacid" => $uniacid));
		} else {
			$store = pdo_getall("xc_train_school", array("uniacid" => $uniacid, "status" => 1), array(), '', "sort DESC,id DESC");
		}
		if ($store) {
			foreach ($store as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			$request["store"] = $store;
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "操作失败");
		}
		break;
	case "score_order":
		$condition["uniacid"] = $_W["uniacid"];
		$condition["order_type"] = 8;
		$condition["status"] = 1;
		$condition["openid"] = $_W["openid"];
		if ($_GPC["curr"] == 1) {
			$condition["order_status"] = -1;
		} elseif ($_GPC["curr"] == 2) {
			$condition["order_status"] = 1;
		}
		$request = pdo_getall($xcmodule . "_order", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["service_data"] = json_decode($x["service_data"], true);
				$x["service_data"]["simg"] = tomedia($x["service_data"]["simg"]);
				if (!empty($x["check_code"])) {
					$x["check_code"] = tomedia($x["check_code"]);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "score_order_detail":
		$request = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "order_type" => 8, "status" => 1, "id" => $_GPC["id"]));
		if ($request) {
			$request["service_data"] = json_decode($request["service_data"], true);
			$request["service_data"]["simg"] = tomedia($request["service_data"]["simg"]);
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "move":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT * FROM " . tablename($xcmodule . "_move") . " WHERE uniacid=:uniacid AND status=1 AND unix_timestamp(start_time)<:times AND unix_timestamp(end_time)>:times ORDER BY sort DESC,id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":times" => time()));
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
				$x["start_time"] = date("Y-m-d", strtotime($x["start_time"]));
				$x["end_time"] = date("Y-m-d", strtotime($x["end_time"]));
				$x["end"] = -1;
				if ($x["member_on"] >= $x["member"]) {
					$x["end"] = 1;
				}
				$x["price"] = 0;
				$x["pro"] = round(intval($x["member_on"]) / intval($x["member"]) * 100, 2);
				if (!empty($x["format"])) {
					$x["format"] = json_decode($x["format"], true);
					if (!empty($x["format"][0]["price"])) {
						$x["price"] = $x["format"][0]["price"];
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "move_detail":
		$request = pdo_get($xcmodule . "_move", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"]));
		if ($request) {
			$request["end"] = -1;
			if ($request["member_on"] >= $request["member"]) {
				$request["end"] = 1;
			}
			if (strtotime($request["start_time"]) > time() || strtotime($request["end_time"]) < time()) {
				$request["end"] = 1;
			} else {
				$request["fail"] = strtotime($request["end_time"]) - time();
			}
			if (!empty($request["bimg"])) {
				$request["bimg"] = explode(",", $request["bimg"]);
				foreach ($request["bimg"] as &$b) {
					$b = tomedia($b);
				}
			}
			$request["price"] = 0;
			if (!empty($request["format"])) {
				$request["format"] = json_decode($request["format"], true);
				if (!empty($request["format"][0]["price"])) {
					$request["price"] = $request["format"][0]["price"];
				}
				foreach ($request["format"] as &$rf) {
					$rf["choose"] = false;
				}
			}
			if (!empty($request["store"])) {
				$store = pdo_get($xcmodule . "_school", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $request["store"]));
				if ($store) {
					$request["store_data"] = $store;
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "活动已下架");
		}
		break;
	case "move_detail_order":
		$request = pdo_getall($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status" => 1, "order_type" => 9), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			$ids = array();
			foreach ($request as $xx) {
				$ids[] = $xx["openid"];
			}
			$user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $ids));
			$user_data = array();
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$user_data[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$x) {
				$x["nick"] = $user_data[$x["openid"]]["nick"];
				$x["avatar"] = $user_data[$x["openid"]]["avatar"];
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "move_pay":
		$request = array();
		$service = pdo_get($xcmodule . "_move", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"]));
		if ($service) {
			$data = $_GPC["data"];
			$data = explode(",", $data);
			$list = array();
			if (!empty($service["format"])) {
				$service["format"] = json_decode($service["format"], true);
				foreach ($service["format"] as $f) {
					foreach ($data as $d) {
						if ($d == $f["name"]) {
							$list[] = array("name" => $f["name"], "price" => $f["price"], "member" => 1);
						}
					}
				}
			}
			$request["service"] = $service;
			$request["list"] = $list;
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "活动已下架");
		}
		break;
	case "move_order":
		$condition["uniacid"] = $_W["uniacid"];
		$condition["openid"] = $_W["openid"];
		$condition["order_type"] = 9;
		if ($_GPC["curr"] == 1) {
			$condition["status"] = 1;
			$condition["order_status"] = -1;
		} elseif ($_GPC["curr"] == 2) {
			$condition["status"] = 1;
			$condition["order_status"] = 1;
		} elseif ($_GPC["curr"] == 3) {
			$condition["status"] = 2;
		} else {
			$condition["status IN"] = array(1, 2);
		}
		$request = pdo_getall($xcmodule . "_order", $condition, array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			foreach ($request as &$x) {
				if (!empty($x["service_data"])) {
					$x["service_data"] = json_decode($x["service_data"], true);
					$x["service_data"]["simg"] = tomedia($x["service_data"]["simg"]);
					$x["service_data"]["start_time"] = date("Y-m-d H:i", strtotime($x["service_data"]["start_time"]));
				}
				if (!empty($x["line_data"])) {
					$x["line_data"] = json_decode($x["line_data"], true);
					foreach ($x["line_data"] as &$xs) {
						$xs["amount"] = round(floatval($xs["price"]) * intval($xs["member"]), 2);
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "move_order_detail":
		$request = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "order_type" => 9, "id" => $_GPC["id"]));
		if ($request) {
			if (!empty($request["service_data"])) {
				$request["service_data"] = json_decode($request["service_data"], true);
			}
			if (!empty($request["store_data"])) {
				$request["store_data"] = json_decode($request["store_data"], true);
			}
			if (!empty($request["line_data"])) {
				$request["line_data"] = json_decode($request["line_data"], true);
			}
			if (!empty($request["check_code"])) {
				$request["check_code"] = tomedia($request["check_code"]);
			} else {
				$filename = "group_" . $request["id"] . ".jpg";
				$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$fileurl = $fileurl . "/" . date("m") . "/";
				if (!is_dir($fileurl)) {
					mkdir($fileurl, 0700, true);
				}
				$account_api = WeAccount::create();
				$token = $account_api->getAccessToken();
				$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
				$wx_data = array("path" => "xc_train/pages/base/base", "scene" => "admin_" . $request["id"]);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wx_data));
				$output = curl_exec($ch);
				curl_close($ch);
				$result = json_decode($output, true);
				if (is_array($result) && !empty($result)) {
					return $this->result(1, "图片生成失败" . $result["errcode"] . ":" . $result["errmsg"], $request);
				} else {
					header("Content-Type: text/plain; charset=utf-8");
					header("Content-type:image/jpeg");
					$jpg = $output;
					$file = fopen($fileurl . $filename, "w");
					$reee = fwrite($file, $jpg);
					fclose($file);
					$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
					pdo_update($xcmodule . "_order", array("check_code" => $code), array("id" => $request["id"], "uniacid" => $uniacid));
					$request["check_code"] = tomedia($code);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "move_order_code":
		$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "move_poster"));
		if ($config) {
			$config = json_decode($config["content"], true);
		}
		if (!empty($config) && $config["status"] == 1) {
			$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
			if ($order) {
				$order["service_data"] = json_decode($order["service_data"], true);
				$order["store_data"] = json_decode($order["store_data"], true);
				$post_data = array("name" => $order["name"], "mobile" => $order["mobile"], "amount" => $order["amount"], "service_name" => $order["service_name"], "store_name" => $order["store_name"], "address" => $order["store_data"]["address"], "start_time" => $order["service_data"]["start_time"], "end_time" => $order["service_data"]["end_time"], "code" => '');
				if (!empty($order["check_order"])) {
					$postdata["code"] = tomedia($order["check_order"]);
				} else {
					$filename = "group_" . $order["id"] . ".jpg";
					$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$fileurl = $fileurl . "/" . date("m") . "/";
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$account_api = WeAccount::create();
					$token = $account_api->getAccessToken();
					$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
					$wx_data = array("path" => "xc_train/pages/base/base", "scene" => "admin_" . $order["id"]);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wx_data));
					$output = curl_exec($ch);
					curl_close($ch);
					$request = json_decode($output, true);
					if (is_array($request) && !empty($request)) {
						return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
					} else {
						header("Content-Type: text/plain; charset=utf-8");
						header("Content-type:image/jpeg");
						$jpg = $output;
						$file = fopen($fileurl . $filename, "w");
						$reee = fwrite($file, $jpg);
						fclose($file);
						$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
						pdo_update($xcmodule . "_order", array("check_code" => $code), array("id" => $order["id"], "uniacid" => $uniacid));
						$post_data["code"] = tomedia($code);
					}
				}
				$code = movecode($config, $post_data, $_W);
				return $this->result(0, "操作成功", array("code" => $code));
			} else {
				return $this->result(1, "订单不存在");
			}
		} else {
			return $this->result(1, "未配置");
		}
		break;
	case "mall_team":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT a.*,b.price,b.format FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND b.status=1 ORDER BY sort DESC,id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":times" => time()));
		if ($request) {
			foreach ($request as &$x) {
				$x["simg"] = tomedia($x["simg"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "mall_team_detail":
		$sql = "SELECT a.*,b.price,b.format FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND a.id=:id AND b.status=1 ORDER BY sort DESC,id DESC LIMIT 1 ";
		$request = pdo_fetch($sql, array(":uniacid" => $_W["uniacid"], ":times" => time(), ":id" => $_GPC["id"]));
		if ($request) {
			if (!empty($request["bimg"])) {
				$request["bimg"] = explode(",", $request["bimg"]);
				foreach ($request["bimg"] as &$b) {
					$b = tomedia($b);
				}
			}
			$request["fail"] = strtotime($request["end_time"]) - time();
			if (!empty($request["format"])) {
				$request["format"] = json_decode($request["format"], true);
			}
			$request["group"] = -1;
			$group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "service" => $request["id"]));
			if ($group) {
				$request["group"] = 1;
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "活动已结束");
		}
		break;
	case "mall_team_code":
		$service = pdo_get($xcmodule . "_mall_team", array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"], "status" => 1));
		if ($service) {
			$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "team_poster"));
			if ($config) {
				$config = json_decode($config["content"], true);
			}
			if (!empty($config) && $config["content"]) {
				$post_data = array("name" => $service["name"], "bimg" => tomedia($service["simg"]), "start_time" => $order["start_time"], "end_time" => $order["end_time"], "code" => '');
				if (!empty($service["code"])) {
					$post_data["code"] = tomedia($service["code"]);
				} else {
					$filename = "team_" . $service["id"] . ".jpg";
					$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$fileurl = $fileurl . "/" . date("m") . "/";
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$account_api = WeAccount::create();
					$token = $account_api->getAccessToken();
					$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
					$post_data = array("path" => "xc_train/pages/base/base", "scene" => "team_" . $service["id"]);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
					$output = curl_exec($ch);
					curl_close($ch);
					$request = json_decode($output, true);
					if (is_array($request) && !empty($request)) {
						return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
					} else {
						header("Content-Type: text/plain; charset=utf-8");
						header("Content-type:image/jpeg");
						$jpg = $output;
						$file = fopen($fileurl . $filename, "w");
						$reee = fwrite($file, $jpg);
						fclose($file);
						$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
						pdo_update($xcmodule . "_mall_team", array("code" => $code), array("id" => $service["id"], "uniacid" => $uniacid));
						$post_data["code"] = tomedia($code);
					}
				}
				$code = movecode($config, $post_data, $_W);
				return $this->result(0, "操作成功", array("code" => $code));
			} else {
				return $this->result(1, "海报未开启");
			}
		} else {
			return $this->result(1, "活动已结束");
		}
		break;
	case "mall_team_pay":
		$request = array("fee" => 0);
		$team = pdo_get($xcmodule . "_mall_team", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"]));
		if ($team) {
			$request["team"] = $team;
			$service = pdo_get($xcmodule . "_mall", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $team["service"]));
			if ($service) {
				if (!empty($service["format"])) {
					$service["format"] = json_decode($service["format"], true);
				}
				$amount = 0;
				if ($_GPC["format"] == -1) {
					$amount = floatval($service["price"]) * intval($_GPC["member"]);
				} else {
					$amount = floatval($service["format"][$_GPC["format"]]["price"]) * intval($_GPC["member"]);
				}
				$config = pdo_get("xc_train_config", array("xkey" => "web", "uniacid" => $uniacid));
				if ($config) {
					$config["content"] = json_decode($config["content"], true);
					if (!empty($config["content"]["fee"])) {
						$request["fee"] = $config["content"]["fee"];
					}
				}
				$request["amount"] = $amount;
				$request["service"] = $service;
				$store = array();
				if (!empty($_GPC["longitude"]) && !empty($_GPC["latitude"])) {
					$sql = "SELECT *,ROUND(6378.138*2*ASIN(SQRT(POW(SIN((:xlatitude*PI()/180-latitude*PI()/180)/2),2)+COS(:xlatitude*PI()/180)*COS(latitude*PI()/180)*POW(SIN((:xlongitude*PI()/180-longitude*PI()/180)/2),2))),2) AS juli FROM " . tablename("xc_train_school") . " WHERE status=1 AND uniacid=:uniacid ORDER BY juli,sort DESC,createtime DESC";
					$store = pdo_fetchall($sql, array(":xlatitude" => $_GPC["latitude"], ":xlongitude" => $_GPC["longitude"], ":uniacid" => $uniacid));
				} else {
					$store = pdo_getall("xc_train_school", array("uniacid" => $uniacid, "status" => 1), array(), '', "sort DESC,id DESC");
				}
				if ($store) {
					foreach ($store as &$x) {
						$x["simg"] = tomedia($x["simg"]);
					}
				}
				$request["store"] = $store;
				if (!empty($_GPC["group_id"])) {
					$group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "id" => $_GPC["group_id"]));
					if ($group) {
						$group_user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
						if ($group_user) {
							$group["nick"] = base64_decode($group_user["nick"]);
							$group["avatar"] = $group_user["avatar"];
						}
						$request["group"] = $group;
						if (!empty($team["member_join"]) && !empty($team["member_sale"])) {
							if ($group["member"] % $team["member_join"] == 0) {
								$request["group_sale"] = $team["member_sale"];
							}
						}
					}
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(1, "活动错误");
			}
		} else {
			return $this->result(1, "活动已结束");
		}
		break;
	case "mall_team_order":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":openid"] = $_W["openid"];
		$where = '';
		if ($_GPC["curr"] == 1) {
			$where .= " AND a.order_status=-1 AND unix_timestamp(a.end_time)>:times ";
			$condition[":times"] = time();
		} elseif ($_GPC["curr"] == 2) {
			$where .= " AND a.order_status=-1 AND unix_timestamp(a.end_time)<:times ";
			$condition[":times"] = time();
		} elseif ($_GPC["curr"] == 3) {
			$where .= " AND a.order_status=1 ";
		}
		$sql = "SELECT a.*,b.openid as group_openid FROM " . tablename($xcmodule . "_order") . " a left join " . tablename($xcmodule . "_team_group") . " b on a.group_id=b.id WHERE a.uniacid=:uniacid AND a.openid=:openid AND a.order_type=10 AND a.status=1 {$where} ORDER BY a.id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, $condition);
		if ($request) {
			$ids = array();
			foreach ($request as $xx) {
				$ids[] = $xx["group_openid"];
			}
			$user = pdo_getall($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid IN" => $ids));
			$user_data = array();
			if ($user) {
				foreach ($user as $u) {
					$u["nick"] = base64_decode($u["nick"]);
					$user_data[$u["openid"]] = $u;
				}
			}
			foreach ($request as &$x) {
				if (!empty($x["group_openid"]) && !empty($user_data[$x["group_openid"]])) {
					$x["group_data"] = $user_data[$x["group_openid"]];
				}
				if (!empty($x["service_data"])) {
					$x["service_data"] = json_decode($x["service_data"], true);
					$x["service_data"]["simg"] = tomedia($x["service_data"]["simg"]);
				}
				if (strtotime($x["end_time"]) > time()) {
					$x["curr"] = 1;
				} else {
					if ($x["order_status"] == 1) {
						$x["curr"] = 3;
					} else {
						$x["curr"] = 2;
					}
				}
				if (!empty($x["check_code"])) {
					$x["check_code"] = tomedia($x["check_code"]);
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "mall_team_order_detail":
		$request = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"], "order_type" => 10));
		if ($request) {
			if (strtotime($request["end_time"]) > time()) {
				$request["curr"] = 1;
			} else {
				if ($request["order_status"] == 1) {
					$request["curr"] = 3;
				} else {
					$request["curr"] = 2;
				}
			}
			if (!empty($request["service_data"])) {
				$request["service_data"] = json_decode($request["service_data"], true);
				$request["service_data"]["simg"] = tomedia($request["service_data"]["simg"]);
			}
			$group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "id" => $request["group_id"]));
			if ($group) {
				$request["group"] = $group;
			}
			$sql = "SELECT a.*,b.avatar FROM " . tablename($xcmodule . "_order") . " a left join " . tablename($xcmodule . "_userinfo") . " b on a.openid=b.openid WHERE a.uniacid=:uniacid AND a.status=1 AND a.order_type=10 AND a.group_id=:group_id ";
			$group_order = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":group_id" => $request["group_id"]));
			if ($group_order) {
				$request["group_order"] = $group_order;
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "mall_team_group":
		$request = array();
		$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"], "order_type" => 10));
		if ($order) {
			$request["order"] = $order;
			$group = pdo_get($xcmodule . "_team_group", array("uniacid" => $_W["uniacid"], "id" => $order["group_id"]));
			if ($group) {
				$sql = "SELECT a.*,b.price,b.format FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND a.id=:id AND b.status=1 ORDER BY sort DESC,id DESC LIMIT 1 ";
				$service = pdo_fetch($sql, array(":uniacid" => $_W["uniacid"], ":times" => time(), ":id" => $group["service"]));
				if ($service) {
					$service["simg"] = tomedia($service["simg"]);
					if (!empty($service["format"])) {
						$service["format"] = json_decode($service["format"], true);
					}
				} else {
					return $this->result(1, "活动不存在", array("redirect" => "../../index/index"));
				}
				$group["curr"] = 1;
				$service["fail"] = strtotime($service["end_time"]) - time();
				$user_order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "status" => 1, "order_type" => 10, "group_id" => $group["id"], "openid" => $_W["openid"]));
				if ($user_order) {
					$group["curr"] = 2;
				} else {
					if (time() <= strtotime($service["start_time"]) || time() >= strtotime($service["end_time"]) || $service["kucun"] <= 0) {
						$group["curr"] = 3;
					}
				}
				$request["group"] = $group;
				$request["service"] = $service;
				$sql = "SELECT a.*,b.avatar FROM " . tablename($xcmodule . "_order") . " a left join " . tablename($xcmodule . "_userinfo") . " b on a.openid=b.openid WHERE a.uniacid=:uniacid AND a.status=1 AND a.order_type=10 AND a.group_id=:group_id ";
				$group_order = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":group_id" => $group["id"]));
				if ($group_order) {
					$request["group_order"] = $group_order;
				}
				$sql = "SELECT a.*,b.price,b.format FROM " . tablename($xcmodule . "_mall_team") . " a left join " . tablename($xcmodule . "_mall") . " b on a.uniacid=b.uniacid AND a.service=b.id WHERE a.uniacid=:uniacid AND a.status=1 AND a.kucun>0 AND unix_timestamp(a.start_time)<:times AND unix_timestamp(a.end_time)>:times AND b.status=1 ORDER BY sort DESC,id DESC LIMIT 10 ";
				$team = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":times" => time()));
				if ($team) {
					foreach ($team as &$t) {
						$t["simg"] = tomedia($t["simg"]);
					}
					$request["team"] = $team;
				}
				return $this->result(0, "操作成功", $request);
			} else {
				return $this->result(1, "团不存在", array("redirect" => "../../index/index"));
			}
		} else {
			return $this->result(1, "订单不存在", array("redirect" => "../../index/index"));
		}
		break;
	case "mall_team_detail_group":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT a.*,b.nick,b.avatar FROM " . tablename($xcmodule . "_team_group") . " a left join " . tablename($xcmodule . "_userinfo") . " b on a.openid=b.openid WHERE a.uniacid=:uniacid AND a.service=:service ORDER BY a.id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":service" => $_GPC["id"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["nick"] = base64_decode($x["nick"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "mall_team_detail_group2":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$sql = "SELECT a.*,b.nick,b.avatar FROM " . tablename($xcmodule . "_order") . " a left join " . tablename($xcmodule . "_userinfo") . " b on a.openid=b.openid WHERE a.uniacid=:uniacid AND a.group_id=:group_id AND a.status=1 AND a.order_type=10 ORDER BY a.id asc LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":group_id" => $_GPC["group"]));
		if ($request) {
			foreach ($request as &$x) {
				$x["nick"] = base64_decode($x["nick"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_index":
		$request = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($request) {
			$request["nick"] = base64_decode($request["nick"]);
			$sql = "SELECT count(*) as new_total,sum(group_fee) as new_amount FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid ";
			$order = pdo_fetch($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
			if ($order) {
				$request["new_total"] = intval($order["new_total"]);
				$request["new_fee"] = round(floatval($order["new_amount"]), 2);
			} else {
				$request["new_total"] = 0;
				$request["new_fee"] = 0;
			}
			$request["new_user"] = 0;
			$sql = "SELECT count(DISTINCT openid) FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid ";
			$user = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
			if ($user) {
				$request["new_user"] = intval($user);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_record":
		$request = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($request) {
			$request["plan_date"] = date("Y-m-d");
			$request["new_fee"] = 0;
			$request["new_amount"] = 0;
			$sql = "SELECT sum(group_fee) FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid AND order_status=-1 AND unix_timestamp(createtime)>=:start_time AND unix_timestamp(createtime)<=:end_time ";
			$amount = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"], ":start_time" => strtotime(date("Y-m-d") . " 00:00:00"), ":end_time" => strtotime(date("Y-m-d") . " 23:59:59")));
			if ($amount) {
				$request["new_fee"] = round(floatval($amount), 2);
			}
			$sql = "SELECT sum(group_fee) FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid AND order_status=1 ";
			$amount = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
			if ($amount) {
				$request["new_amount"] = round(floatval($amount), 2);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_order":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$where = " a.uniacid=:uniacid AND a.status=1 AND a.order_type=10 AND a.group_openid=:openid ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":openid"] = $_W["openid"];
		if ($_GPC["curr"] == 1) {
			$start_time = date("Y-m-d H:i:s", strtotime("-7 days"));
			$end_time = date("Y-m-d H:i:s");
			$where .= " AND unix_timestamp(a.createtime)>:start_time AND unix_timestamp(a.createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		} elseif ($_GPC["curr"] == 2) {
			$start_time = date("Y-m-d H:i:s", strtotime("-30 days"));
			$end_time = date("Y-m-d H:i:s");
			$where .= " AND unix_timestamp(a.createtime)>:start_time AND unix_timestamp(a.createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		} elseif ($_GPC["curr"] == 3) {
			$start_time = $_GPC["start_time"] . " 00:00:00";
			$end_time = $_GPC["end_time"] . " 23:59:59";
			$where .= " AND unix_timestamp(a.createtime)>:start_time AND unix_timestamp(a.createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		}
		$sql = "SELECT a.*,b.nick,b.avatar FROM " . tablename($xcmodule . "_order") . " a left join " . tablename($xcmodule . "_userinfo") . " b on a.openid=b.openid WHERE {$where} ORDER BY a.id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, $condition);
		if ($request) {
			foreach ($request as &$x) {
				$x["nick"] = base64_decode($x["nick"]);
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_count":
		$where = " uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:group_openid ";
		$condition[":uniacid"] = $_W["uniacid"];
		$condition[":group_openid"] = $_W["openid"];
		$start_time = '';
		$end_time = '';
		if ($_GPC["curr"] == 0) {
			$start_time = date("Y-m-d") . " 00:00:00";
			$end_time = date("Y-m-d") . " 23:59:59";
		} elseif ($_GPC["curr"] == 1) {
			$start_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y")));
			$end_time = date("Y-m-d") . " 23:59:59";
		} elseif ($_GPC["curr"] == 2) {
			$start_time = date("Y-m") . "-01 00:00:00";
			$end_time = date("Y-m-d") . " 23:59:59";
		} elseif ($_GPC["curr"] == 3) {
		}
		$where .= " AND unix_timestamp(createtime)>:start_time AND unix_timestamp(createtime)<:end_time ";
		$condition[":start_time"] = strtotime($start_time);
		$condition[":end_time"] = strtotime($end_time);
		$sql = "SELECT count(*) as total,sum(o_amount) as amount,sum(group_fee) as fee FROM " . tablename($xcmodule . "_order") . " WHERE {$where}";
		$request = pdo_fetch($sql, $condition);
		if ($request) {
			if (empty($request["amount"])) {
				$request["amount"] = 0;
			}
			if (empty($request["fee"])) {
				$request["fee"] = 0;
			}
		} else {
			$request = array("total" => 0, "amount" => 0, "fee" => 0);
		}
		return $this->result(0, "操作成功", $request);
		break;
	case "team_count_table":
		$request = array("plan_date" => date("Y-m-d"));
		$table = array();
		for ($i = 6; $i >= 0; $i--) {
			$table[] = array("plan_date" => date("Y-m-d", strtotime("-{$i} days")), "fee" => 0);
		}
		$sql = "SELECT sum(group_fee) as fee,from_unixtime(unix_timestamp(createtime),'%Y-%m-%d') as plan_date FROM " . tablename($xcmodule . "_order") . " WHERE uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid GROUP BY plan_date ";
		$order = pdo_fetchall($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
		$order_data = array();
		if ($order) {
			foreach ($order as $o) {
				$order_data[$o["plan_date"]] = $o;
			}
		}
		$table_x = array();
		$table_y = array();
		foreach ($table as $t) {
			if (!empty($order_data[$t["plan_date"]])) {
				$t["fee"] = $order_data[$t["plan_date"]]["fee"];
			}
			$table_x[] = date("m-d", strtotime($t["plan_date"]));
			$table_y[] = $t["fee"];
		}
		$request["table_x"] = $table_x;
		$request["table_y"] = $table_y;
		return $this->result(0, "操作成功", $request);
		break;
	case "team_group_order":
		$pagesize = intval($_GPC["pagesize"]);
		$page = (intval($_GPC["page"]) - 1) * $pagesize;
		$where = " uniacid=:uniacid AND status=1 AND order_type=10 AND group_openid=:openid ";
		$condition = array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]);
		if ($_GPC["curr"] == 1) {
			$where .= " AND order_status=-1 AND unix_timestamp(end_time)>:times ";
			$condition[":times"] = time();
		} elseif ($_GPC["curr"] == 2) {
			$where .= " AND order_status=-1 AND unix_timestamp(end_time)<:times ";
			$condition[":times"] = time();
		} elseif ($_GPC["curr"] == 3) {
			$where .= " AND order_status=1 ";
		}
		if (!empty($_GPC["content"])) {
			$where .= " AND out_trade_no LIKE :out_trade_no ";
			$condition[":out_trade_no"] = "%" . $_GPC["content"] . "%";
		}
		if ($_GPC["curr2"] == 1) {
			$start_time = date("Y-m-d H:i:s", strtotime("-7 days"));
			$end_time = date("Y-m-d H:i:s");
			$where .= " AND unix_timestamp(createtime)>:start_time AND unix_timestamp(createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		} elseif ($_GPC["curr2"] == 2) {
			$start_time = date("Y-m-d H:i:s", strtotime("-30 days"));
			$end_time = date("Y-m-d H:i:s");
			$where .= " AND unix_timestamp(createtime)>:start_time AND unix_timestamp(createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		} elseif ($_GPC["curr2"] == 3) {
			$start_time = $_GPC["start_time"] . " 00:00:00";
			$end_time = $_GPC["end_time"] . " 23:59:59";
			$where .= " AND unix_timestamp(createtime)>:start_time AND unix_timestamp(createtime)<:end_time ";
			$condition[":start_time"] = strtotime($start_time);
			$condition[":end_time"] = strtotime($end_time);
		}
		$sql = "SELECT * FROM " . tablename($xcmodule . "_order") . " WHERE {$where} ORDER BY id DESC LIMIT {$page},{$pagesize} ";
		$request = pdo_fetchall($sql, $condition);
		if ($request) {
			foreach ($request as &$x) {
				if (!empty($x["service_data"])) {
					$x["service_data"] = json_decode($x["service_data"], true);
					$x["service_data"]["simg"] = tomedia($x["service_data"]["simg"]);
				}
				if (strtotime($x["end_time"]) > time()) {
					$x["curr"] = 1;
				} else {
					if ($x["order_status"] == 1) {
						$x["curr"] = 3;
					} else {
						$x["curr"] = 2;
					}
				}
			}
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_withdraw":
		$request = array("no" => 0, "on" => 0);
		$user = pdo_get($xcmodule . "_userinfo", array("openid" => $_W["openid"], "uniacid" => $_W["uniacid"]));
		if ($user) {
			$request["user"] = $user;
		}
		$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($config) {
			$request["config"] = json_decode($config["content"], true);
		}
		$sql = "SELECT sum(amount) FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND openid=:openid AND status=-1 AND order_type=2 ";
		$no = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
		if ($no) {
			$request["no"] = round(floatval($no), 2);
		}
		$sql = "SELECT sum(amount) FROM " . tablename($xcmodule . "_share_withdraw") . " WHERE uniacid=:uniacid AND openid=:openid AND status=1 AND order_type=2 ";
		$on = pdo_fetchcolumn($sql, array(":uniacid" => $_W["uniacid"], ":openid" => $_W["openid"]));
		if ($on) {
			$request["on"] = round(floatval($on), 2);
		}
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(0, "操作失败");
		}
		break;
	case "team_withdraw_fee":
		$share = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "share"));
		if ($share) {
			$share = json_decode($share["content"], true);
			if (!empty($share) && $share["status"] == 1 && $share["withdraw"] == 1) {
				$xc = $_GPC["xc"];
				$amount = $xc["amount"];
				if (!empty($share["withdraw_limit"]) && floatval($share["withdraw_limit"]) > floatval($xc["amount"])) {
					return $this->result(1, "金额错误");
				}
				$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
				if (floatval($amount) > floatval($user["team_fee"])) {
					return $this->result(1, "余额不足");
				}
				$fee = 0;
				if ($share["fee_type"] == 2 && !empty($share["fee_price"])) {
					$fee = $share["fee_price"];
				} else {
					if ($share["fee_type"] == 3 && !empty($share["fee_pro"])) {
						$fee = round(floatval($amount) * floatval($share["fee_pro"]) / 100, 2);
					}
				}
				$xc["amount"] = round(floatval($amount) - floatval($fee), 2);
				if (floatval($xc["amount"]) < 0) {
					return $this->result(1, "提现金额错误");
				}
				$xc["uniacid"] = $_W["uniacid"];
				$xc["openid"] = $_W["openid"];
				$xc["out_trade_no"] = setcode();
				$xc["fee"] = $fee;
				$xc["order_type"] = 2;
				$xc["status"] = -1;
				$xc["createtime"] = date("Y-m-d H:i:s");
				$request = pdo_insert($xcmodule . "_share_withdraw", $xc);
				if ($request) {
					pdo_update($xcmodule . "_userinfo", array("team_fee -=" => floatval($amount)), array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
					return $this->result(0, "操作成功", array("status" => 1));
				} else {
					return $this->result(1, "操作失败");
				}
			} else {
				return $this->result(1, "没有权限");
			}
		} else {
			return $this->result(1, "没有权限");
		}
		break;
	case "team_withdraw_order":
		$request = pdo_getall($xcmodule . "_share_withdraw", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "order_type" => 2), array(), '', "id DESC", array($_GPC["page"], $_GPC["pagesize"]));
		if ($request) {
			return $this->result(0, "操作成功", $request);
		} else {
			return $this->result(1, "没有权限");
		}
		break;
	case "group_code":
		$order = pdo_get($xcmodule . "_order", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"], "order_type" => 7, "status" => 1, "id" => $_GPC["id"]));
		if ($order) {
			$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "group_poster"));
			if ($config) {
				$config = json_decode($config["content"], true);
			}
			if (!empty($config) && $config["status"] == 1) {
				$service = pdo_get($xcmodule . "_service_group", array("uniacid" => $_W["uniacid"], "id" => $order["pid"]));
				$group = pdo_get($xcmodule . "_group_service", array("uniacid" => $_W["uniacid"], "id" => $order["group_id"]));
				$post_data = array("title" => $order["title"], "bimg" => tomedia($service["simg"]), "price" => $group["group_price"], "code" => '');
				if (!empty($order["code"])) {
					$post_data["code"] = tomedia($order["code"]);
				} else {
					$filename = "group_" . $order["id"] . ".jpg";
					$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$fileurl = $fileurl . "/" . date("m") . "/";
					if (!is_dir($fileurl)) {
						mkdir($fileurl, 0700, true);
					}
					$account_api = WeAccount::create();
					$token = $account_api->getAccessToken();
					$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
					$post_data = array("path" => "xc_train/pages/base/base", "scene" => "group_" . $order["group_id"]);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
					$output = curl_exec($ch);
					curl_close($ch);
					$request = json_decode($output, true);
					if (is_array($request) && !empty($request)) {
						return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
					} else {
						header("Content-Type: text/plain; charset=utf-8");
						header("Content-type:image/jpeg");
						$jpg = $output;
						$file = fopen($fileurl . $filename, "w");
						$reee = fwrite($file, $jpg);
						fclose($file);
						$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
						$dd = pdo_update($xcmodule . "_order", array("code" => $code), array("id" => $order["id"], "uniacid" => $uniacid));
						$post_data["code"] = tomedia($code);
					}
				}
				$code = movecode($config, $post_data, $_W);
				return $this->result(0, "操作成功", array("code" => $code));
			} else {
				return $this->result(1, "海报未开启");
			}
		} else {
			return $this->result(1, "订单不存在");
		}
		break;
	case "cut_code":
		$service = pdo_get($xcmodule . "_cut", array("uniacid" => $_W["uniacid"], "status" => 1, "id" => $_GPC["id"]));
		$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
		if ($service) {
			$order = pdo_get($xcmodule . "_cut_order", array("uniacid" => $_W["uniacid"], "cid" => $service["id"], "openid" => $_W["openid"]));
			if ($order) {
				$config = pdo_get($xcmodule . "_config", array("uniacid" => $_W["uniacid"], "xkey" => "cut_poster"));
				if ($config) {
					$config = json_decode($config["content"], true);
				}
				if (!empty($config) && $config["status"] == 1) {
					$class = pdo_get($xcmodule . "_service", array("uniacid" => $_W["uniacid"], "id" => $service["pid"]));
					$post_data = array("title" => $class["name"] . " " . $service["mark"], "bimg" => tomedia($class["bimg"]), "price" => $service["min_price"], "code" => '');
					if (!empty($order["code"])) {
						$post_data["code"] = tomedia($order["code"]);
					} else {
						$filename = "group_" . $order["id"] . ".jpg";
						$fileurl = IA_ROOT . "/attachment/images/" . $_W["uniacid"] . "/" . date("Y");
						if (!is_dir($fileurl)) {
							mkdir($fileurl, 0700, true);
						}
						$fileurl = $fileurl . "/" . date("m") . "/";
						if (!is_dir($fileurl)) {
							mkdir($fileurl, 0700, true);
						}
						$account_api = WeAccount::create();
						$token = $account_api->getAccessToken();
						$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
						$wx_data = array("path" => "xc_train/pages/base/base", "scene" => "cut_" . $service["id"] . "_user_" . $user["id"]);
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($wx_data));
						$output = curl_exec($ch);
						curl_close($ch);
						$request = json_decode($output, true);
						if (is_array($request) && !empty($request)) {
							return $this->result(1, "图片生成失败" . $request["errcode"] . ":" . $request["errmsg"], $request);
						} else {
							header("Content-Type: text/plain; charset=utf-8");
							header("Content-type:image/jpeg");
							$jpg = $output;
							$file = fopen($fileurl . $filename, "w");
							$reee = fwrite($file, $jpg);
							fclose($file);
							$code = "images/" . $_W["uniacid"] . "/" . date("Y") . "/" . date("m") . "/" . $filename;
							$dd = pdo_update($xcmodule . "_cut_order", array("code" => $code), array("id" => $order["id"], "uniacid" => $uniacid));
							$post_data["code"] = tomedia($code);
						}
					}
					$code = movecode($config, $post_data, $_W);
					return $this->result(0, "操作成功", array("code" => $code));
				} else {
					return $this->result(1, "海报未开启");
				}
			} else {
				return $this->result(1, "未参与");
			}
		} else {
			return $this->result(1, "活动已结束");
		}
		break;
	case "tmpl":
		$data = json_decode(htmlspecialchars_decode($_GPC["data"]), true);
		$order = pdo_get($xcmodule . "_order", array("uniacid" => $uniacid, "id" => $_GPC["id"]));
		if ($order) {
			if (!empty($order["userinfo"])) {
				$order["userinfo"] = json_decode($order["userinfo"], true);
			}
			pdo_update($xcmodule . "_order", array("tmpl" => json_encode($data)), array("id" => $order["id"]));
			$template = pdo_get($xcmodule . "_config", array("xkey" => "moban_pay", "uniacid" => $uniacid));
			if ($template) {
				$moban = json_decode($template["content"], true);
				if ($moban["status"] == 1 && $data[$moban["template_id"]] == "accept") {
					$account_api = WeAccount::create();
					$token = $account_api->getAccessToken();
					$templateParam = array("out_trade_no" => $order["out_trade_no"], "service" => mb_substr($order["title"], 0, 20), "name" => $order["name"], "mobile" => $order["mobile"], "amount" => $order["o_amount"], "createtime" => $order["createtime"]);
					if (!empty($order["userinfo"]["name"])) {
						$templateParam["name"] = $order["userinfo"]["name"];
					}
					if (!empty($order["userinfo"]["mobile"])) {
						$templateParam["mobile"] = $order["userinfo"]["mobile"];
					}
					$postdata = $moban["content"];
					foreach ($postdata as &$ppp) {
						foreach ($templateParam as $key => $d) {
							$ppp["value"] = str_replace("{{" . $key . "}}", $d, $ppp["value"]);
						}
					}
					$postdata = moban_limit($postdata);
					$post_data["touser"] = $order["openid"];
					$post_data["template_id"] = $moban["template_id"];
					$post_data["page"] = "xc_train/pages/base/base";
					$post_data["data"] = $postdata;
					$url = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=" . $token;
					$post_data = json_encode($post_data);
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
					$output = curl_exec($ch);
					curl_close($ch);
				}
			}
		}
		return $this->result(0, "操作成功");
		break;
	case "manage":
		$request = array();
		$user = pdo_get($xcmodule . "_userinfo", array("uniacid" => $uniacid, "openid" => $_W["openid"]));
		if ($user && ($user["shop"] == 1 || $user["shop"] == 2)) {
			$user["nick"] = base64_decode($user["nick"]);
			$request["user"] = $user;
			if ($user["shop"] == 2) {
				$store = pdo_get($xcmodule . "_school", array("uniacid" => $uniacid, "status" => 1, "id" => $user["shop_id"]));
				if ($store) {
					$request["store"] = $store;
				} else {
					return $this->result(1, "校区错误", array("redirect" => "../index/index"));
				}
			}
		} else {
			return $this->result(1, "没有权限", array("redirect" => "../index/index"));
		}
		return $this->result(0, "操作成功", $request);
		break;
}