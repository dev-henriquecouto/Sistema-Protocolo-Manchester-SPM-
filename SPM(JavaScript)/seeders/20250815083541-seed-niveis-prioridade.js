// Seed niveis prioridades JS 

'use strict';

module.exports = {
  async up (queryInterface) {
    const now = new Date();
    // Ajuste os SLA conforme sua operação (estes são exemplos comuns)
    const rows = [
      { codigo: 'VERMELHO', sla_minutos: 0,   peso_ordenacao: 5, createdAt: now, updatedAt: now },
      { codigo: 'LARANJA',  sla_minutos: 10,  peso_ordenacao: 4, createdAt: now, updatedAt: now },
      { codigo: 'AMARELO',  sla_minutos: 60,  peso_ordenacao: 3, createdAt: now, updatedAt: now },
      { codigo: 'VERDE',    sla_minutos: 120, peso_ordenacao: 2, createdAt: now, updatedAt: now },
      { codigo: 'AZUL',     sla_minutos: 240, peso_ordenacao: 1, createdAt: now, updatedAt: now }
    ];

    // Evita duplicar caso o seeder rode mais de uma vez
    const existentes = await queryInterface.sequelize.query(
      'SELECT codigo FROM niveis_prioridade',
      { type: queryInterface.sequelize.QueryTypes.SELECT }
    );
    const jaTem = new Set(existentes.map(r => r.codigo));
    const inserir = rows.filter(r => !jaTem.has(r.codigo));
    if (inserir.length) {
      await queryInterface.bulkInsert('niveis_prioridade', inserir);
    }
  },

  async down (queryInterface) {
    await queryInterface.bulkDelete('niveis_prioridade', null, {});
  }
};
