document.addEventListener('DOMContentLoaded', () => {
    
    // --- Mobile Sidebar Toggle ---
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (sidebarToggle && sidebar) {
        // Show toggle button only on small screens
        const updateToggleVisibility = () => {
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('open');
            }
        };
        
        window.addEventListener('resize', updateToggleVisibility);
        updateToggleVisibility();
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // --- Auto-hide Flash Messages ---
    const flashMsg = document.getElementById('flash-msg');
    if (flashMsg) {
        setTimeout(() => {
            flashMsg.style.opacity = '0';
            flashMsg.style.transition = 'opacity 0.5s ease';
            setTimeout(() => flashMsg.remove(), 500);
        }, 5000);
    }

    // --- Modal System ---
    const modals = document.querySelectorAll('.modal-overlay');
    const openModalBtns = document.querySelectorAll('[data-modal-target]');
    const closeModalBtns = document.querySelectorAll('[data-modal-close]');

    openModalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            if (modal) modal.classList.add('open');
        });
    });

    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-overlay');
            if (modal) modal.classList.remove('open');
        });
    });

    // Close on click outside modal content
    modals.forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('open');
            }
        });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            modals.forEach(modal => modal.classList.remove('open'));
        }
    });

});
