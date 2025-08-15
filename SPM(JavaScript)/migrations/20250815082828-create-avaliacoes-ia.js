// Migrations avaliações-IA 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('avaliacoes_ia', {
      id: { type: Sequelize.BIGINT.UNSIGNED, autoIncrement: true, primaryKey: true },

      // 1—1 com sessoes_triagem
      sessao_triagem_id: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        unique: true,
        references: { model: 'sessoes_triagem', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'CASCADE'
      },

      // FK -> niveis_prioridade
      prioridade_prevista: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        references: { model: 'niveis_prioridade', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'RESTRICT'
      },

      confianca:     { type: Sequelize.DECIMAL(5,4), allowNull: true }, // ex.: 0.0–1.0
      laudo_ia:      { type: Sequelize.TEXT, allowNull: true },
      modelo_nome:   { type: Sequelize.STRING(80), allowNull: true },
      modelo_versao: { type: Sequelize.STRING(40), allowNull: true },
      payload_bruto: { type: Sequelize.JSON, allowNull: true },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    await queryInterface.addIndex('avaliacoes_ia', ['prioridade_prevista']);
  },

  async down(queryInterface) {
    await queryInterface.removeIndex('avaliacoes_ia', ['prioridade_prevista']);
    await queryInterface.dropTable('avaliacoes_ia');
  }
};
