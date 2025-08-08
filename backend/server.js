require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const session = require('express-session');
const path = require('path');
const router = express.Router();
const nodemailer = require('nodemailer');
const { body, validationResult } = require('express-validator');

// Importar rotas
const authRoutes = require('./routes/authRoutes');
const propertyRoutes = require('./routes/propertyRoutes');
const imageRoutes = require('./routes/imageRoutes');

const app = express();

// Configurações
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:3000',
  credentials: true
}));

app.use(session({
  secret: process.env.SESSION_SECRET || 'secret-key',
  resave: false,
  saveUninitialized: false,
  cookie: { secure: process.env.NODE_ENV === 'production' }
}));

// Rotas públicas
app.use('/api/auth', authRoutes);

// Middleware de autenticação
const { authenticate } = require('./middlewares/authMiddleware');
app.use(authenticate);

// Rotas protegidas
app.use('/api/properties', propertyRoutes);
app.use('/api/images', imageRoutes);

// Servir arquivos estáticos (se necessário)
app.use('/uploads', express.static(path.join(__dirname, 'public/uploads')));

// Tratamento de erros
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Erro interno do servidor' });
});

// Iniciar servidor
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Servidor rodando na porta ${PORT}`);
});

// Configuração do transporte de e-mail
const transporter = nodemailer.createTransport({
  host: process.env.EMAIL_HOST,
  port: process.env.EMAIL_PORT,
  secure: true,
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASS
  }
});

// Rota de contato com validação
router.post('/api/contato', 
  [
    body('name').trim().notEmpty().withMessage('Nome é obrigatório'),
    body('email').isEmail().withMessage('E-mail inválido'),
    body('phone').matches(/^\(\d{2}\) \d{4,5}-\d{4}$/).withMessage('Telefone inválido'),
    body('subject').notEmpty().withMessage('Assunto é obrigatório'),
    body('message').trim().notEmpty().withMessage('Mensagem é obrigatória')
  ],
  async (req, res) => {
    // Validação dos dados
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }

    try {
      // Dados do formulário
      const { name, email, phone, subject, message } = req.body;

      // 1. Salvar no banco de dados (opcional)
      // await saveContactForm(name, email, phone, subject, message);

      // 2. Enviar e-mail
      const mailOptions = {
        from: `"Site Base Imóveis" <${process.env.EMAIL_FROM}>`,
        to: process.env.EMAIL_TO,
        subject: `Novo contato: ${subject}`,
        html: `
          <h2>Novo contato recebido</h2>
          <p><strong>Nome:</strong> ${name}</p>
          <p><strong>E-mail:</strong> ${email}</p>
          <p><strong>Telefone:</strong> ${phone}</p>
          <p><strong>Assunto:</strong> ${subject}</p>
          <p><strong>Mensagem:</strong></p>
          <p>${message}</p>
        `
      };

      await transporter.sendMail(mailOptions);

      // 3. Responder ao cliente
      res.json({ 
        success: true,
        message: 'Mensagem enviada com sucesso!'
      });

    } catch (error) {
      console.error('Erro no formulário de contato:', error);
      res.status(500).json({ 
        success: false,
        message: 'Erro ao enviar mensagem. Tente novamente mais tarde.'
      });
    }
  }
);

module.exports = router;