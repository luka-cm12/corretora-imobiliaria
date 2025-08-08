// controllers/authController.js
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const db = require('../config/database');
const emailService = require('../services/emailService');
const { validateRegisterInput, validateLoginInput } = require('../validators/authValidator');

// Configurações
const JWT_SECRET = process.env.JWT_SECRET;
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '8h';
const SALT_ROUNDS = 12;

/**
 * Registro de novo usuário (corretor/admin)
 */
const register = async (req, res) => {
    try {
        // Validação dos dados de entrada
        const { errors, isValid } = validateRegisterInput(req.body);
        
        if (!isValid) {
            return res.status(400).json({ success: false, errors });
        }

        const { name, email, password, phone, type } = req.body;

        // Verificar se o email já está cadastrado
        const [existingUser] = await db.query(
            'SELECT id FROM usuarios WHERE email = ?', 
            [email]
        );

        if (existingUser.length > 0) {
            return res.status(400).json({ 
                success: false, 
                message: 'Email já está em uso' 
            });
        }

        // Criptografar senha
        const hashedPassword = await bcrypt.hash(password, SALT_ROUNDS);

        // Criar código de verificação
        const verificationCode = crypto.randomBytes(20).toString('hex');
        const verificationExpires = new Date(Date.now() + 24 * 60 * 60 * 1000); // 24 horas

        // Inserir novo usuário no banco de dados
        const [result] = await db.query(
            'INSERT INTO usuarios (nome, email, senha_hash, telefone, tipo, verification_code, verification_expires) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [name, email, hashedPassword, phone, type, verificationCode, verificationExpires]
        );

        // Enviar email de verificação
        const verificationUrl = `${process.env.FRONTEND_URL}/verify-email?code=${verificationCode}`;
        
        await emailService.sendVerificationEmail(
            email,
            name,
            verificationUrl
        );

        // Gerar token JWT
        const token = generateToken(result.insertId, email, type);

        res.status(201).json({
            success: true,
            message: 'Registro realizado com sucesso. Verifique seu email para ativar sua conta.',
            token,
            user: {
                id: result.insertId,
                name,
                email,
                type
            }
        });

    } catch (error) {
        console.error('Erro no registro:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro no servidor ao processar registro' 
        });
    }
};

/**
 * Login de usuário
 */
const login = async (req, res) => {
    try {
        // Validação dos dados de entrada
        const { errors, isValid } = validateLoginInput(req.body);
        
        if (!isValid) {
            return res.status(400).json({ success: false, errors });
        }

        const { email, password } = req.body;

        // Buscar usuário no banco de dados
        const [users] = await db.query(
            'SELECT * FROM usuarios WHERE email = ?', 
            [email]
        );

        if (users.length === 0) {
            return res.status(401).json({ 
                success: false, 
                message: 'Credenciais inválidas' 
            });
        }

        const user = users[0];

        // Verificar se a conta está ativa
        if (!user.email_verificado) {
            return res.status(403).json({
                success: false,
                message: 'Conta não verificada. Por favor, verifique seu email.'
            });
        }

        // Verificar senha
        const isMatch = await bcrypt.compare(password, user.senha_hash);
        
        if (!isMatch) {
            return res.status(401).json({ 
                success: false, 
                message: 'Credenciais inválidas' 
            });
        }

        // Gerar token JWT
        const token = generateToken(user.id, user.email, user.tipo);

        res.json({
            success: true,
            message: 'Login realizado com sucesso',
            token,
            user: {
                id: user.id,
                name: user.nome,
                email: user.email,
                type: user.tipo,
                phone: user.telefone
            }
        });

    } catch (error) {
        console.error('Erro no login:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro no servidor ao processar login' 
        });
    }
};

/**
 * Verificação de email
 */
const verifyEmail = async (req, res) => {
    try {
        const { code } = req.params;

        // Buscar usuário pelo código de verificação
        const [users] = await db.query(
            'SELECT * FROM usuarios WHERE verification_code = ? AND verification_expires > NOW()',
            [code]
        );

        if (users.length === 0) {
            return res.status(400).json({ 
                success: false, 
                message: 'Código de verificação inválido ou expirado' 
            });
        }

        const user = users[0];

        // Atualizar usuário como verificado
        await db.query(
            'UPDATE usuarios SET email_verificado = TRUE, verification_code = NULL, verification_expires = NULL WHERE id = ?',
            [user.id]
        );

        res.json({
            success: true,
            message: 'Email verificado com sucesso'
        });

    } catch (error) {
        console.error('Erro na verificação de email:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro ao verificar email' 
        });
    }
};

