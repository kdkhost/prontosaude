<?php
require_once('header.php');

// 1. Endpoint AJAX: Exclusão Segura de Categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        try {
            // Busca fotos das notícias vinculadas para remoção física
            $stmt_news = $pdo->prepare("SELECT photo FROM tbl_news WHERE category_id = ?");
            $stmt_news->execute([$id]);
            $fotos_noticias = $stmt_news->fetchAll(PDO::FETCH_ASSOC);

            foreach ($fotos_noticias as $fn) {
                $arq = trim($fn['photo'] ?? '');
                if (!empty($arq)) {
                    $caminhos = [
                        __DIR__ . '/../assets/uploads/' . $arq,
                        __DIR__ . '/../assets/uploads/news/' . $arq
                    ];
                    foreach ($caminhos as $path) {
                        if (file_exists($path) && is_file($path)) {
                            @unlink($path);
                        }
                    }
                }
            }

            // Exclui comentários das notícias dessa categoria se a tabela existir
            try {
                $pdo->prepare("DELETE FROM tbl_news_comment WHERE news_id IN (SELECT news_id FROM tbl_news WHERE category_id = ?)")->execute([$id]);
            } catch (Exception $e) {}

            // Exclui notícias filhas
            $pdo->prepare("DELETE FROM tbl_news WHERE category_id = ?")->execute([$id]);

            // Exclui a categoria
            $pdo->prepare("DELETE FROM tbl_category WHERE category_id = ?")->execute([$id]);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Categoria e suas dependências foram removidas com sucesso!'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Erro ao excluir categoria: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Identificador de categoria inválido.']);
    }
    exit;
}

