<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Exception;

class SystemHealthController extends Controller
{
    /**
     * Display System Information & Health Status (Admin Only).
     */
    public function index()
    {
        $appVersion     = config('arsipari.version', '1.0.0');
        $laravelVersion = app()->version();
        $phpVersion     = PHP_VERSION;

        // Database status
        $dbDriver   = config('database.default');
        $sqlitePath = database_path('database.sqlite');
        $dbSize     = File::exists($sqlitePath) ? File::size($sqlitePath) : 0;
        $dbStatus   = 'OK';

        try {
            DB::connection()->getPdo();
        } catch (Exception $e) {
            $dbStatus = 'FAILED';
        }

        // Storage status
        $archivePath    = storage_path('app/private/archives');
        $backupPath     = storage_path('app/backups');
        $archiveWritable = is_writable($archivePath) || (!File::exists($archivePath) && is_writable(storage_path('app/private')));
        $backupWritable  = is_writable($backupPath) || (!File::exists($backupPath) && is_writable(storage_path('app')));

        // PHP extensions check
        $requiredExtensions = [
            'pdo_sqlite' => extension_loaded('pdo_sqlite'),
            'mbstring'   => extension_loaded('mbstring'),
            'zip'        => extension_loaded('zip'),
            'xml'        => extension_loaded('xml'),
            'fileinfo'   => extension_loaded('fileinfo'),
            'gd'         => extension_loaded('gd'),
        ];

        return view('admin.system.index', compact(
            'appVersion',
            'laravelVersion',
            'phpVersion',
            'dbDriver',
            'dbSize',
            'dbStatus',
            'archiveWritable',
            'backupWritable',
            'requiredExtensions'
        ));
    }
}
