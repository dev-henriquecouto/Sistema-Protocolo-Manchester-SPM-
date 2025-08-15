require('dotenv').config();
const { sequelize } = require('../models'); // importa index.js gerado pelo CLI
(async () => {
  try {
    await sequelize.authenticate();
    console.log('Sequelize conectado e models carregados ✅');
    process.exit(0);
  } catch (e) {
    console.error('Falha ao conectar:', e);
    process.exit(1);
  }
})();
