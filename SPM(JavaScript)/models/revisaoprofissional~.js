'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class RevisaoProfissional extends Model {
    static associate(models) {
      RevisaoProfissional.belongsTo(models.SessaoTriagem, { foreignKey: 'sessao_triagem_id', as: 'sessao' });
      RevisaoProfissional.belongsTo(models.Usuario, { foreignKey: 'revisor_usuario_id', as: 'revisor' });
      RevisaoProfissional.belongsTo(models.NivelPrioridade, { foreignKey: 'prioridade_final', as: 'prioridadeFinal' });
    }
  }
  RevisaoProfissional.init({
    observacoes: DataTypes.TEXT,
    revisado_em: DataTypes.DATE
  }, {
    sequelize,
    modelName: 'RevisaoProfissional',
    tableName: 'revisoes_profissionais'
  });
  return RevisaoProfissional;
};
