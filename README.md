# 🏥 SISTEMA PROTOCOLO MANCHESTER

Sistema web desenvolvido como **Trabalho de Conclusão de Curso (TCC)**, com o objetivo de automatizar o processo de triagem hospitalar utilizando a **Escala de Manchester**.

O sistema recebe sintomas relatados por pacientes, que são processados por uma **Inteligência Artificial**. A IA gera um **laudo clínico** e um **nível de prioridade**, que são enviados para o **painel de controle hospitalar**, onde os profissionais de saúde podem verificar e validar os dados.  

💡 Nosso foco é **reduzir filas**, **melhorar a organização no atendimento** e **aumentar o controle sobre o fluxo de pacientes**.

---

# SPM 1.0 — Triagem com IA (MVP)

> Sistema de Priorização de Manchester (SPM) com apoio de IA para classificar risco e organizar a fila de atendimento. Projeto acadêmico/protótipo.

---

## Integrantes do Projeto

| Nome completo | Matrícula |
| --- | --- |
| _Henrique Andrade Couto_ | _12402079_ |
| _Leonardo da Rocha_ | _12400599_ |
| _Janine de Souza_ | _12401803_ |
| _Mariana Clara_ | _12302481_ |

---

## Funcionalidades (16)

1. **Identificação do paciente** via Google One Tap / Sign-In (OIDC) **ou** formulário manual.  
2. **Cadastro de sessão de triagem** com campos: queixa principal, sintomas, antecedentes, alergias e medicamentos.  
3. **Registro de consentimento** (LGPD) com IP e User-Agent vinculados à sessão.  
4. **Classificação automática por IA (Gemini 2.0 Flash)** com **fallback por palavras‑chave** quando a API key não está configurada/indisponível.  
5. **Geração de código de chamada** (formato `ABC-123`) por sessão de triagem.  
6. **Fila de atendimento** ordenada por **prioridade prevista/final** e **tempo de entrada**, com **busca** e **filtro por prioridade**.  
7. **Área do profissional** (login + sessão) para **revisar**, **confirmar prioridade final** e **adicionar observações** por sessão.  
8. **Chamada de paciente** (remove da fila e registra horário de saída).  
9. **Painel público de chamadas (TV)** com últimas chamadas e atualização automática a cada 30s.  
10. **Estatísticas rápidas na fila** (contagem por prioridade) e componentes de UI responsivos com Bootstrap 5.
11. **Filtro por prioridade no painel (TV) com seleção de prioridades exibidas** persistência via URL (querystring) e manutenção após autoatualização a cada 30s.
12. **Pesquisa de pacientes por nome, documento e código de chamada (ABC-123)** com tolerância a acentos/typos (normalização) e destaque do termo encontrado.
13. **Notificação de chamada ao usuário por e-mail/SMS/WhatsApp/Web Push (configurável)** com consentimento explícito, rate-limit, templates personalizáveis e log de entregas/falhas.
14. **Relatório diário, semanal e mensal**
15. **Cards dinâmicos por prioridade (contadores com cor/ícone)** atualizados em tempo real; clique aplica filtro na fila; tooltips com percentuais e variação vs. período anterior.
16. **Controle de prioridade pelo profissional** com revisão/override da IA, obrigatoriedade de motivo, trilha de auditoria (quem/quando/de→para) e permissões por perfil.

---

## Como rodar o projeto

### 1) Pré‑requisitos
- **PHP 8.1+** (com extensões `pdo_mysql`, `curl`, `mbstring`).
- **MariaDB 10.4+** (ou MySQL compatível).
- Navegador moderno.
- Relatórios (DOMPDF via Composer): na raiz do projeto, execute composer require dompdf/dompdf; garanta escrita em /storage/dompdf/{cache,fonts}; e inclua require __DIR__.'/vendor/autoload.php';
> O projeto é **PHP puro** (com Composer) e utiliza **Bootstrap/Font Awesome via CDN**.

### 2) Banco de dados
1. Crie o banco e tabelas importando o script:  
   ```sql
   -- No seu cliente SQL (MySQL/MariaDB)
   SOURCE SPM1.0/SPM(Prot).sql;
   ```
