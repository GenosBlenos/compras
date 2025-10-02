-- Migration script for database refactoring
-- Step 1: Rename old tables for backup
ALTER TABLE agua RENAME TO agua_old;
ALTER TABLE energia RENAME TO energia_old;
ALTER TABLE telefone RENAME TO telefone_old;
ALTER TABLE internet RENAME TO internet_old;
ALTER TABLE semparar RENAME TO semparar_old;
ALTER TABLE faturas RENAME TO faturas_old;

-- Step 2: Create new tables
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE,
    cnpj VARCHAR(18) UNIQUE,
    endereco VARCHAR(255),
    contato VARCHAR(255)
);

CREATE TABLE faturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unidade_id INT,
    categoria_id INT,
    fornecedor_id INT,
    data_emissao DATE,
    data_vencimento DATE NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    numero_fatura VARCHAR(255),
    arquivo_pdf VARCHAR(255),
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unidade_id) REFERENCES unidades(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id)
);

CREATE TABLE fatura_detalhes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    chave VARCHAR(255) NOT NULL,
    valor VARCHAR(255) NOT NULL,
    FOREIGN KEY (fatura_id) REFERENCES faturas(id) ON DELETE CASCADE
);

CREATE TABLE fatura_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    quantidade DECIMAL(10, 2),
    valor_unitario DECIMAL(10, 2),
    valor_total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (fatura_id) REFERENCES faturas(id) ON DELETE CASCADE
);

-- Step 3: Populate categorias table
INSERT INTO categorias (nome) VALUES ('agua'), ('energia'), ('telefone'), ('internet'), ('semparar');

-- Note: Data from old tables will need to be migrated manually.
