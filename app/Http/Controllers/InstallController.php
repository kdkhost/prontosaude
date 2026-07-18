<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class InstallController extends Controller
{
    public function index()
    {
        $requirements = [
            'PHP >= 8.4' => version_compare(PHP_VERSION, '8.4.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
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

        return view('install.requirements', compact('requirements', 'permissions', 'canInstall'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function setupDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // Testa a conexão com o MariaDB antes de salvar
        try {
            $dsn = "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}";
            $pdo = new \PDO($dsn, $request->db_username, $request->db_password ?? '');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            return back()->withErrors(['db_connection' => 'Falha na conexão com o MariaDB: ' . $e->getMessage()])->withInput();
        }

        // Cria o arquivo .env a partir do .env.example se ele não existir
        if (!File::exists(base_path('.env')) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Atualiza o arquivo .env com as credenciais fornecidas
        $this->updateEnv([
            'DB_CONNECTION' => 'mariadb',
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password ?? '',
            'APP_URL' => url('/'),
        ]);

        // Limpa o cache de config para que o Laravel releia o .env no próximo request
        Artisan::call('config:clear');

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function setupAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Roda as migrations no banco MariaDB informado
        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            return back()->withErrors(['migration' => 'Erro ao executar migrations: ' . $e->getMessage()])->withInput();
        }

        // Cria o usuário administrador
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Cria o lock file que sinaliza que a instalação foi concluída
        File::put(storage_path('app/installed.lock'), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'admin_email' => $request->email,
            'version' => '2.0.0',
        ]));

        // Limpa e otimiza o cache
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return redirect()->route('install.complete');
    }

    public function complete()
    {
        return view('install.complete');
    }

    /**
     * Atualiza chaves no arquivo .env de forma segura
     */
    private function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            // Se a chave existe, substitui o valor
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                // Se não existe, adiciona ao final
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }
}
