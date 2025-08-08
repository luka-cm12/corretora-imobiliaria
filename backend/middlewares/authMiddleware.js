// middlewares/authMiddleware.js
const jwt = require('jsonwebtoken');
const { authenticate, authorize } = require('./middlewares/authMiddleware');

// Rota apenas para autenticados
router.get('/profile', authenticate, (req, res) => {
    res.json(req.user);
});

// Rota apenas para admin
router.get('/admin', authenticate, authorize(['admin']), (req, res) => {
    res.json({ message: 'Acesso admin autorizado' });
});

function authenticate(req, res, next) {
    // Obter token do header
    const token = req.header('Authorization')?.replace('Bearer ', '');
    
    if (!token) {
        return res.status(401).json({ 
            success: false, 
            message: 'Acesso negado. Token não fornecido.' 
        });
    }

    try {
        // Verificar token
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        
        // Adicionar usuário ao request
        req.user = decoded;
        next();
    } catch (error) {
        console.error('Erro na autenticação:', error);
        
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({ 
                success: false, 
                message: 'Token expirado. Faça login novamente.' 
            });
        }
        
        res.status(401).json({ 
            success: false, 
            message: 'Token inválido' 
        });
    }
}

function authorize(roles = []) {
    return (req, res, next) => {
        if (roles.length && !roles.includes(req.user.type)) {
            return res.status(403).json({ 
                success: false, 
                message: 'Acesso não autorizado' 
            });
        }
        next();
    };
}

module.exports = {
    authenticate,
    authorize
};