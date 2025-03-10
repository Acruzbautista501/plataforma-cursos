CREATE DATABASE plataforma_cursos;

USE plataforma_cursos;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('usuario', 'admin') DEFAULT 'usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion_corta VARCHAR(255) NOT NULL,
    descripcion_larga TEXT NOT NULL,
    imagen VARCHAR(255),
    video VARCHAR(255),
    pdf VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE lecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curso_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    tipo_contenido ENUM('texto', 'video', 'pdf') NOT NULL,
    orden INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

CREATE TABLE progreso_lecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    leccion_id INT,
    completado BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (leccion_id) REFERENCES lecciones(id)
);


ALTER TABLE usuarios 
ADD COLUMN apellido_paterno VARCHAR(100),
ADD COLUMN apellido_materno VARCHAR(100),
ADD COLUMN fecha_nacimiento DATE,
ADD COLUMN pais VARCHAR(100),
ADD COLUMN estado VARCHAR(100),
ADD COLUMN ciudad VARCHAR(100),
ADD COLUMN direccion VARCHAR(255),
ADD COLUMN codigo_postal VARCHAR(20),
ADD COLUMN telefono VARCHAR(20),
ADD COLUMN confirm_token VARCHAR(255) NULL,
ADD COLUMN confirmado TINYINT(1) DEFAULT 0;

ALTER TABLE cursos
DROP COLUMN video,
DROP COLUMN pdf;

ALTER TABLE lecciones
ADD COLUMN video VARCHAR(255) NULL,
ADD COLUMN pdf VARCHAR(255) NULL;

-- Crear la tabla evaluaciones
CREATE TABLE evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leccion_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    minimo_aprobatorio DECIMAL(5,2) DEFAULT 60.00, -- Nota mínima aprobatoria
    FOREIGN KEY (leccion_id) REFERENCES lecciones(id) ON DELETE CASCADE
);

-- Tabla para almacenar los intentos de los usuarios
CREATE TABLE intentos_evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    usuario_id INT NOT NULL,
    puntaje DECIMAL(5,2),
    intentos INT DEFAULT 1,
    ultima_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprobado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluaciones(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    curso_id INT NOT NULL,
    puntaje DECIMAL(5,2) NOT NULL,
    fecha_terminacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
);

