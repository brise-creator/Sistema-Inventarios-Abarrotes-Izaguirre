CREATE DATABASE IF NOT EXISTS sistema_gestion
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sistema_gestion;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL UNIQUE,
    categoria VARCHAR(80) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL DEFAULT 0,
    existencias INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket VARCHAR(20) NOT NULL UNIQUE,
    cliente VARCHAR(120) NOT NULL,
    producto_id INT NOT NULL,
    producto_nombre VARCHAR(120) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    metodo_pago VARCHAR(40) NOT NULL,
    fecha DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

CREATE TABLE IF NOT EXISTS movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NULL,
    producto_nombre VARCHAR(120) NOT NULL,
    tipo ENUM('Registro', 'Entrada', 'Salida', 'Venta', 'Merma') NOT NULL,
    cantidad INT NOT NULL,
    fecha DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(40) NOT NULL,
    correo VARCHAR(120) NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Administrador', 'Cajero') NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nombre, usuario, password, rol, estado)
VALUES
    ('Administrador', 'Admin', '1234', 'Administrador', 'Activo'),
    ('Cajero', 'Cajero', '5678', 'Cajero', 'Activo')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    rol = VALUES(rol),
    estado = VALUES(estado);

CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha DATE NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);
