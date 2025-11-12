// ================================================
// FÓRUM - JavaScript Vanilla
// ================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ================================================
    // VALIDAÇÃO DE FORMULÁRIOS EM TEMPO REAL
    // ================================================
    
    // Validar formulário de registro
    const formRegistro = document.querySelector('form[action*="registro"]');
    if (formRegistro) {
        const senha = formRegistro.querySelector('#senha');
        const confirmarSenha = formRegistro.querySelector('#confirmar_senha');
        
        if (confirmarSenha) {
            confirmarSenha.addEventListener('input', function() {
                if (senha.value !== confirmarSenha.value) {
                    confirmarSenha.setCustomValidity('As senhas não coincidem');
                } else {
                    confirmarSenha.setCustomValidity('');
                }
            });
        }
    }
    
    // ================================================
    // CONTADOR DE CARACTERES PARA TEXTAREA
    // ================================================
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        
        if (maxLength) {
            const contador = document.createElement('small');
            contador.className = 'char-counter';
            contador.style.display = 'block';
            contador.style.textAlign = 'right';
            contador.style.marginTop = '0.25rem';
            contador.style.color = 'var(--text-light)';
            
            textarea.parentNode.appendChild(contador);
            
            const atualizarContador = () => {
                const atual = textarea.value.length;
                contador.textContent = `${atual}/${maxLength} caracteres`;
                
                if (atual > maxLength * 0.9) {
                    contador.style.color = 'var(--danger)';
                } else {
                    contador.style.color = 'var(--text-light)';
                }
            };
            
            textarea.addEventListener('input', atualizarContador);
            atualizarContador();
        }
    });
    
    // ================================================
    // CONFIRMAÇÃO ANTES DE SAIR (formulários não salvos)
    // ================================================
    const formularios = document.querySelectorAll('form');
    formularios.forEach(form => {
        let formAlterado = false;
        
        // Marcar como alterado quando houver input
        form.addEventListener('input', function() {
            formAlterado = true;
        });
        
        // Limpar flag ao submeter
        form.addEventListener('submit', function() {
            formAlterado = false;
        });
        
        // Avisar antes de sair
        window.addEventListener('beforeunload', function(e) {
            if (formAlterado) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    });
    
    // ================================================
    // AUTO-EXPANDIR TEXTAREA
    // ================================================
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
    
    // ================================================
    // ANIMAÇÃO DE FADE IN PARA CARDS
    // ================================================
    const cards = document.querySelectorAll('.topico-card, .resposta-card');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                entry.target.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
                
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    cards.forEach(card => {
        observer.observe(card);
    });
    
    // ================================================
    // FECHAR ALERTAS AUTOMATICAMENTE
    // ================================================
    const alertas = document.querySelectorAll('.alert-success');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.5s ease';
            alerta.style.opacity = '0';
            setTimeout(() => {
                alerta.remove();
            }, 500);
        }, 5000);
    });
    
    // ================================================
    // SCROLL SUAVE PARA ÂNCORAS
    // ================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ================================================
    // LOADING BUTTON STATE
    // ================================================
    const formsComSubmit = document.querySelectorAll('form');
    formsComSubmit.forEach(form => {
        form.addEventListener('submit', function(e) {
            const botao = this.querySelector('button[type="submit"]');
            if (botao && !botao.disabled) {
                const textoOriginal = botao.innerHTML;
                botao.disabled = true;
                botao.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
                    </svg>
                    Processando...
                `;
                
                // Restaurar após 10 segundos (caso não redirecione)
                setTimeout(() => {
                    botao.disabled = false;
                    botao.innerHTML = textoOriginal;
                }, 10000);
            }
        });
    });
    
    // ================================================
    // ADICIONAR ANIMAÇÃO DE SPIN
    // ================================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // ================================================
    // DEBUG: Log para confirmar que JS foi carregado
    // ================================================
    console.log('✅ Forum JavaScript carregado com sucesso!');
    
});