/**
 * Solicitação de redefinição de senha
 */
const forgotPassword = async (req, res) => {
    try {
        const { email } = req.body;

        // Verificar se o email existe
        const [users] = await db.query(
            'SELECT * FROM usuarios WHERE email = ?',
            [email]
        );

        if (users.length === 0) {
            return res.status(404).json({ 
                success: false, 
                message: 'Email não encontrado' 
            });
        }

        const user = users[0];

        // Criar token de redefinição
        const resetToken = crypto.randomBytes(20).toString('hex');
        const resetExpires = new Date(Date.now() + 3600000); // 1 hora

        // Salvar token no banco de dados
        await db.query(
            'UPDATE usuarios SET reset_token = ?, reset_expires = ? WHERE id = ?',
            [resetToken, resetExpires, user.id]
        );

        // Enviar email com link de redefinição
        const resetUrl = `${process.env.FRONTEND_URL}/reset-password?token=${resetToken}`;
        
        await emailService.sendPasswordResetEmail(
            user.email,
            user.nome,
            resetUrl
        );

        res.json({
            success: true,
            message: 'Email de redefinição de senha enviado'
        });

    } catch (error) {
        console.error('Erro ao solicitar redefinição de senha:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro ao processar solicitação' 
        });
    }
};

/**
 * Redefinição de senha
 */
const resetPassword = async (req, res) => {
    try {
        const { token } = req.params;
        const { password } = req.body;

        // Verificar token válido
        const [users] = await db.query(
            'SELECT * FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()',
            [token]
        );

        if (users.length === 0) {
            return res.status(400).json({ 
                success: false, 
                message: 'Token inválido ou expirado' 
            });
        }

        const user = users[0];

        // Criptografar nova senha
        const hashedPassword = await bcrypt.hash(password, SALT_ROUNDS);

        // Atualizar senha e limpar token
        await db.query(
            'UPDATE usuarios SET senha_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?',
            [hashedPassword, user.id]
        );

        res.json({
            success: true,
            message: 'Senha redefinida com sucesso'
        });

    } catch (error) {
        console.error('Erro ao redefinir senha:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro ao redefinir senha' 
        });
    }
};

/**
 * Atualização de perfil
 */
const updateProfile = async (req, res) => {
    try {
        const userId = req.user.id;
        const { name, phone } = req.body;

        // Validar dados
        if (!name || !phone) {
            return res.status(400).json({ 
                success: false, 
                message: 'Nome e telefone são obrigatórios' 
            });
        }

        // Atualizar perfil
        await db.query(
            'UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?',
            [name, phone, userId]
        );

        res.json({
            success: true,
            message: 'Perfil atualizado com sucesso'
        });

    } catch (error) {
        console.error('Erro ao atualizar perfil:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro ao atualizar perfil' 
        });
    }
};

/**
 * Alteração de senha
 */
const changePassword = async (req, res) => {
    try {
        const userId = req.user.id;
        const { currentPassword, newPassword } = req.body;

        // Buscar usuário
        const [users] = await db.query(
            'SELECT senha_hash FROM usuarios WHERE id = ?',
            [userId]
        );

        if (users.length === 0) {
            return res.status(404).json({ 
                success: false, 
                message: 'Usuário não encontrado' 
            });
        }

        const user = users[0];

        // Verificar senha atual
        const isMatch = await bcrypt.compare(currentPassword, user.senha_hash);
        
        if (!isMatch) {
            return res.status(401).json({ 
                success: false, 
                message: 'Senha atual incorreta' 
            });
        }

        // Criptografar nova senha
        const hashedPassword = await bcrypt.hash(newPassword, SALT_ROUNDS);

        // Atualizar senha
        await db.query(
            'UPDATE usuarios SET senha_hash = ? WHERE id = ?',
            [hashedPassword, userId]
        );

        res.json({
            success: true,
            message: 'Senha alterada com sucesso'
        });

    } catch (error) {
        console.error('Erro ao alterar senha:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Erro ao alterar senha' 
        });
    }
};

/**
 * Gera um token JWT
 */
function generateToken(userId, email, userType) {
    return jwt.sign(
        { id: userId, email, type: userType },
        JWT_SECRET,
        { expiresIn: JWT_EXPIRES_IN }
    );
}

module.exports = {
    register,
    login,
    verifyEmail,
    forgotPassword,
    resetPassword,
    updateProfile,
    changePassword
};