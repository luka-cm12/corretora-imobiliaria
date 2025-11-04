    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Corretora Claudia Colombo</h3>
                    <p>Oferecendo soluções imobiliárias completas com transparência e profissionalismo.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/corretoraclaudiacolombo" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://w.app/cggjle" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Links Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="imoveis.php">Imóveis</a></li>
                        <li><a href="contato.php">Contato</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contato</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Av. Ruben Bento Alves, 8434 - Cinquentenário, Caxias do Sul - RS, CEP: 95012-366</li>
                        <li><i class="fas fa-phone"></i> (54) 99627-0304</li>
                        <li><i class="fas fa-envelope"></i> claudiacolomboimoveis@gmail.com</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; <?= date('Y') ?> Corretora Claudia Colombo. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="public/assets/js/main.js"></script>
    <?php if (isset($load_lightbox) && $load_lightbox): ?>
        <script src="public/assets/js/lightbox.js"></script>
    <?php endif; ?>
    
    <!-- Scripts adicionais específicos por página -->
    <?php if (isset($custom_scripts)): ?>
        <?php foreach ($custom_scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>