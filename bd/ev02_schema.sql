USE ev02;

-- 1) Pasa la tabla usuarios a InnoDB
ALTER TABLE usuarios
  ENGINE=InnoDB;

-- 2) Añade columna id auto‐incremental como PK
ALTER TABLE usuarios
  ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

-- 3) Renombra la columna de contraseña y aumenta su longitud
ALTER TABLE usuarios
  CHANGE COLUMN contrasena contrasena_hash VARCHAR(255) NOT NULL;

-- 4) Añade timestamps de auditoría
ALTER TABLE usuarios
  ADD COLUMN created_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER apellidos,
  ADD COLUMN updated_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- 5) Evita duplicados con índices únicos
ALTER TABLE usuarios
  ADD UNIQUE INDEX uq_usuario (usuario),
  ADD UNIQUE INDEX uq_email   (email);