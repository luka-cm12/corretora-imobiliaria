// Controle da Sidebar em dispositivos móveis
$(document).ready(function() {
    // Botão para toggle da sidebar
    $('.sidebar-toggle').click(function() {
        $('.admin-sidebar').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
    });
    
    // Fechar sidebar ao clicar no overlay
    $('.sidebar-overlay').click(function() {
        $('.admin-sidebar').removeClass('active');
        $(this).removeClass('active');
    });
    
    // Ativar submenus
    $('.menu-item a.dropdown-toggle').click(function(e) {
        // Evita que o link feche imediatamente
        if ($(this).attr('href') === '#') {
            e.preventDefault();
        }
        
        // Fecha outros submenus abertos
        if (!$(this).next().hasClass('show')) {
            $('.sidebar-menu .collapse').collapse('hide');
        }
    });
    
    // Marcar item ativo baseado na URL
    const currentPage = window.location.pathname.split('/').pop();
    $('.sidebar-menu a').each(function() {
        const linkPage = $(this).attr('href').split('/').pop();
        if (linkPage === currentPage) {
            $(this).addClass('active');
            // Abre o submenu pai se existir
            $(this).closest('.collapse').addClass('show');
            $(this).closest('.menu-item').find('.dropdown-toggle').attr('aria-expanded', 'true');
        }
    });
});