// Migrations - Consentimentos 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('consentimentos', {
      id: { type: Sequelize.BIGINT.UNSIGNED, autoIncrement: true, primaryKey: true },

      // N—1 usuarios (paciente)
      paciente_usuario_id: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: false,
        references: { model: 'usuarios', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'RESTRICT'
      },

      // N—1 sessoes_triagem (opcional, quando for específico de uma submissão)
      sessao_triagem_id: {
        type: Sequelize.BIGINT.UNSIGNED,
        allowNull: true,
        references: { model: 'sessoes_triagem', key: 'id' },
        onUpdate: 'CASCADE',
        onDelete: 'SET NULL'
      },

      // tipo livre no MVP (ex.: "termos", "privacidade", "tratamento_dados", "uso_ia")
      tipo_consentimento: { type: Sequelize.STRING(50), allowNull: false },

      concedido: { type: Sequelize.BOOLEAN, allowNull: false },

      ip: { type: Sequelize.STRING(45), allowNull: true },          // IPv4/IPv6
      user_agent: { type: Sequelize.STRING(255), allowNull: true },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    await queryInterface.addIndex('consentimentos', ['paciente_usuario_id', 'createdAt']);
  },

  async down(queryInterface) {
    await queryInterface.removeIndex('consentimentos', ['paciente_usuario_id', 'createdAt']);
    await queryInterface.dropTable('consentimentos');
  }
};