2. Opcionalmente, **crie um usuário PROFISSIONAL** para acesso ao painel:
   ```sql
   -- gere o hash com: php -r "echo password_hash('admin123', PASSWORD_BCRYPT), PHP_EOL;"
   INSERT INTO usuarios (nome, email, senha_hash, papel, ativo)
   VALUES ('Admin Demo', 'admin@spm.local', '<SUBSTITUA_PELO_HASH_BCRYPT>', 'profissional', 1);
   ```

### 3) Configuração de credenciais
Edite `SPM1.0/APP/config.php` e ajuste as constantes:
```php
// Banco
const DB_HOST = '127.0.0.1';
const DB_NAME = 'spm';
const DB_USER = 'root';
const DB_PASS = 'senha';
const DB_CHARSET = 'utf8mb4';

// Google Sign-In (paciente)
define('GOOGLE_CLIENT_ID', '<SEU_CLIENT_ID>'); // ou mantenha o demo

// IA Gemini (classificação automática)
define('GEMINI_API_KEY', '<SUA_API_KEY>'); // deixe '' para usar o fallback por palavras‑chave
```

### 4) Executando (servidor embutido do PHP)
No diretório **raiz do repositório**, rode:
```bash
php -S localhost:8080 -t SPM1.0
```
Acesse:
- Fluxo do paciente: `http://localhost:8080/` → **Iniciar triagem**  
- Login do profissional: `http://localhost:8080/?r=auth/login`  
  - Após login: **Fila** `?r=admin/fila`, **Revisão** `?r=admin/sessao&sid=<ID>`, **Painel (TV)** `?r=painel/chamadas`

---

## Estrutura do projeto (resumo)

```
SPM1.0/
├─ index.php                # roteador leve por query-string (?r=...)
├─ SPM(Prot).sql            # schema do banco (MariaDB 10.4+)
└─ APP/
   ├─ config.php            # configurações (DB, Google, Gemini)
   ├─ database.php          # singleton PDO
   ├─ Controllers/
   │  ├─ AuthController.php       # login/sessão do profissional + Google para paciente
   │  ├─ TriagemController.php    # fluxo de triagem + IA + fila
   │  ├─ FilaController.php       # listagem/estatísticas/UX da fila
   │  ├─ RevisaoController.php    # revisão e decisão final
   │  └─ PainelController.php     # painel público de chamadas
   ├─ Models/
   │  └─ Usuario.php
   ├─ Repositories/
   │  ├─ UsuarioRepository.php
   │  ├─ TriagemRepository.php
   │  └─ FilaRepository.php
   ├─ Services/
   │  └─ GeminiIAService.php      # integração com Gemini 2.0 Flash
   └─ Views/
      ├─ home.php
      ├─ auth-login.php
      ├─ triagem-identificar.php
      ├─ triagem-nova.php
      ├─ triagem-sucesso.php
      ├─ fila-index.php
      ├─ revisao-detalhe.php
      ├─ painel-chamadas.php
      └─ includes/header.php
```

---

## Rotas principais

- `GET /` → Home (CTA **Iniciar triagem**).  
- `GET /?r=triagem/identificar` → Identificação do paciente (Google ou manual).  
- `POST /?r=triagem/identificar-post` → Processa identificação/cadastro.  
- `GET /?r=triagem/nova` → Formulário da triagem.  
- `POST /?r=triagem/criar` → Cria sessão, registra consentimento, classifica (IA/fallback) e coloca na fila.  
- `GET /?r=triagem/sucesso&sid=<ID>` → Confirmação com código de chamada.  
- `GET /?r=auth/login` → Login do profissional.  
- `GET /?r=admin/fila` → Fila de atendimento (busca/filtro/estatísticas).  
- `GET /?r=admin/sessao&sid=<ID>` → Revisão da sessão e decisão final.  
- `POST /?r=admin/sessao/confirmar` → Persiste prioridade final/observações.  
- `POST /?r=admin/sessao/chamar` → Chama paciente (remove da fila).  
- `GET /?r=painel/chamadas` → Painel público (TV) com últimas chamadas.

---

### Créditos & Licença

Projeto acadêmico/protótipo para fins educacionais. Sem garantias. Ajuste a licença conforme necessidade da disciplina/curso.


