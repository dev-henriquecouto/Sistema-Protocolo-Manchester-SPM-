'use strict';
const { Model } = require('sequelize');

module.exports = (sequelize, DataTypes) => {
  class Usuario extends Model {
    static associate(models) {
      Usuario.hasMany(models.SessaoTriagem, {
        foreignKey: 'paciente_usuario_id',
        as: 'sessoesPaciente'
      });
      Usuario.hasMany(models.RevisaoProfissional, {
        foreignKey: 'revisor_usuario_id',
        as: 'revisoesFeitas'
      });
    }
  }
  Usuario.init({
    nome: DataTypes.STRING(120),
    email: DataTypes.STRING(160),
    senha_hash: DataTypes.STRING(255),
    papel: DataTypes.ENUM('paciente','profissional','administrador'),
    telefone: DataTypes.STRING(30),
    ativo: DataTypes.BOOLEAN
  }, {
    sequelize,
    modelName: 'Usuario',
    tableName: 'usuarios'
  });
  return Usuario;
};