// 2. Consulta de Categorias com Contagem de Artigos
$statement = $pdo->prepare("SELECT 
                                c.category_id,
                                c.category_name,
                                c.category_slug,
                                c.meta_title,
                                c.meta_keyword,
                                c.meta_description,
                                COUNT(n.news_id) AS total_news
                            FROM tbl_category c
                            LEFT JOIN tbl_news n ON c.category_id = n.category_id
                            GROUP BY c.category_id
                            ORDER BY c.category_id ASC");
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

// 3. Métricas de KPIs
$total_categorias      = count($categories);
$total_com_artigos     = 0;
$total_sem_artigos     = 0;
$total_artigos_geral   = 0;

foreach ($categories as $cat) {
    $qtd = (int)($cat['total_news'] ?? 0);
    $total_artigos_geral += $qtd;
    if ($qtd > 0) {
        $total_com_artigos++;
    } else {
        $total_sem_artigos++;
    }
}
?>

<style>
/* Linhas de Tabela e Badges */
.table-categories tbody tr {
    transition: background-color 0.15s ease-in-out;
}
.badge-news-count {
    font-size: 0.8rem;
    padding: 0.35rem 0.65rem;
}
</style>

<!-- Cabeçalho da Página -->
<div class="app-content-header pb-2 mb-3 border-bottom">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 fw-bold text-body">
                    <i class="bi bi-tags text-primary me-2"></i>Categorias de Notícias &amp; Blog
                </h3>
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Início</a></li>
                    <li class="breadcrumb-item"><a href="news.php" class="text-decoration-none">Notícias</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categorias</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="../news.php" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Ver portal de notícias e blog no site público">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <a href="news.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Gerenciar todos os artigos de notícias">
                    <i class="bi bi-newspaper me-1"></i> Notícias
                </a>
                <a href="category-add.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="tooltip" title="Cadastrar uma nova categoria">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Categoria
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <!-- Mini Cards de Indicadores (KPIs) com .kpi-icon-circle perfeitamente redondo -->
        <div class="row g-3 mb-4">
            <!-- Total de Categorias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Total de Categorias</span>
                            <h3 class="mb-0 fw-bold text-body" id="kpiTotal"><?= $total_categorias; ?></h3>
                            <span class="text-body-secondary small">Editorias do blog</span>
                        </div>
                        <div class="kpi-icon-circle bg-primary-subtle text-primary">
                            <i class="bi bi-tags fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categorias com Notícias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Com Artigos Ativos</span>
                            <h3 class="mb-0 fw-bold text-success" id="kpiComArtigos"><?= $total_com_artigos; ?></h3>
                            <span class="text-body-secondary small">Com matérias publicadas</span>
                        </div>
                        <div class="kpi-icon-circle bg-success-subtle text-success">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categorias Sem Notícias -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Sem Artigos</span>
                            <h3 class="mb-0 fw-bold text-warning" id="kpiSemArtigos"><?= $total_sem_artigos; ?></h3>
                            <span class="text-body-secondary small">Vazias no momento</span>
                        </div>
                        <div class="kpi-icon-circle bg-warning-subtle text-warning">
                            <i class="bi bi-tag fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Geral de Artigos Vinculados -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-uppercase fw-semibold text-body-secondary small d-block mb-1">Artigos Vinculados</span>
                            <h3 class="mb-0 fw-bold text-info" id="kpiArtigosTotal"><?= $total_artigos_geral; ?></h3>
                            <span class="text-body-secondary small">Matérias categorizadas</span>
                        </div>
                        <div class="kpi-icon-circle bg-info-subtle text-info">
                            <i class="bi bi-newspaper fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Principal de Categorias -->
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm mb-4 border-0">
                    
                    <div class="card-header bg-transparent py-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="card-title fw-bold mb-0 text-body">
                                <i class="bi bi-folder2-open text-primary me-2"></i>Acervo de Categorias Cadastradas
                            </h5>
                        </div>

                        <!-- Filtros Rápidos (Pílulas) -->
                        <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar Categorias">
                            <button type="button" class="btn btn-outline-secondary active filter-cat-btn" data-filter="all">
                                Todas <span class="badge bg-secondary-subtle text-body ms-1"><?= $total_categorias; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-success filter-cat-btn" data-filter="with-news">
                                Com Artigos <span class="badge bg-success-subtle text-success ms-1"><?= $total_com_artigos; ?></span>
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-cat-btn" data-filter="no-news">
                                Sem Artigos <span class="badge bg-warning-subtle text-warning ms-1"><?= $total_sem_artigos; ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="card-body table-responsive">
                        <table id="tabelaCategorias" class="table table-hover align-middle w-100 table-categories">
                            <thead>
                                <tr>
                                    <th width="45" class="text-center">#</th>
                                    <th>Nome da Categoria</th>
                                    <th>URL Amigável (Slug)</th>
                                    <th width="160" class="text-center">Artigos Vinculados</th>
                                    <th width="140" class="text-center">Otimização SEO</th>
                                    <th width="140" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $seq = 0;
                                foreach ($categories as $cat):
                                    $seq++;
                                    $c_id       = (int)$cat['category_id'];
                                    $c_name     = htmlspecialchars($cat['category_name'] ?? '');
                                    $c_slug     = htmlspecialchars($cat['category_slug'] ?? '');
                                    $c_news_qtd = (int)($cat['total_news'] ?? 0);
                                    $has_news   = ($c_news_qtd > 0);
                                    
                                    $has_seo = !empty(trim($cat['meta_title'] ?? '')) || !empty(trim($cat['meta_description'] ?? ''));
                                ?>
                                <tr id="tr-cat-<?= $c_id; ?>" data-has-news="<?= $has_news ? 'yes' : 'no'; ?>">
                                    <td class="text-center text-body-secondary fw-semibold"><?= $seq; ?></td>

                                    <!-- Nome da Categoria -->
                                    <td>
                                        <div class="fw-bold text-body fs-6 d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                                <i class="bi bi-tag-fill"></i>
                                            </span>
                                            <a href="javascript:void(0)" class="text-decoration-none text-body hover-primary btn-view-cat"
                                               data-id="<?= $c_id; ?>"
                                               data-name="<?= htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-slug="<?= htmlspecialchars($c_slug, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-news="<?= $c_news_qtd; ?>"
                                               data-meta-title="<?= htmlspecialchars($cat['meta_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                               data-meta-kw="<?= htmlspecialchars($cat['meta_keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                               data-meta-desc="<?= htmlspecialchars($cat['meta_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= $c_name; ?>
                                            </a>
                                        </div>
                                    </td>

                                    <!-- Slug / URL Amigável -->
                                    <td>
                                        <?php if (!empty($c_slug)): ?>
                                            <a href="../category.php?slug=<?= urlencode($c_slug); ?>" target="_blank" rel="noopener" class="text-decoration-none" title="Ver artigos desta categoria no site">
                                                <code class="bg-body-secondary px-2 py-1 rounded text-primary border border-secondary-subtle">
                                                    <i class="bi bi-link-45deg me-1"></i>/category/<?= $c_slug; ?>
                                                </code>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-body-secondary small">Sem slug configurado</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Artigos Vinculados -->
                                    <td class="text-center">
                                        <?php if ($has_news): ?>
                                            <a href="news.php" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill text-decoration-none badge-news-count" title="Ver notícias associadas">
                                                <i class="bi bi-newspaper me-1"></i><?= $c_news_qtd; ?> <?= $c_news_qtd === 1 ? 'artigo' : 'artigos'; ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill badge-news-count">
                                                Nenhum artigo
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- SEO & Metadados -->
                                    <td class="text-center">
                                        <?php if ($has_seo): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small" title="Meta tags configuradas para o Google">
                                                <i class="bi bi-check-circle me-1"></i>Configurado
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-body-secondary text-body-secondary border rounded-pill small" title="Sem meta título ou descrição específicos">
                                                Padrão
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ações -->
                                    <td class="text-center text-nowrap">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Ver Detalhes (Modal Rápido) -->
                                            <button type="button" class="btn btn-outline-info btn-sm btn-view-cat"
                                                    data-id="<?= $c_id; ?>"
                                                    data-name="<?= htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-slug="<?= htmlspecialchars($c_slug, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-news="<?= $c_news_qtd; ?>"
                                                    data-meta-title="<?= htmlspecialchars($cat['meta_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-meta-kw="<?= htmlspecialchars($cat['meta_keyword'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-meta-desc="<?= htmlspecialchars($cat['meta_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Visualizar detalhes">
                                                <i class="bi bi-eye"></i>
                                            </button>

                                            <!-- Editar -->
                                            <a href="category-edit.php?id=<?= $c_id; ?>" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Editar categoria">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- Excluir Seguro com SweetAlert2 -->
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-cat"
                                                    data-id="<?= $c_id; ?>"
                                                    data-name="<?= htmlspecialchars($c_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-news="<?= $c_news_qtd; ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Excluir categoria">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: DETALHES DA CATEGORIA               -->
<!-- ========================================== -->
<div class="modal fade" id="modalVisualizarCategoria" tabindex="-1" aria-labelledby="modalVisualizarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body text-body border-0 shadow-lg rounded-3">
            <div class="modal-header bg-body-tertiary py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-pill p-2"><i class="bi bi-tags"></i></span>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="mvcTitulo">Visualizar Categoria</h5>
                        <small class="text-body-secondary">Editoria do Portal</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-body-secondary mb-1">Nome da Categoria</label>
                    <h5 class="fw-bold text-primary mb-0" id="mvcNome"></h5>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-body-secondary mb-1">URL Amigável (Slug)</label>
                    <div>
                        <code class="bg-body-secondary px-2 py-1 rounded text-primary border" id="mvcSlug"></code>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-body-secondary mb-1">Notícias Cadastradas</label>
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fs-6" id="mvcArtigos"></span>
                    </div>
                </div>

                <hr class="opacity-25 my-3">

                <!-- SEO / Metadados -->
                <div class="p-3 bg-body-tertiary rounded-3 border">
                    <h6 class="fw-bold text-body mb-2 small"><i class="bi bi-google text-primary me-1"></i>Informações de SEO</h6>
                    <div class="mb-2">
                        <small class="text-body-secondary d-block">Meta Título:</small>
                        <span class="fw-semibold small" id="mvcMetaTitle">Padrão do sistema</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-body-secondary d-block">Palavras-chave:</small>
                        <span class="small" id="mvcMetaKw">-</span>
                    </div>
                    <div>
                        <small class="text-body-secondary d-block">Meta Descrição:</small>
                        <span class="small" id="mvcMetaDesc">-</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary border-top py-2 d-flex justify-content-between">
                <a href="#" id="mvcLinkSite" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver no Site
                </a>
                <div class="d-flex gap-2">
                    <a href="#" id="mvcLinkEditar" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-pencil-square me-1"></i> Editar
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Variável Global do DataTables
var tabelaCategoriasDT = null;

// 2. Abertura do Modal de Detalhes
window.abrirModalCategoria = function(data) {
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const nomeEl = document.getElementById('mvcNome');
    if (nomeEl) nomeEl.textContent = data.name || 'Categoria';

    const slugEl = document.getElementById('mvcSlug');
    if (slugEl) slugEl.textContent = '/category/' + (data.slug || '');

    const artsEl = document.getElementById('mvcArtigos');
    const qtd = parseInt(data.news || 0);
    if (artsEl) artsEl.innerHTML = `<i class="bi bi-newspaper me-1"></i> ${qtd} ${qtd === 1 ? 'artigo cadastrado' : 'artigos cadastrados'}`;

    const mtEl = document.getElementById('mvcMetaTitle');
    if (mtEl) mtEl.textContent = data.metaTitle || 'Padrão do sistema';

    const kwEl = document.getElementById('mvcMetaKw');
    if (kwEl) kwEl.textContent = data.metaKw || '-';

    const mdEl = document.getElementById('mvcMetaDesc');
    if (mdEl) mdEl.textContent = data.metaDesc || '-';

    const linkSite = document.getElementById('mvcLinkSite');
    if (linkSite) {
        if (data.slug) {
            linkSite.href = '../category.php?slug=' + encodeURIComponent(data.slug);
            linkSite.classList.remove('d-none');
        } else {
            linkSite.classList.add('d-none');
        }
    }

    const linkEdit = document.getElementById('mvcLinkEditar');
    if (linkEdit) linkEdit.href = 'category-edit.php?id=' + (data.id || '');

    const modalEl = document.getElementById('modalVisualizarCategoria');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modalInst = bootstrap.Modal.getOrCreateInstance(modalEl);
        modalInst.show();
    }
};

// 3. Exclusão Segura com SweetAlert2
window.confirmarExclusaoCategoria = function(id, name, newsCount) {
    document.querySelectorAll('.tooltip').forEach(t => t.remove());

    const qtd = parseInt(newsCount || 0);
    let avisoNoticias = '';
    if (qtd > 0) {
        avisoNoticias = `<div class="alert alert-danger py-2 px-3 small mt-2 mb-0 text-start">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <strong>Atenção:</strong> Existem <strong>${qtd}</strong> artigos de notícias vinculados a esta categoria. Ao excluir, todos esses artigos e suas fotos serão removidos permanentemente.
        </div>`;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Excluir Categoria?',
            html: `Deseja realmente remover a categoria <strong>"${name}"</strong>?<br>${avisoNoticias}`,
            icon: qtd > 0 ? 'error' : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Sim, excluir categoria',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-danger px-3 me-2',
                cancelButton: 'btn btn-secondary px-3'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.executarExclusaoCategoria(id);
            }
        });
    } else {
        window.executarExclusaoCategoria(id);
    }
};

window.executarExclusaoCategoria = function(id) {
    fetch('category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            action: 'delete_category',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Categoria Removida!',
                    text: data.message,
                    timer: 1600,
                    showConfirmButton: false
                }).then(() => {
                    const tr = document.getElementById('tr-cat-' + id);
                    if (tr) {
                        if (tabelaCategoriasDT) {
                            tabelaCategoriasDT.row($(tr)).remove().draw();
                        } else {
                            tr.remove();
                        }
                        window.atualizarKPIsCategorias();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                window.location.reload();
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir',
                    text: data.message || 'Não foi possível excluir a categoria.'
                });
            }
        }
    })
    .catch(err => {
        console.error(err);
        window.location.href = 'category-delete.php?id=' + id;
    });
};

