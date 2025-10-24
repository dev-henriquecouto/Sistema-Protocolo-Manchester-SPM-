<?php
session_start();

// Database 
require_once __DIR__ . '/APP/database.php';

// Exponha a conexão PDO de forma explícita (qualquer nome que o database.php usar)
if (isset($pdo) && $pdo instanceof PDO) {
    $GLOBALS['pdo'] = $pdo;
}
if (isset($db) && $db instanceof PDO) {
    $GLOBALS['db'] = $db;
    if (!isset($GLOBALS['pdo'])) { $GLOBALS['pdo'] = $db; }
}
if (isset($conn) && $conn instanceof PDO) {
    $GLOBALS['conn'] = $conn;
    if (!isset($GLOBALS['pdo'])) { $GLOBALS['pdo'] = $conn; }
}


/* =========================[ INÍCIO DAS INCLUSÕES NOVAS ]========================= */
// Services / EventBus (novo)
require_once __DIR__ . '/APP/Services/EventBus.php';

// Repositório e Observer da notificação (novos)
require_once __DIR__ . '/APP/Repositories/NotificacaoRepository.php';
require_once __DIR__ . '/APP/Observers/NotificarPacienteChamadoObserver.php';

// Evento de domínio (novo)
require_once __DIR__ . '/APP/Events/PacienteChamadoEvent.php';

use APP\Services\EventBus;

// Instancia o EventBus para este request (novo)
$eventBus = new EventBus();

// Disponibiliza globalmente (evita alterar construtores existentes) (novo)
$GLOBALS['eventBus'] = $eventBus;

// Registra os observers (precisa do $db/$pdo e $eventBus) (novo)
require_once __DIR__ . '/APP/bootstrap_observers.php';
/* ==========================[ FIM DAS INCLUSÕES NOVAS ]========================== */

// Controllers
require_once __DIR__ . '/APP/Controllers/AuthController.php';
require_once __DIR__ . '/APP/Controllers/TriagemController.php';
require_once __DIR__ . '/APP/Controllers/FilaController.php';
require_once __DIR__ . '/APP/Controllers/RevisaoController.php';
require_once __DIR__ . '/APP/Controllers/PainelController.php';
require_once __DIR__ . '/APP/Controllers/NotificacaoController.php';
require_once __DIR__ . '/APP/Controllers/RelatorioController.php';

// Roteador mínimo por query string (?r=controller/acao)
$r = $_GET['r'] ?? '';

switch ($r) {
    // HOME
    case '':
        require __DIR__ . '/APP/Views/home.php';
        break;

    // AUTH (Profissional/Admin)
    case 'auth/login':
        AuthController::loginForm();
        break;

    case 'auth/entrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=auth/login'); exit; }
        AuthController::entrar();
        break;

    case 'auth/sair':
        AuthController::sair();
        break;

    case 'auth/google-paciente':
        AuthController::googlePaciente();
        break;

    // PACIENTE – Identificação + Triagem
    case 'triagem/identificar':
        TriagemController::identificar();
        break;

    case 'triagem/identificar-post':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=triagem/identificar'); exit; }
        TriagemController::identificarPost();
        break;

    case 'triagem/nova':
        TriagemController::nova();
        break;

    case 'triagem/criar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=triagem/nova'); exit; }
        TriagemController::criar();
        break;

    case 'triagem/sucesso':
        TriagemController::sucesso();
        break;

    // RELATÓRIO (Profissional): diário/semanal/mensal
    case 'relatorio/atendimentos':
        if (empty($_SESSION['user_id'])) { header('Location: ?r=auth/login'); exit; }
        \APP\Controllers\RelatorioController::atendimentos();
        break;

    // API — Notificações do paciente (JSON)
    case 'api/notificacoes':
        // GET: lista não lidas (identificação por sessão ou ?codigo=ABC-123 na 1ª chamada)
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); exit; }
        \APP\Controllers\NotificacaoController::listar();
        break;

    case 'api/notificacoes/marcar':
        // POST: marca IDs como lidas do próprio paciente
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        \APP\Controllers\NotificacaoController::marcar();
        break;

    // PAINEL DO PROFISSIONAL (Fila + Revisão + Chamada)
    case 'admin/fila':
        if (empty($_SESSION['user_id'])) { header('Location: ?r=auth/login'); exit; }
        FilaController::index();
        break;

    case 'admin/sessao':
        RevisaoController::detalhe();
        break;

    case 'admin/sessao/confirmar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=admin/fila'); exit; }
        RevisaoController::confirmar();
        break;

    case 'admin/sessao/chamar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ?r=admin/fila'); exit; }
        RevisaoController::chamar();
        break;

    // PAINEL PÚBLICO (TV)
    case 'painel/chamadas':
        PainelController::chamadas();
        break;

    // 404
    default:
        http_response_code(404);
        echo '<div style="padding:2rem;font-family:system-ui">404 - Rota não encontrada</div>';
        break;
}
