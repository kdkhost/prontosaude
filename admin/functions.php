<?php

// Funções utilitárias globais do Pronto Saúde

if (!function_exists('formata_mes_ano')) {
    /**
     * Formata data para o formato "Mês de Ano" em português sem depender de strftime (compatível com PHP 8.4+)
     */
    function formata_mes_ano($data) {
        if (empty($data)) return '';
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $ts = strtotime($data);
        if (!$ts) return (string)$data;
        $m = (int)date('n', $ts);
        $y = date('Y', $ts);
        return ($meses[$m] ?? '') . ' de ' . $y;
    }
}

if (!function_exists('sanitize_output')) {
    function sanitize_output($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('gerar_slug')) {
    /**
     * Gera slug limpo e amigável em português para URLs (compatível com PHP 8.4+)
     */
    function gerar_slug($texto) {
        if (empty($texto)) return '';
        // Converte acentos
        if (function_exists('transliterator_transliterate')) {
            $trans = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', (string)$texto);
            if ($trans !== false) {
                $texto = $trans;
            }
        }
        $texto = preg_replace('~[^\pL\d]+~u', '-', (string)$texto);
        if (function_exists('iconv')) {
            $conv = @iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $texto);
            if ($conv !== false) {
                $texto = $conv;
            }
        }
        $texto = preg_replace('~[^-\w]+~', '', $texto);
        $texto = trim($texto, '-');
        $texto = preg_replace('~-+~', '-', $texto);
        $texto = strtolower($texto);
        return $texto ?: 'item-' . time();
    }
}

if (!function_exists('pronto_get_system_pages')) {
    /**
     * Retorna o catálogo canônico de páginas institucionais nativas do Pronto Saúde
     */
    function pronto_get_system_pages() {
        return [
            [
                'slug' => 'doctor',
                'name' => 'Corpo Clínico & Médicos',
                'layout' => 'Doctor Page Layout',
                'banner' => 'about.jpg',
                'url' => 'doctor.php',
                'title' => 'Corpo Clínico & Médicos Especialistas | Pronto Saúde',
                'desc' => 'Conheça nossa equipe de médicos e especialistas dedicados a cuidar da sua saúde integral com atendimento humanizado.',
                'kw' => 'medicos, especialistas, cardiologia, pediatria, clinicos, pronto saude'
            ],
            [
                'slug' => 'servicos',
                'name' => 'Nossos Serviços Médicos',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'servicos.php',
                'title' => 'Nossos Serviços Médicos | Pronto Saúde',
                'desc' => 'Conheça nossos serviços médicos, exames laboratoriais, consultas e procedimentos especializados.',
                'kw' => 'servicos medicos, exames laboratoriais, consultas, clinica rj'
            ],
            [
                'slug' => 'fidelidade',
                'name' => 'Planos & Cartão Fidelidade',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'fidelidade.php',
                'title' => 'Cartão Fidelidade | Pronto Saúde',
                'desc' => 'Conheça nossos Planos Fidelidade e aproveite descontos e benefícios exclusivos em consultas e exames.',
                'kw' => 'cartao fidelidade, planos de saude, convenio, desconto consultas'
            ],
            [
                'slug' => 'odontologia',
                'name' => 'Odontologia Especializada',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'odontologia.php',
                'title' => 'Odontologia Especializada | Pronto Saúde',
                'desc' => 'Oferecemos tratamentos ortodônticos avançados, próteses, clareamento e estética dental.',
                'kw' => 'dentista, ortodontia, clareamento, aparelhos, odonto rj'
            ],
            [
                'slug' => 'news',
                'name' => 'Notícias & Blog de Saúde',
                'layout' => 'Blog Page Layout',
                'banner' => 'about.jpg',
                'url' => 'news.php',
                'title' => 'Notícias & Blog de Saúde | Pronto Saúde',
                'desc' => 'Artigos informativos, dicas de bem-estar, prevenção e novidades da medicina.',
                'kw' => 'blog saude, dicas de saude, medicina, noticias medicas'
            ],
            [
                'slug' => 'trabalhe-conosco',
                'name' => 'Trabalhe Conosco',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'trabalhe-conosco.php',
                'title' => 'Trabalhe Conosco | Pronto Saúde',
                'desc' => 'Faça parte da nossa equipe médica e administrativa. Confira oportunidades abertas.',
                'kw' => 'vagas saude, empregos clinica, trabalhe conosco rj'
            ],
            [
                'slug' => 'convenio',
                'name' => 'Convênio Livre Escolha',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'convenio.php',
                'title' => 'Convênio Livre Escolha | Pronto Saúde',
                'desc' => 'Atendimento por sistema de livre escolha com reembolso e facilidades para conveniados de diversos planos.',
                'kw' => 'convenio, livre escolha, reembolso saude, planos'
            ],
            [
                'slug' => 'privacidade',
                'name' => 'Política de Privacidade',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'privacidade.php',
                'title' => 'Política de Privacidade | Pronto Saúde',
                'desc' => 'Conheça como tratamos e protegemos seus dados pessoais de acordo com a LGPD.',
                'kw' => 'privacidade, lgpd, termos, dados pessoais'
            ],
            [
                'slug' => 'rsocial',
                'name' => 'Responsabilidade Social',
                'layout' => 'Full Width Page Layout',
                'banner' => 'about.jpg',
                'url' => 'rsocial.php',
                'title' => 'Responsabilidade Social | Pronto Saúde',
                'desc' => 'Conheça nossas ações e projetos sociais voltados ao bem-estar da comunidade.',
                'kw' => 'responsabilidade social, comunidade, acoes sociais'
            ]
        ];
    }
}

