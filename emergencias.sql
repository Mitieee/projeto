CREATE TABLE usuarios (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  telefone VARCHAR(15),
  nome VARCHAR(100) NOT NULL,
  data_nascimento DATE NOT NULL,
  tipo_sanguineo ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  peso DECIMAL(5,2) NOT NULL,
  altura DECIMAL(5,2) NOT NULL,
  alergia VARCHAR(255) DEFAULT NULL,
  outras_informacoes TEXT DEFAULT NULL,
  criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (id)
) 



CREATE TABLE IF NOT EXISTS emergencias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_usuario INT(11) NOT NULL,
    tipo_emergencia ENUM(
        'POLÍCIA 190', 
        'BOMBEIRO 193', 
        'SAMU 192', 
        'GUARDA MUNICIPAL 153', 
        'POLÍCIA CIVIL 199', 
        'DISQUE DENÚNCIA 181', 
        'DIREITOS HUMANOS 100', 
        'DELEGACIA DA MULHER 180', 
        'CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141', 
        'DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700', 
        'CENTRAL DA PESSOA IDOSA 3236-1100', 
        'DEFESA CIVIL 3476-3400', 
        'Plantão 24hs (51) 99322-5764'
    ) NOT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    latitude_atendente DECIMAL(10, 8),
    longitude_atendente DECIMAL(11, 8),
    em_atendimento BOOLEAN DEFAULT FALSE,
    resolvida BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
) 




CREATE TABLE IF NOT EXISTS mensagens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_emergencia INT(11) NOT NULL,
    id_remetente INT(11) NOT NULL,
    mensagem TEXT NOT NULL,
    resposta TEXT DEFAULT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (id_emergencia) REFERENCES emergencias(id),
    FOREIGN KEY (id_remetente) REFERENCES usuarios(id)
) 