// 4. Atualização Dinâmica dos KPIs
window.atualizarKPIsCategorias = function() {
    const rows = document.querySelectorAll('#tabelaCategorias tbody tr[data-has-news]');
    let total = rows.length;
    let comArtigos = 0;
    let semArtigos = 0;

    rows.forEach(r => {
        if (r.getAttribute('data-has-news') === 'yes') comArtigos++;
        else semArtigos++;
    });

    const kpiTotal = document.getElementById('kpiTotal');
    const kpiCom = document.getElementById('kpiComArtigos');
    const kpiSem = document.getElementById('kpiSemArtigos');

    if (kpiTotal) kpiTotal.textContent = total;
    if (kpiCom) kpiCom.textContent = comArtigos;
    if (kpiSem) kpiSem.textContent = semArtigos;
};

// 5. Delegação de Eventos de Clique
document.addEventListener('click', function (e) {
    // A. Visualizar no Modal
    const btnView = e.target.closest('.btn-view-cat');
    if (btnView) {
        e.preventDefault();
        const id        = btnView.getAttribute('data-id');
        const name      = btnView.getAttribute('data-name') || 'Categoria';
        const slug      = btnView.getAttribute('data-slug') || '';
        const news      = btnView.getAttribute('data-news') || '0';
        const metaTitle = btnView.getAttribute('data-meta-title') || '';
        const metaKw    = btnView.getAttribute('data-meta-kw') || '';
        const metaDesc  = btnView.getAttribute('data-meta-desc') || '';
        window.abrirModalCategoria({ id, name, slug, news, metaTitle, metaKw, metaDesc });
        return;
    }

    // B. Excluir Categoria
    const btnDel = e.target.closest('.btn-delete-cat');
    if (btnDel) {
        e.preventDefault();
        const id    = btnDel.getAttribute('data-id');
        const name  = btnDel.getAttribute('data-name') || 'esta categoria';
        const news  = btnDel.getAttribute('data-news') || '0';
        window.confirmarExclusaoCategoria(id, name, news);
        return;
    }

    // C. Filtros em Pílulas
    const filterBtn = e.target.closest('.filter-cat-btn');
    if (filterBtn) {
        e.preventDefault();
        document.querySelectorAll('.filter-cat-btn').forEach(b => b.classList.remove('active'));
        filterBtn.classList.add('active');
        const filter = filterBtn.getAttribute('data-filter');

        if (tabelaCategoriasDT) {
            if (filter === 'all') {
                tabelaCategoriasDT.column(3).search('').draw();
            } else if (filter === 'with-news') {
                tabelaCategoriasDT.column(3).search('artigo').draw();
            } else if (filter === 'no-news') {
                tabelaCategoriasDT.column(3).search('Nenhum artigo').draw();
            }
        }
        return;
    }
});

// 6. Inicialização do DataTables
function initTabelaCategorias() {
    if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.DataTable !== 'undefined') {
        const $table = $('#tabelaCategorias');
        if ($table.length && !$.fn.DataTable.isDataTable('#tabelaCategorias')) {
            tabelaCategoriasDT = $table.DataTable({
                responsive: true,
                language: {
                    search: "Buscar Categoria:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Exibindo _START_ a _END_ de _TOTAL_ categorias",
                    infoEmpty: "Nenhuma categoria cadastrada",
                    infoFiltered: "(filtrado de _MAX_ no total)",
                    zeroRecords: "Nenhuma categoria encontrada",
                    paginate: {
                        first: "Primeira",
                        previous: "Anterior",
                        next: "Próxima",
                        last: "Última"
                    }
                },
                order: [[0, 'asc']]
            });
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTabelaCategorias);
} else {
    initTabelaCategorias();
}
</script>

<?php require_once('footer.php'); ?>
