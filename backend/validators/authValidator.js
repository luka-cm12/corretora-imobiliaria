// validators/authValidator.js
const validator = require('validator');

function validateRegisterInput(data) {
    const errors = {};
    
    // Nome
    if (!data.name || !validator.isLength(data.name, { min: 2, max: 50 })) {
        errors.name = 'Nome deve ter entre 2 e 50 caracteres';
    }
    
    // Email
    if (!data.email || !validator.isEmail(data.email)) {
        errors.email = 'Email inválido';
    }
    
    // Senha
    if (!data.password || !validator.isLength(data.password, { min: 8 })) {
        errors.password = 'Senha deve ter no mínimo 8 caracteres';
    }
    
    // Telefone
    if (!data.phone || !validator.isMobilePhone(data.phone, 'pt-BR')) {
        errors.phone = 'Telefone inválido';
    }
    
    // Tipo de usuário
    if (!data.type || !['corretor', 'admin'].includes(data.type)) {
        errors.type = 'Tipo de usuário inválido';
    }
    
    return {
        errors,
        isValid: Object.keys(errors).length === 0
    };
}

function validateLoginInput(data) {
    const errors = {};
    
    // Email
    if (!data.email || !validator.isEmail(data.email)) {
        errors.email = 'Email inválido';
    }
    
    // Senha
    if (!data.password) {
        errors.password = 'Senha é obrigatória';
    }
    
    return {
        errors,
        isValid: Object.keys(errors).length === 0
    };
}

module.exports = {
    validateRegisterInput,
    validateLoginInput
};