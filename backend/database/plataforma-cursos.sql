CREATE DATABASE `plataforma-cursos`;

USE `plataforma-cursos`;

-- Tabla de usuarios
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('admin', 'usuario') NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `confirmado` TINYINT(1) DEFAULT 0
);

-- Tabla de cursos
CREATE TABLE `cursos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `imagen` VARCHAR(255),
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `admin_id` INT,
  FOREIGN KEY (`admin_id`) REFERENCES `usuarios`(`id`)
);

-- Tabla de lecciones
CREATE TABLE `lecciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `curso_id` INT,
  `titulo` VARCHAR(255) NOT NULL,
  `contenido_texto` TEXT,
  `video_url` VARCHAR(255),
  `pdf_url` VARCHAR(255),
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`)
);

CREATE TABLE IF NOT EXISTS evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    FOREIGN KEY (leccion_id) REFERENCES lecciones(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    pregunta TEXT NOT NULL,
    respuestas_correctas TEXT NOT NULL,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS intentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    evaluacion_id INT NOT NULL,
    intento INT NOT NULL,
    puntaje FLOAT NOT NULL,
    fecha_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id) ON DELETE CASCADE
);

CREATE TABLE `resultados` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT,
  `evaluacion_id` INT,
  `intentos` INT DEFAULT 0,
  `puntaje` INT,
  `fecha_intento` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`),
  FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones`(`id`)
);