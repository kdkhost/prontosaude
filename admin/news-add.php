<?php
require_once('header.php');

// 1. Carrega todas as categorias disponíveis
$stmt_cats = $pdo->prepare("SELECT category_id, category_name, category_slug FROM tbl_category ORDER BY category_name ASC");
$stmt_cats->execute();
$categorias = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

// Métricas de apoio para os KPIs do topo
$total_noticias    = (int)$pdo->query("SELECT COUNT(*) FROM tbl_news")->fetchColumn();
$total_ativas      = (int)$pdo->query("SELECT COUNT(*) FROM tbl_news WHERE status = 'Active'")->fetchColumn();
$total_categorias  = count($categorias);
$total_assinantes  = (int)$pdo->query("SELECT COUNT(*) FROM tbl_subscriber WHERE subs_active = 1")->fetchColumn();

// 2. Processamento do Envio (Suporta AJAX e POST tradicional)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_POST['action']) && $_POST['action'] === 'add_news_ajax');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['form1']) || $is_ajax)) {
    $news_title       = trim($_POST['news_title'] ?? '');
    $news_slug_input  = trim($_POST['news_slug'] ?? '');
    $news_content     = trim($_POST['news_content'] ?? '');
    $news_date        = trim($_POST['news_date'] ?? date('d/m/Y'));
    $category_id      = (int)($_POST['category_id'] ?? 0);
    $publisher        = trim($_POST['publisher'] ?? '');
    $status           = trim($_POST['status'] ?? 'Active');
    $notify_subs      = isset($_POST['notify_subscribers']) && $_POST['notify_subscribers'] === '1';

    $meta_title       = trim(strip_tags($_POST['meta_title'] ?? ''));
    $meta_keyword     = trim(strip_tags($_POST['meta_keyword'] ?? ''));
    $meta_description = trim(strip_tags($_POST['meta_description'] ?? ''));
    $meta_description = preg_replace('/[\r\n\t]+/', ' ', $meta_description);
    $meta_description = preg_replace('/\s+/', ' ', $meta_description);

    $valid = 1;
    $erros = [];

    // Validação de Título
    if (empty($news_title)) {
        $valid = 0;
        $erros[] = 'Informe o título da notícia.';
    } else {
        // Verifica duplicidade de título
        $stmt_check = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_title = ?");
        $stmt_check->execute([$news_title]);
        if ($stmt_check->rowCount() > 0) {
            $valid = 0;
            $erros[] = 'Já existe uma notícia cadastrada com este mesmo título. Escolha um título diferente.';
        }
    }

    // Validação de Conteúdo
    if (empty($news_content)) {
        $valid = 0;
        $erros[] = 'O corpo/conteúdo da notícia não pode ficar vazio.';
    }

    // Validação de Data
    if (empty($news_date)) {
        $valid = 0;
        $erros[] = 'Informe a data de publicação da notícia.';
    }

    // Validação de Categoria
    if ($category_id <= 0) {
        $valid = 0;
        $erros[] = 'Selecione uma categoria para organizar a notícia.';
    }

    if (!in_array($status, ['Active', 'Inactive'])) {
        $status = 'Active';
    }

    // Editor / Autor padrão
    if (empty($publisher)) {
        $publisher = $_SESSION['user']['full_name'] ?? 'Equipe Pronto Saúde';
    }

    // Processamento de Upload da Imagem de Capa
    $final_photo_name = '';
    if (isset($_FILES['photo']) && !empty($_FILES['photo']['name'])) {
        $file_name = $_FILES['photo']['name'];
        $file_tmp  = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        $file_err  = $_FILES['photo']['error'];

        if ($file_err !== UPLOAD_ERR_OK) {
            $valid = 0;
            $erros[] = 'Erro durante o upload da imagem de destaque (Código: ' . $file_err . ').';
        } else {
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($ext, $ext_permitidas)) {
                $valid = 0;
                $erros[] = 'Formato de imagem inválido. Formatos aceitos: WEBP, JPG, JPEG, PNG e GIF.';
            }

            // Limite de 15MB
            if ($file_size > 15 * 1024 * 1024) {
                $valid = 0;
                $erros[] = 'A imagem selecionada excede o limite máximo permitido de 15MB.';
            }
        }
    }

    if ($valid === 1) {
        // Geração do Slug Amigável
        $base_slug = !empty($news_slug_input) ? $news_slug_input : $news_title;
        $temp_slug = strtolower(trim($base_slug));
        // Remove acentuação
        $temp_slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $temp_slug);
        $final_slug = preg_replace('/[^a-z0-9\-]+/', '-', $temp_slug);
        $final_slug = trim(preg_replace('/-+/', '-', $final_slug), '-');

        if (empty($final_slug)) {
            $final_slug = 'noticia-' . time();
        }

        // Verifica duplicidade de slug
        $stmt_slug = $pdo->prepare("SELECT news_id FROM tbl_news WHERE news_slug = ?");
        $stmt_slug->execute([$final_slug]);
        if ($stmt_slug->rowCount() > 0) {
            $final_slug .= '-' . time();
        }

        // Se enviou foto, move para assets/uploads/
        if (isset($_FILES['photo']) && !empty($_FILES['photo']['name']) && !empty($file_tmp)) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $final_photo_name = 'news-' . time() . '-' . rand(100, 999) . '.' . $ext;
            $dir_uploads = __DIR__ . '/../assets/uploads/';

            if (!is_dir($dir_uploads)) {
                @mkdir($dir_uploads, 0777, true);
            }

            if (!move_uploaded_file($file_tmp, $dir_uploads . $final_photo_name)) {
                $final_photo_name = '';
            }
        }

        // Inserção no Banco de Dados
        $stmt_ins = $pdo->prepare("INSERT INTO tbl_news (
            news_title,
            news_slug,
            news_content,
            news_date,
            photo,
            category_id,
            publisher,
            total_view,
            meta_title,
            meta_keyword,
            meta_description,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)");

        $stmt_ins->execute([
            $news_title,
            $final_slug,
            $news_content,
            $news_date,
            $final_photo_name,
            $category_id,
            $publisher,
            $meta_title,
            $meta_keyword,
            $meta_description,
            $status
        ]);

        $novo_id = $pdo->lastInsertId();

        // Disparo opcional de e-mails para assinantes da Newsletter (em segundo plano)
        if ($notify_subs && !empty($receive_email)) {
            $stmt_subs = $pdo->prepare("SELECT subs_email FROM tbl_subscriber WHERE subs_active = 1");
            $stmt_subs->execute();
            $subscribers = $stmt_subs->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($subscribers)) {
                $news_url = BASE_URL . 'news/' . $final_slug;
                $subject  = "Nova Notícia Publicada: " . $news_title;
                $headers  = 'From: ' . $receive_email . "\r\n" .
                            'Reply-To: ' . $receive_email . "\r\n" .
                            'X-Mailer: PHP/' . phpversion() . "\r\n" . 
                            "MIME-Version: 1.0\r\n" . 
                            "Content-Type: text/html; charset=UTF-8\r\n";

                $corpo_email = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333;">
                    <h2 style="color: #0284c7;">Nova Notícia no Portal Pronto Saúde</h2>
                    <p>Olá,</p>
                    <p>Um novo artigo acabou de ser publicado no nosso portal:</p>
                    <h3 style="margin-top: 15px; margin-bottom: 5px;">' . htmlspecialchars($news_title, ENT_QUOTES, 'UTF-8') . '</h3>
                    <p style="color: #666; font-size: 14px;">Publicado em ' . htmlspecialchars($news_date, ENT_QUOTES, 'UTF-8') . ' por ' . htmlspecialchars($publisher, ENT_QUOTES, 'UTF-8') . '</p>
                    <p style="margin-top: 20px;">
                        <a href="' . $news_url . '" style="background: #0284c7; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Ler Artigo Completo</a>
                    </p>
                    <hr style="margin-top: 30px; border: 0; border-top: 1px solid #eee;">
                    <p style="font-size: 12px; color: #999;">Você recebeu esta mensagem porque está inscrito na nossa lista de novidades.</p>
                </div>';

                foreach ($subscribers as $sub) {
                    $to = trim($sub['subs_email'] ?? '');
                    if (!empty($to) && filter_var($to, FILTER_VALIDATE_EMAIL)) {
                        @mail($to, $subject, $corpo_email, $headers);
                    }
                }
            }
        }

        $msg_sucesso = 'Notícia publicada com sucesso no portal!';

        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'success',
                'message' => $msg_sucesso,
                'news_id' => $novo_id
            ]);
            exit;
        }

        $_SESSION['sucesso'] = $msg_sucesso;
        header('Location: news.php');
        exit;
    } else {
        $msg_erro = implode('<br>', $erros);
        if ($is_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'message' => $msg_erro
            ]);
            exit;
        }
        $error_message = $msg_erro;
    }
}
?>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-newspaper text-primary me-2"></i>Adicionar Notícia / Artigo
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="news.php" class="text-decoration-none">Portal de Notícias</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nova Notícia</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../page.php?slug=noticias" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Visualizar feed de notícias no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="category.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar categorias de notícias">
                    <i class="bi bi-tags me-1"></i> Categorias
                </a>
                <a href="news.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver listagem completa de notícias">
                    <i class="bi bi-list-ul me-1"></i> Ver Notícias
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mensagens Tradicionais de Sessão (Fallback) -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div><?= $error_message; ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Notícias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total de Notícias</span>
                            <h3 class="mb-0 fw-bold text-body"><?= $total_noticias; ?></h3>
                            <span class="text-body-secondary small">Artigos no portal</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-newspaper fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notícias Ativas -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Publicadas no Ar</span>
                            <h3 class="mb-0 fw-bold text-success"><?= $total_ativas; ?></h3>
                            <span class="text-body-secondary small">Visíveis aos leitores</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categorias Disponíveis -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Categorias</span>
                            <h3 class="mb-0 fw-bold text-info"><?= $total_categorias; ?></h3>
                            <span class="text-body-secondary small">Editorias ativas</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-tags fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assinantes de Newsletter -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Assinantes Ativos</span>
                            <h3 class="mb-0 fw-bold text-warning"><?= $total_assinantes; ?></h3>
                            <span class="text-body-secondary small">Inscritos na newsletter</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-envelope-paper fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Criação -->
        <form action="" method="post" enctype="multipart/form-data" id="formNewsAdd" novalidate>
            <input type="hidden" name="form1" value="1">

            <div class="row g-4">
                <!-- Coluna Principal (8 colunas) -->
                <div class="col-12 col-xl-8">
                    <!-- Card de Conteúdo Principal -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-richtext text-primary fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold text-body">Conteúdo da Notícia</h5>
                            </div>
                            <span class="text-body-secondary small">
                                <i class="bi bi-info-circle me-1"></i>Campos com (<span class="text-danger">*</span>) são obrigatórios
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Título da Notícia -->
                            <div class="mb-4">
                                <label for="news_title" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-type text-primary me-1"></i> Título da Notícia <span class="text-danger">*</span>
                                    </span>
                                    <small class="text-body-secondary fw-normal" id="charCounterTitle">0/200 caracteres</small>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                        <i class="bi bi-card-heading"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control form-control-lg bg-body text-body border" 
                                           id="news_title" 
                                           name="news_title" 
                                           maxlength="200" 
                                           required 
                                           placeholder="Ex: Pronto Saúde inaugura novo centro especializado em cardiologia...">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-info-circle me-1"></i> Um título claro, atraente e direto gera mais engajamento e cliques.
                                </div>
                            </div>

                            <!-- Slug da Notícia (Automático) -->
                            <div class="mb-4">
                                <label for="news_slug" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>
                                        <i class="bi bi-link-45deg text-primary me-1"></i> Slug da Notícia (URL Amigável)
                                    </span>
                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace small">
                                        <i class="bi bi-lock-fill me-1 text-primary"></i> Automático
                                    </span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body-tertiary text-body-secondary border font-monospace small">
                                        /news/
                                    </span>
                                    <input type="text" 
                                           class="form-control bg-body text-body border font-monospace ps-slug-locked" 
                                           id="news_slug" 
                                           name="news_slug" 
                                           readonly 
                                           placeholder="gerado-automaticamente-ao-digitar-o-titulo"
                                           style="background-color: var(--bs-tertiary-bg, #f1f5f9); cursor: not-allowed; border-style: dashed;">
                                </div>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-shield-lock me-1"></i> Gerado e padronizado automaticamente pelo sistema para preservar a integridade dos links externos e SEO.
                                </div>
                            </div>

                            <!-- Conteúdo Completo (Summernote) -->
                            <div class="mb-4">
                                <label for="news_content" class="form-label fw-bold text-body">
                                    <i class="bi bi-text-paragraph text-primary me-1"></i> Corpo da Notícia / Artigo <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="news_content" 
                                          name="news_content" 
                                          rows="14" 
                                          placeholder="Escreva aqui o conteúdo da notícia..."></textarea>
                                <div class="form-text text-body-secondary small mt-1">
                                    <i class="bi bi-palette me-1"></i> Utilize o editor visual acima para formatar parágrafos, adicionar subtítulos, citações, links e listas.
                                </div>
                            </div>

                            <!-- Data de Publicação -->
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="news_date" class="form-label fw-bold text-body">
                                        <i class="bi bi-calendar-event text-primary me-1"></i> Data de Publicação <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-calendar3"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="news_date" 
                                               name="news_date" 
                                               value="<?= date('d/m/Y'); ?>" 
                                               required 
                                               placeholder="dd/mm/aaaa">
                                    </div>
                                    <div class="form-text text-body-secondary small">Formato: dd/mm/aaaa</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="publisher" class="form-label fw-bold text-body">
                                        <i class="bi bi-person text-primary me-1"></i> Autor / Editor
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body-tertiary text-body-secondary border">
                                            <i class="bi bi-pen"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control bg-body text-body border" 
                                               id="publisher" 
                                               name="publisher" 
                                               value="<?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'Equipe Pronto Saúde', ENT_QUOTES, 'UTF-8'); ?>" 
                                               placeholder="Nome do autor ou editor">
                                    </div>
                                    <div class="form-text text-body-secondary small">Deixe em branco para assinar como o usuário logado</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card de SEO & Mecanismos de Busca -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-google text-primary fs-5"></i>
                                <h5 class="card-title mb-0 fw-bold text-body">Otimização para Mecanismos de Busca (SEO)</h5>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                Google Snippet Preview
                            </span>
                        </div>

                        <div class="card-body p-4">
                            <!-- Prévia do Google Snippet em Tempo Real -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-4">
                                <div class="small text-body-secondary mb-1 d-flex align-items-center gap-1 font-monospace" style="font-size: 12px;">
                                    <span><?= BASE_URL; ?>news/</span><span id="seoPreviewSlug" class="text-primary fw-semibold">nova-noticia</span>
                                </div>
                                <h6 class="text-primary mb-1 fw-bold text-truncate cursor-pointer" id="seoPreviewTitle" style="font-size: 17px;">
                                    Título da Notícia - Pronto Saúde
                                </h6>
                                <p class="text-body-secondary small mb-0 text-break" id="seoPreviewDesc" style="font-size: 13px; line-height: 1.4;">
                                    Resumo da notícia que aparecerá nos resultados de busca do Google e prévias de compartilhamento nas redes sociais...
                                </p>
                            </div>

                            <!-- Meta Título -->
                            <div class="mb-3">
                                <label for="meta_title" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>Meta Título (Page Title)</span>
                                    <small class="text-body-secondary" id="metaTitleCounter">0/70 caracteres</small>
                                </label>
                                <input type="text" 
                                       class="form-control bg-body text-body border" 
                                       id="meta_title" 
                                       name="meta_title" 
                                       maxlength="70" 
                                       placeholder="Título específico para os motores de busca (opcional)">
                            </div>

                            <!-- Palavras-chave -->
                            <div class="mb-3">
                                <label for="meta_keyword" class="form-label fw-bold text-body">
                                    Palavras-chave (Meta Keywords)
                                </label>
                                <input type="text" 
                                       class="form-control bg-body text-body border" 
                                       id="meta_keyword" 
                                       name="meta_keyword" 
                                       placeholder="saúde, cardiologia, consultas, exames rj, pronto atendimento (separadas por vírgula)">
                            </div>

                            <!-- Meta Descrição -->
                            <div>
                                <label for="meta_description" class="form-label fw-bold text-body d-flex align-items-center justify-content-between">
                                    <span>Meta Descrição (Meta Description)</span>
                                    <small class="text-body-secondary" id="metaDescCounter">0/160 caracteres</small>
                                </label>
                                <textarea class="form-control no-summernote bg-body text-body border" 
                                          data-no-summernote="true" 
                                          id="meta_description" 
                                          name="meta_description" 
                                          rows="3" 
                                          maxlength="160" 
                                          placeholder="Breve resumo (150 a 160 caracteres) que será exibido abaixo do título nos resultados do Google"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Lateral (4 colunas) -->
                <div class="col-12 col-xl-4">
                    <!-- Card de Publicação & Visibilidade -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-send-check text-primary"></i> Publicação & Visibilidade
                            </h6>
                        </div>

                        <div class="card-body p-3">
                            <!-- Switch de Visibilidade -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="statusSwitch" class="fw-bold text-body mb-0 d-block cursor-pointer">Status da Notícia</label>
                                        <small class="text-body-secondary" id="statusLabelDesc">
                                            Publicar e exibir no portal
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-4 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="statusSwitch" 
                                               checked>
                                        <input type="hidden" name="status" id="statusInput" value="Active">
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria de Notícia -->
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-bold text-body">
                                    <i class="bi bi-tag text-primary me-1"></i> Categoria <span class="text-danger">*</span>
                                </label>
                                <select class="form-select bg-body text-body border" id="category_id" name="category_id" required>
                                    <option value="" disabled selected>Selecione uma categoria...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['category_id']; ?>">
                                            <?= htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-body-secondary small">Editoria temática</span>
                                    <a href="category-add.php" target="_blank" class="small text-decoration-none">
                                        <i class="bi bi-plus-circle me-1"></i>Nova Categoria
                                    </a>
                                </div>
                            </div>

                            <!-- Switch de Notificação por E-mail (Newsletter) -->
                            <div class="p-3 rounded-3 bg-body-tertiary border mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label for="notify_subs" class="fw-semibold text-body mb-0 d-block small cursor-pointer">
                                            <i class="bi bi-envelope-check text-warning me-1"></i> Notificar Assinantes
                                        </label>
                                        <small class="text-body-secondary" style="font-size: 12px;">
                                            Disparar aviso para <?= $total_assinantes; ?> assinante(s)
                                        </small>
                                    </div>
                                    <div class="form-check form-switch fs-5 mb-0">
                                        <input class="form-check-control form-check-input" 
                                               type="checkbox" 
                                               role="switch" 
                                               id="notify_subs" 
                                               name="notify_subscribers" 
                                               value="1" 
                                               checked>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 shadow-sm fw-semibold" id="btnSalvarNoticia">
                                    <i class="bi bi-check-lg me-1"></i> Publicar Notícia
                                </button>
                                <a href="news.php" class="btn btn-outline-secondary rounded-pill py-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Foto em Destaque (Capa & Redes Sociais) -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-body py-3 border-bottom">
                            <h6 class="card-title mb-0 fw-bold text-body d-flex align-items-center gap-2">
                                <i class="bi bi-image text-primary"></i> Foto em Destaque
                            </h6>
                        </div>

                        <div class="card-body p-3">
                            <!-- Container de Preview de Imagem -->
                            <div class="position-relative mb-3 rounded-3 overflow-hidden border bg-body-tertiary d-flex align-items-center justify-content-center" 
                                 id="photoPreviewWrapper" 
                                 style="aspect-ratio: 16/9; min-height: 180px;">
                                <div id="photoPlaceholder" class="text-center p-3 text-body-secondary">
                                    <i class="bi bi-image fs-1 d-block mb-2 text-body-tertiary"></i>
                                    <span class="small d-block fw-semibold">Nenhuma imagem selecionada</span>
                                    <span class="small text-body-secondary" style="font-size: 11px;">Formatos: WEBP, JPG, PNG (Até 15MB)</span>
                                </div>
                                <img src="" alt="Prévia da foto" id="photoPreviewImg" class="w-100 h-100 object-fit-cover d-none">
                            </div>

                            <!-- Input de Upload -->
                            <div class="mb-2">
                                <input type="file" 
                                       class="form-control bg-body text-body border" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-none" id="btnRemoverFoto">
                                    <i class="bi bi-trash3 me-1"></i> Remover
                                </button>
                                <span class="badge bg-secondary-subtle text-body border small ms-auto">
                                    Proporção: 1.91:1
                                </span>
                            </div>

                            <div class="alert alert-info border-0 p-2 px-3 rounded-3 mb-0 small text-body-secondary" style="font-size: 12px;">
                                <i class="bi bi-share-fill text-info me-1"></i>
                                <strong>Dica de Redes:</strong> Esta imagem é exibida como capa e na miniatura ao compartilhar no <strong>WhatsApp, Facebook e Twitter/X</strong> (Recomendado: 1200 x 630 px).
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- ======================================================= -->
<!-- Scripts JavaScript Integrados                           -->
<!-- ======================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Contador de Caracteres e Geração Automática de Slug
    const inputTitle     = document.getElementById('news_title');
    const inputSlug      = document.getElementById('news_slug');
    const counterTitle   = document.getElementById('charCounterTitle');
    const seoTitle       = document.getElementById('meta_title');
    const seoDesc        = document.getElementById('meta_description');
    const seoPrevTitle   = document.getElementById('seoPreviewTitle');
    const seoPrevSlug    = document.getElementById('seoPreviewSlug');
    const seoPrevDesc    = document.getElementById('seoPreviewDesc');
    const counterMetaT   = document.getElementById('metaTitleCounter');
    const counterMetaD   = document.getElementById('metaDescCounter');

    function gerarSlug(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    if (inputTitle) {
        inputTitle.addEventListener('input', function () {
            const len = this.value.length;
            if (counterTitle) counterTitle.textContent = `${len}/200 caracteres`;

            const slug = gerarSlug(this.value);
            if (inputSlug) inputSlug.value = slug;
            if (seoPrevSlug) seoPrevSlug.textContent = slug || 'nova-noticia';

            // Atualiza prévia de SEO caso o Meta Título não tenha sido customizado
            if (seoPrevTitle) {
                if (seoTitle && seoTitle.value.trim()) {
                    seoPrevTitle.textContent = seoTitle.value;
                } else {
                    seoPrevTitle.textContent = this.value.trim() ? `${this.value.trim()} - Pronto Saúde` : 'Título da Notícia - Pronto Saúde';
                }
            }
        });
    }

    if (seoTitle) {
        seoTitle.addEventListener('input', function () {
            const len = this.value.length;
            if (counterMetaT) counterMetaT.textContent = `${len}/70 caracteres`;
            if (seoPrevTitle) {
                seoPrevTitle.textContent = this.value.trim() ? this.value : (inputTitle.value.trim() ? `${inputTitle.value.trim()} - Pronto Saúde` : 'Título da Notícia - Pronto Saúde');
            }
        });
    }

    if (seoDesc) {
        seoDesc.addEventListener('input', function () {
            const len = this.value.length;
            if (counterMetaD) counterMetaD.textContent = `${len}/160 caracteres`;
            if (seoPrevDesc) {
                seoPrevDesc.textContent = this.value.trim() ? this.value : 'Resumo da notícia que aparecerá nos resultados de busca do Google e prévias de compartilhamento nas redes sociais...';
            }
        });
    }

    // 2. Switch de Visibilidade / Status
    const statusSwitch = document.getElementById('statusSwitch');
    const statusInput  = document.getElementById('statusInput');
    const statusDesc   = document.getElementById('statusLabelDesc');

    if (statusSwitch && statusInput) {
        statusSwitch.addEventListener('change', function () {
            if (this.checked) {
                statusInput.value = 'Active';
                if (statusDesc) statusDesc.textContent = 'Publicar e exibir no portal';
            } else {
                statusInput.value = 'Inactive';
                if (statusDesc) statusDesc.textContent = 'Salvar como rascunho (oculto)';
            }
        });
    }

    // 3. Pré-visualização Instantânea da Foto de Capa
    const inputPhoto       = document.getElementById('photo');
    const photoPreviewImg  = document.getElementById('photoPreviewImg');
    const photoPlaceholder = document.getElementById('photoPlaceholder');
    const btnRemoverFoto   = document.getElementById('btnRemoverFoto');

    if (inputPhoto) {
        inputPhoto.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();

                reader.onload = function (e) {
                    photoPreviewImg.src = e.target.result;
                    photoPreviewImg.classList.remove('d-none');
                    photoPlaceholder.classList.add('d-none');
                    if (btnRemoverFoto) btnRemoverFoto.classList.remove('d-none');
                };

                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoverFoto) {
        btnRemoverFoto.addEventListener('click', function () {
            if (inputPhoto) inputPhoto.value = '';
            photoPreviewImg.src = '';
            photoPreviewImg.classList.add('d-none');
            photoPlaceholder.classList.remove('d-none');
            this.classList.add('d-none');
        });
    }

    // 4. Submissão Segura via AJAX com SweetAlert2
    const formNews = document.getElementById('formNewsAdd');
    const btnSalvar = document.getElementById('btnSalvarNoticia');

    if (formNews) {
        formNews.addEventListener('submit', function (e) {
            e.preventDefault();

            // Sincroniza Summernote se estiver ativo
            if (window.jQuery && jQuery.fn.summernote) {
                jQuery('#news_content').val(jQuery('#news_content').summernote('code'));
            }

            // Validações básicas no frontend
            const titulo = (inputTitle ? inputTitle.value.trim() : '');
            const catVal = document.getElementById('category_id').value;
            const conteudo = document.getElementById('news_content').value.trim();

            if (!titulo) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Título Obrigatório',
                        text: 'Por favor, informe o título da notícia.'
                    });
                }
                if (inputTitle) inputTitle.focus();
                return;
            }

            if (!catVal || parseInt(catVal, 10) <= 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Categoria Obrigatória',
                        text: 'Por favor, selecione uma categoria para organizar a notícia.'
                    });
                }
                document.getElementById('category_id').focus();
                return;
            }

            if (!conteudo || conteudo === '<p><br></p>') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Conteúdo Obrigatório',
                        text: 'Por favor, escreva o conteúdo da notícia antes de publicar.'
                    });
                }
                return;
            }

            // Prepara o FormData
            const formData = new FormData(formNews);
            formData.append('action', 'add_news_ajax');

            const btnOriginalHtml = btnSalvar.innerHTML;
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Publicando notícia...';

            fetch('news-add.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;

                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Notícia Publicada!',
                            text: data.message || 'O artigo foi salvo com sucesso no portal.',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = 'news.php';
                        });
                    } else {
                        window.location.href = 'news.php';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Publicar',
                            html: data.message || 'Não foi possível publicar a notícia.'
                        });
                    }
                }
            })
            .catch(err => {
                console.error(err);
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = btnOriginalHtml;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Falha de Comunicação',
                        text: 'Ocorreu um erro ao enviar os dados para o servidor. Tente novamente.'
                    });
                }
            });
        });
    }

    // 5. Tooltips com Auto-Hide
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover', boundary: 'window' });
            el.addEventListener('mouseleave', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
            el.addEventListener('click', () => {
                const tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.hide();
            });
        });
    }
});
</script>

<?php require_once('footer.php'); ?>
