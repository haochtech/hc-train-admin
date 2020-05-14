<?php

defined('IN_IA') or exit('Access Denied');
global $xcmodule;
$xcmodule = 'xc_train';
include_once IA_ROOT . '/addons/xc_train/common/function.php';
class Xc_trainModuleSite extends WeModuleSite
{
	protected function createWebUrl($do, $query = array())
	{
		global $_GPC;
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		$query['version_id'] = intval($_GPC['version_id']);
		return wurl('site/entry', $query);
	}
	public function doWebExport()
	{
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$condition = array();
		$condition['status'] = 1;
		$condition['order_type IN'] = array(1, 2);
		$condition['uniacid'] = $_W['uniacid'];
		if (!empty($_GPC['out_trade_no'])) {
			$condition['out_trade_no LIKE'] = '%' . $_GPC['out_trade_no'] . '%';
		}
		if (!empty($_GPC['openid'])) {
			$condition['openid LIKE'] = '%' . $_GPC['openid'] . '%';
		}
		if (!empty($_GPC['mobile'])) {
			$condition['mobile LIKE'] = '%' . $_GPC['mobile'] . '%';
		}
		if (!empty($_GPC['use'])) {
			$condition['use'] = $_GPC['use'];
		}
		if (!empty($_GPC['choose'])) {
			$condition['id IN'] = explode(',', $_GPC['choose']);
		}
		$order = pdo_getall('xc_train_order', $condition, array(), '', 'id DESC');
		$store = pdo_getall('xc_train_school', array("uniacid" => $uniacid));
		$store_list = array();
		if ($store) {
			foreach ($store as $s) {
				$store_list[$s['id']] = $s;
			}
		}
		if ($order) {
			$ids = array();
			$share_ids = array();
			foreach ($order as $oo) {
				$ids[] = $oo['openid'];
			}
			$user = pdo_getall('xc_train_userinfo', array("uniacid" => $uniacid, "openid IN" => $ids));
			$user_data = array();
			$share_data = array();
			if ($user) {
				foreach ($user as $u) {
					$u['nick'] = base64_decode($u['nick']);
					$user_data[$u['openid']] = $u;
					if (!empty($u['share'])) {
						$share_ids[] = $u['share'];
					}
				}
				if (!empty($share_ids)) {
					$share_order = pdo_getall('xc_train_order', array("uniacid" => $uniacid, "status" => 1, "openid IN" => $share_ids));
					if ($share_order) {
						foreach ($share_order as $so) {
							$share_data[$so['openid']] = $so;
						}
					}
				}
			}
			header('Content-type: application/vnd.ms-excel; charset=utf8');
			header('Content-Disposition: attachment; filename=order.xlsx');
			$data = '<tr>';
			$xc = htmlspecialchars_decode($_GPC['xc']);
			$xc = json_decode($xc, true);
			foreach ($xc as $x) {
				if ($x['status'] == 1) {
					$data .= '<th>' . $x['name'] . '</th>';
				}
			}
			$data .= '</tr>';
			foreach ($order as $v) {
				$v['nick'] = $user_data[$v['openid']]['nick'];
				$v['user_share'] = $user_data[$v['openid']]['share'];
				if (!empty($v['user_share']) && !empty($share_data[$v['user_share']]) && !empty($share_data[$v['user_share']]['mobile'])) {
					$v['share_mobile'] = $share_data[$v['user_share']]['mobile'];
				}
				if ($v['use'] == 1) {
					$v['status_name'] = '已使用';
				} else {
					$v['status_name'] = '未使用';
				}
				$v['store_name'] = $store_list[$v['store']]['name'];
				$data = $data . '<tr>';
				foreach ($xc as $x) {
					if ($x['status'] == 1) {
						if ($x['key'] == 'sign') {
							$data .= '<td>';
							if (!empty($v['sign'])) {
								$v['sign'] = json_decode($v['sign'], true);
								if (is_array($v['sign']) && !empty($v['sign'])) {
									foreach ($v['sign'] as $vs) {
										$data .= $vs['name'] . '：' . $vs['value'] . '<br/>';
									}
								}
							}
							$data .= '</td>';
						} else {
							$data .= '<td style=\'vnd.ms-excel.numberformat:@\'>' . $v[$x['key']] . '</td>';
						}
					}
				}
				$data = $data . '</tr>';
			}
			$data = '<table border=\'1\'>' . $data . '</table>';
			echo iconv('UTF-8', 'GBK//TRANSLIT', $data);
			exit;
		}
	}
	public function doWebPost()
	{
		global $_GPC, $_W;
		load()->func('communication');
		$url = $_GPC['url'];
		$sms = pdo_get('xc_beauty_config', array("xkey" => "sms"));
		if ($sms) {
			$sms['content'] = json_decode($sms['content'], true);
			$customize = $sms['content']['customize'];
			$post = $sms['content']['post'];
			if (is_array($post) && !empty($post)) {
				$post = json_encode($post);
				if (is_array($customize)) {
					foreach ($customize as $x) {
						$post = str_replace('{{' . $x['attr'] . '}}', $x['value'], $post);
					}
				}
				$post = str_replace('{{webnamex}}', '美容', $post);
				$post = str_replace('{{trade}}', '1220171127101100000017', $post);
				$post = str_replace('{{amount}}', '199元', $post);
				$post = str_replace('{{namex}}', '张三', $post);
				$post = str_replace('{{phonex}}', '18888888888', $post);
				$post = str_replace('{{addrx}}', '中国北京', $post);
				$post = str_replace('{{datex}}', date('Y-m-d H:i'), $post);
				$post = json_decode($post, true);
				$data = array();
				foreach ($post as $x2) {
					$data[$x2['attr']] = $x2['value'];
				}
				$request_post = ihttp_post($url, $data);
			}
			$get = $sms['content']['get'];
			if (is_array($get) && !empty($get)) {
				$get = json_encode($get);
				if (is_array($customize)) {
					foreach ($customize as $x) {
						$get = str_replace('{{' . $x['attr'] . '}}', $x['value'], $get);
					}
				}
				$get = str_replace('{{webnamex}}', '美容', $get);
				$get = str_replace('{{trade}}', '1220171127101100000017', $get);
				$get = str_replace('{{amount}}', '199元', $get);
				$get = str_replace('{{namex}}', '张三', $get);
				$get = str_replace('{{phonex}}', '18888888888', $get);
				$get = str_replace('{{addrx}}', '中国北京', $get);
				$get = str_replace('{{datex}}', date('Y-m-d H:i'), $get);
				$get = json_decode($get, true);
				$url_data = '';
				foreach ($get as $x3) {
					if (empty($url_data)) {
						$url_data = urlencode($x3['attr']) . '=' . urlencode($x3['value']);
					} else {
						$url_data = $url_data . '&' . urlencode($x3['attr']) . '=' . urlencode($x3['value']);
					}
				}
				if (strpos($url, '?') !== false) {
					$url = $url . $url_data;
				} else {
					$url = $url . '?' . $url_data;
				}
				$request_get = ihttp_get($url);
				echo $request_get['content'];
			}
		}
	}
	public function doWebOrderRefund()
	{
		global $_GPC, $_W;
		$uniacid = $_W['uniacid'];
		$order = pdo_get('xc_beauty_order', array("id" => $_GPC['id'], "uniacid" => $uniacid));
		if ($order) {
			$userinfo = pdo_get('xc_beauty_userinfo', array("status" => 1, "openid" => $order['openid'], "uniacid" => $uniacid));
			if (!empty($order['score'])) {
				$score = $userinfo['score'] - $order['score'];
				pdo_update('xc_beauty_userinfo', array("score" => $score), array("status" => 1, "openid" => $order['openid'], "uniacid" => $uniacid));
			}
			if (floatval($order['canpay']) != 0) {
				$money = round(floatval($userinfo['money']) + floatval($order['canpay']), 2);
				$request = pdo_update('xc_beauty_userinfo', array("money" => $money), array("status" => 1, "openid" => $order['openid'], "uniacid" => $uniacid));
			}
			if (floatval($order['wxpay']) != 0) {
				$config = pdo_get('uni_settings', array("uniacid" => $uniacid));
				$cert = pdo_get('xc_beauty_config', array("uniacid" => $uniacid, "xkey" => "refund"));
				if ($config && $cert) {
					$cert['content'] = json_decode($cert['content'], true);
					if (!empty($cert['content']['cert']) && !empty($cert['content']['key'])) {
						$config['payment'] = unserialize($config['payment']);
						$appid = $_W['account']['key'];
						$transaction_id = $order['wx_out_trade_no'];
						$total_fee = floatval($order['wxpay']) * 100;
						$refund_fee = floatval($order['wxpay']) * 100;
						$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
						$ref = strtoupper(md5('appid=' . $appid . '&mch_id=' . $config['payment']['wechat']['mchid'] . '&nonce_str=123456' . '&out_refund_no=' . $transaction_id . '&out_trade_no=' . $transaction_id . '&refund_fee=' . $refund_fee . '&total_fee=' . $total_fee . '&key=' . $config['payment']['wechat']['signkey']));
						$refund = array("appid" => $appid, "mch_id" => $config['payment']['wechat']['mchid'], "nonce_str" => "123456", "out_refund_no" => $transaction_id, "out_trade_no" => $transaction_id, "refund_fee" => $refund_fee, "total_fee" => $total_fee, "sign" => $ref);
						$xml = arrayToXml($refund);
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
						curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
						$cert_file = '../addons/' . $_GPC['m'] . '/resource/' . rand(100000, 999999) . '.pem';
						if (($TxtRes = fopen($cert_file, 'w+')) === FALSE) {
							echo '创建可写文件：' . $cert_file . '失败';
							exit;
						}
						$StrConents = $cert['content']['cert'];
						if (!fwrite($TxtRes, $StrConents)) {
							echo '尝试向文件' . $cert_file . '写入' . $StrConents . '失败！';
							fclose($TxtRes);
							exit;
						}
						fclose($TxtRes);
						curl_setopt($ch, CURLOPT_SSLCERT, $cert_file);
						curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'pem');
						$key_file = '../addons/' . $_GPC['m'] . '/resource/' . rand(100000, 999999) . '.pem';
						if (($TxtRes = fopen($key_file, 'w+')) === FALSE) {
							echo '创建可写文件：' . $key_file . '失败';
							exit;
						}
						$StrConents = $cert['content']['key'];
						if (!fwrite($TxtRes, $StrConents)) {
							echo '尝试向文件' . $key_file . '写入' . $StrConents . '失败！';
							fclose($TxtRes);
							exit;
						}
						fclose($TxtRes);
						curl_setopt($ch, CURLOPT_SSLKEY, $key_file);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						$data = curl_exec($ch);
						unlink($cert_file);
						unlink($key_file);
						if ($data) {
							curl_close($ch);
							$data = xmlToArray($data);
							if ($data['return_code'] == 'SUCCESS') {
								if ($data['result_code'] != 'SUCCESS') {
								}
							}
						} else {
							$error = curl_errno($ch);
							curl_close($ch);
						}
					}
				}
			}
			$share = pdo_get('xc_beauty_share', array("status" => 1, "uniacid" => $uniacid, "openid" => $order['openid'], "out_trade_no" => $order['out_trade_no']));
			if ($share) {
				$share_o_amount = round(floatval($userinfo['share_o_amount']) - floatval($share['amount']), 2);
				$share_empty = round(floatval($userinfo['share_empty']) + floatval($share['amount']));
				pdo_update('xc_beauty_userinfo', array("share_o_amount" => $share_o_amount, "share_empty" => $share_empty), array("status" => 1, "openid" => $order['openid'], "uniacid" => $uniacid));
				pdo_update('xc_beauty_share', array("status" => 2), array("status" => 1, "uniacid" => $uniacid, "openid" => $order['openid'], "out_trade_no" => $order['out_trade_no']));
			}
			$request = pdo_update('xc_beauty_order', array("refund_status" => 1, "status" => 2), array("id" => $_GPC['id'], "uniacid" => $uniacid));
			if ($request) {
				$json = array("status" => 1, "msg" => "操作成功");
				echo json_encode($json);
			} else {
				$json = array("status" => -1, "msg" => "操作失败");
				echo json_encode($json);
			}
		} else {
			$json = array("status" => -1, "msg" => "操作失败");
			echo json_encode($json);
		}
	}
	public function doWebUpSql()
	{
		include_once '../addons/xc_train/upsql.php';
		upsql();
	}
}