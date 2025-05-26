USE ev02;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(32) NOT NULL,
    contrasena_hash VARCHAR(255) NOT NULL,
    nombres VARCHAR(64) NOT NULL,
    apellidos VARCHAR(64) NOT NULL,
    email VARCHAR(128) NOT NULL,
    created_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX uq_usuario (usuario),
    UNIQUE INDEX uq_email   (email)
) ENGINE=InnoDB;

-- Tabla de movimientos financieros, enlazada a usuario
CREATE TABLE IF NOT EXISTS finanzas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    descripcion VARCHAR(100),
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
