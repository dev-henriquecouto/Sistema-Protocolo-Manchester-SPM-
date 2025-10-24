<?php
// NÃO use "use PDO;" aqui — este arquivo não tem namespace
use APP\Services\EventBus;
use APP\Events\PacienteChamadoEvent;
use APP\Observers\NotificarPacienteChamadoObserver;
use APP\Repositories\NotificacaoRepository;

/** Recupera o EventBus criado no index.php */
$eventBus = $GLOBALS['eventBus'] ?? null;

/** Recupera o PDO de forma robusta */
global $db; // caso seu database.php tenha definido $db
if (!($db ?? null) instanceof \PDO) {
    // tenta variáveis comuns
    if (isset($pdo) && $pdo instanceof \PDO) {
        $db = $pdo;
    } elseif (isset($conn) && $conn instanceof \PDO) {
        $db = $conn;
    } else {
        // garante include do database.php, se ainda não ocorreu
        require_once __DIR__ . '/database.php';
        if (!($db ?? null) instanceof \PDO) {
            if (isset($pdo) && $pdo instanceof \PDO) $db = $pdo;
            elseif (isset($conn) && $conn instanceof \PDO) $db = $conn;
        }
    }
}

/** Se algo faltar, sai silenciosamente (não quebra fluxo principal) */
if (!($eventBus instanceof EventBus) || !($db instanceof \PDO)) {
    return;
}

/** Registra o observer */
$notifRepo = new NotificacaoRepository($db);
$observer  = new NotificarPacienteChamadoObserver($notifRepo);
$eventBus->subscribe(PacienteChamadoEvent::NAME, [$observer, 'handle']);
