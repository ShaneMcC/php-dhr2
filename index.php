<?php

	// Implementation of https://github.com/tellnes/dhr2/blob/master/Spec.md

	function parseDHR2($entry) {
		$bits = explode(";", $entry);

		$dhr2 = array('v' => FALSE,
		              'l' => FALSE,
		              's' => 'f',
		              't' => FALSE,
		              'd' => '*',
		              'p' => FALSE,
		              'i' => FALSE,
		        );


		foreach ($bits AS $bit) {
			$kv = explode("=", trim($bit), 2);
			if (isset($dhr2[strtolower($kv[0])])) {
				$dhr2[strtolower($kv[0])] = isset($kv[1]) ? $kv[1] : TRUE;
			}
		}

		if ($dhr2['p'] === FALSE) {
			if ($dhr2['d'] == '*') { $dhr2['p'] = -3; }
			else if (strpos($dhr2['d'], '*') !== FALSE) { $dhr2['p'] = -2; }
			else { $dhr2['p'] = -1; }
		}

		if ($dhr2['v'] == 'DHR2/1' && $dhr2['l'] !== FALSE && strpos($dhr2['d'], "\n") === FALSE && strpos($dhr2['d'], "\r") === FALSE) {
			return $dhr2;
		} else {
			return FALSE;
		}
	}

	function doDHR2($domain) {
		$domain = $_SERVER['HTTP_HOST'];

		$dhr2 = array();

		$dns = dns_get_record($domain, DNS_TXT);

		foreach ($dns as $entries) {
			$d = parseDHR2($entries['txt']);

			if ($d !== FALSE && fnmatch(strtolower($d['d']), strtolower($domain))) {
				$dhr2[] = $d;
			}
		}

		if (count($dhr2) > 0) {
			usort($dhr2, function($a, $b){ if ($a['p'] == $b['p']) { return 0; } else { return ($a['p'] > $b['p']) ? -1 : 1; }});

			$dhr2 = $dhr2[0];

			$url = $dhr2['l'];

			if ($dhr2['i'] === FALSE) {
				$url = rtrim($url, '/');
				$url .= $_SERVER['REQUEST_URI'];
			}

			$code = 301;
			if ($dhr2['s'] == 'm') { $code = 302; }

			header('Location: ' . $url , true, $code);

			if ($dhr2['t'] !== FALSE) {
				header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $dhr2['t']) . " GMT");
				header('Cache-Control: public, max-age=' . $dhr2['t']);
			}

			echo '<!DOCTYPE html><title>', $code, ' Moved</title><h1>', $code, ' Moved</h1>';
			echo 'The document has moved <a href="', $url, '">here</a>.';

			die();
		}
	}

	if (isset($_SERVER['HTTP_HOST'])) {
		doDHR2($_SERVER['HTTP_HOST']);
	}

	if (file_exists(dirname(__FILE__) . '/noRedirect.php')) {
		require_once(dirname(__FILE__) . '/noRedirect.php');
	} else {
		echo 'There is nothing here.';
	}
?>
