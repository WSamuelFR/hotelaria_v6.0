<?php
// Arquivo: app/Views/Layout/footer.php
// Inclui o Footer, scripts e fecha o <body> e <html>.
?>
</main>

<footer class="bg-light text-center py-3 mt-5">
    <p class="mb-0">© 2025 HOTELARIA. Todos os direitos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('appSidebar');
        const toggleButton = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');

        // --- Menu Lateral (Gaveta) ---
        // Verifica se o botão e o sidebar existem antes de adicionar o evento
        if (toggleButton && sidebar) {
            toggleButton.addEventListener('click', () => {
                sidebar.classList.toggle('open');
                // Adiciona margem ao conteúdo principal quando o menu está aberto
                if (mainContent) {
                    mainContent.classList.toggle('sidebar-opened-margin');
                }
            });
        }

        // O bloco de "Logout Real" via JavaScript foi removido.
        // A lógica agora é processada diretamente pelo link <a> no header.php.
    });
</script>
</body>

</html>