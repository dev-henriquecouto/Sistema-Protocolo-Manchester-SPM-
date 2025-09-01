<?php
// Header comum a todas as páginas do fluxo do usuário
// Use $pageTitle para definir o <title> e $showHomeLink=true para exibir o botão "Início" no canto
if (!isset($pageTitle)) { $pageTitle = 'Triagem SPM'; }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <!-- Estilos globais (mobile-first) -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root{
      --spm-green: #22c55e;     /* primário (botões) */
      --spm-secondary: #e5e6e6; /* secundário (bordas/detalhes) */
    }

    body{
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif;
      background:#fff; color:#1f2937;
    }

    .fade-in{ animation: fadeIn .5s ease-in both; }
    @keyframes fadeIn { from{opacity:0; transform: translateY(20px);} to{opacity:1; transform: translateY(0);} }

    /* Radius menor e sombras suaves */
    .rounded-xl{ border-radius: .5rem; }
    .shadow-soft{ box-shadow: 0 6px 18px rgba(0,0,0,.06); }

    .sticky-header{ position: sticky; top:0; z-index:1030; }

    .icon-tile{
      width:40px; height:40px; background: var(--spm-green);
      border-radius: .5rem; display:inline-flex; align-items:center; justify-content:center; color:#fff;
    }
    .icon-circle{
      width:72px; height:72px; background: #dcfce7;
      border-radius: 999px; display:flex; align-items:center; justify-content:center; color: var(--spm-green);
      margin-inline:auto;
    }

    /* Botão primário SPM (#22c55e) */
    .btn-spm{
      --bs-btn-color:#fff;
      --bs-btn-bg: var(--spm-green);
      --bs-btn-border-color: var(--spm-green);
      --bs-btn-hover-bg:#1eaf53;
      --bs-btn-hover-border-color:#1ea951;
      --bs-btn-focus-shadow-rgb:34,197,94;
    }
    .btn-compact{ padding:.625rem 1rem; font-weight:600; }

    /* Secundário leve */
    .border-secondary-soft{ border-color: var(--spm-secondary) !important; }
    .text-secondary-soft{ color:#6b7280; }

    @media(min-width:768px){ .container-narrow{ max-width:760px; } }
  </style>
</head>
<body class="min-vh-100 d-flex flex-column">

  <!-- Header -->
  <header class="bg-white border-bottom border-secondary-soft shadow-sm sticky-header">
    <div class="container py-3 px-3">
      <div class="d-flex align-items-center justify-content-between">
        <!-- Branding -->
        <div class="d-flex align-items-center gap-3">
          <div class="icon-tile">
            <i class="fa-solid fa-heart-pulse"></i>
          </div>
          <div class="lh-sm">
            <h1 class="h5 mb-0 text-dark fw-semibold">Triagem SPM</h1>
            <small class="text-muted">Sistema de Triagem</small>
          </div>
        </div>

        <!-- Ações no canto -->
        <div class="d-flex align-items-center gap-2">
          <?php if (!empty($showHomeLink)): ?>
            <a class="btn btn-light btn-sm rounded-xl border-secondary-soft" href="./">
              <i class="fa-solid fa-house me-1"></i> Início
            </a>
          <?php endif; ?>
          <a href="?r=auth/login" class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft">
            <i class="fa-solid fa-user-shield me-1"></i> Admin
          </a>
          <button type="button" class="btn btn-light btn-sm rounded-xl border-secondary-soft" onclick="toggleLanguage()" aria-label="Alternar idioma">
            <span class="small text-secondary">PT</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <script>
    function toggleLanguage(){
      // Placeholder de alternância de idioma (futuro i18n)
      alert('Funcionalidade de idioma em desenvolvimento');
    }
  </script>
