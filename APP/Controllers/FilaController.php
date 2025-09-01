<?php
require_once __DIR__ . '/../Repositories/FilaRepository.php';

final class FilaController
{
    public static function index(): void
    {
        if (empty($_SESSION['user_id'])) { header('Location: ?r=auth/login'); exit; }

        // Busca via repository (remove SQL do controller)
        $rows = FilaRepository::listarEmFila();

        require __DIR__ . '/../Views/fila-index.php';
    }
}
