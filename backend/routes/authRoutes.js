// routes/authRoutes.js
const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');

// Rotas públicas
router.post('/register', authController.register);
router.post('/login', authController.login);
router.get('/verify-email/:code', authController.verifyEmail);
router.post('/forgot-password', authController.forgotPassword);
router.post('/reset-password/:token', authController.resetPassword);

// Rotas protegidas (requerem autenticação)
const { authenticate } = require('../middlewares/authMiddleware');

router.put('/profile', authenticate, authController.updateProfile);
router.put('/change-password', authenticate, authController.changePassword);

module.exports = router;