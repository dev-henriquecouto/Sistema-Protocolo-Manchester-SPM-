<?php
$pageTitle = 'SPM - Fila de Atendimento';
$showHomeLink = true;
require __DIR__ . '/includes/header.php';

/** @var array $rows  Estrutura vinda do controller:
 *  [
 *    ['sessao_id'=>..., 'codigo_chamada'=>..., 'paciente_nome'=>..., 'entrada_fila_em'=>..., 'prioridade'=>..., 'peso'=>...],
 *    ...
 *  ]
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

// estatísticas simples
$stats = ['TOTAL'=>0,'VERMELHO'=>0,'LARANJA'=>0,'AMARELO'=>0,'VERDE'=>0,'AZUL'=>0];
foreach ($rows as $r) {
  $stats['TOTAL']++;
  $k = strtoupper((string)($r['prioridade'] ?? ''));
  if (isset($stats[$k])) $stats[$k]++;
}
?>
<style>
  /* laranja para badge (Bootstrap não tem utilitário nativo) */
  .badge-orange { color:#fff; background-color:#fd7e14; }
  .table thead th { font-size:.75rem; letter-spacing:.04em; text-transform:uppercase; color:#6b7280; }
  .chip { border:1px solid var(--spm-secondary); border-radius:.5rem; padding:.25rem .5rem; font-size:.75rem; color:#6b7280; }
</style>

<main class="flex-grow-1">
  <div class="container px-3 py-4 py-md-5">

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert alert-warning rounded-xl mb-3" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <!-- Barra superior -->
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h2 class="h4 fw-semibold mb-0">Fila de Atendimento</h2>
      <div class="d-flex gap-2 align-items-center">

        <!-- NOVO: Botões de Relatório (PDF) -->
        <div class="btn-group" role="group" aria-label="Relatórios">
          <a class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft"
             href="?r=relatorio/atendimentos&period=diario&format=pdf"
             target="_blank" rel="noopener">
            <i class="fa-solid fa-file-arrow-down me-1"></i> Diário
          </a>
          <a class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft"
             href="?r=relatorio/atendimentos&period=semanal&format=pdf"
             target="_blank" rel="noopener">
            Semanal
          </a>
          <a class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft"
             href="?r=relatorio/atendimentos&period=mensal&format=pdf"
             target="_blank" rel="noopener">
            Mensal
          </a>
        </div>

        <!-- Botão existente: Abrir Painel -->
        <a class="btn btn-light btn-sm rounded-xl border-secondary-soft" href="?r=painel/chamadas" target="_blank" rel="noopener">
          <i class="fa-solid fa-tv me-1"></i> Abrir Painel
        </a>
        <a class="btn btn-outline-secondary btn-sm rounded-xl border-secondary-soft" href="?r=auth/sair">
          <i class="fa-solid fa-right-from-bracket me-1"></i> Sair
        </a>
      </div>
    </div>

    <!-- Cards de estatística -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary-soft small">Total em Fila</div>
              <div class="h4 fw-bold mb-0"><?php echo (int)$stats['TOTAL']; ?></div>
            </div>
            <div class="icon-tile"><i class="fa-solid fa-users"></i></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary-soft small">Emergências</div>
              <div class="h4 fw-bold text-danger mb-0"><?php echo (int)$stats['VERMELHO']; ?></div>
            </div>
            <div class="icon-tile" style="background:#dc3545;"><i class="fa-solid fa-triangle-exclamation"></i></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary-soft small">Amarelo</div>
              <div class="h4 fw-bold text-warning mb-0"><?php echo (int)$stats['AMARELO']; ?></div>
            </div>
            <div class="icon-tile" style="background:#ffc107;"><i class="fa-solid fa-clock"></i></div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="text-secondary-soft small">Verde</div>
              <div class="h4 fw-bold text-success mb-0"><?php echo (int)$stats['VERDE']; ?></div>
            </div>
            <div class="icon-tile"><i class="fa-solid fa-check-circle"></i></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white border border-secondary-soft rounded-xl shadow-soft p-3 mb-3">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text rounded-xl border-secondary-soft"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input id="searchInput" type="text" class="form-control rounded-xl border-secondary-soft" placeholder="Buscar por código ou paciente...">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <select id="prioFilter" class="form-select rounded-xl border-secondary-soft">
            <option value="">Todas as prioridades</option>
            <option value="VERMELHO">Vermelho (Emergência)</option>
            <option value="LARANJA">Laranja (Urgente)</option>
            <option value="AMARELO">Amarelo (Moderada)</option>
            <option value="VERDE">Verde (Não urgente)</option>
            <option value="AZUL">Azul (Consulta)</option>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <span class="chip"><span class="me-2 d-inline-block rounded-circle" style="width:8px;height:8px;background:#22c55e"></span>Tempo real</span>
        </div>
        <div class="col-12 col-md-2 text-md-end">
          <button class="btn btn-light btn-sm rounded-xl border-secondary-soft" onclick="location.reload()">
            <i class="fa-solid fa-rotate me-1"></i> Atualizar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabela -->
    <div class="table-responsive bg-white border border-secondary-soft rounded-xl shadow-soft">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Código</th>
            <th>Paciente</th>
            <th>Prioridade</th>
            <th>Entrada na fila</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody id="filaBody">
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-secondary-soft py-4">Nenhum paciente na fila.</td></tr>
        <?php else: $i=1; foreach ($rows as $r): 
          $sid   = (int)$r['sessao_id'];
          $cod   = htmlspecialchars((string)$r['codigo_chamada'], ENT_QUOTES, 'UTF-8');
          $nome  = htmlspecialchars((string)$r['paciente_nome'], ENT_QUOTES, 'UTF-8');
          $prio  = htmlspecialchars((string)($r['prioridade'] ?? ''), ENT_QUOTES, 'UTF-8');
          $badge = prio_badge_class($r['prioridade'] ?? null);
          $dt    = htmlspecialchars((string)$r['entrada_fila_em'], ENT_QUOTES, 'UTF-8');
        ?>
          <tr data-prio="<?php echo strtoupper($prio); ?>" data-search="<?php echo strtoupper($cod.' '.$nome); ?>">
            <td class="fw-semibold"><?php echo $i++; ?></td>
            <td class="fw-semibold"><?php echo $cod; ?></td>
            <td><?php echo $nome; ?></td>
            <td><span class="badge <?php echo $badge; ?>"><?php echo $prio ?: '—'; ?></span></td>
            <td><span class="text-secondary-soft"><?php echo $dt; ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-spm rounded-xl" href="?r=admin/sessao&sid=<?php echo $sid; ?>">
                <i class="fa-solid fa-notes-medical me-1"></i> Revisar
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <small class="text-muted">Dica: use a busca para filtrar por nome ou código.</small>
    </div>

  </div>
</main>

<script>
  // filtro client-side por prioridade e busca
  (function(){
    const input = document.getElementById('searchInput');
    const select= document.getElementById('prioFilter');
    const rows  = Array.from(document.querySelectorAll('#filaBody tr'));

    function apply(){
      const term = (input.value || '').toUpperCase().trim();
      const prio = (select.value || '').toUpperCase();

      rows.forEach(tr => {
        const okTerm = !term || (tr.dataset.search||'').includes(term);
        const okPrio = !prio || (tr.dataset.prio === prio);
        tr.style.display = (okTerm && okPrio) ? '' : 'none';
      });
    }

    input.addEventListener('input', apply);
    select.addEventListener('change', apply);
  })();

  // opcional: auto-refresh leve
  setTimeout(() => { /* atualiza a cada 30s */ location.reload(); }, 30000);
</script>

</body>
</html>
