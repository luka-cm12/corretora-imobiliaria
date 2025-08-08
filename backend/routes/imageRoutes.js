// routes/imageRoutes.js
const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const { authenticate } = require('../middlewares/authMiddleware');
const imageController = require('../controllers/imageController');
const upload = require('../config/multer');


// Configuração do Multer para upload
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, path.join(__dirname, '../public/uploads/properties'));
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    cb(null, `${Date.now()}${ext}`);
  }
});

const upload = multer({ 
  storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
  fileFilter: (req, file, cb) => {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (allowedTypes.includes(file.mimetype)) {
      cb(null, true);
    } else {
      cb(new Error('Tipo de arquivo não suportado. Apenas JPEG, PNG e WEBP são permitidos.'));
    }
  }
});

// Rotas protegidas
router.post('/properties/:id/images', authenticate, upload.single('image'), imageController.uploadImage);
router.put('/properties/:id/images/order', authenticate, imageController.updateImageOrder);
router.get('/properties/:id/images', authenticate, imageController.getPropertyImages);
router.delete('/images/:id', authenticate, imageController.deleteImage);

// Upload de imagens (máx 12 arquivos)
router.post(
    '/properties/:propertyId/images',
    authenticate,
    upload.array('images', 12),
    imageController.uploadPropertyImages
);

// Listar imagens
router.get(
    '/properties/:propertyId/images',
    imageController.getPropertyImages
);

// Definir imagem principal
router.put(
    '/properties/:propertyId/images/:imageId/set-main',
    authenticate,
    imageController.setMainPropertyImage
);

// Reordenar imagens
router.put(
    '/properties/:propertyId/images/reorder',
    authenticate,
    imageController.reorderPropertyImages
);

// Excluir imagem
router.delete(
    '/properties/:propertyId/images/:imageId',
    authenticate,
    imageController.deletePropertyImage
);

module.exports = router;