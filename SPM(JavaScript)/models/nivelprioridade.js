'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class NivelPrioridade extends Model {
    static associate(models) {
      NivelPrioridade.hasMany(models.AvaliacaoIA, { foreignKey: 'prioridade_prevista', as: 'avaliacoes' });
      NivelPrioridade.hasMany(models.RevisaoProfissional, { foreignKey: 'prioridade_final', as: 'revisoes' });
    }
  }
  NivelPrioridade.init({
    codigo: DataTypes.ENUM('AZUL','VERDE','AMARELO','LARANJA','VERMELHO'),
    sla_minutos: DataTypes.INTEGER.UNSIGNED,
    peso_ordenacao: DataTypes.INTEGER
  }, {
    sequelize,
    modelName: 'NivelPrioridade',
    tableName: 'niveis_prioridade'
  });
  return NivelPrioridade;
};
