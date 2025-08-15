// Migrations - Sessões Triagem 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('sessoes_triagem', {
      id: { type: Sequelize.BIGINT.UNSIGNED, primaryKey: true, autoIncrement: true },

      // FK -> usuarios (paciente)
      paciente_usuario_id: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        references: { model: 'usuarios', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'RESTRICT'
      },

      // estado do fluxo
      status: {
        type: Sequelize.ENUM(
          'pendente_ia',
          'em_fila',
          'em_revisao',
          'chamado',
          'concluido',
          'cancelado'
        ),
        allowNull: false,
        defaultValue: 'pendente_ia'
      },

      // coleta do formulário (texto livre no MVP)
      queixa_principal: { type: Sequelize.TEXT, allowNull: true },
      sintomas_texto:   { type: Sequelize.TEXT, allowNull: true },
      antecedentes_texto:{ type: Sequelize.TEXT, allowNull: true },
      alergias_texto:   { type: Sequelize.TEXT, allowNull: true },
      medicamentos_texto:{ type: Sequelize.TEXT, allowNull: true },

      // "fila" embutida na sessão
      entrada_fila_em: { type: Sequelize.DATE, allowNull: true },
      saida_fila_em:   { type: Sequelize.DATE, allowNull: true },
      codigo_chamada:  { type: Sequelize.STRING(40), allowNull: true },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    // Índices recomendados
    await queryInterface.addIndex('sessoes_triagem', ['status']);
    await queryInterface.addIndex('sessoes_triagem', ['entrada_fila_em']);
    await queryInterface.addIndex('sessoes_triagem', ['paciente_usuario_id', 'createdAt']);
  },

  async down(queryInterface) {
    await queryInterface.removeIndex('sessoes_triagem', ['status']);
    await queryInterface.removeIndex('sessoes_triagem', ['entrada_fila_em']);
    await queryInterface.removeIndex('sessoes_triagem', ['paciente_usuario_id', 'createdAt']);
    await queryInterface.dropTable('sessoes_triagem');
  }
};
