<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Repositories/UsuarioRepository.php';
require_once __DIR__ . '/../Repositories/TriagemRepository.php';
require_once __DIR__ . '/../Services/GeminiIAService.php';

final class TriagemController
{
    // Form de identificação/cadastro do paciente
    public static function identificar(): void
    {
        require __DIR__ . '/../Views/triagem-identificar.php';
    }

    // Processa identificação/cadastro
    public static function identificarPost(): void
    {
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nome === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = 'Informe nome e e-mail válidos.';
            header('Location: ?r=triagem/identificar'); exit;
        }

        try {
            $pacienteId = UsuarioRepository::criarPacienteSeNaoExiste($nome, $email);
            $_SESSION['paciente_id']    = $pacienteId;
            $_SESSION['paciente_nome']  = $nome;
            $_SESSION['paciente_email'] = $email;
        } catch (\Throwable $e) {
            $_SESSION['flash'] = 'Erro ao identificar/cadastrar: ' . $e->getMessage();
            header('Location: ?r=triagem/identificar'); exit;
        }

        header('Location: ?r=triagem/nova'); exit;
    }

    public static function nova(): void
    {
        if (empty($_SESSION['paciente_id'])) {
            $_SESSION['flash'] = 'Identifique-se antes de iniciar a triagem.';
            header('Location: ?r=triagem/identificar'); exit;
        }
        require __DIR__ . '/../Views/triagem-nova.php';
    }

    public static function criar(): void
    {
        $pacienteId = (int)($_SESSION['paciente_id'] ?? 0);
        if ($pacienteId <= 0) {
            $_SESSION['flash'] = 'Identifique-se antes de enviar a triagem.';
            header('Location: ?r=triagem/identificar'); exit;
        }

        // Inputs
        $queixa       = trim($_POST['queixa_principal'] ?? '');
        $sintomas     = trim($_POST['sintomas_texto'] ?? '');
        $antecedentes = trim($_POST['antecedentes_texto'] ?? '');
        $alergias     = trim($_POST['alergias_texto'] ?? '');
        $medicamentos = trim($_POST['medicamentos_texto'] ?? '');
        $consentido   = isset($_POST['consentimento']) ? 1 : 0;

        if ($queixa === '') {
            $_SESSION['flash'] = 'Informe a queixa principal.';
            header('Location: ?r=triagem/nova'); exit;
        }

        // Limites simples
        $sintomas     = substr($sintomas, 0, 8000);
        $antecedentes = substr($antecedentes, 0, 8000);
        $alergias     = substr($alergias, 0, 4000);
        $medicamentos = substr($medicamentos, 0, 4000);

        $codigo = self::codigoChamada();

        $pdo = Database::get();
        $pdo->beginTransaction();
        try {
            // 1) Criar sessão
            $sessaoId = TriagemRepository::criarSessao(
                $pacienteId, $queixa, $sintomas, $antecedentes, $alergias, $medicamentos, $codigo
            );

            // 2) Consentimento
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            TriagemRepository::inserirConsentimento($pacienteId, $sessaoId, $consentido, $ip, $ua);

            // 3) IA (real com fallback) + colocar em fila
            self::avaliarIAeEnfileirar($sessaoId, "$queixa\n$sintomas\n$antecedentes\n$alergias\n$medicamentos");

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            $_SESSION['flash'] = 'Erro ao salvar triagem: ' . $e->getMessage();
            header('Location: ?r=triagem/nova'); exit;
        }

        header('Location: ?r=triagem/sucesso&sid=' . $sessaoId); exit;
    }

    public static function sucesso(): void
    {
        $sid = (int)($_GET['sid'] ?? 0);
        if ($sid <= 0) { header('Location: ./'); exit; }

        $sessao = TriagemRepository::detalharSessao($sid);
        if (!$sessao) { header('Location: ./'); exit; }

        $codigo = $sessao['codigo_chamada'] ?? '';
        $status = $sessao['status'] ?? '';
        require __DIR__ . '/../Views/triagem-sucesso.php';
    }

    private static function codigoChamada(): string
    {
        $letras = '';
        for ($i = 0; $i < 3; $i++) { $letras .= chr(mt_rand(65, 90)); } // A-Z
        $nums = str_pad((string)mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        return $letras . '-' . $nums;
    }

    /* =========================
       IA (real com fallback)
       ========================= */
    private static function avaliarIAeEnfileirar(int $sessaoId, string $texto): void
    {
        // 1) Tenta IA real (se chave existir)
        $viaIA = GeminiIAService::classificar($texto);

        if ($viaIA) {
            $codigo = $viaIA['codigo'];                                   // VERMELHO/LARANJA/AMARELO/VERDE/AZUL
            $conf   = 0.75;                                               // confiança default (sem probabilidade explícita)
            $motivo = $viaIA['justificativa'] ?: 'classificação automática (IA)';
            $modelo = $viaIA['modelo'] ?? 'gemini';
            $versao = $viaIA['versao'] ?? 'latest';
        } else {
            // 2) Fallback local por palavras-chave
            $txt = mb_strtolower($texto, 'UTF-8');
            $hit = function (array $kw) use ($txt): bool {
                foreach ($kw as $k) { if (mb_strpos($txt, $k) !== false) return true; }
                return false;
            };
            $codigo='AMARELO'; $conf=0.60; $motivo='regra padrão'; $modelo='MVP-Keyword'; $versao='0.1';
            if ($hit(['parada','sem pulso','inconsciente','convuls','hemorragia massiva'])) {
                $codigo='VERMELHO'; $conf=0.95; $motivo='palavras-chave críticas';
            } elseif ($hit(['dor no peito','falta de ar','dispneia','trauma','sangramento','avc','derrame'])) {
                $codigo='LARANJA'; $conf=0.80; $motivo='sintomas de alto risco';
            } elseif ($hit(['febre','vômito','vomito','diarreia','infecção','infeccao','dor moderada'])) {
                $codigo='AMARELO'; $conf=0.65; $motivo='sintomas gerais';
            } elseif ($hit(['dor leve','tosse','resfriado','curativo','medicação','medicacao'])) {
                $codigo='VERDE'; $conf=0.55; $motivo='baixa gravidade';
            }
        }

        // Resolve ID e persiste avaliação
        $nivelId = TriagemRepository::obterNivelIdPorCodigo($codigo) ?? TriagemRepository::obterNivelMaisAltoPeso();
        $laudo   = "Classificação automática – $motivo. Prioridade prevista: $codigo.";
        TriagemRepository::salvarAvaliacaoIA($sessaoId, (int)$nivelId, (float)$conf, $laudo, $modelo, $versao);

        // Coloca em fila
        TriagemRepository::colocarEmFila($sessaoId);
    }
}
