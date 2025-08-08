// controllers/imageController.js
const { uploadImage, getImageUrls, deleteImage, listImages } = require('../config/s3');
const db = require('../config/database');
const sharp = require('sharp');
const path = require('path');
const { v4: uuidv4 } = require('uuid');

// Tipos de arquivo permitidos
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

/**
 * Upload de imagens para um imóvel
 */
const uploadPropertyImages = async (req, res) => {
    try {
        const { propertyId } = req.params;
        const files = req.files;
        const userId = req.user.id;

        // Verificar se o imóvel existe e pertence ao usuário
        const [property] = await db.query(
            'SELECT id FROM imoveis WHERE id = ? AND usuario_id = ?',
            [propertyId, userId]
        );

        if (!property.length) {
            return res.status(404).json({
                success: false,
                message: 'Imóvel não encontrado ou não autorizado'
            });
        }

        // Processar cada imagem
        const uploadResults = [];
        
        for (const file of files) {
            if (!ALLOWED_TYPES.includes(file.mimetype)) {
                continue; // Pular arquivos não permitidos
            }

            // Otimizar imagem antes do upload
            const optimizedImage = await sharp(file.buffer)
                .rotate() // Corrigir orientação EXIF
                .resize(2000, 2000, { // Limitar tamanho máximo
                    fit: 'inside',
                    withoutEnlargement: true
                })
                .webp({ quality: 80 }) // Converter para WebP
                .toBuffer();

            // Fazer upload para o S3
            const result = await uploadImage(
                optimizedImage,
                'image/webp',
                `properties/${propertyId}`
            );

            // Salvar metadados no banco de dados
            const [dbResult] = await db.query(
                'INSERT INTO imagens_imovel (imovel_id, url_imagem, is_principal, ordem, usuario_id) VALUES (?, ?, ?, ?, ?)',
                [propertyId, result.urls.original, false, uploadResults.length, userId]
            );

            uploadResults.push({
                id: dbResult.insertId,
                urls: result.urls,
                isMain: false,
                order: uploadResults.length
            });
        }

        // Se for a primeira imagem, definir como principal
        if (uploadResults.length > 0 && !await hasMainImage(propertyId)) {
            await setMainImage(propertyId, uploadResults[0].id);
            uploadResults[0].isMain = true;
        }

        res.status(201).json({
            success: true,
            message: `${uploadResults.length} imagem(ns) enviada(s) com sucesso`,
            images: uploadResults
        });

    } catch (error) {
        console.error('Erro no upload de imagens:', error);
        res.status(500).json({
            success: false,
            message: 'Erro ao processar imagens',
            error: process.env.NODE_ENV === 'development' ? error.message : undefined
        });
    }
};

/**
 * Listar imagens de um imóvel
 */
const getPropertyImages = async (req, res) => {
    try {
        const { propertyId } = req.params;

        // Buscar imagens no banco de dados
        const [images] = await db.query(
            `SELECT id, imovel_id, url_imagem, is_principal, ordem 
             FROM imagens_imovel 
             WHERE imovel_id = ? 
             ORDER BY ordem ASC`,
            [propertyId]
        );

        // Gerar URLs assinadas se estiver usando S3 privado
        const imagesWithUrls = await Promise.all(
            images.map(async img => {
                const imageName = path.basename(img.url_imagem);
                const folder = `properties/${propertyId}`;
                
                const urls = await getImageUrls(
                    imageName.replace(/(_thumbnail|_medium|_large|_original)\.webp$/, ''),
                    folder
                );

                return {
                    ...img,
                    urls: urls.success ? urls.urls : null
                };
            })
        );

        res.json({
            success: true,
            images: imagesWithUrls
        });

    } catch (error) {
        console.error('Erro ao listar imagens:', error);
        res.status(500).json({
            success: false,
            message: 'Erro ao buscar imagens'
        });
    }
};

/**
 * Definir imagem principal
 */
const setMainPropertyImage = async (req, res) => {
    try {
        const { propertyId, imageId } = req.params;
        const userId = req.user.id;

        // Verificar se o imóvel pertence ao usuário
        const [property] = await db.query(
            'SELECT id FROM imoveis WHERE id = ? AND usuario_id = ?',
            [propertyId, userId]
        );

        if (!property.length) {
            return res.status(404).json({
                success: false,
                message: 'Imóvel não encontrado ou não autorizado'
            });
        }

        await setMainImage(propertyId, imageId);

        res.json({
            success: true,
            message: 'Imagem principal atualizada com sucesso'
        });

    } catch (error) {
        console.error('Erro ao definir imagem principal:', error);
        res.status(500).json({
            success: false,
            message: 'Erro ao atualizar imagem principal'
        });
    }
};

