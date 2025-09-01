<?php
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../config.php';

final class AuthController
{
    public static function loginForm(): void
    {
        require __DIR__ . '/../Views/auth-login.php';
    }

    public static function entrar(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = (string)($_POST['senha'] ?? '');

        if ($email === '' || $senha === '') {
            $_SESSION['flash'] = 'Informe e-mail e senha.';
            header('Location: ?r=auth/login'); exit;
        }

        // Usa o Repository
        $user = UsuarioRepository::verificarLogin($email, $senha);
        if (!$user) {
            $_SESSION['flash'] = 'Credenciais inválidas.';
            header('Location: ?r=auth/login'); exit;
        }

        if (!in_array($user['papel'], ['profissional','administrador'], true)) {
            $_SESSION['flash'] = 'Usuário sem permissão para o painel.';
            header('Location: ?r=auth/login'); exit;
        }

        if ((int)$user['ativo'] !== 1) {
            $_SESSION['flash'] = 'Usuário inativo. Solicite ativação ao administrador.';
            header('Location: ?r=auth/login'); exit;
        }

        // Login OK
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['papel']   = $user['papel'];
        session_regenerate_id(true);

        header('Location: ?r=admin/fila'); exit;
    }

    public static function sair(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ./'); exit;
    }

    public static function googlePaciente(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

        $idToken = $_POST['credential'] ?? '';
        if ($idToken === '') {
            $_SESSION['flash'] = 'Credencial Google ausente.';
            header('Location: ?r=triagem/identificar'); exit;
        }

        $info = self::verificarGoogleIdToken($idToken);
        if (!$info) {
            $_SESSION['flash'] = 'Falha ao validar token do Google.';
            header('Location: ?r=triagem/identificar'); exit;
        }

        // checa o client id (aud) e e-mail verificado
        $aud   = $info['aud']   ?? '';
        $email = $info['email'] ?? '';
        $name  = $info['name']  ?? trim(($info['given_name'] ?? '').' '.($info['family_name'] ?? ''));

        if ($aud !== GOOGLE_CLIENT_ID || empty($email) || ($info['email_verified'] ?? 'false') !== 'true') {
            $_SESSION['flash'] = 'Token inválido (aud/email).';
            header('Location: ?r=triagem/identificar'); exit;
        }

        try {
            // Usa o Repository para criar/associar paciente
            $pacienteId = UsuarioRepository::criarPacienteSeNaoExiste($name ?: 'Paciente', $email);
            $_SESSION['paciente_id']    = $pacienteId;
            $_SESSION['paciente_nome']  = $name ?: 'Paciente';
            $_SESSION['paciente_email'] = $email;
        } catch (\Throwable $e) {
            $_SESSION['flash'] = 'Erro ao criar/associar paciente: ' . $e->getMessage();
            header('Location: ?r=triagem/identificar'); exit;
        }

        // Redireciona direto para a triagem
        header('Location: ?r=triagem/nova'); exit;
    }

    // Verificação mínima via endpoint tokeninfo (ok para MVP)
    private static function verificarGoogleIdToken(string $idToken): ?array
    {
        $url  = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
        $resp = @file_get_contents($url);
        if ($resp === false) return null;
        $data = json_decode($resp, true);
        return is_array($data) ? $data : null;
    }
}
