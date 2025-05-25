USE ev02;

-- Pasa la tabla usuarios a InnoDB
ALTER TABLE usuarios
  ENGINE=InnoDB;

-- Anade columna id auto‐incremental como PK
ALTER TABLE usuarios
  ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

-- Renombra la columna de contraseña y aumenta su longitud
ALTER TABLE usuarios
  CHANGE COLUMN contrasena contrasena_hash VARCHAR(255) NOT NULL;

-- Anade timestamps de auditoría
ALTER TABLE usuarios
  ADD COLUMN created_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER apellidos,
  ADD COLUMN updated_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Evita duplicados con índices únicos
ALTER TABLE usuarios
  ADD UNIQUE INDEX uq_usuario (usuario),
  ADD UNIQUE INDEX uq_email   (email);