// services/emailService.js
const nodemailer = require('nodemailer');

// Configuração do transporte de email
const transporter = nodemailer.createTransport({
    host: process.env.EMAIL_HOST,
    port: process.env.EMAIL_PORT,
    secure: true, // SSL/TLS
    auth: {
        user: process.env.EMAIL_USER,
        pass: process.env.EMAIL_PASS
    }
});

async function sendVerificationEmail(email, name, verificationUrl) {
    try {
        await transporter.sendMail({
            from: `"Base Imóveis" <${process.env.EMAIL_FROM}>`,
            to: email,
            subject: 'Verifique seu email - Base Imóveis',
            html: `
                <h2>Olá ${name},</h2>
                <p>Por favor, clique no link abaixo para verificar seu email:</p>
                <p><a href="${verificationUrl}" style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Verificar Email</a></p>
                <p>Se você não solicitou este email, por favor ignore esta mensagem.</p>
                <p>Atenciosamente,<br>Equipe Base Imóveis</p>
            `
        });
    } catch (error) {
        console.error('Erro ao enviar email de verificação:', error);
        throw new Error('Falha ao enviar email de verificação');
    }
}

async function sendPasswordResetEmail(email, name, resetUrl) {
    try {
        await transporter.sendMail({
            from: `"Base Imóveis" <${process.env.EMAIL_FROM}>`,
            to: email,
            subject: 'Redefinição de Senha - Base Imóveis',
            html: `
                <h2>Olá ${name},</h2>
                <p>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo para continuar:</p>
                <p><a href="${resetUrl}" style="background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Redefinir Senha</a></p>
                <p>Este link expirará em 1 hora. Se você não solicitou a redefinição de senha, por favor ignore este email.</p>
                <p>Atenciosamente,<br>Equipe Base Imóveis</p>
            `
        });
    } catch (error) {
        console.error('Erro ao enviar email de redefinição:', error);
        throw new Error('Falha ao enviar email de redefinição');
    }
}

module.exports = {
    sendVerificationEmail,
    sendPasswordResetEmail
};