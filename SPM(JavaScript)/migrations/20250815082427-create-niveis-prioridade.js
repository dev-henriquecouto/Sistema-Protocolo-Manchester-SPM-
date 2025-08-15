// Migrations Nivel-Prioridade 

'use strict';

module.exports = {
  async up(queryInterface, Sequelize) {
    await queryInterface.createTable('niveis_prioridade', {
      id: { type: Sequelize.BIGINT.UNSIGNED, autoIncrement: true, primaryKey: true },

      codigo: {
        // Cores do Manchester
        type: Sequelize.ENUM('AZUL', 'VERDE', 'AMARELO', 'LARANJA', 'VERMELHO'),
        allowNull: false,
        unique: true
      },

      // SLA em minutos para atendimento do nível
      sla_minutos: { type: Sequelize.INTEGER.UNSIGNED, allowNull: false },

      // Para ordenar a fila (maior = mais urgente)
      peso_ordenacao: { type: Sequelize.INTEGER, allowNull: false },

      createdAt: { type: Sequelize.DATE, allowNull: false, defaultValue: Sequelize.fn('CURRENT_TIMESTAMP') },
      updatedAt: { type: Sequelize.DATE, allowNull: true }
    });

    await queryInterface.addIndex('niveis_prioridade', ['peso_ordenacao']);
  },

  async down(queryInterface, Sequelize) {
    await queryInterface.removeIndex('niveis_prioridade', ['peso_ordenacao']);
    await queryInterface.dropTable('niveis_prioridade');
    // MySQL: o tipo ENUM é removido com a tabela.
  }
};
