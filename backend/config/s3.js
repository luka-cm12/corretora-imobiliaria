// config/s3.js
const { S3Client, PutObjectCommand, GetObjectCommand, DeleteObjectCommand, ListObjectsV2Command } = require('@aws-sdk/client-s3');
const { getSignedUrl } = require('@aws-sdk/s3-request-presigner');
const sharp = require('sharp');
const crypto = require('crypto');
const path = require('path');

// Configuração do cliente S3
const s3Client = new S3Client({
  region: process.env.AWS_REGION,
  credentials: {
    accessKeyId: process.env.AWS_ACCESS_KEY_ID,
    secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY
  }
});

// Gera um nome único para o arquivo
const generateFileName = (bytes = 32) => 
  crypto.randomBytes(bytes).toString('hex');

// Tipos de imagens suportados
const supportedImageTypes = [
  'image/jpeg',
  'image/png',
  'image/webp'
];

// Tamanhos de imagem para gerar
const imageSizes = {
  thumbnail: { width: 400, height: 300 },
  medium: { width: 800, height: 600 },
  large: { width: 1200, height: 800 },
  original: {} // Mantém o tamanho original
};

/**
 * Faz upload de uma imagem para o S3 com otimização e múltiplos tamanhos
 * @param {Buffer} buffer - Buffer da imagem
 * @param {string} mimetype - Tipo MIME da imagem
 * @param {string} folder - Pasta no S3 (ex: 'properties/123')
 * @returns {Promise<Object>} URLs das imagens geradas
 */
async function uploadImage(buffer, mimetype, folder) {
  try {
    // Verifica se o tipo de imagem é suportado
    if (!supportedImageTypes.includes(mimetype)) {
      throw new Error(`Tipo de imagem não suportado: ${mimetype}`);
    }

    // Gera um nome base para os arquivos
    const fileName = generateFileName();
    const extension = mimetype.split('/')[1];
    const baseKey = `${folder}/${fileName}`;

    const imageUrls = {};
    const uploadPromises = [];

    // Processa cada tamanho de imagem
    for (const [size, dimensions] of Object.entries(imageSizes)) {
      const key = `${baseKey}_${size}.webp`; // Sempre converte para WebP
      let processedImage = sharp(buffer);

      // Redimensiona se necessário
      if (size !== 'original' && dimensions.width && dimensions.height) {
        processedImage = processedImage.resize({
          width: dimensions.width,
          height: dimensions.height,
          fit: 'inside',
          withoutEnlargement: true
        });
      }

      // Otimiza a imagem
      processedImage = processedImage
        .webp({ quality: size === 'thumbnail' ? 70 : 80 })
        .toBuffer();

      uploadPromises.push(
        processedImage.then(data => {
          const putCommand = new PutObjectCommand({
            Bucket: process.env.AWS_BUCKET_NAME,
            Key: key,
            Body: data,
            ContentType: 'image/webp'
          });
          return s3Client.send(putCommand);
        }).then(() => {
          imageUrls[size] = `${process.env.AWS_BUCKET_URL}/${key}`;
        })
      );
    }

    // Aguarda todos os uploads
    await Promise.all(uploadPromises);

    return {
      success: true,
      message: 'Imagem enviada com sucesso',
      imageId: fileName,
      urls: imageUrls
    };

  } catch (error) {
    console.error('Erro no upload para S3:', error);
    throw new Error(`Falha no upload da imagem: ${error.message}`);
  }
}

/**
 * Gera URLs assinadas para as imagens
 * @param {string} imageId - ID base da imagem (sem extensão)
 * @param {string} folder - Pasta no S3
 * @param {number} expiresIn - Tempo em segundos para expirar
 * @returns {Promise<Object>} URLs assinadas
 */
async function getImageUrls(imageId, folder, expiresIn = 3600) {
  try {
    const baseKey = `${folder}/${imageId}`;
    const urls = {};

    for (const size of Object.keys(imageSizes)) {
      const key = `${baseKey}_${size}.webp`;
      const command = new GetObjectCommand({
        Bucket: process.env.AWS_BUCKET_NAME,
        Key: key
      });
      
      urls[size] = await getSignedUrl(s3Client, command, { expiresIn });
    }

    return {
      success: true,
      urls
    };
  } catch (error) {
    console.error('Erro ao gerar URLs assinadas:', error);
    throw new Error(`Falha ao gerar URLs: ${error.message}`);
  }
}

/**
 * Lista todas as imagens de uma pasta no S3
 * @param {string} folder - Pasta no S3
 * @returns {Promise<Array>} Lista de objetos
 */
async function listImages(folder) {
  try {
    const command = new ListObjectsV2Command({
      Bucket: process.env.AWS_BUCKET_NAME,
      Prefix: folder
    });

    const { Contents } = await s3Client.send(command);
    
    if (!Contents) return [];

    // Filtra apenas imagens processadas (com tamanhos)
    const imagesMap = new Map();

    Contents.forEach(item => {
      const parts = item.Key.split('/');
      const fileName = parts[parts.length - 1];
      const match = fileName.match(/^(.+)_(thumbnail|medium|large|original)\.webp$/);
      
      if (match) {
        const [, imageId, size] = match;
        if (!imagesMap.has(imageId)) {
          imagesMap.set(imageId, {
            id: imageId,
            key: `${folder}/${imageId}`,
            sizes: {}
          });
        }
        imagesMap.get(imageId).sizes[size] = {
          key: item.Key,
          lastModified: item.LastModified,
          size: item.Size
        };
      }
    });

    return Array.from(imagesMap.values());
  } catch (error) {
    console.error('Erro ao listar imagens:', error);
    throw new Error(`Falha ao listar imagens: ${error.message}`);
  }
}

/**
 * Exclui uma imagem e todos os seus tamanhos do S3
 * @param {string} imageId - ID base da imagem (sem extensão)
 * @param {string} folder - Pasta no S3
 * @returns {Promise<Object>} Resultado da operação
 */
async function deleteImage(imageId, folder) {
  try {
    const baseKey = `${folder}/${imageId}`;
    const deletePromises = [];

    // Para cada tamanho, cria um comando de exclusão
    for (const size of Object.keys(imageSizes)) {
      const key = `${baseKey}_${size}.webp`;
      deletePromises.push(
        s3Client.send(new DeleteObjectCommand({
          Bucket: process.env.AWS_BUCKET_NAME,
          Key: key
        }))
      );
    }

    await Promise.all(deletePromises);

    return {
      success: true,
      message: 'Imagem excluída com sucesso'
    };
  } catch (error) {
    console.error('Erro ao excluir imagem:', error);
    throw new Error(`Falha ao excluir imagem: ${error.message}`);
  }
}

/**
 * Exclui todas as imagens de uma pasta no S3
 * @param {string} folder - Pasta no S3
 * @returns {Promise<Object>} Resultado da operação
 */
async function deleteFolderImages(folder) {
  try {
    const images = await listImages(folder);
    const deletePromises = images.map(img => deleteImage(img.id, folder));
    
    await Promise.all(deletePromises);

    return {
      success: true,
      message: `Todas as imagens da pasta ${folder} foram excluídas`,
      count: images.length
    };
  } catch (error) {
    console.error('Erro ao excluir pasta:', error);
    throw new Error(`Falha ao excluir pasta: ${error.message}`);
  }
}

module.exports = {
  uploadImage,
  getImageUrls,
  listImages,
  deleteImage,
  deleteFolderImages
};