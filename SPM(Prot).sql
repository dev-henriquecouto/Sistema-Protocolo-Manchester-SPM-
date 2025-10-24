-- Target: MariaDB 10.4+
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE `spm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `spm`;

CREATE TABLE `usuarios` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `papel` ENUM('paciente','profissional','administrador') NOT NULL,
  `telefone` VARCHAR(30) DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  KEY `ix_usuarios_papel` (`papel`),
  KEY `ix_usuarios_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `niveis_prioridade` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` ENUM('AZUL','VERDE','AMARELO','LARANJA','VERMELHO') NOT NULL,
  `sla_minutos` INT(10) UNSIGNED NOT NULL,
  `peso_ordenacao` INT(11) NOT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_niveis_prioridade_codigo` (`codigo`),
  KEY `ix_niveis_prioridade_peso` (`peso_ordenacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessoes_triagem` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paciente_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
  `status` ENUM('pendente_ia','em_fila','em_revisao','chamado','concluido','cancelado') NOT NULL DEFAULT 'pendente_ia',
  `queixa_principal` TEXT DEFAULT NULL,
  `sintomas_texto` TEXT DEFAULT NULL,
  `antecedentes_texto` TEXT DEFAULT NULL,
  `alergias_texto` TEXT DEFAULT NULL,
  `medicamentos_texto` TEXT DEFAULT NULL,
  `entrada_fila_em` DATETIME DEFAULT NULL,
  `saida_fila_em` DATETIME DEFAULT NULL,
  `codigo_chamada` VARCHAR(40) DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `ix_sessoes_status` (`status`),
  KEY `ix_sessoes_entrada_fila_em` (`entrada_fila_em`),
  KEY `ix_sessoes_paciente_created` (`paciente_usuario_id`,`createdAt`),
  CONSTRAINT `fk_sessoes_paciente` FOREIGN KEY (`paciente_usuario_id`) REFERENCES `usuarios`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `avaliacoes_ia` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sessao_triagem_id` BIGINT(20) UNSIGNED NOT NULL,
  `prioridade_prevista` BIGINT(20) UNSIGNED NOT NULL,
  `confianca` DECIMAL(5,4) DEFAULT NULL,
  `laudo_ia` TEXT DEFAULT NULL,
  `modelo_nome` VARCHAR(80) DEFAULT NULL,
  `modelo_versao` VARCHAR(40) DEFAULT NULL,
  -- Em MariaDB JSON é alias de LONGTEXT; para evitar erro no Workbench, NÃO usar CHECK aqui
  `payload_bruto` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_avaliacoes_sessao` (`sessao_triagem_id`),
  KEY `ix_avaliacoes_prioridade` (`prioridade_prevista`),
  CONSTRAINT `fk_avaliacoes_sessao` FOREIGN KEY (`sessao_triagem_id`) REFERENCES `sessoes_triagem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_avaliacoes_prioridade` FOREIGN KEY (`prioridade_prevista`) REFERENCES `niveis_prioridade`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `consentimentos` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paciente_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
  `sessao_triagem_id` BIGINT(20) UNSIGNED DEFAULT NULL,
  `tipo_consentimento` VARCHAR(50) NOT NULL,
  `concedido` TINYINT(1) NOT NULL,
  `ip` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `ix_consent_sessao` (`sessao_triagem_id`),
  KEY `ix_consent_paciente_created` (`paciente_usuario_id`,`createdAt`),
  CONSTRAINT `fk_consent_paciente` FOREIGN KEY (`paciente_usuario_id`) REFERENCES `usuarios`(`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_consent_sessao` FOREIGN KEY (`sessao_triagem_id`) REFERENCES `sessoes_triagem`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `revisoes_profissionais` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sessao_triagem_id` BIGINT(20) UNSIGNED NOT NULL,
  `revisor_usuario_id` BIGINT(20) UNSIGNED NOT NULL,
  `prioridade_final` BIGINT(20) UNSIGNED NOT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `revisado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_revisoes_sessao` (`sessao_triagem_id`),
  KEY `ix_revisoes_revisor` (`revisor_usuario_id`),
  KEY `ix_revisoes_prioridade_revisado` (`prioridade_final`,`revisado_em`),
  CONSTRAINT `fk_revisoes_sessao` FOREIGN KEY (`sessao_triagem_id`) REFERENCES `sessoes_triagem`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_revisoes_revisor` FOREIGN KEY (`revisor_usuario_id`) REFERENCES `usuarios`(`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_revisoes_prioridade` FOREIGN KEY (`prioridade_final`) REFERENCES `niveis_prioridade`(`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `sequelizemeta` (
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

COMMIT;


INSERT INTO niveis_prioridade (id, codigo, peso_ordenacao, sla_minutos) VALUES
(1,'AZUL',     1,240),
(2,'VERDE',    2,120),
(3,'AMARELO',  3, 60),
(4,'LARANJA',  4, 30),
(5,'VERMELHO', 5,  0);

select * from usuarios;

INSERT INTO usuarios (nome, email, senha_hash, papel) 
VALUES ('Admin', 'admin@spm.local', '$2y$12$zBzjtbHFg4mCLhZtHbDZHO1syvH3E87XlTxpP6syZWougDS5GJcoa', 2);

update usuarios set papel = 2 where email = 'admin@spm.local';

-- Notificações simples, leves e auditáveis
CREATE TABLE notificacoes (
  id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT(20) UNSIGNED NOT NULL,
  tipo VARCHAR(50) NOT NULL,               -- ex.: 'paciente_chamado'
  mensagem VARCHAR(255) NOT NULL,          -- texto curto da notificação
  metadata JSON NULL,                      -- MariaDB 10.4+ aceita JSON (alias de LONGTEXT)
  lida TINYINT(1) NOT NULL DEFAULT 0,      -- 0 = não lida, 1 = lida
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  INDEX idx_notif_usuario (usuario_id),
  CONSTRAINT fk_notif_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
    ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

SHOW CREATE TABLE usuarios;
SHOW CREATE TABLE notificacoes;

