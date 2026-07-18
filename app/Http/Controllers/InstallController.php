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

        // Passa no teste! Salva na sessão temporária, NÃO cria o .env ainda
        $request->session()->put('install_db', [
            'host' => $request->db_host,
            'port' => $request->db_port,
            'database' => $request->db_database,
            'username' => $request->db_username,
            'password' => $request->db_password ?? '',
        ]);

        return redirect()->route('install.admin');
    }

    public function admin(Request $request)
    {
        if (!$request->session()->has('install_db')) {
            return redirect()->route('install.database')->withErrors(['db_connection' => 'Por favor, configure o banco de dados primeiro.']);
        }
        return view('install.admin');
    }

    public function setupAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $db = $request->session()->get('install_db');
        if (!$db) {
            return redirect()->route('install.database')->withErrors(['db_connection' => 'Dados do banco expiraram. Preencha novamente.']);
        }

        // AQUI SIM: Instalação final aprovada! Criamos o .env
        if (!File::exists(base_path('.env')) && File::exists(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
            Artisan::call('key:generate', ['--force' => true]);
        }

        $this->updateEnv([
            'DB_CONNECTION' => 'mariadb',
            'DB_HOST' => $db['host'],
            'DB_PORT' => $db['port'],
            'DB_DATABASE' => $db['database'],
            'DB_USERNAME' => $db['username'],
            'DB_PASSWORD' => $db['password'],
            'APP_URL' => url('/'),
        ]);

        // Força o Laravel a usar a nova conexão MariaDB agora, em memória, para rodar as migrations 
        config([
            'database.connections.mariadb.host' => $db['host'],
            'database.connections.mariadb.port' => $db['port'],
            'database.connections.mariadb.database' => $db['database'],
            'database.connections.mariadb.username' => $db['username'],
            'database.connections.mariadb.password' => $db['password'],
            'database.default' => 'mariadb'
        ]);
        DB::purge('mariadb'); // Limpa qualquer conexão anterior
        DB::reconnect('mariadb');

        // Roda as migrations no banco
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

        // Limpa a sessão
        $request->session()->forget('install_db');

        // Limpa e otimiza o cache para o próximo request rodar 100% no .env
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
