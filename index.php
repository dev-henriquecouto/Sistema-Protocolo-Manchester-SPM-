<?php
session_start();
// Database 
require_once __DIR__ . '/APP/database.php';

// Controllers
require_once __DIR__ . '/APP/Controllers/AuthController.php';
require_once __DIR__ . '/APP/Controllers/TriagemController.php';
require_once __DIR__ . '/APP/Controllers/FilaController.php';
require_once __DIR__ . '/APP/Controllers/RevisaoController.php';
require_once __DIR__ . '/APP/Controllers/PainelController.php';

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
        AuthController::googlePaciente(); break;

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
