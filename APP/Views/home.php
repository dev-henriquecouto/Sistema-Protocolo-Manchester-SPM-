<?php
// Home — front-end apenas. Usa o header parcial.
// Opcional: $showHomeLink = false; (por padrão não mostra "Início" no canto)
$pageTitle = 'Triagem SPM - Início';
require __DIR__ . '/includes/header.php';
?>

<main class="flex-grow-1">
  <div class="container px-3 py-4 py-md-5" style="max-width: 760px;">

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-warning rounded-xl mb-4" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <!-- Sessão: Bem-vindo -->
    <section class="fade-in mb-4 mb-md-5 text-center">
      <div class="icon-circle mb-3">
        <i class="fa-solid fa-heart fa-lg"></i>
      </div>
      <h2 class="h4 fw-bold mb-2 text-dark">Bem-vindo à Triagem</h2>
      <p class="mb-0 text-secondary-soft">
        Vamos ajudá-lo a identificar a urgência do seu atendimento de forma rápida e segura.
      </p>
    </section>

    <!-- CTA principal -->
    <section class="mb-4 mb-md-5">
      <div class="d-grid">
        <a href="?r=triagem/identificar"
           class="btn btn-spm btn-compact rounded-xl shadow-soft d-flex align-items-center justify-content-center gap-2">
          <i class="fa-solid fa-arrow-right"></i>
          <span>Iniciar triagem</span>
        </a>
      </div>
    </section>

    <!-- Como funciona -->
    <section class="bg-white border border-secondary-soft rounded-xl p-4 shadow-soft">
      <div class="d-flex align-items-start gap-3">
        <div class="text-success mt-1">
          <i class="fa-solid fa-circle-info"></i>
        </div>
        <div>
          <h3 class="h6 fw-semibold text-success mb-1">Como funciona?</h3>
          <p class="mb-0 text-secondary-soft small">
            Você responderá algumas perguntas sobre seus sintomas e nossa IA gerará um relatório médico inicial.
          </p>
        </div>
      </div>
    </section>

    <!-- Rodapé curto / LGPD -->
    <div class="text-center mt-5">
      <small class="text-muted">MVP • LGPD: coletamos apenas o necessário para atendimento.</small>
    </div>

  </div>
</main>

</body>
</html>
