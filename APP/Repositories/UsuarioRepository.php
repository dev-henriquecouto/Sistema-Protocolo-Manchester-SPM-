<?php
require_once __DIR__ . '/../database.php';

final class UsuarioRepository
{
    public static function buscarPorEmail(string $email): ?array
    {
        $pdo = Database::get();
        $st  = $pdo->prepare('SELECT id, nome, email, senha_hash, papel, ativo FROM usuarios WHERE email=? LIMIT 1');
        $st->execute([$email]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function verificarLogin(string $email, string $senha): ?array
    {
        $u = self::buscarPorEmail($email);
        if (!$u || !password_verify($senha, $u['senha_hash'])) return null;
        return $u;
    }

    public static function criarPacienteSeNaoExiste(string $nome, string $email): int
    {
        $ex = self::buscarPorEmail($email);
        if ($ex) return (int)$ex['id'];

        $pdo  = Database::get();
        $rand = bin2hex(random_bytes(16));
        $hash = password_hash($rand, PASSWORD_BCRYPT);

        $st = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, papel, ativo) VALUES (?, ?, ?, 'paciente', 1)");
        $st->execute([$nome, $email, $hash]);
        return (int)$pdo->lastInsertId();
    }
}
