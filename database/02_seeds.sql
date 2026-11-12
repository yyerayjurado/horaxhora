-- =========================================================
-- * HoraXHora · Datos de prueba (seeds)
-- ! Se ejecuta después de 01_schema.sql
-- =========================================================

-- ! Sin esto, los acentos se guardan mal al importar desde Docker
SET NAMES utf8mb4;

USE sistema_trueque;

-- =========================================================
-- * DATOS DE PRUEBA
-- ! Contraseña de TODOS los usuarios de prueba: 1234
-- =========================================================

INSERT INTO categorias (id_categoria, nombre, descripcion) VALUES
                                                               (1,  'Hogar y reparaciones',    'Fontanería, pintura y pequeños arreglos en casa.'),
                                                               (2,  'Clases y formación',      'Clases particulares y apoyo escolar.'),
                                                               (3,  'Informática',             'Reparación de equipos, desarrollo web y soporte técnico.'),
                                                               (4,  'Electricidad',            'Instalaciones y revisiones eléctricas.'),
                                                               (5,  'Jardinería y exteriores', 'Cuidado de jardines, poda, riego y piscinas.'),
                                                               (6,  'Mudanzas y transporte',   'Ayuda para mover muebles, cajas y vaciar espacios.'),
                                                               (7,  'Cuidado de personas',     'Cuidado de niños y acompañamiento de personas mayores.'),
                                                               (8,  'Mascotas',                'Paseos, cuidado y aseo de animales.'),
                                                               (9,  'Belleza y bienestar',     'Peluquería, estética y masajes a domicilio.'),
                                                               (10, 'Diseño y multimedia',     'Diseño gráfico, fotografía, vídeo y redes sociales.'),
                                                               (11, 'Cocina y eventos',        'Comidas, repostería y organización de celebraciones.'),
                                                               (12, 'Motor y vehículos',       'Mecánica básica y cuidado del coche.'),
                                                               (13, 'Gestiones y papeleo',     'Trámites, declaraciones, currículums y traducciones.'),
                                                               (14, 'Deporte y salud',         'Entrenamiento personal y clases deportivas.'),
                                                               (15, 'Música y arte',           'Clases de instrumentos, canto y dibujo.');


