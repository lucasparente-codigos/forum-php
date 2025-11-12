// ================================================
// FÓRUM - Funcionalidades AJAX para Edição
// ================================================

// ================================================
// EDITAR TÓPICO
// ================================================
async function editarTopico(topicoId) {
    try {
        // Buscar dados atuais do tópico
        const response = await fetch('/forum/topicos/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=buscar&id=${topicoId}`
        });
        
        const data = await response.json();
        
        if (!data.success) {
            alert(data.message);
            return;
        }
        
        // Criar formulário inline
        const tituloElement = document.getElementById(`topico-titulo-${topicoId}`);
        const conteudoElement = document.getElementById(`topico-conteudo-${topicoId}`);
        
        const tituloAtual = data.titulo;
        const conteudoAtual = data.conteudo;
        
        // Substituir título por input
        tituloElement.innerHTML = `
            <input type="text" id="edit-titulo-${topicoId}" value="${escapeHtml(tituloAtual)}" class="edit-input-titulo">
        `;
        
        // Substituir conteúdo por textarea
        conteudoElement.innerHTML = `
            <textarea id="edit-conteudo-${topicoId}" class="edit-textarea" rows="10">${escapeHtml(conteudoAtual)}</textarea>
            <div class="edit-actions">
                <button onclick="salvarTopico(${topicoId})" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Salvar
                </button>
                <button onclick="cancelarEdicaoTopico(${topicoId}, '${escapeHtml(tituloAtual)}', \`${escapeHtml(conteudoAtual)}\`)" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancelar
                </button>
            </div>
        `;
        
        // Focar no título
        document.getElementById(`edit-titulo-${topicoId}`).focus();
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar dados do tópico');
    }
}

// ================================================
// SALVAR EDIÇÃO DO TÓPICO
// ================================================
async function salvarTopico(topicoId) {
    const novoTitulo = document.getElementById(`edit-titulo-${topicoId}`).value;
    const novoConteudo = document.getElementById(`edit-conteudo-${topicoId}`).value;
    
    if (!novoTitulo.trim() || !novoConteudo.trim()) {
        alert('Título e conteúdo são obrigatórios!');
        return;
    }
    
    try {
        const response = await fetch('/forum/topicos/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=editar&id=${topicoId}&titulo=${encodeURIComponent(novoTitulo)}&conteudo=${encodeURIComponent(novoConteudo)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Restaurar elementos normais
            document.getElementById(`topico-titulo-${topicoId}`).innerHTML = escapeHtml(data.titulo);
            document.getElementById(`topico-conteudo-${topicoId}`).innerHTML = data.conteudo;
            
            // Mostrar mensagem de sucesso
            mostrarAlerta('Tópico atualizado com sucesso!', 'success');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar tópico');
    }
}

// ================================================
// CANCELAR EDIÇÃO DO TÓPICO
// ================================================
function cancelarEdicaoTopico(topicoId, tituloOriginal, conteudoOriginal) {
    document.getElementById(`topico-titulo-${topicoId}`).innerHTML = tituloOriginal;
    document.getElementById(`topico-conteudo-${topicoId}`).innerHTML = conteudoOriginal.replace(/\n/g, '<br>');
}

// ================================================
// EDITAR RESPOSTA
// ================================================
async function editarResposta(respostaId) {
    try {
        // Buscar dados atuais da resposta
        const response = await fetch('/forum/respostas/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=buscar&id=${respostaId}`
        });
        
        const data = await response.json();
        
        if (!data.success) {
            alert(data.message);
            return;
        }
        
        // Criar formulário inline
        const conteudoElement = document.getElementById(`resposta-conteudo-${respostaId}`);
        const conteudoAtual = data.conteudo;
        
        conteudoElement.innerHTML = `
            <textarea id="edit-resposta-${respostaId}" class="edit-textarea" rows="6">${escapeHtml(conteudoAtual)}</textarea>
            <div class="edit-actions">
                <button onclick="salvarResposta(${respostaId})" class="btn btn-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Salvar
                </button>
                <button onclick="cancelarEdicaoResposta(${respostaId}, \`${escapeHtml(conteudoAtual)}\`)" class="btn btn-outline btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Cancelar
                </button>
            </div>
        `;
        
        // Focar no textarea
        document.getElementById(`edit-resposta-${respostaId}`).focus();
        
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar dados da resposta');
    }
}

// ================================================
// SALVAR EDIÇÃO DA RESPOSTA
// ================================================
async function salvarResposta(respostaId) {
    const novoConteudo = document.getElementById(`edit-resposta-${respostaId}`).value;
    
    if (!novoConteudo.trim()) {
        alert('O conteúdo não pode estar vazio!');
        return;
    }
    
    try {
        const response = await fetch('/forum/respostas/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `acao=editar&id=${respostaId}&conteudo=${encodeURIComponent(novoConteudo)}`
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Restaurar elemento normal
            document.getElementById(`resposta-conteudo-${respostaId}`).innerHTML = data.conteudo;
            
            // Mostrar mensagem de sucesso
            mostrarAlerta('Resposta atualizada com sucesso!', 'success');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao salvar resposta');
    }
}

// ================================================
// CANCELAR EDIÇÃO DA RESPOSTA
// ================================================
function cancelarEdicaoResposta(respostaId, conteudoOriginal) {
    document.getElementById(`resposta-conteudo-${respostaId}`).innerHTML = conteudoOriginal.replace(/\n/g, '<br>');
}

// ================================================
// FUNÇÃO AUXILIAR: Escapar HTML
// ================================================
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ================================================
// FUNÇÃO AUXILIAR: Mostrar alerta
// ================================================
function mostrarAlerta(mensagem, tipo = 'info') {
    // Remover alertas anteriores
    const alertasAntigos = document.querySelectorAll('.alert-flutuante');
    alertasAntigos.forEach(a => a.remove());
    
    // Criar novo alerta
    const alerta = document.createElement('div');
    alerta.className = `alert-flutuante alert-${tipo}`;
    alerta.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        ${mensagem}
    `;
    
    document.body.appendChild(alerta);
    
    // Animar entrada
    setTimeout(() => alerta.classList.add('show'), 100);
    
    // Remover após 3 segundos
    setTimeout(() => {
        alerta.classList.remove('show');
        setTimeout(() => alerta.remove(), 300);
    }, 3000);
}

// ================================================
// LOG: Confirmar carregamento
// ================================================
console.log('✅ Forum.js (AJAX) carregado com sucesso!');