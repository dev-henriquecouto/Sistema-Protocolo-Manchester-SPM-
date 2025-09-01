<?php
require_once __DIR__ . '/../Repositories/TriagemRepository.php';

final class RevisaoController
{
    private static function exigeLogin(): void
    {
        if (empty($_SESSION['user_id'])) { header('Location: ?r=auth/login'); exit; }
        if (!in_array($_SESSION['papel'] ?? '', ['profissional','administrador'], true)) {
            $_SESSION['flash'] = 'Sem permissão.';
            header('Location: ./'); exit;
        }
    }

    public static function detalhe(): void
    {
        self::exigeLogin();
        $sid = (int)($_GET['sid'] ?? 0);
        if ($sid <= 0) { header('Location: ?r=admin/fila'); exit; }

        $sessao = TriagemRepository::detalharSessao($sid);
        if (!$sessao) {
            $_SESSION['flash'] = 'Sessão não encontrada.'; 
            header('Location: ?r=admin/fila'); exit;
        }

        $opts = TriagemRepository::listarPrioridades();
        require __DIR__ . '/../Views/revisao-detalhe.php';
    }

    public static function confirmar(): void
    {
        self::exigeLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=admin/fila'); exit; }

        $sid         = (int)($_POST['sid'] ?? 0);
        $prioridade  = (int)($_POST['prioridade_final'] ?? 0);
        $observacoes = trim($_POST['observacoes'] ?? '');

        if ($sid <= 0 || $prioridade <= 0) {
            $_SESSION['flash'] = 'Dados inválidos.'; 
            header('Location: ?r=admin/fila'); exit;
        }

        TriagemRepository::upsertRevisao($sid, (int)$_SESSION['user_id'], $prioridade, $observacoes);

        $_SESSION['flash'] = 'Prioridade final registrada.';
        header('Location: ?r=admin/sessao&sid=' . $sid); exit;
    }

    public static function chamar(): void
    {
        self::exigeLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=admin/fila'); exit; }

        $sid = (int)($_POST['sid'] ?? 0);
        if ($sid <= 0) { 
            $_SESSION['flash'] = 'Sessão inválida.'; 
            header('Location: ?r=admin/fila'); exit; 
        }

        TriagemRepository::chamarSessao($sid);

        $_SESSION['flash'] = 'Paciente chamado (removido da fila).';
        header('Location: ?r=admin/fila'); exit;
    }
}
