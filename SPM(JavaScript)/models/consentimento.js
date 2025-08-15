'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class Consentimento extends Model {
    static associate(models) {
      Consentimento.belongsTo(models.Usuario, { foreignKey: 'paciente_usuario_id', as: 'paciente' });
      Consentimento.belongsTo(models.SessaoTriagem, { foreignKey: 'sessao_triagem_id', as: 'sessao' });
    }
  }
  Consentimento.init({
    tipo_consentimento: DataTypes.STRING(50),
    concedido: DataTypes.BOOLEAN,
    ip: DataTypes.STRING(45),
    user_agent: DataTypes.STRING(255)
  }, {
    sequelize,
    modelName: 'Consentimento',
    tableName: 'consentimentos'
  });
  return Consentimento;
};