/**
 * Reordenar imagens
 */
const reorderPropertyImages = async (req, res) => {
    try {
        const { propertyId } = req.params;
        const { order } = req.body; // Array de IDs na nova ordem
        const userId = req.user.id;

        if (!Array.isArray(order) || order.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Ordem inválida'
            });
        }

        // Verificar se o imóvel pertence ao usuário
        const [property] = await db.query(
            'SELECT id FROM imoveis WHERE id = ? AND usuario_id = ?',
            [propertyId, userId]
        );

        if (!property.length) {
            return res.status(404).json({
                success: false,
                message: 'Imóvel não encontrado ou não autorizado'
            });
        }

        // Atualizar ordem no banco de dados
        await Promise.all(
            order.map(async (imageId, index) => {
                await db.query(
                    'UPDATE imagens_imovel SET ordem = ? WHERE id = ? AND imovel_id = ?',
                    [index, imageId, propertyId]
                );
            })
        );

        res.json({
            success: true,
            message: 'Ordem das imagens atualizada com sucesso'
        });

    } catch (error) {
        console.error('Erro ao reordenar imagens:', error);
        res.status(500).json({
            success: false,
            message: 'Erro ao reordenar imagens'
        });
    }
};

/**
 * Excluir imagem
 */
const deletePropertyImage = async (req, res) => {
    try {
        const { propertyId, imageId } = req.params;
        const userId = req.user.id;

        // Verificar se o imóvel pertence ao usuário
        const [property] = await db.query(
            'SELECT id FROM imoveis WHERE id = ? AND usuario_id = ?',
            [propertyId, userId]
        );

        if (!property.length) {
            return res.status(404).json({
                success: false,
                message: 'Imóvel não encontrado ou não autorizado'
            });
        }

        // Buscar imagem no banco de dados
        const [images] = await db.query(
            'SELECT id, url_imagem FROM imagens_imovel WHERE id = ? AND imovel_id = ?',
            [imageId, propertyId]
        );

        if (!images.length) {
            return res.status(404).json({
                success: false,
                message: 'Imagem não encontrada'
            });
        }

        const image = images[0];
        const imageName = path.basename(image.url_imagem);
        const baseName = imageName.replace(/(_thumbnail|_medium|_large|_original)\.webp$/, '');

        // Excluir do S3
        await deleteImage(baseName, `properties/${propertyId}`);

        // Excluir do banco de dados
        await db.query(
            'DELETE FROM imagens_imovel WHERE id = ?',
            [imageId]
        );

        // Se era a imagem principal, definir uma nova
        if (image.is_principal) {
            const [remainingImages] = await db.query(
                'SELECT id FROM imagens_imovel WHERE imovel_id = ? LIMIT 1',
                [propertyId]
            );

            if (remainingImages.length > 0) {
                await setMainImage(propertyId, remainingImages[0].id);
            }
        }

        res.json({
            success: true,
            message: 'Imagem excluída com sucesso'
        });

    } catch (error) {
        console.error('Erro ao excluir imagem:', error);
        res.status(500).json({
            success: false,
            message: 'Erro ao excluir imagem'
        });
    }
};

// --- Funções auxiliares ---

/**
 * Verifica se o imóvel já tem uma imagem principal
 */
async function hasMainImage(propertyId) {
    const [result] = await db.query(
        'SELECT 1 FROM imagens_imovel WHERE imovel_id = ? AND is_principal = TRUE LIMIT 1',
        [propertyId]
    );
    return result.length > 0;
}

/**
 * Define uma imagem como principal
 */
async function setMainImage(propertyId, imageId) {
    // Remover imagem principal atual
    await db.query(
        'UPDATE imagens_imovel SET is_principal = FALSE WHERE imovel_id = ?',
        [propertyId]
    );

    // Definir nova imagem principal
    await db.query(
        'UPDATE imagens_imovel SET is_principal = TRUE WHERE id = ? AND imovel_id = ?',
        [imageId, propertyId]
    );
}

module.exports = {
    uploadPropertyImages,
    getPropertyImages,
    setMainPropertyImage,
    reorderPropertyImages,
    deletePropertyImage
};