if (!function_exists('pronto_sync_system_pages')) {
    /**
     * Sincroniza e garante a existência de todas as páginas nativas em tbl_page
     */
    function pronto_sync_system_pages($pdo) {
        if (!$pdo) return;
        static $synced = false;
        if ($synced) return;
        $synced = true;

        $systemPages = pronto_get_system_pages();
        foreach ($systemPages as $sp) {
            try {
                $stmt = $pdo->prepare("SELECT page_id FROM tbl_page WHERE page_slug = ? OR page_slug = ? LIMIT 1");
                $stmt->execute([$sp['slug'], $sp['slug'] . '.php']);
                $exists = $stmt->fetchColumn();

                if (!$exists) {
                    $insert = $pdo->prepare("INSERT INTO tbl_page (page_name, page_slug, page_content, page_layout, banner, status, meta_title, meta_keyword, meta_description) VALUES (?, ?, '', ?, ?, 'Active', ?, ?, ?)");
                    $insert->execute([
                        $sp['name'],
                        $sp['slug'],
                        $sp['layout'],
                        $sp['banner'],
                        $sp['title'],
                        $sp['kw'],
                        $sp['desc']
                    ]);
                }
            } catch (Exception $e) {
                // Silencioso em caso de restrição momentânea
            }
        }
    }
}

if (!function_exists('pronto_is_page_active')) {
    /**
     * Verifica de forma segura e com cache em memória se uma página está ativa
     */
    function pronto_is_page_active($slug_or_url, $pdo = null) {
        if ($pdo === null) {
            global $pdo;
        }
        if (!$pdo) return true;

        static $statusMap = null;
        if ($statusMap === null) {
            $statusMap = [];
            try {
                $st = $pdo->query("SELECT page_slug, status FROM tbl_page");
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $clean_slug = strtolower(trim(str_replace('.php', '', $row['page_slug'])));
                    $statusMap[$clean_slug] = ($row['status'] === 'Active');
                }
            } catch (Exception $e) {
                // Fallback silencioso
            }
        }

        if (empty($slug_or_url)) return true;

        $clean = strtolower(trim((string)$slug_or_url));
        $clean = str_replace(['http://', 'https://'], '', $clean);
        if (strpos($clean, '/') !== false) {
            $parts = explode('/', $clean);
            $clean = end($parts);
        }
        $clean = strtok($clean, '?');
        $clean = str_replace('.php', '', $clean);

        if (isset($statusMap[$clean])) {
            return $statusMap[$clean];
        }

        return true;
    }
}

if (!function_exists('pronto_is_menu_active')) {
    /**
     * Valida se um item de menu deve ser exibido baseado no status da página de destino
     */
    function pronto_is_menu_active($menu_item, $pdo = null) {
        if (empty($menu_item)) return true;

        $type = $menu_item['menu_type'] ?? '';
        if ($type === 'Page') {
            $slug = $menu_item['category_or_page_slug'] ?? '';
            return pronto_is_page_active($slug, $pdo);
        } elseif ($type === 'Other') {
            $url = $menu_item['menu_url'] ?? '';
            if (empty($url) || $url === '#' || strpos($url, 'wa.me') !== false || strpos($url, 'whatsapp') !== false) {
                return true;
            }
            return pronto_is_page_active($url, $pdo);
        }
        return true;
    }
}