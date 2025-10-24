<?php
$pageTitle = 'Triagem SPM - Sucesso';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';
?>

<main class="flex-grow-1">
  <div class="container container-narrow px-3 py-4 py-md-5">

    <!-- Sucesso -->

    <section class="fade-in mb-4 text-center">
      <div class="icon-circle mb-3">
        <i class="fa-solid fa-circle-check fa-lg"></i>
      </div>
      <!-- Wrapper existente da mensagem de sucesso -->
      <div id="sucesso-notify-root" data-codigo="<?= isset($codigo_chamada) ? htmlspecialchars($codigo_chamada, ENT_QUOTES, 'UTF-8') : '' ?>">
        <h1 id="success-title">Triagem enviada com sucesso</h1>
        <p id="success-subtitle">Aguarde ser chamado. A equipe está analisando suas informações.</p>
</div>


<script>
(function () {
  const ROOT = document.getElementById('sucesso-notify-root');
  if (!ROOT) return;

  const codigo = ROOT.getAttribute('data-codigo') || '';

  function aplicarTextoChamado() {
    const title = document.getElementById('success-title') || document.querySelector('h1');
    const subtitle = document.getElementById('success-subtitle') || ROOT.querySelector('.lead, p');
    if (title)    title.textContent = 'Agora é a sua vez, por favor siga para o consultório.';
    if (subtitle) subtitle.textContent = '';
    const alertEl = ROOT.querySelector('.alert, .callout, .toast, [role="alert"]');
    if (alertEl) {
      alertEl.textContent = 'Agora é a sua vez, por favor siga para o consultório.';
      alertEl.classList?.remove('alert-info','alert-warning');
      alertEl.classList?.add('alert-success');
    }
  }

  // (A) Fallback imediato: se já está "chamado" na página, troca o texto na hora
  (function checarStatusNoDOM() {
    const badge = document.getElementById('badge-status');
    const statusAttr = badge ? (badge.getAttribute('data-status') || '').toLowerCase() : '';
    const statusTxt  = badge ? (badge.textContent || '').toLowerCase().trim() : '';
    if (statusAttr === 'chamado' || statusTxt === 'chamado') {
      aplicarTextoChamado();
      // não retorna; deixamos o polling marcar como lida caso exista notificação pendente
    }
  })();

  // (B) Polling de notificações (como antes)
  async function fetchNotificacoes() {
    const qs = codigo ? ('?r=api/notificacoes&codigo=' + encodeURIComponent(codigo)) : '?r=api/notificacoes';
    const res = await fetch(qs, { credentials: 'same-origin' });
    if (!res.ok) return null;
    return res.json();
  }

  async function marcarLidas(ids) {
    try {
      const meta = document.querySelector('meta[name="csrf-token"]');
      const csrf = meta ? meta.getAttribute('content') : '';
      const res = await fetch('?r=api/notificacoes/marcar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        credentials: 'same-origin',
        body: JSON.stringify({ ids })
      });
      return res.ok;
    } catch (_) { return false; }
  }

  let running = true;
  async function tick() {
    if (!running) return;
    try {
      const itens = await fetchNotificacoes();
      if (Array.isArray(itens) && itens.length > 0) {
        const chamado = itens.find(n => n && n.tipo === 'paciente_chamado');
        if (chamado) {
          aplicarTextoChamado();
          await marcarLidas([chamado.id]);
          running = false;
          return;
        }
      }
    } catch (_) {} 
    finally {
      if (running) setTimeout(tick, 5000);
    }
  }

  setTimeout(tick, 1200);
})();
</script>

    <!-- Código / Status -->
    <section class="bg-white border border-secondary-soft rounded-xl p-4 shadow-soft text-center mb-4">
      <h3 class="h6 text-success fw-semibold mb-2">Seu código de atendimento</h3>
      <p class="display-6 fw-bold mb-0" style="letter-spacing:.04em;">
        <?php echo htmlspecialchars($codigo ?? '', ENT_QUOTES, 'UTF-8'); ?>
      </p>
      <?php if (!empty($status)): ?>
        <div class="mt-2">
          <span class="badge text-bg-secondary"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
      <?php endif; ?>
    </section>

    <!-- Ações -->
    <div class="d-grid gap-2">
      <a href="./" class="btn btn-spm btn-compact rounded-xl">
        <i class="fa-solid fa-house me-1"></i> Voltar ao início
      </a>
      <a href="?r=triagem/identificar" class="btn btn-outline-secondary rounded-xl border-secondary-soft">
        Nova triagem
      </a>
    </div>

    <div class="text-center mt-5">
      <small class="text-muted">MVP • LGPD: coletamos apenas o necessário para atendimento.</small>
    </div>

  </div>
</main>

</body>
</html>
