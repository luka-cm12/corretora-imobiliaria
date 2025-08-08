const db = require('../config/database');

module.exports = {
  // Listar todos os imóveis
  async listProperties(req, res) {
    try {
      const [properties] = await db.query(`
        SELECT i.*, 
          (SELECT url_imagem FROM imagens_imovel 
           WHERE imovel_id = i.id AND is_principal = TRUE LIMIT 1) as imagem_principal
        FROM imoveis i
        ORDER BY i.data_cadastro DESC
        LIMIT 50
      `);
      res.json(properties);
    } catch (error) {
      console.error(error);
      res.status(500).json({ error: 'Erro ao buscar imóveis' });
    }
  },

  // Busca avançada
  async advancedSearch(req, res) {
    try {
      const filters = req.body;
      let query = `SELECT DISTINCT i.* FROM imoveis i WHERE 1=1`;
      const params = [];

      // Implementar filtros conforme mostrado anteriormente
      // ...

      const [properties] = await db.query(query, params);
      res.json(properties);
    } catch (error) {
      console.error(error);
      res.status(500).json({ error: 'Erro na busca de imóveis' });
    }
  },

  // Criar novo imóvel
  async createProperty(req, res) {
    try {
      const propertyData = req.body;
      const [result] = await db.query('INSERT INTO imoveis SET ?', propertyData);
      const [property] = await db.query('SELECT * FROM imoveis WHERE id = ?', [result.insertId]);
      res.status(201).json(property[0]);
    } catch (error) {
      console.error(error);
      res.status(500).json({ error: 'Erro ao criar imóvel' });
    }
  },

  // Outros métodos (atualizar, deletar, detalhes)...
};