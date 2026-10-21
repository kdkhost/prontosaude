/**
 * Pronto Saúde - Gerador Automático de Slug em Tempo Real
 * Preenche o slug sozinho a partir do título/nome e desativa edição manual
 * Evita inconsistências, caracteres especiais e bugs de URLs amigáveis
 */

(function () {
  'use strict';

  /**
   * Converte texto em slug limpo para URL em português
   */
  function slugify(text) {
    if (!text) return '';
    return text
      .toString()
      .normalize('NFD') // divide caracteres com acento em letra base + diacrítico
      .replace(/[\u0300-\u036f]/g, '') // remove os acentos
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9\s-]/g, '') // remove símbolos especiais
      .replace(/[\s_]+/g, '-') // espaços e underscores viram hífen
      .replace(/-+/g, '-') // reduz múltiplos hífens consecutivos
      .replace(/^-+|-+$/g, ''); // remove hífens das extremidades
  }

  window.psSlugify = slugify;

  // Pares mapeados de campo de Título/Nome -> Campo de Slug no Admin
  const slugPairs = [
    { title: 'dep_name', slug: 'dep_slug' },
    { title: 'page_name', slug: 'page_slug' },
    { title: 'news_title', slug: 'news_slug' },
    { title: 'category_name', slug: 'category_slug' },
    { title: 'name', slug: 'slug' }
  ];

  function initSlugAutoGenerator(container) {
    container = container || document;

    // 1. Vincula pares mapeados
    slugPairs.forEach(pair => {
      const titleInputs = container.querySelectorAll(`input[name="${pair.title}"]`);
      titleInputs.forEach(titleInput => {
        const form = titleInput.closest('form');
        if (!form) return;

        const slugInput = form.querySelector(`input[name="${pair.slug}"]`);
        if (!slugInput) return;

        setupSlugInput(slugInput, titleInput);
      });
    });

    // 2. Fallback dinâmico para qualquer outro campo que termine com slug
    const genericSlugInputs = container.querySelectorAll('input[name$="slug"], input[id$="slug"], input[name="slug"]');
    genericSlugInputs.forEach(slugInput => {
      if (slugInput.dataset.psSlugBound === 'true') return;
      const form = slugInput.closest('form');
      if (!form) return;

      const titleInput = form.querySelector('input[name*="title"], input[name*="name"], input[id*="title"], input[id*="name"]');
      if (titleInput && titleInput !== slugInput) {
        setupSlugInput(slugInput, titleInput);
      }
    });
  }

  function setupSlugInput(slugInput, titleInput) {
    // Evita duplicar listeners
    if (slugInput.dataset.psSlugBound === 'true') return;
    slugInput.dataset.psSlugBound = 'true';

    // Bloqueia digitação manual (readonly mantém o envio no POST / FormData)
    slugInput.readOnly = true;
    slugInput.setAttribute('readonly', 'readonly');
    slugInput.classList.add('ps-slug-locked');
    slugInput.style.backgroundColor = 'var(--bs-tertiary-bg, #f1f5f9)';
    slugInput.style.cursor = 'not-allowed';
    slugInput.style.borderStyle = 'dashed';

    // Insere badge informativo visual caso ainda não exista
    const parentContainer = slugInput.closest('.col-sm-4, .col-sm-6, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-12, .mb-3, .form-group') || slugInput.parentElement;
    if (parentContainer && !parentContainer.querySelector('.ps-slug-helper')) {
      const helper = document.createElement('div');
      helper.className = 'form-text small text-body-secondary mt-1 ps-slug-helper d-flex align-items-center gap-1';
      helper.innerHTML = '<i class="bi bi-lock-fill text-primary"></i> <span class="fw-semibold">Slug automático:</span> preenchido sozinho pelo sistema (edição manual desativada para evitar bugs de URL)';
      slugInput.insertAdjacentElement('afterend', helper);
    }

    // Se estiver vazio e o título já tiver texto, preenche imediatamente
    if (!slugInput.value.trim() && titleInput.value.trim()) {
      slugInput.value = slugify(titleInput.value);
    }

    // Listener para digitação em tempo real no campo de título/nome
    const handleTitleInput = function () {
      const cleanSlug = slugify(titleInput.value);
      slugInput.value = cleanSlug;
      slugInput.dispatchEvent(new Event('change', { bubbles: true }));
    };

    titleInput.addEventListener('input', handleTitleInput);
    titleInput.addEventListener('keyup', handleTitleInput);
    titleInput.addEventListener('change', handleTitleInput);
    titleInput.addEventListener('paste', function () {
      setTimeout(handleTitleInput, 50);
    });
  }

  window.initSlugAutoGenerator = initSlugAutoGenerator;

  // Inicialização no DOMContentLoaded
  document.addEventListener('DOMContentLoaded', function () {
    initSlugAutoGenerator(document);
  });
})();
