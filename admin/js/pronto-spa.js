/**
 * Pronto Saúde - Motor SPA (Single Page Application) Desacoplado
 * Navegação e Submissão 100% via AJAX sem refresh e sem piscar tela
 * AdminLTE 4 + Bootstrap 5.3 + PHP 8.4
 */

(function () {
  'use strict';

  // 1. BARRA DE PROGRESSO NO TOPO
  const progressBar = document.createElement('div');
  progressBar.id = 'ps-spa-progressbar';
  progressBar.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #0a6b74, #10b981, #06b6d4);
    z-index: 99999;
    transition: width 0.3s ease, opacity 0.3s ease;
    opacity: 0;
    box-shadow: 0 0 10px rgba(10, 107, 116, 0.7);
  `;
  document.body.appendChild(progressBar);

  let progressInterval = null;

  function startProgress() {
    clearInterval(progressInterval);
    progressBar.style.opacity = '1';
    progressBar.style.width = '15%';
    let w = 15;
    progressInterval = setInterval(() => {
      if (w < 85) {
        w += Math.random() * 12;
        progressBar.style.width = Math.min(w, 85) + '%';
      }
    }, 150);
  }

  function finishProgress() {
    clearInterval(progressInterval);
    progressBar.style.width = '100%';
    setTimeout(() => {
      progressBar.style.opacity = '0';
      setTimeout(() => {
        progressBar.style.width = '0%';
      }, 300);
    }, 200);
  }

  // 2. CICLO DE RE-INICIALIZAÇÃO DE COMPONENTES
  function reinitAllComponents(container) {
    container = container || document.querySelector('.app-main') || document;

    // A. Tooltips Bootstrap 5
    if (window.bootstrap && window.bootstrap.Tooltip) {
      const tooltipTriggerList = container.querySelectorAll('[data-bs-toggle="tooltip"], [title]:not([data-bs-toggle])');
      tooltipTriggerList.forEach(el => {
        try {
          if (!bootstrap.Tooltip.getInstance(el)) {
            new bootstrap.Tooltip(el, {
              boundary: document.body,
              delay: { show: 100, hide: 50 }
            });
          }
        } catch (e) {}
      });
    }

    // B. DataTables em Português com Extensão Responsiva (Estilo AdminLTE 3 / 4)
    if (window.jQuery && jQuery.fn.dataTable) {
      const tables = container.querySelectorAll(
        'table.table:not(.dataTable):not(.no-datatable):not(.ps-datatable-init), ' +
        '#example1:not(.ps-datatable-init), #example2:not(.ps-datatable-init), ' +
        'table.table-bordered:not(.dataTable):not(.no-datatable):not(.ps-datatable-init), ' +
        'table.table-striped:not(.dataTable):not(.no-datatable):not(.ps-datatable-init), ' +
        'table.table-datatable:not(.dataTable):not(.no-datatable):not(.ps-datatable-init)'
      );
      tables.forEach(tbl => {
        try {
          if (jQuery.fn.DataTable.isDataTable(tbl) || tbl.classList.contains('ps-datatable-init') || tbl.classList.contains('dataTable')) {
            return;
          }
          tbl.classList.add('ps-datatable-init');
          tbl.classList.add('dt-responsive', 'nowrap');
          tbl.style.width = '100%';

          // Permite ao DataTables Responsive acionar os breakpoints sem corte de scroll
          const parentResponsive = tbl.closest('.table-responsive');
          if (parentResponsive) {
            parentResponsive.style.overflowX = 'clip';
            parentResponsive.style.overflowY = 'visible';
          }

          const dtInstance = jQuery(tbl).DataTable({
            language: {
              sEmptyTable: "Nenhum registro encontrado",
              sInfo: "Mostrando de _START_ até _END_ de _TOTAL_ registros",
              sInfoEmpty: "Mostrando 0 até 0 de 0 registros",
              sInfoFiltered: "(Filtrados de _MAX_ registros)",
              sLengthMenu: "_MENU_ registros por página",
              sLoadingRecords: "Carregando...",
              sProcessing: "Processando...",
              sZeroRecords: "Nenhum registro encontrado",
              sSearch: "Pesquisar:",
              oPaginate: {
                sNext: "Próximo",
                sPrevious: "Anterior",
                sFirst: "Primeiro",
                sLast: "Último"
              },
              oAria: {
                sSortAscending: ": Ordenar colunas de forma ascendente",
                sSortDescending: ": Ordenar colunas de forma descendente"
              }
            },
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            columnDefs: [
              { responsivePriority: 1, targets: 0 },
              { responsivePriority: 2, targets: -1 }
            ]
          });

          setTimeout(() => {
            try { dtInstance.columns.adjust().responsive.recalc(); } catch (e) {}
          }, 120);
        } catch (e) {}
      });
    }

    // C. Summernote Universal como padrão em todos os textareas (Blindado contra Duplicação)
    if (window.jQuery && jQuery.fn.summernote) {
      try {
        if (!window.jQuery.fn.tooltip) {
          window.jQuery.fn.tooltip = function () { return this; };
        }

        // Seletor restritivo: NUNCA seleciona textareas internos do summernote nem campos de SEO/Metadados/Código
        const textareas = container.querySelectorAll(
          'textarea:not(.note-codable):not(.no-summernote):not(.no-editor):not(.font-monospace):not([data-no-summernote]):not([data-no-editor]):not([name*="meta"]):not([name*="keyword"]):not([name*="slug"])'
        );

        textareas.forEach(ed => {
          try {
            // Ignora campos de metadados por segurança adicional
            const fieldName = (ed.getAttribute('name') || '').toLowerCase();
            const fieldId = (ed.getAttribute('id') || '').toLowerCase();
            if (fieldName.includes('meta') || fieldName.includes('keyword') || fieldId.includes('meta') || fieldId.includes('seo')) {
              return;
            }

            const $ed = jQuery(ed);

            // Guarda de segurança absoluta anti-duplicação:
            // 1. Se já tem classe de inicialização
            // 2. Se já tem instância de summernote no jQuery.data
            // 3. Se está dentro de uma .note-editor (codeview ou plugin interno)
            // 4. Se já possui um irmão .note-editor ao lado
            if (
              $ed.hasClass('ps-summernote-init') ||
              $ed.data('summernote') ||
              $ed.closest('.note-editor').length > 0 ||
              $ed.siblings('.note-editor').length > 0 ||
              $ed.next('.note-editor').length > 0
            ) {
              return;
            }

            // Pula campos técnicos que não devem ter HTML rico (código iframe, listas brutas de IP, meta tags SEO)
            const name = ($ed.attr('name') || '').toLowerCase();
            const id = ($ed.attr('id') || '').toLowerCase();
            if (
              name.includes('iframe') || 
              name.includes('ip_') || 
              name.includes('meta_') || 
              id.includes('iframe') || 
              id.includes('meta') || 
              $ed.hasClass('font-monospace')
            ) {
              return;
            }

            // Marca imediatamente para evitar corridas assíncronas
            $ed.addClass('ps-summernote-init');

            // Configuração dinâmica de altura baseada em rows ou padrão
            const rows = parseInt($ed.attr('rows') || '5', 10);
            const calculatedHeight = Math.min(450, Math.max(180, rows * 36));

            $ed.summernote({
              tabsize: 2,
              height: calculatedHeight,
              lang: 'pt-BR',
              placeholder: $ed.attr('placeholder') || 'Escreva o conteúdo aqui...',
              dialogsInBody: true,
              toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
              ],
              callbacks: {
                onChange: function(contents) {
                  $ed.val(contents);
                }
              }
            });
          } catch (e) {}
        });
      } catch (e) {}
    }

    // D. Select2 com Guarda Anti-Duplicação
    if (window.jQuery && jQuery.fn.select2) {
      try {
        jQuery(container).find('.select2:not(.select2-hidden-accessible):not(.ps-select2-init)').each(function() {
          const $sel = jQuery(this);
          if ($sel.hasClass('select2-hidden-accessible') || $sel.hasClass('ps-select2-init') || $sel.data('select2')) {
            return;
          }
          $sel.addClass('ps-select2-init');
          $sel.select2({
            width: '100%',
            language: 'pt-BR'
          });
        });
      } catch (e) {}
    }

    // E. Datepicker com Guarda Anti-Duplicação
    if (window.jQuery && jQuery.fn.datepicker) {
      try {
        jQuery(container).find('#datepicker:not(.ps-datepicker-init), #datepicker1:not(.ps-datepicker-init)').each(function() {
          const $dp = jQuery(this);
          if ($dp.hasClass('ps-datepicker-init') || $dp.data('datepicker')) return;
          $dp.addClass('ps-datepicker-init');
          $dp.datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayBtn: 'linked'
          });
        });
      } catch (e) {}
    }

    // F. Drag and Drop de Imagens com Preview
    if (window.initImageDropzones) {
      window.initImageDropzones(container);
    }

    // G. Gerador Automático de Slugs com Bloqueio Manual
    if (window.initSlugAutoGenerator) {
      window.initSlugAutoGenerator(container);
    }

    // H. Máscaras Universais e ViaCEP com Autofocus
    if (window.initUniversalMasks) {
      window.initUniversalMasks(container);
    }

    // I. Painel de Segurança e WAF
    if (window.initSecurityPanel) {
      window.initSecurityPanel(container);
    }

    // J. ApexCharts (se estiver na página inicial/dashboard)
    if (container.querySelector('#chart-indicadores') && window.renderDashboardCharts) {
      const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
      window.renderDashboardCharts(isDark);
    }

    // K. Módulo de Otimização & SEO Completo
    if (window.initSEOModule && (container.querySelector('#seoPagesList') || container.querySelector('#formSEO'))) {
      const seoList = container.querySelector('#seoPagesList');
      if (seoList) seoList.dataset.seoInit = 'false';
      window.initSEOModule(container);
    }

    // K. Restauração de Tabs ativas via Hash
    restoreTabFromHash(container);

    // L. Notificações Toastr a partir de Callouts (Converte qualquer mensagem em Toastr e remove da tela)
    let hadCalloutNotification = false;
    const callouts = container.querySelectorAll('.callout, .alert:not(.alert-keep):not(.alert-info)');
    callouts.forEach(c => {
      const txt = c.innerText.trim();
      if (txt) {
        hadCalloutNotification = true;
        if (c.classList.contains('callout-success') || c.classList.contains('alert-success')) {
          window.notificar && window.notificar('success', txt, 'Sucesso!');
        } else if (c.classList.contains('callout-danger') || c.classList.contains('alert-danger')) {
          window.notificar && window.notificar('error', txt, 'Atenção!');
        } else if (c.classList.contains('callout-warning') || c.classList.contains('alert-warning')) {
          window.notificar && window.notificar('warning', txt, 'Alerta');
        }
      }
      // Remove o elemento visual da página para não duplicar na tela
      c.remove();
    });
    return hadCalloutNotification;
  }

  window.reinitAllComponents = reinitAllComponents;

  // 3. ATUALIZAÇÃO DO MENU LATERAL ATIVO COM MAPEAMENTO TREEVIEW E ACORDEÃO
  function updateActiveSidebar(url) {
    if (!url) return;
    const filename = url.split('/').pop().split('?')[0];

    // Mapeia páginas de adição/edição para o arquivo base da sidebar
    let targetFile = filename;
    if (targetFile.includes('-add.php') || targetFile.includes('-edit.php') || targetFile.includes('-update.php')) {
      targetFile = targetFile.replace('-add.php', '.php').replace('-edit.php', '.php').replace('-update.php', '.php');
    }
    if (targetFile === 'vagas.php' || filename === 'vagas-add.php' || filename === 'vagas-edit.php') {
      targetFile = 'trabalhe-conosco.php';
    }
    if (filename === 'subscriber-csv.php') {
      targetFile = 'subscriber.php';
    }

    const sidebar = document.querySelector('.sidebar-menu');
    if (!sidebar) return;

    const sidebarLinks = sidebar.querySelectorAll('a.nav-link');

    // Remove active anterior e fecha menus para modo acordeão
    sidebarLinks.forEach(link => link.classList.remove('active'));
    sidebar.querySelectorAll('.nav-item.menu-open').forEach(item => {
      item.classList.remove('menu-open');
    });

    let matchedLink = null;

    sidebarLinks.forEach(link => {
      const href = link.getAttribute('href') || '';
      const linkFilename = href.split('/').pop().split('?')[0];

      if (linkFilename && (linkFilename === filename || linkFilename === targetFile)) {
        matchedLink = link;
        link.classList.add('active');

        // Se estiver dentro de um treeview, abre o treeview e ativa o menu pai
        const parentTreeview = link.closest('.nav-treeview');
        if (parentTreeview) {
          const parentNavItem = parentTreeview.closest('.nav-item');
          if (parentNavItem) {
            parentNavItem.classList.add('menu-open');
            const parentLink = parentNavItem.querySelector(':scope > a.nav-link');
            if (parentLink) parentLink.classList.add('active');
          }
        }
      }
    });

    // Se encontrou o link ativo, garante visibilidade suave dentro do scroll da sidebar
    if (matchedLink) {
      setTimeout(() => {
        try {
          matchedLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (e) {}
      }, 100);
    }
  }

  // Suporte ao clique manual para alternar menus treeview com MODO ACORDEÃO
  document.addEventListener('click', function(e) {
    const treeviewToggle = e.target.closest('.sidebar-menu .nav-item.has-treeview > a.nav-link, .sidebar-menu .nav-item > a.nav-link[href="#"]');
    if (!treeviewToggle) return;

    const href = treeviewToggle.getAttribute('href');
    if (href && href !== '#' && href !== 'javascript:;') return;

    e.preventDefault();
    e.stopPropagation();
    const parentNavItem = treeviewToggle.closest('.nav-item');
    if (!parentNavItem) return;

    const isAlreadyOpen = parentNavItem.classList.contains('menu-open');
    const menuRoot = parentNavItem.closest('.sidebar-menu');

    // Comportamento ACORDEÃO: Fecha todos os outros nav-items de primeiro nível
    if (menuRoot) {
      const openSiblings = menuRoot.querySelectorAll(':scope > .nav-item.menu-open');
      openSiblings.forEach(item => {
        if (item !== parentNavItem) {
          item.classList.remove('menu-open');
          const itemLink = item.querySelector(':scope > a.nav-link');
          if (itemLink && !item.querySelector('.nav-treeview a.nav-link.active')) {
            itemLink.classList.remove('active');
          }
        }
      });
    }

    if (isAlreadyOpen) {
      parentNavItem.classList.remove('menu-open');
      if (!parentNavItem.querySelector('.nav-treeview a.nav-link.active')) {
        treeviewToggle.classList.remove('active');
      }
    } else {
      parentNavItem.classList.add('menu-open');
    }
  });

  // 4. CARREGADOR DE PÁGINA SPA VIA AJAX
  function loadPageSPA(url, pushState = true) {
    startProgress();

    const mainContainer = document.querySelector('.app-main');
    if (mainContainer) {
      mainContainer.style.transition = 'opacity 0.2s ease';
      mainContainer.style.opacity = '0.35';
    }

    fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-AdminLTE-SPA': '1'
      }
    })
    .then(response => {
      // Se redirecionar para fora ou login
      if (response.redirected && response.url.includes('login.php')) {
        window.location.href = response.url;
        return;
      }
      return response.text().then(html => ({ html, finalUrl: response.url || url }));
    })
    .then(data => {
      if (!data) return;
      const { html, finalUrl } = data;

      // Cria um DOM virtual para parsear
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      // Extrai título
      const newTitle = doc.querySelector('title') ? doc.querySelector('title').innerText : document.title;
      document.title = newTitle;

      // Extrai o conteúdo principal (.app-main ou .content-wrapper ou fallback)
      let newContent = doc.querySelector('.app-main');
      if (!newContent) {
        newContent = doc.querySelector('.content-wrapper');
      }
      if (!newContent) {
        newContent = doc.querySelector('body');
      }

      if (mainContainer && newContent) {
        mainContainer.innerHTML = newContent.innerHTML;
        mainContainer.style.opacity = '1';

        // Executa scripts inline específicos da nova página carregada via SPA
        const inlineScripts = mainContainer.querySelectorAll('script');
        inlineScripts.forEach(oldScript => {
          if (!oldScript.src) {
            try {
              const newScript = document.createElement('script');
              Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
              newScript.appendChild(document.createTextNode(oldScript.innerHTML));
              oldScript.parentNode.replaceChild(newScript, oldScript);
            } catch (e) {}
          }
        });
      }

      // Atualiza URL no histórico
      if (pushState) {
        window.history.pushState({ url: finalUrl }, newTitle, finalUrl);
      }

      // Atualiza sidebar ativa
      updateActiveSidebar(finalUrl);

      // Re-executa inicializações de plugins
      reinitAllComponents(mainContainer);

      // Rola para o topo suavemente
      window.scrollTo({ top: 0, behavior: 'smooth' });

      finishProgress();
    })
    .catch(error => {
      console.error('Erro no carregamento SPA:', error);
      finishProgress();
      if (mainContainer) mainContainer.style.opacity = '1';
      window.location.href = url; // Fallback para carregamento tradicional seguro
    });
  }

  window.loadPageSPA = loadPageSPA;

  // 5. INTERCEPTADOR GLOBAL DE CLIQUES EM LINKS INTERNOS
  document.addEventListener('click', function (e) {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('https://wa.me')) return;

    // Ignora links que controlam abas (tabs / pills) ou componentes dinâmicos
    if (
      link.hasAttribute('data-bs-toggle') ||
      link.hasAttribute('data-toggle') ||
      link.getAttribute('role') === 'tab' ||
      link.closest('.nav-tabs') ||
      link.closest('.nav-pills') ||
      link.closest('.card-outline-tabs') ||
      link.closest('.nav-tabs-custom')
    ) {
      return;
    }

    // Ignora links que abrem nova aba
    if (link.getAttribute('target') === '_blank' || link.getAttribute('target') === 'new') return;

    // Ignora botões de logout ou exclusão
    if (href.includes('logout.php') || href.includes('-delete.php') || link.getAttribute('data-href')) return;

    // Ignora links de download
    if (href.endsWith('.pdf') || href.endsWith('.zip') || href.endsWith('.csv') || href.endsWith('.xlsx')) return;

    // Apenas links internos dentro do admin
    const currentHost = window.location.host;
    const urlObj = new URL(href, window.location.href);

    if (urlObj.host === currentHost && urlObj.pathname.includes('/admin/')) {
      e.preventDefault();
      loadPageSPA(urlObj.href, true);
    }
  });

  // 6. INTERCEPTADOR GLOBAL DE SUBMISSÃO DE FORMULÁRIOS VIA AJAX
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form || form.nodeName !== 'FORM') return;

    // Se o evento já foi tratado e cancelado por um script específico do formulário, não intercepta
    if (e.defaultPrevented) return;

    // Permite que formulários com data-no-spa ou data-ajax sejam submetidos normalmente pelo seu próprio handler
    if (form.getAttribute('data-no-spa') === 'true' || form.classList.contains('no-spa') || form.getAttribute('data-ajax') === 'true') {
      return;
    }

    // Apenas formulários dentro do painel
    const formAction = form.getAttribute('action') || window.location.href;
    const urlObj = new URL(formAction, window.location.href);

    if (urlObj.pathname.includes('/admin/')) {
      e.preventDefault();
      startProgress();

      const submitBtn = e.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
      let originalBtnHtml = '';
      if (submitBtn) {
        originalBtnHtml = submitBtn.innerHTML || submitBtn.value;
        submitBtn.disabled = true;
        if (submitBtn.tagName === 'INPUT') {
          submitBtn.value = 'Salvando...';
        } else {
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Salvando...';
        }
      }

      // Sincroniza qualquer editor Summernote com o textarea antes de empacotar o FormData
      if (window.jQuery && jQuery.fn.summernote) {
        jQuery(form).find('textarea.ps-summernote-init').each(function () {
          const $ta = jQuery(this);
          try {
            if ($ta.data('summernote')) {
              $ta.val($ta.summernote('code'));
            }
          } catch (e) {}
        });
      }

      const formData = new FormData(form);
      // Se houver submitter com nome/valor, anexa
      if (e.submitter && e.submitter.name) {
        formData.append(e.submitter.name, e.submitter.value || '');
      }

      fetch(urlObj.href, {
        method: form.method || 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-AdminLTE-SPA': '1'
        }
      })
      .then(response => {
        if (response.redirected && response.url.includes('login.php')) {
          window.location.href = response.url;
          return;
        }

        const contentType = response.headers.get('content-type') || '';
        const isJsonHeader = contentType.includes('application/json');

        return response.text().then(text => {
          let jsonData = null;
          let isJson = isJsonHeader;

          if (!isJson) {
            const trimmed = text.trim();
            if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
              try {
                jsonData = JSON.parse(trimmed);
                isJson = true;
              } catch (parseErr) {}
            }
          } else {
            try {
              jsonData = JSON.parse(text);
            } catch (parseErr) {}
          }

          return { isJson, jsonData, html: text, finalUrl: response.url || urlObj.href };
        });
      })
      .then(result => {
        if (!result) return;
        const { isJson, jsonData, html, finalUrl } = result;

        finishProgress();

        if (submitBtn) {
          submitBtn.disabled = false;
          if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = originalBtnHtml;
          } else {
            submitBtn.innerHTML = originalBtnHtml;
          }
        }

        // Se a resposta for JSON, nunca tenta injetar no DOM (evita tela em branco com JSON cru)
        if (isJson && jsonData) {
          const isSuccess = jsonData.success !== false && jsonData.status !== 'error';
          const msg = jsonData.message || (isSuccess ? 'Dados atualizados com sucesso!' : 'Erro ao processar requisição.');
          if (window.notificar) {
            window.notificar(isSuccess ? 'success' : 'error', msg, isSuccess ? 'Salvo com Sucesso' : 'Atenção');
          }

          if (jsonData.redirect) {
            if (window.loadPageSPA) {
              window.loadPageSPA(jsonData.redirect, true);
            } else {
              window.location.href = jsonData.redirect;
            }
          }
          return;
        }

        // Se a resposta for HTML normal, atualiza a view sem piscar
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newTitle = doc.querySelector('title') ? doc.querySelector('title').innerText : document.title;
        document.title = newTitle;

        let newContent = doc.querySelector('.app-main') || doc.querySelector('.content-wrapper');
        const mainContainer = document.querySelector('.app-main');

        if (mainContainer && newContent) {
          mainContainer.innerHTML = newContent.innerHTML;

          const inlineScripts = mainContainer.querySelectorAll('script');
          inlineScripts.forEach(oldScript => {
            if (!oldScript.src) {
              try {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
              } catch (e) {}
            }
          });
        }

        window.history.pushState({ url: finalUrl }, newTitle, finalUrl);
        updateActiveSidebar(finalUrl);
        const hadCallout = reinitAllComponents(mainContainer);
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Se nenhum callout ou alert específico foi renderizado pelo backend, emite notificação padrão
        const hasAlertOnPage = mainContainer ? (mainContainer.querySelector('.alert-success, .callout-success, .alert-danger, .callout-danger') !== null) : false;
        if (!hadCallout && !hasAlertOnPage && window.notificar) {
          window.notificar('success', 'Dados atualizados com sucesso!', 'Salvo!');
        }
      })
      .catch(err => {
        console.error('Erro no envio AJAX do formulário:', err);
        finishProgress();
        if (submitBtn) {
          submitBtn.disabled = false;
          if (submitBtn.tagName === 'INPUT') {
            submitBtn.value = originalBtnHtml;
          } else {
            submitBtn.innerHTML = originalBtnHtml;
          }
        }
        if (window.notificar) {
          window.notificar('error', 'Ocorreu um problema ao salvar os dados. Tente novamente.', 'Falha');
        }
      });
    }
  });

  // 7. SUPORTE A HISTÓRICO DO NAVEGADOR (BACK/FORWARD)
  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.url) {
      loadPageSPA(e.state.url, false);
    } else {
      loadPageSPA(window.location.href, false);
    }
  });

  // 8. INICIALIZAÇÃO INICIAL APÓS O CARREGAMENTO DA PÁGINA
  document.addEventListener('DOMContentLoaded', function () {
    // Guarda o estado inicial
    window.history.replaceState({ url: window.location.href }, document.title, window.location.href);
    reinitAllComponents(document);
  });

  // 9. FUNÇÃO UNIVERSAL PARA ALTERNAR ABAS DE FORMA SÍNCRONA E DEFINITIVA
  function switchTab(tabTrigger) {
    if (!tabTrigger) return false;

    const targetSelector = tabTrigger.getAttribute('data-bs-target') || 
                           tabTrigger.getAttribute('data-target') || 
                           tabTrigger.getAttribute('href');

    if (!targetSelector || !targetSelector.startsWith('#')) return false;

    // Encontra o painel alvo
    const targetPane = document.querySelector(targetSelector);
    if (!targetPane) return false;

    // 1. Encontra a lista de abas (container dos botões/links)
    const navList = tabTrigger.closest('.nav-tabs, .nav-pills, .nav, .nav-tabs-custom') || 
                    tabTrigger.parentElement.parentElement;

    if (navList) {
      // Desativa todos os botões/links do grupo
      navList.querySelectorAll('.nav-link, button, a, li, .nav-item').forEach(el => {
        el.classList.remove('active');
        if (el.tagName === 'BUTTON' || el.tagName === 'A') {
          el.setAttribute('aria-selected', 'false');
        }
      });
    }

    // 2. Ativa o botão/link clicado e o li pai se existir
    tabTrigger.classList.add('active');
    tabTrigger.setAttribute('aria-selected', 'true');
    const parentLi = tabTrigger.closest('li, .nav-item');
    if (parentLi) parentLi.classList.add('active');

    // 3. Localiza e desativa todos os painéis irmãos
    const tabContent = targetPane.closest('.tab-content') || targetPane.parentElement;
    const cardScope = tabTrigger.closest('.card, .card-outline-tabs, .nav-tabs-custom');

    if (tabContent) {
      tabContent.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active', 'show');
        pane.style.setProperty('display', 'none', 'important');
        pane.style.setProperty('opacity', '0', 'important');
      });
    }

    if (cardScope) {
      cardScope.querySelectorAll('.tab-pane').forEach(pane => {
        if (pane !== targetPane) {
          pane.classList.remove('active', 'show');
          pane.style.setProperty('display', 'none', 'important');
          pane.style.setProperty('opacity', '0', 'important');
        }
      });
    }

    // 4. ATIVA O PAINEL ALVO COM MÁXIMA FORÇA E EXIBIÇÃO DIRETA
    targetPane.classList.add('active', 'show');
    targetPane.style.setProperty('display', 'block', 'important');
    targetPane.style.setProperty('opacity', '1', 'important');
    targetPane.style.setProperty('visibility', 'visible', 'important');
    targetPane.style.setProperty('height', 'auto', 'important');

    // 5. Preserva o hash na URL sem provocar salto de tela
    try {
      history.replaceState(null, null, targetSelector);
    } catch (err) {}

    // 6. Recalcula tabelas DataTables ou outros componentes gráficos dentro da aba recém-aberta
    if (window.jQuery && jQuery.fn.dataTable) {
      try {
        jQuery(targetPane).find('table.dataTable').each(function () {
          jQuery(this).DataTable().columns.adjust().responsive.recalc();
        });
      } catch (err) {}
    }

    // 7. Dispara evento compatível com Bootstrap
    try {
      tabTrigger.dispatchEvent(new CustomEvent('shown.bs.tab', { bubbles: true, detail: { relatedTarget: null } }));
    } catch (err) {}

    return true;
  }

  window.switchProntoTab = switchTab;

  // Interceptador Global Prioritário (Capture Phase) para cliques em qualquer Aba
  document.addEventListener('click', function (e) {
    const tabTrigger = e.target.closest(
      '[data-toggle="tab"], [data-bs-toggle="tab"], [data-toggle="pill"], [data-bs-toggle="pill"], ' +
      '.nav-tabs button, .nav-tabs a, .nav-pills button, .nav-pills a, ' +
      '.card-outline-tabs .nav-link, .nav-tabs-custom .nav-link, .nav-tabs-custom li > a'
    );
    if (!tabTrigger) return;

    const targetSelector = tabTrigger.getAttribute('data-bs-target') || 
                           tabTrigger.getAttribute('data-target') || 
                           tabTrigger.getAttribute('href');

    if (!targetSelector || !targetSelector.startsWith('#')) return;

    e.preventDefault();
    e.stopPropagation();

    switchTab(tabTrigger);
  }, true);

  // 10. FUNÇÃO PARA ATIVAR ABA BASEADA NO HASH DA URL OU NO ESTADO INICIAL DO PHP
  function restoreTabFromHash(container) {
    const root = container || document;
    const hash = window.location.hash;
    let activated = false;

    if (hash && (hash.startsWith('#tab_') || hash.startsWith('#tab-') || hash.includes('tab'))) {
      const activeTab = root.querySelector(`[data-bs-target="${hash}"], [href="${hash}"], [data-target="${hash}"]`);
      if (activeTab) {
        activated = switchTab(activeTab);
      }
    }

    // Se nenhuma aba foi ativada pelo hash, sincroniza a aba marcada como ativa pelo PHP
    if (!activated) {
      root.querySelectorAll('.nav-tabs, .nav-pills').forEach(nav => {
        let activeTab = nav.querySelector('.nav-link.active, button.active, a.active');
        if (!activeTab) {
          activeTab = nav.querySelector('.nav-link, button, a');
        }
        if (activeTab) {
          switchTab(activeTab);
        }
      });
    }
  }

  window.restoreTabFromHash = restoreTabFromHash;

  // Garante a restauração logo no onload final
  window.addEventListener('load', function () {
    restoreTabFromHash(document);
  });

})();

