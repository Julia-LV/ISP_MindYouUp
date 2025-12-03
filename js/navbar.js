// navbar.js

document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('toggleButton');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const menuText = document.getElementById('menuText');
    const linkTexts = document.querySelectorAll('.link-text');
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    // Estado do sidebar
    let isExpanded = false;
    
    // Função para expandir o sidebar (mostrar textos)
    function expandSidebar() {
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-64');
        
        // Mostra textos
        linkTexts.forEach(text => text.classList.remove('hidden'));
        if (menuText) menuText.classList.remove('hidden');
        
        // Mostra overlay apenas em mobile
        if (window.innerWidth < 768) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
        }
        
        isExpanded = true;
        
        // Salva estado no localStorage
        localStorage.setItem('sidebarExpanded', 'true');
    }
    
    // Função para recolher o sidebar (esconder textos)
    function collapseSidebar() {
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-20');
        
        // Esconde textos
        linkTexts.forEach(text => text.classList.add('hidden'));
        if (menuText) menuText.classList.add('hidden');
        
        // Em mobile, esconde completamente
        if (window.innerWidth < 768) {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        }
        
        isExpanded = false;
        
        // Salva estado no localStorage
        localStorage.setItem('sidebarExpanded', 'false');
    }
    
    // Função para alternar entre expandido/recolhido
    function toggleSidebar() {
        if (isExpanded) {
            collapseSidebar();
        } else {
            expandSidebar();
        }
    }
    
    // Inicializa o sidebar
    function initSidebar() {
        // Verifica estado salvo no localStorage
        const savedState = localStorage.getItem('sidebarExpanded');
        
        // Por padrão, inicia recolhido (apenas ícones)
        if (savedState === 'true') {
            expandSidebar();
        } else {
            collapseSidebar();
        }
        
        // Em desktop, garante que está visível
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }
    }
    
    // Função para highlight do item ativo
    function highlightActivePage() {
        const currentPath = window.location.pathname;
        
        sidebarLinks.forEach(link => {
            // Remove a classe ativa de todos os links
            link.classList.remove('active', 'bg-[#005949]');
            
            // Remove classes de cor branca
            const icon = link.querySelector('svg');
            const text = link.querySelector('.link-text');
            const path = link.querySelector('svg path');
            
            if (icon) icon.classList.remove('text-white');
            if (text) text.classList.remove('text-white');
            if (path) path.classList.remove('fill-white');
            
            // Verifica se este link corresponde à página atual
            const linkHref = link.getAttribute('href');
            if (linkHref) {
                // Extrai apenas o nome do arquivo do href
                const linkFileName = linkHref.split('/').pop();
                const currentFileName = currentPath.split('/').pop();
                
                // Compara os nomes dos arquivos
                if (linkFileName && currentFileName && linkFileName === currentFileName) {
                    // Adiciona classes para o estado ativo
                    link.classList.add('active', 'bg-[#005949]');
                    
                    // Adiciona classes para cor branca
                    if (icon) icon.classList.add('text-white');
                    if (text) text.classList.add('text-white');
                    if (path) path.classList.add('fill-white');
                }
            }
        });
    }
    
    // Adiciona evento de clique para destacar o item clicado
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Se for mobile e sidebar está expandido, recolhe após clicar
            if (window.innerWidth < 768 && isExpanded) {
                collapseSidebar();
            }
            
            // Remove a classe ativa de todos os links
            sidebarLinks.forEach(l => {
                l.classList.remove('active', 'bg-[#005949]');
                const icon = l.querySelector('svg');
                const text = l.querySelector('.link-text');
                const path = l.querySelector('svg path');
                
                if (icon) icon.classList.remove('text-white');
                if (text) text.classList.remove('text-white');
                if (path) path.classList.remove('fill-white');
            });
            
            // Adiciona a classe ativa ao link clicado
            this.classList.add('active', 'bg-[#005949]');
            
            // Adiciona classes para cor branca
            const icon = this.querySelector('svg');
            const text = this.querySelector('.link-text');
            const path = this.querySelector('svg path');
            
            if (icon) icon.classList.add('text-white');
            if (text) text.classList.add('text-white');
            if (path) path.classList.add('fill-white');
        });
    });
    
    // Event Listeners
    if (toggleButton) {
        toggleButton.addEventListener('click', toggleSidebar);
    }
    
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', collapseSidebar);
    }
    
    // Gerencia redimensionamento da janela
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            // Desktop: sidebar sempre visível
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
        } else {
            // Mobile: se estiver expandido, mostra overlay
            if (isExpanded) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (sidebarOverlay) sidebarOverlay.classList.remove('hidden');
            } else {
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full');
                if (sidebarOverlay) sidebarOverlay.classList.add('hidden');
            }
        }
    });
    
    // Inicializa
    initSidebar();
    highlightActivePage();
    
    // Atualiza o highlight quando a página muda
    window.addEventListener('popstate', highlightActivePage);
});