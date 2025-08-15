'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class AvaliacaoIA extends Model {
    static associate(models) {
      AvaliacaoIA.belongsTo(models.SessaoTriagem, { foreignKey: 'sessao_triagem_id', as: 'sessao' });
      AvaliacaoIA.belongsTo(models.NivelPrioridade, { foreignKey: 'prioridade_prevista', as: 'prioridade' });
    }
  }
  AvaliacaoIA.init({
    confianca: DataTypes.DECIMAL(5,4),
    laudo_ia: DataTypes.TEXT,
    modelo_nome: DataTypes.STRING(80),
    modelo_versao: DataTypes.STRING(40),
    payload_bruto: DataTypes.JSON
  }, {
    sequelize,
    modelName: 'AvaliacaoIA',
    tableName: 'avaliacoes_ia'
  });
  return AvaliacaoIA;
};
