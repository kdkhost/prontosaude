<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstallController extends Controller
{
    public function index()
    {
        $requirements = [
            'PHP >= 8.4' => version_compare(PHP_VERSION, '8.4.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'cURL Extension' => extension_loaded('curl'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'CType Extension' => extension_loaded('ctype'),
            'JSON Extension' => extension_loaded('json'),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
        ];
        
        $permissions = [
            'storage/app' => is_writable(storage_path('app')),
            'storage/framework' => is_writable(storage_path('framework')),
            'storage/logs' => is_writable(storage_path('logs')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
        ];
        
        $canInstall = !in_array(false, $requirements) && !in_array(false, $permissions);
        
        return view('install', compact('requirements', 'permissions', 'canInstall'));
    }
}
