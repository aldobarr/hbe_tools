<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class MassPMController extends Controller
{
	public function version(){
		$version = env('MASS_PM_VERSION');
		echo $version;
		die();
	}

	public function getApp(){
		$filename = 'PnW Mass PM.exe';
		$file = dirname(dirname(dirname(dirname(__FILE__)))) . '/mass_pm.exe';
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}
	
	public function getRunner(){
		$file = dirname(dirname(dirname(dirname(__FILE__)))) . '/runner_app.exe';
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($file) . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}
}