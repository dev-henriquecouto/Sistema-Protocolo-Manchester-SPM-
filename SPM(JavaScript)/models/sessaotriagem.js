'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class SessaoTriagem extends Model {
    static associate(models) {
      SessaoTriagem.belongsTo(models.Usuario, { foreignKey: 'paciente_usuario_id', as: 'paciente' });
      SessaoTriagem.hasOne(models.AvaliacaoIA, { foreignKey: 'sessao_triagem_id', as: 'avaliacao' });
      SessaoTriagem.hasOne(models.RevisaoProfissional, { foreignKey: 'sessao_triagem_id', as: 'revisao' });
    }
  }
  SessaoTriagem.init({
    status: DataTypes.ENUM('pendente_ia','em_fila','em_revisao','chamado','concluido','cancelado'),
    queixa_principal: DataTypes.TEXT,
    sintomas_texto: DataTypes.TEXT,
    antecedentes_texto: DataTypes.TEXT,
    alergias_texto: DataTypes.TEXT,
    medicamentos_texto: DataTypes.TEXT,
    entrada_fila_em: DataTypes.DATE,
    saida_fila_em: DataTypes.DATE,
    codigo_chamada: DataTypes.STRING(40)
  }, {
    sequelize,
    modelName: 'SessaoTriagem',
    tableName: 'sessoes_triagem'
  });
  return SessaoTriagem;
};
