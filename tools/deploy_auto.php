<?php
// Script de automação git e deploy via PHP puro
header('Content-Type: text/plain; charset=utf-8');

$repo_dir = dirname(__DIR__);
chdir($repo_dir);

echo "Diretório de trabalho: " . getcwd() . "\n\n";

function run_proc($cmd) {
    echo ">> $cmd\n";
    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $process = proc_open($cmd, $descriptors, $pipes);
    if (is_resource($process)) {
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $ret = proc_close($process);
        echo "Saída:\n" . trim($stdout) . "\n";
        if (!empty($stderr)) {
            echo "Erros/Avisos:\n" . trim($stderr) . "\n";
        }
        echo "Código de Retorno: $ret\n\n";
        return $ret;
    } else {
        echo "Falha ao iniciar o processo proc_open.\n\n";
        return -1;
    }
}

// 1. Status inicial
run_proc("git status -s");

// 2. Git add dos arquivos modificados
$files = [
    "admin/doctor-edit.php",
    "admin/designation.php",
    "admin/extra.php",
    "admin/extra-add.php",
    "admin/extra-edit.php",
    "admin/news.php",
    "inc/popup-promocional.php",
    "footer.php"
];

foreach ($files as $f) {
    run_proc("git add \"$f\"");
}

// 3. Commit humanizado em Português Brasileiro
$commit_msg = "feat(admin): reformula modulos de medicos, especialidades e escalas, ativa popup e corrige capas";
run_proc("git commit -m \"$commit_msg\"");

// 4. Push para o GitHub
run_proc("git push origin main");

// 5. Deploy via SSH no servidor de produção
run_proc("ssh -o BatchMode=yes -o ConnectTimeout=15 prontosaude \"cd public_html && git pull origin main\"");

echo "=== FIM DO PROCESSO ===\n";

