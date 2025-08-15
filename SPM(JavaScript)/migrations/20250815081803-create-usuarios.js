// Migration - Usuarios 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('usuarios', {
      id: { type: Sequelize.BIGINT.UNSIGNED, autoIncrement: true, primaryKey: true },

      nome: { type: Sequelize.STRING(120), allowNull: false },

      email: { type: Sequelize.STRING(160), allowNull: false, unique: true },

      senha_hash: { type: Sequelize.STRING(255), allowNull: false },

      papel: { 
        type: Sequelize.ENUM('paciente', 'profissional', 'administrador'),
        allowNull: false
      },

      telefone: { type: Sequelize.STRING(30), allowNull: true },

      ativo: { type: Sequelize.BOOLEAN, allowNull: false, defaultValue: true },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    // Índices úteis
    await queryInterface.addIndex('usuarios', ['papel']);
    await queryInterface.addIndex('usuarios', ['ativo']);
  },

  async down(queryInterface, Sequelize) {
    // Remover índices primeiro (boa prática)
    await queryInterface.removeIndex('usuarios', ['papel']);
    await queryInterface.removeIndex('usuarios', ['ativo']);

    await queryInterface.dropTable('usuarios');

    // Em MySQL, ENUM vive na tabela; após dropar, já era.
    // Se fosse Postgres, removeríamos o tipo ENUM manualmente.
  }
};