INSERT INTO usuarios (id_usuario, nombre, apellidos, email, password, telefono, rol, puntos_saldo, fecha_registro) VALUES
                                                                                                                       (1, 'Admin',  'HoraXHora',    'admin@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '600000000', 'ADMIN',       0,  '2026-10-01 09:00:00'),
                                                                                                                       (2, 'Laura',  'Martínez Gil', 'laura@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '611111111', 'PROFESIONAL', 68, '2026-10-02 10:15:00'),
                                                                                                                       (3, 'Carlos', 'Ruiz Ortega',  'carlos@horaxhora.es', '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '622222222', 'PROFESIONAL', 50, '2026-10-03 11:30:00'),
                                                                                                                       (4, 'Ana',    'López Vela',   'ana@horaxhora.es',    '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '633333333', 'PROFESIONAL', 35, '2026-10-04 17:45:00'),
                                                                                                                       (5, 'David',  'Moreno Cano',  'david@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '644444444', 'PROFESIONAL', 47, '2026-10-05 19:00:00');


-- ! Los 8 primeros no se tocan: las ofertas de prueba apuntan a esos id
INSERT INTO servicios (id_servicio, id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
                                                                                                   (1,  1,  'Reparación de tuberías',              'Fugas, atascos y cambio de grifería.',                   15),
                                                                                                   (2,  1,  'Pintura de interiores',               'Pintado de habitaciones y pequeños retoques.',           12),
                                                                                                   (3,  2,  'Clases de matemáticas',               'Apoyo de ESO y Bachillerato.',                           10),
                                                                                                   (4,  2,  'Clases de inglés',                    'Conversación y preparación de exámenes oficiales.',      10),
                                                                                                   (5,  3,  'Reparación de ordenadores',           'Formateo, limpieza y cambio de componentes.',            12),
                                                                                                   (6,  3,  'Diseño de páginas web',               'Webs sencillas para negocios y particulares.',           20),
                                                                                                   (7,  4,  'Instalación eléctrica',               'Enchufes, puntos de luz y cableado.',                    18),
                                                                                                   (8,  4,  'Revisión de cuadro eléctrico',        'Diagnóstico de saltos de luz y diferenciales.',          15),

-- * Hogar y reparaciones
                                                                                                   (9,  1,  'Montaje de muebles',                  'Montaje de muebles de kit, estanterías y cuadros.',      12),
                                                                                                   (10, 1,  'Albañilería y alicatado',             'Pequeñas obras, azulejos y arreglos de paredes.',        18),
                                                                                                   (11, 1,  'Carpintería',                         'Ajuste de puertas, armarios y reparaciones en madera.',  16),
                                                                                                   (12, 1,  'Limpieza a fondo',                    'Limpieza general de viviendas tras obras o mudanzas.',   10),
                                                                                                   (13, 1,  'Cerrajería',                          'Cambio de bombines y apertura de puertas.',              18),

-- * Clases y formación
                                                                                                   (14, 2,  'Clases de física y química',          'Apoyo de ESO y Bachillerato.',                           12),
                                                                                                   (15, 2,  'Clases de programación',              'Iniciación a la programación y apoyo a estudiantes.',    15),
                                                                                                   (16, 2,  'Preparación de selectividad',         'Repaso y simulacros para la PAU.',                       14),
                                                                                                   (17, 2,  'Clases de francés',                   'Gramática, conversación y apoyo escolar.',               10),

-- * Informática
                                                                                                   (18, 3,  'Instalación de redes y wifi',         'Router, repetidores y cableado de red en casa.',         14),
                                                                                                   (19, 3,  'Recuperación de datos',               'Rescate de archivos de discos y memorias dañadas.',      18),
                                                                                                   (20, 3,  'Configuración de móviles y tablets',  'Puesta a punto, copias de seguridad y apps.',             8),

-- * Electricidad
                                                                                                   (21, 4,  'Instalación de lámparas y focos',     'Colocación de lámparas, apliques y focos LED.',          12),
                                                                                                   (22, 4,  'Domótica',                            'Enchufes, luces y persianas inteligentes.',              20),
                                                                                                   (23, 4,  'Cargador de coche eléctrico',         'Instalación de puntos de carga en garaje.',              22),

-- * Jardinería y exteriores
                                                                                                   (24, 5,  'Mantenimiento de jardín',             'Césped, plantas y limpieza de hojas.',                   10),
                                                                                                   (25, 5,  'Poda de árboles',                     'Poda de árboles y setos.',                               15),
                                                                                                   (26, 5,  'Instalación de riego',                'Riego por goteo y programadores.',                       15),
                                                                                                   (27, 5,  'Limpieza de piscinas',                'Limpieza y puesta a punto para el verano.',              12),

-- * Mudanzas y transporte
                                                                                                   (28, 6,  'Ayuda en mudanzas',                   'Cargar, descargar y embalar.',                           10),
                                                                                                   (29, 6,  'Transporte de muebles',               'Traslado de muebles con furgoneta.',                     14),
                                                                                                   (30, 6,  'Vaciado de trasteros',                'Vaciado y retirada de trastos.',                         10),

-- * Cuidado de personas
                                                                                                   (31, 7,  'Cuidado de niños',                    'Canguro por horas y recogida del colegio.',              10),
                                                                                                   (32, 7,  'Acompañamiento de mayores',           'Compañía, paseos y acompañamiento al médico.',           10),

-- * Mascotas
                                                                                                   (33, 8,  'Paseo de perros',                     'Paseos diarios o puntuales.',                             8),
                                                                                                   (34, 8,  'Cuidado de mascotas',                 'Cuidado en casa durante vacaciones.',                    10),
                                                                                                   (35, 8,  'Peluquería canina',                   'Baño, corte y arreglo de uñas.',                         14),

-- * Belleza y bienestar
                                                                                                   (36, 9,  'Peluquería a domicilio',              'Cortes, peinados y tintes en casa.',                     14),
                                                                                                   (37, 9,  'Manicura y pedicura',                 'Cuidado de manos y pies.',                               12),
                                                                                                   (38, 9,  'Masajes',                             'Masajes relajantes y descontracturantes.',               16),
                                                                                                   (39, 9,  'Maquillaje para eventos',             'Bodas, comuniones y sesiones de fotos.',                 15),

-- * Diseño y multimedia
                                                                                                   (40, 10, 'Diseño de logotipos',                 'Logos e identidad visual para negocios.',                18),
                                                                                                   (41, 10, 'Gestión de redes sociales',           'Publicaciones y contenido para Instagram y TikTok.',     15),
                                                                                                   (42, 10, 'Fotografía de eventos',               'Cumpleaños, bodas y eventos deportivos.',                20),
                                                                                                   (43, 10, 'Edición de vídeo',                    'Montaje de vídeos para redes o recuerdos.',              18),

-- * Cocina y eventos
                                                                                                   (44, 11, 'Cocinero a domicilio',                'Comidas y cenas preparadas en tu casa.',                 15),
                                                                                                   (45, 11, 'Repostería por encargo',              'Tartas y dulces para celebraciones.',                    12),
                                                                                                   (46, 11, 'Organización de fiestas',             'Decoración y planificación de cumpleaños y eventos.',    14),
                                                                                                   (47, 11, 'DJ para eventos',                     'Música y equipo de sonido para fiestas.',                20),

-- * Motor y vehículos
                                                                                                   (48, 12, 'Mecánica básica',                     'Cambio de aceite, filtros y pastillas de freno.',        16),
                                                                                                   (49, 12, 'Limpieza de coche',                   'Limpieza interior y exterior a domicilio.',               8),
                                                                                                   (50, 12, 'Cambio de neumáticos',                'Cambio y revisión de la presión de ruedas.',             10),
                                                                                                   (51, 12, 'Revisión antes de la ITV',            'Comprobación de luces, frenos y niveles.',               12),

-- * Gestiones y papeleo
                                                                                                   (52, 13, 'Declaración de la renta',             'Ayuda para hacer y revisar la declaración.',             18),
                                                                                                   (53, 13, 'Trámites con la administración',      'Citas, certificados y gestiones online.',                12),
                                                                                                   (54, 13, 'Redacción de currículums',            'Currículum y carta de presentación.',                    10),
                                                                                                   (55, 13, 'Traducción de documentos',            'Traducciones de inglés y francés.',                      15),

-- * Deporte y salud
                                                                                                   (56, 14, 'Entrenador personal',                 'Rutinas de fuerza y seguimiento.',                       15),
                                                                                                   (57, 14, 'Clases de yoga',                      'Sesiones individuales o en grupo.',                      12),
                                                                                                   (58, 14, 'Clases de pádel',                     'Técnica para principiantes y nivel medio.',              14),
                                                                                                   (59, 14, 'Plan de entrenamiento para correr',   'Preparación de carreras populares.',                     12),

-- * Música y arte
                                                                                                   (60, 15, 'Clases de guitarra',                  'Guitarra española y eléctrica.',                         12),
                                                                                                   (61, 15, 'Clases de piano',                     'Iniciación y lenguaje musical.',                         14),
                                                                                                   (62, 15, 'Clases de dibujo y pintura',          'Dibujo, acuarela y óleo.',                               10),
                                                                                                   (63, 15, 'Clases de canto',                     'Técnica vocal y respiración.',                           12);


INSERT INTO ofertas_profesionales (id_oferta, id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
                                                                                                                          (1, 2, 1, 15, 'Fontanera con 10 años de experiencia. Urgencias en el día.',  TRUE),
                                                                                                                          (2, 2, 2, 12, 'Pinto con cuidado y dejo todo recogido.',                     TRUE),
                                                                                                                          (3, 3, 7, 18, 'Electricista autorizado. Trabajo con boletín si hace falta.', TRUE),
                                                                                                                          (4, 3, 8, 15, 'Reviso el cuadro y te explico qué falla.',                    TRUE),
                                                                                                                          (5, 4, 3, 10, 'Profesora de matemáticas en secundaria.',                     TRUE),
                                                                                                                          (6, 4, 4, 12, 'Nivel C1, preparo B1 y B2.',                                  TRUE),
                                                                                                                          (7, 5, 5, 12, 'Técnico informático. Me paso por tu casa.',                   TRUE),
                                                                                                                          (8, 5, 6, 20, 'Desarrollador web. Ahora mismo sin disponibilidad.',          FALSE);  -- * Oferta desactivada para probar el filtro


-- =========================================================
-- * SERVICIOS QUE PUEDE OFERTAR CADA PROFESIONAL
-- ! Añadido al enunciado: en el registro se eligen; en "Crear oferta" solo salen estos
-- =========================================================

-- * Todos los servicios que ya tienen ofertados
INSERT INTO servicios_profesional (id_profesional, id_servicio)
SELECT id_profesional, id_servicio FROM ofertas_profesionales;

-- * Y uno más cada uno, todavía SIN ofertar, para poder probar "Crear oferta"
INSERT INTO servicios_profesional (id_profesional, id_servicio) VALUES
                                                                    (2, 9),   -- Laura:  Montaje de muebles
                                                                    (3, 21),  -- Carlos: Instalación de lámparas y focos
                                                                    (4, 17),  -- Ana:    Clases de francés
                                                                    (5, 18);  -- David:  Instalación de redes y wifi


-- * Una solicitud de cada estado para probar la agenda y el panel admin
INSERT INTO solicitudes_trueque
(id_solicitud, id_cliente, id_oferta, horas_estimadas, puntos_totales, fecha_propuesta, estado, confirmado_cliente, confirmado_prestador, fecha_creacion) VALUES
                                                                                                                                                              (1, 4, 1, 2.00, 30, '2026-10-10 10:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-08 12:00:00'),  -- Ana contrata a Laura
                                                                                                                                                              (2, 5, 5, 1.50, 15, '2026-10-12 17:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-09 09:30:00'),  -- David contrata a Ana
                                                                                                                                                              (3, 2, 7, 1.00, 12, '2026-10-15 18:00:00', 'COMPLETADO',   TRUE,  TRUE,  '2026-10-13 20:10:00'),  -- Laura contrata a David
                                                                                                                                                              (4, 3, 5, 2.00, 20, '2026-11-10 16:00:00', 'ACEPTADO',     FALSE, FALSE, '2026-11-01 11:00:00'),  -- Carlos contrata a Ana
                                                                                                                                                              (5, 4, 3, 1.00, 18, '2026-11-12 09:00:00', 'SOLICITADO',   FALSE, FALSE, '2026-11-02 08:45:00'),  -- Ana pide a Carlos
                                                                                                                                                              (6, 5, 2, 3.00, 36, '2026-11-08 10:00:00', 'RECHAZADO',    FALSE, FALSE, '2026-11-01 15:20:00'),  -- Laura rechaza a David
                                                                                                                                                              (7, 2, 4, 1.00, 15, '2026-11-02 12:00:00', 'EN_EJECUCION', FALSE, TRUE,  '2026-10-30 10:00:00');  -- * Falta que confirme el cliente


-- * Solo las solicitudes COMPLETADAS tienen valoración
INSERT INTO valoraciones (id_solicitud, puntuacion, comentario, fecha_valoracion) VALUES
                                                                                      (1, 5, 'Arregló la fuga en un momento. Muy recomendable.', '2026-10-10 14:00:00'),
                                                                                      (2, 4, 'Explica muy bien, mi hijo lo entendió todo.',       '2026-10-12 19:30:00'),
                                                                                      (3, 5, 'El portátil va como nuevo.',                        '2026-10-15 20:00:00');

-- ? Saldos de los seeds = 50 de bienvenida ± trueques completados:
-- ?   Laura  50 + 30 - 12 = 68
-- ?   Ana    50 - 30 + 15 = 35
-- ?   David  50 - 15 + 12 = 47
-- ?   Carlos 50 (aún no tiene ninguno completado)
