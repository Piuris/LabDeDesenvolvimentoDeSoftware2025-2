-- Estrutura do banco de dados para o sistema de mentorias

CREATE DATABASE IF NOT EXISTS sistema_mentorias CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_mentorias;

-- Tabela de usuários (cadastro simples 1)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'mentor') DEFAULT 'aluno',
    telefone VARCHAR(20),
    foto_perfil VARCHAR(255) DEFAULT 'default-avatar.png',
    biografia TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de categorias (cadastro simples 2)
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(50),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de mentorias (cadastro 1..n - um mentor pode ter várias mentorias)
CREATE TABLE mentorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentor_id INT NOT NULL,
    categoria_id INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    duracao INT NOT NULL COMMENT 'Duração em minutos',
    imagem VARCHAR(255) DEFAULT 'default-mentoria.jpg',
    ativo BOOLEAN DEFAULT TRUE,
    visualizacoes INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    INDEX idx_mentor (mentor_id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de avaliações (relacionamento n..n - cadastro n..n 1)
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mentoria_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL CHECK (nota BETWEEN 1 AND 5),
    comentario TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentoria_id) REFERENCES mentorias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avaliacao (mentoria_id, usuario_id),
    INDEX idx_mentoria (mentoria_id),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de tags (para relacionamento n..n 2)
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de relacionamento mentorias_tags (n..n - cadastro n..n 2)
CREATE TABLE mentorias_tags (
    mentoria_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (mentoria_id, tag_id),
    FOREIGN KEY (mentoria_id) REFERENCES mentorias(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de carrinho
CREATE TABLE carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    mentoria_id INT NOT NULL,
    adicionado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (mentoria_id) REFERENCES mentorias(id) ON DELETE CASCADE,
    UNIQUE KEY unique_carrinho (usuario_id, mentoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de contatos
CREATE TABLE contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    assunto VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    respondido BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_respondido (respondido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir categorias padrão
INSERT INTO categorias (nome, descricao, icone) VALUES
('Programação', 'Mentorias de desenvolvimento de software e programação', '💻'),
('Design', 'Mentorias de design gráfico, UI/UX e criatividade', '🎨'),
('Marketing', 'Mentorias de marketing digital e estratégias', '📱'),
('Negócios', 'Mentorias de empreendedorismo e gestão', '💼'),
('Idiomas', 'Mentorias de ensino de idiomas', '🌍'),
('Redação', 'Mentorias de escrita criativa e redação', '✍️');

-- Inserir tags padrão
INSERT INTO tags (nome) VALUES
('Iniciante'), ('Intermediário'), ('Avançado'), ('Online'), ('Presencial'),
('JavaScript'), ('Python'), ('React'), ('Node.js'), ('UI/UX'),
('SEO'), ('Redes Sociais'), ('E-commerce'), ('Inglês'), ('Espanhol');

-- Inserir usuário de teste (senha: 123456)
INSERT INTO usuarios (nome, email, senha, tipo, biografia) VALUES
('João Silva', 'mentor@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor', 'Desenvolvedor Full Stack com 10 anos de experiência'),
('Maria Santos', 'aluno@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', 'Estudante de programação');

-- Inserir mentorias de exemplo
INSERT INTO mentorias (mentor_id, categoria_id, titulo, descricao, preco, duracao) VALUES
(1, 1, 'Introdução ao JavaScript Moderno', 'Aprenda JavaScript do zero com as melhores práticas e recursos modernos da linguagem.', 99.90, 60),
(1, 1, 'React para Iniciantes', 'Domine os fundamentos do React e construa aplicações web interativas.', 149.90, 90),
(1, 6, 'Redação para Vestibular e ENEM', 'Técnicas avançadas de redação dissertativa-argumentativa.', 79.90, 60);
