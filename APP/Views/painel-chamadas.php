<?php
$pageTitle = 'SPM - Painel de Chamadas';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';

/** @var array $rows  (PainelController::chamadas)
 *  Cada item: ['codigo_chamada'=>..., 'prioridade'=>..., 'saida_fila_em'=>...]
 */
function prio_badge_class(?string $p): string {
  $p = strtoupper((string)$p);
  return match ($p) {
    'VERMELHO' => 'text-bg-danger',
    'LARANJA'  => 'badge-orange',
    'AMARELO'  => 'text-bg-warning',
    'VERDE'    => 'text-bg-success',
    'AZUL'     => 'text-bg-primary',
    default    => 'text-bg-secondary',
  };
}
?>
<style>
  .badge-orange{color:#fff;background-color:#fd7e14}
  .code-card{ letter-spacing:.06em; font-weight:800; }
</style>

<main class="flex-grow-1">
  <div class="container px-3 py-4 py-md-5">

    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="h4 fw-semibold mb-0">Chamadas Recentes</h2>
      <div class="d-flex gap-2">
        <button class="btn btn-light btn-sm rounded-xl border-secondary-soft" onclick="location.reload()">
          <i class="fa-solid fa-rotate me-1"></i> Atualizar
        </button>
      </div>
    </div>

    <?php if (empty($rows)): ?>
      <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-4 text-center">
        <div class="icon-circle mb-2"><i class="fa-solid fa-bell-slash"></i></div>
        <div class="fw-semibold">Nenhuma chamada registrada ainda.</div>
        <div class="text-secondary-soft small">Este painel atualiza automaticamente.</div>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($rows as $r):
          $cod = htmlspecialchars((string)$r['codigo_chamada'], ENT_QUOTES, 'UTF-8');
          $prio= htmlspecialchars((string)($r['prioridade'] ?? ''), ENT_QUOTES, 'UTF-8');
          $dt  = htmlspecialchars((string)($r['saida_fila_em'] ?? ''), ENT_QUOTES, 'UTF-8');
          $badge = prio_badge_class($prio);
        ?>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge <?php echo $badge; ?>"><?php echo $prio ?: '—'; ?></span>
              <small class="text-secondary-soft"><i class="fa-solid fa-clock me-1"></i><?php echo $dt; ?></small>
            </div>
            <div class="display-6 code-card text-center text-dark"><?php echo $cod; ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="text-center mt-4">
      <small class="text-muted">Atualização automática a cada 30s.</small>
    </div>

  </div>
</main>

<script>
  // auto-refresh simples
  setTimeout(() => { location.reload(); }, 30000);
</script>

</body>
</html>
