<?php
// sidebar.php - Barra Lateral Moderna Hospitalar Pronto Saúde
?>
<!-- Sidebar Container Start -->
<div class="ps-blog-sidebar">
    <!-- 1. Campo de Busca de Alta Conversão -->
    <div class="ps-sidebar-card">
        <h5 class="ps-sidebar-title">
            <i class="fas fa-search text-primary me-2"></i> Buscar no Blog
        </h5>
        <form action="<?php echo BASE_URL; ?>search" method="post" class="ps-search-form">
            <input type="text" name="search_string" class="ps-search-input" placeholder="Buscar artigos, notícias e dicas..." required>
            <button type="submit" class="ps-search-btn" aria-label="Pesquisar">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    <!-- 2. Categorias com Contagem Real de Posts -->
    <div class="ps-sidebar-card">
        <h5 class="ps-sidebar-title">
            <i class="fas fa-folder-open text-primary me-2"></i> Categorias
        </h5>
        <ul class="ps-cat-list">
            <?php
            $statement = $pdo->prepare("SELECT c.category_name, c.category_slug, COUNT(n.news_id) AS total_posts 
                                       FROM tbl_category c 
                                       LEFT JOIN tbl_news n ON c.category_id = n.category_id 
                                       GROUP BY c.category_id 
                                       ORDER BY c.category_name ASC");
            $statement->execute();
            $cats = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($cats as $cat) {
                ?>
                <li class="ps-cat-item">
                    <a href="<?php echo BASE_URL; ?>category/<?php echo $cat['category_slug']; ?>">
                        <span><i class="fas fa-angle-right text-muted me-2"></i><?php echo htmlspecialchars($cat['category_name']); ?></span>
                        <span class="ps-cat-badge"><?php echo (int)$cat['total_posts']; ?></span>
                    </a>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>

    <!-- 3. Postagens Recentes com Miniaturas e Datas -->
    <div class="ps-sidebar-card">
        <h5 class="ps-sidebar-title">
            <i class="far fa-clock text-primary me-2"></i> Artigos Recentes
        </h5>
        <div class="ps-recent-list">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY news_id DESC LIMIT 4");
            $statement->execute();
            $recent_posts = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($recent_posts as $post) {
                $post_photo = (!empty($post['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $post['photo']))
                    ? BASE_URL . 'assets/uploads/' . $post['photo']
                    : BASE_URL . 'assets/uploads/department-1.png';
                ?>
                <a href="<?php echo BASE_URL; ?>news/<?php echo $post['news_slug']; ?>" class="ps-recent-item">
                    <img src="<?php echo $post_photo; ?>" alt="<?php echo htmlspecialchars($post['news_title']); ?>" class="ps-recent-thumb">
                    <div class="ps-recent-info">
                        <h6 class="ps-recent-title"><?php echo htmlspecialchars($post['news_title']); ?></h6>
                        <span class="ps-recent-date">
                            <i class="far fa-calendar-alt text-primary"></i> <?php echo htmlspecialchars($post['news_date']); ?>
                        </span>
                    </div>
                </a>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- 4. Postagens Populares (Mais Lidas) -->
    <div class="ps-sidebar-card">
        <h5 class="ps-sidebar-title">
            <i class="fas fa-fire text-danger me-2"></i> Mais Lidos
        </h5>
        <div class="ps-recent-list">
            <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_news ORDER BY total_view DESC LIMIT 4");
            $statement->execute();
            $popular_posts = $statement->fetchAll(PDO::FETCH_ASSOC);							
            foreach ($popular_posts as $pop) {
                $pop_photo = (!empty($pop['photo']) && file_exists(__DIR__ . '/assets/uploads/' . $pop['photo']))
                    ? BASE_URL . 'assets/uploads/' . $pop['photo']
                    : BASE_URL . 'assets/uploads/department-1.png';
                ?>
                <a href="<?php echo BASE_URL; ?>news/<?php echo $pop['news_slug']; ?>" class="ps-recent-item">
                    <img src="<?php echo $pop_photo; ?>" alt="<?php echo htmlspecialchars($pop['news_title']); ?>" class="ps-recent-thumb">
                    <div class="ps-recent-info">
                        <h6 class="ps-recent-title"><?php echo htmlspecialchars($pop['news_title']); ?></h6>
                        <span class="ps-recent-date">
                            <i class="fas fa-eye text-primary"></i> <?php echo (int)($pop['total_view'] ?? 0); ?> visualizações
                        </span>
                    </div>
                </a>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- 5. Card de Agendamento Rápido no WhatsApp -->
    <?php
    $sidebar_zap = !empty($contact_phone) ? preg_replace('/[^0-9]/', '', $contact_phone) : '5521985831464';
    if (strlen($sidebar_zap) <= 11 && substr($sidebar_zap, 0, 2) !== '55') {
        $sidebar_zap = '55' . $sidebar_zap;
    }
    ?>
    <div class="ps-sidebar-cta">
        <div class="ps-sidebar-cta-icon">
            <i class="fas fa-user-md"></i>
        </div>
        <h4 class="ps-sidebar-cta-title">Precisa de Atendimento?</h4>
        <p class="ps-sidebar-cta-desc">Agende sua consulta ou exame com nossa equipe médica em poucos minutos.</p>
        <a href="https://wa.me/<?php echo $sidebar_zap; ?>?text=<?php echo urlencode('Olá! Li o blog da Pronto Saúde e gostaria de agendar uma consulta ou exame.'); ?>" target="_blank" rel="noopener noreferrer" class="ps-sidebar-cta-btn">
            <i class="fab fa-whatsapp fs-5"></i> Agendar pelo WhatsApp
        </a>
    </div>
</div>
<!-- Sidebar Container End -->