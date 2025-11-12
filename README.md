# 💬 Fórum PHP Simples

Um sistema de fórum de discussão básico e funcional, desenvolvido com **PHP** para o *back-end* e **JavaScript Vanilla** para o *front-end*. O foco principal deste projeto é a implementação de um sistema de **autenticação de usuários** robusto para proteger as funcionalidades de escrita e interação.

## 🌟 Funcionalidades Principais

O sistema é estruturado em módulos que exigem autenticação para garantir a integridade das discussões e a segurança dos dados.

### 1. Autenticação e Segurança

| Funcionalidade | Arquivos Relacionados | Descrição |
| :--- | :--- | :--- |
| **Registro** (Signup) | `auth/registro.php` | Criação de novas contas de usuário. |
| **Login** (Signin) | `auth/login.php` | Acesso ao sistema via sessão. |
| **Logout** | `auth/logout.php` | Encerramento seguro da sessão. |
| **Verificação de Sessão** | `auth/verificar_sessao.php` | Checagem obrigatória de login antes de permitir acesso a áreas restritas. |
| **Configuração** | `config.php` | Arquivo central para configuração de banco de dados e outras variáveis globais. |

### 2. Tópicos (Discussões)

| Funcionalidade | Arquivos Relacionados | Descrição |
| :--- | :--- | :--- |
| **Criação** | `topicos/criar.php` | Formulário e lógica para iniciar uma nova discussão. |
| **Visualização** | `topicos/ver.php` | Exibição de um tópico específico, incluindo todas as suas respostas. |
| **Edição/Exclusão** | `topicos/editar.php`, `topicos/excluir.php` | Gerenciamento de tópicos pelo autor. |
| **API** | `topicos/api.php` | Ponto de acesso para interações assíncronas (ex: via JavaScript). |

### 3. Respostas (Comentários)

| Funcionalidade | Arquivos Relacionados | Descrição |
| :--- | :--- | :--- |
| **Postagem** | (Integrado em `topicos/ver.php`) | Lógica para submeter uma resposta a um tópico. |
| **Edição/Exclusão** | `respostas/editar.php`, `respostas/excluir.php` | Gerenciamento de respostas pelo autor. |

### 4. Estrutura Geral

| Arquivo/Diretório | Descrição |
| :--- | :--- |
| `index.php` | Página inicial do fórum, listando os tópicos. |
| `includes/` | Contém partes reutilizáveis do layout (`header.php`, `footer.php`). |
| `assets/` | Contém os arquivos estáticos (`css/style.css`, `js/main.js`, `js/forum.js`). |
| `usuario/perfil.php` | Página de perfil do usuário (a ser implementada ou visualizada). |

## ⚙️ Configuração e Instalação

### 1. Requisitos

*   Servidor Web (Apache, Nginx, etc.)
*   PHP (versão 7.4 ou superior recomendada)
*   Banco de Dados MySQL ou MariaDB

### 2. Configuração do Banco de Dados

O projeto utiliza um banco de dados relacional para persistência de dados.

#### A. Modelagem

A estrutura do banco de dados é definida pelas seguintes tabelas:

| Tabela | Propósito | Chaves |
| :--- | :--- | :--- |
| `usuarios` | Armazena informações de autenticação e perfil. | `id` (PK), `nome_usuario` (UNIQUE), `email` (UNIQUE) |
| `topicos` | Armazena as discussões principais. | `id` (PK), `id_usuario` (FK para `usuarios`) |
| `respostas` | Armazena os comentários dentro de cada tópico. | `id` (PK), `id_topico` (FK para `topicos`), `id_usuario` (FK para `usuarios`) |

#### B. Script SQL

O arquivo `database.sql` contém o script necessário para criar as tabelas:

```sql
-- Estrutura simplificada do banco de dados (MySQL/MariaDB)

-- TABELA 1: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL, -- Armazenar HASH da senha (ex: bcrypt)
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- TABELA 2: topicos
CREATE TABLE topicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- TABELA 3: respostas
CREATE TABLE respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_topico INT NOT NULL,
    id_usuario INT NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_topico) REFERENCES topicos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

**Passos para Instalação:**

1.  Clone o repositório: `git clone https://github.com/lucasparente-codigos/forum-php.git`
2.  Crie um banco de dados e importe o script SQL de `database.sql`.
3.  Edite o arquivo `config.php` com as credenciais do seu banco de dados.
4.  Acesse o projeto pelo seu navegador.

## 🔒 Fluxo de Autenticação (PHP)

O sistema segue um fluxo de autenticação padrão e seguro, utilizando sessões PHP:

1.  O usuário submete o formulário de login (`auth/login.php`).
2.  O PHP consulta o banco de dados e verifica se o **HASH da senha** fornecida corresponde ao `senha_hash` armazenado (utilizando funções como `password_verify()`).
3.  Se a verificação for correta, o PHP inicia a sessão (`session_start()`) e define uma variável de sessão, como `$_SESSION['user_id'] = $id_do_usuario`.
4.  Para proteger qualquer página (ex: `topicos/criar.php`), o código verifica a existência da variável de sessão:

    ```php
    <?php
    session_start();
    // ... código de conexão com o banco, includes, etc.

    if (!isset($_SESSION['user_id'])) {
        // Redireciona para a página de login se não estiver logado
        header("Location: /auth/login.php");
        exit();
    }

    // O restante do código da página protegida
    ?>
    ```

## 🤝 Contribuição

Sinta-se à vontade para abrir *issues* ou enviar *pull requests* para melhorias.

---
Desenvolvido por [lucasparente-codigos](https://github.com/lucasparente-codigos)
