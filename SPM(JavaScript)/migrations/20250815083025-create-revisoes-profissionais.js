// Migrations - Revisões Profissionais 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('revisoes_profissionais', {
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

      // N—1 usuarios (profissional revisor)
      revisor_usuario_id: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        references: { model: 'usuarios', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'RESTRICT'
      },

      // N—1 niveis_prioridade
      prioridade_final: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        references: { model: 'niveis_prioridade', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'RESTRICT'
      },

      observacoes: { type: Sequelize.TEXT, allowNull: true },
      revisado_em: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    await queryInterface.addIndex('revisoes_profissionais', ['prioridade_final', 'revisado_em']);
  },

  async down(queryInterface) {
    await queryInterface.removeIndex('revisoes_profissionais', ['prioridade_final', 'revisado_em']);
    await queryInterface.dropTable('revisoes_profissionais');
  }
};
