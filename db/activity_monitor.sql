-- Tabela de usuários para login
CREATE TABLE usuario (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    bloqueado BOOLEAN DEFAULT FALSE,
    data_bloqueio TIMESTAMP NULL DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Tabelas de monitoramento
CREATE TABLE log_atividade (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE tentativa_login (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    success BOOLEAN DEFAULT FALSE,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ip (ip_address),
    INDEX idx_attempt_time (attempt_time)
);


-- Tabelas principais já com campos de auditoria
CREATE TABLE energia (
    id_energia INT NOT NULL PRIMARY KEY,
    mes INT(2),
    local VARCHAR(60),
    consumo FLOAT NOT NULL,
    multa FLOAT,
    total FLOAT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT,
    atualizado_por INT,
    Conta_status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_vencimento DATE NOT NULL,
    secretaria VARCHAR(100),
    classe_consumo VARCHAR(100),
    instalacao VARCHAR(100),
    observacoes VARCHAR(100)
);

CREATE TABLE telefone (
    id_telefone INT NOT NULL PRIMARY KEY,
    mes INT(2),
    local VARCHAR(60), 
    numero VARCHAR(20),
    tridigito INT,
    consumo FLOAT NOT NULL,
    multa FLOAT,
    cobrar FLOAT,
    total FLOAT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT,
    atualizado_por INT,
    Conta_status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_vencimento DATE NOT NULL,
    secretaria VARCHAR(100),
    classe_consumo VARCHAR(100),
    instalacao VARCHAR(100),
    observacoes VARCHAR(100)
);

CREATE TABLE internet (
    id_wifi INT NOT NULL PRIMARY KEY,
    mes INT(2),
    local VARCHAR(60), 
    consumo FLOAT NOT NULL,
    multa FLOAT,
    cobrar FLOAT,
    total FLOAT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT,
    atualizado_por INT,
    Conta_status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    data_vencimento DATE NOT NULL,
    secretaria VARCHAR(100),
    classe_consumo VARCHAR(100),
    instalacao VARCHAR(100),
    observacoes VARCHAR(100)
);

CREATE TABLE agua (
    id_agua INT NOT NULL PRIMARY KEY,
    mes INT(2),
    local VARCHAR(60),
    faturado INT,
    tarifa FLOAT,
    afastamento FLOAT,
    esgoto FLOAT,
    desconto FLOAT,
    outros FLOAT,
    multa FLOAT,
    total FLOAT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT,
    atualizado_por INT,
    Conta_status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    consumo FLOAT NOT NULL,
    data_vencimento DATE NOT NULL,
    secretaria VARCHAR(100),
    classe_consumo VARCHAR(100),
    instalacao VARCHAR(100),
    observacoes VARCHAR(100)
);

CREATE TABLE semparar (
    id_semparar INT AUTO_INCREMENT PRIMARY KEY, -- Adicionada chave primária auto-incrementada
    placa VARCHAR(30) NOT NULL,
    combustivel VARCHAR(50),
    veiculo VARCHAR(50),
    marca VARCHAR(50),
    modelo VARCHAR(50),
    tipo VARCHAR(50),
    departamento VARCHAR(50),
    ficha INT,
    secretaria VARCHAR(50),
    tag INT,
    mensalidade FLOAT,
    passagens FLOAT,
    estacionamento FLOAT,
    estabelecimentos FLOAT,
    credito FLOAT,
    isento BOOLEAN,
    mes INT(2),
    total FLOAT,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    criado_por INT,
    atualizado_por INT,
    Conta_status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0,
    consumo FLOAT NOT NULL,
    data_vencimento DATE NOT NULL,
    data_org DATE NOT NULL,
    observacoes VARCHAR(100),
    INDEX idx_placa (placa)
);

CREATE TABLE IF NOT EXISTS compras_pdf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave_acesso VARCHAR(255) NULL,
    cnpj_fornecedor VARCHAR(20) NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    data_emissao DATE NULL,
    nome_arquivo_original VARCHAR(255) NOT NULL,
    data_processamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);