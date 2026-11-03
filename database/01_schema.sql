-- =========================================================
-- * HoraXHora · Estructura de la base de datos
-- =========================================================

-- ! Sin esto, los acentos de los seeds se guardan mal al importar desde Docker
SET NAMES utf8mb4;

USE sistema_trueque;

-- * Se borran en orden inverso a las FK para poder relanzar el script
DROP TABLE IF EXISTS valoraciones;
DROP TABLE IF EXISTS solicitudes_trueque;
DROP TABLE IF EXISTS ofertas_profesionales;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS categorias;


-- =========================================================
-- * TABLAS
-- =========================================================

CREATE TABLE categorias (
                            id_categoria INT AUTO_INCREMENT PRIMARY KEY,
                            nombre       VARCHAR(100) NOT NULL UNIQUE,
                            descripcion  TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE usuarios (
                          id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
                          nombre         VARCHAR(100) NOT NULL,
                          apellidos      VARCHAR(100) NOT NULL,
                          email          VARCHAR(150) NOT NULL UNIQUE,
                          password       VARCHAR(255) NOT NULL,                 -- * Hash de password_hash()
                          telefono       VARCHAR(20),
                          rol            ENUM('ADMIN', 'PROFESIONAL') NOT NULL DEFAULT 'PROFESIONAL',
                          puntos_saldo   INT NOT NULL DEFAULT 50,               -- * Puntos de bienvenida
                          fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    -- ! Nadie puede quedarse en negativo
                          CONSTRAINT chk_saldo_positivo CHECK (puntos_saldo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE servicios (
                           id_servicio            INT AUTO_INCREMENT PRIMARY KEY,
                           id_categoria           INT NOT NULL,
                           nombre                 VARCHAR(150) NOT NULL,
                           descripcion            TEXT,
                           puntos_hora_referencia INT NOT NULL DEFAULT 10,
                           CONSTRAINT fk_servicio_categoria FOREIGN KEY (id_categoria)
                               REFERENCES categorias(id_categoria) ON DELETE CASCADE,
                           CONSTRAINT chk_referencia_positiva CHECK (puntos_hora_referencia > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE ofertas_profesionales (
                                       id_oferta            INT AUTO_INCREMENT PRIMARY KEY,
                                       id_profesional       INT NOT NULL,
                                       id_servicio          INT NOT NULL,
                                       puntos_hora          INT NOT NULL,                    -- * Tarifa propia del profesional
                                       descripcion_personal TEXT,
                                       activo               BOOLEAN DEFAULT TRUE,
                                       CONSTRAINT fk_oferta_profesional FOREIGN KEY (id_profesional)
                                           REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
                                       CONSTRAINT fk_oferta_servicio FOREIGN KEY (id_servicio)
                                           REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    -- * Un profesional no puede ofrecer dos veces el mismo servicio
                                       CONSTRAINT uk_profesional_servicio UNIQUE (id_profesional, id_servicio),
                                       CONSTRAINT chk_puntos_hora_positivo CHECK (puntos_hora > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE solicitudes_trueque (
                                     id_solicitud         INT AUTO_INCREMENT PRIMARY KEY,
                                     id_cliente           INT NOT NULL,
                                     id_oferta            INT NOT NULL,
                                     horas_estimadas      DECIMAL(4,2) NOT NULL,
                                     puntos_totales       INT NOT NULL,                    -- * puntos_hora × horas_estimadas
                                     fecha_propuesta      DATETIME NOT NULL,
                                     estado               ENUM('SOLICITADO', 'ACEPTADO', 'RECHAZADO', 'EN_EJECUCION', 'COMPLETADO', 'CANCELADO')
                         DEFAULT 'SOLICITADO',
                                     confirmado_cliente   BOOLEAN DEFAULT FALSE,
                                     confirmado_prestador BOOLEAN DEFAULT FALSE,
                                     fecha_creacion       DATETIME DEFAULT CURRENT_TIMESTAMP,
                                     CONSTRAINT fk_solicitud_cliente FOREIGN KEY (id_cliente)
                                         REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
                                     CONSTRAINT fk_solicitud_oferta FOREIGN KEY (id_oferta)
                                         REFERENCES ofertas_profesionales(id_oferta) ON DELETE CASCADE,
                                     CONSTRAINT chk_horas_positivas CHECK (horas_estimadas > 0),
                                     CONSTRAINT chk_puntos_totales_positivos CHECK (puntos_totales > 0),
    -- * Índice para la agenda y el panel admin, que filtran mucho por estado
                                     INDEX idx_solicitud_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE valoraciones (
                              id_valoracion    INT AUTO_INCREMENT PRIMARY KEY,
                              id_solicitud     INT NOT NULL UNIQUE,                 -- * 1:1, una valoración por solicitud
                              puntuacion       TINYINT NOT NULL,
                              comentario       TEXT,
                              fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
                              CONSTRAINT fk_valoracion_solicitud FOREIGN KEY (id_solicitud)
                                  REFERENCES solicitudes_trueque(id_solicitud) ON DELETE CASCADE,
                              CONSTRAINT chk_puntuacion_rango CHECK (puntuacion BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;