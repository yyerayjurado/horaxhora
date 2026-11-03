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
                                                               (1, 'Hogar y reparaciones', 'Fontanería, pintura y pequeños arreglos en casa.'),
                                                               (2, 'Clases y formación',   'Clases particulares y apoyo escolar.'),
                                                               (3, 'Informática',          'Reparación de equipos, desarrollo web y soporte técnico.'),
                                                               (4, 'Electricidad',         'Instalaciones y revisiones eléctricas.');


INSERT INTO usuarios (id_usuario, nombre, apellidos, email, password, telefono, rol, puntos_saldo, fecha_registro) VALUES
                                                                                                                       (1, 'Admin', 'HoraXHora',     'admin@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '600000000', 'ADMIN',       0,  '2026-10-01 09:00:00'),
                                                                                                                       (2, 'Laura',  'Martínez Gil', 'laura@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '611111111', 'PROFESIONAL', 68, '2026-10-02 10:15:00'),
                                                                                                                       (3, 'Carlos', 'Ruiz Ortega',  'carlos@horaxhora.es', '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '622222222', 'PROFESIONAL', 50, '2026-10-03 11:30:00'),
                                                                                                                       (4, 'Ana',    'López Vela',   'ana@horaxhora.es',    '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '633333333', 'PROFESIONAL', 35, '2026-10-04 17:45:00'),
                                                                                                                       (5, 'David',  'Moreno Cano',  'david@horaxhora.es',  '$2y$10$jMwkO2cY0Mfq858Tat3w2u3hoHa8Xt5Y61jYimni9cCxpdy9MMTJ2', '644444444', 'PROFESIONAL', 47, '2026-10-05 19:00:00');


INSERT INTO servicios (id_servicio, id_categoria, nombre, descripcion, puntos_hora_referencia) VALUES
                                                                                                   (1, 1, 'Reparación de tuberías',       'Fugas, atascos y cambio de grifería.',              15),
                                                                                                   (2, 1, 'Pintura de interiores',        'Pintado de habitaciones y pequeños retoques.',      12),
                                                                                                   (3, 2, 'Clases de matemáticas',        'Apoyo de ESO y Bachillerato.',                      10),
                                                                                                   (4, 2, 'Clases de inglés',             'Conversación y preparación de exámenes oficiales.', 10),
                                                                                                   (5, 3, 'Reparación de ordenadores',    'Formateo, limpieza y cambio de componentes.',       12),
                                                                                                   (6, 3, 'Diseño de páginas web',        'Webs sencillas para negocios y particulares.',      20),
                                                                                                   (7, 4, 'Instalación eléctrica',        'Enchufes, puntos de luz y cableado.',               18),
                                                                                                   (8, 4, 'Revisión de cuadro eléctrico', 'Diagnóstico de saltos de luz y diferenciales.',    15);


INSERT INTO ofertas_profesionales (id_oferta, id_profesional, id_servicio, puntos_hora, descripcion_personal, activo) VALUES
                                                                                                                          (1, 2, 1, 15, 'Fontanera con 10 años de experiencia. Urgencias en el día.',     TRUE),
                                                                                                                          (2, 2, 2, 12, 'Pinto con cuidado y dejo todo recogido.',                        TRUE),
                                                                                                                          (3, 3, 7, 18, 'Electricista autorizado. Trabajo con boletín si hace falta.',     TRUE),
                                                                                                                          (4, 3, 8, 15, 'Reviso el cuadro y te explico qué falla.',                       TRUE),
                                                                                                                          (5, 4, 3, 10, 'Profesora de matemáticas en secundaria.',                        TRUE),
                                                                                                                          (6, 4, 4, 12, 'Nivel C1, preparo B1 y B2.',                                     TRUE),
                                                                                                                          (7, 5, 5, 12, 'Técnico informático. Me paso por tu casa.',                      TRUE),
                                                                                                                          (8, 5, 6, 20, 'Desarrollador web. Ahora mismo sin disponibilidad.',             FALSE);  -- * Oferta desactivada para probar el filtro


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
                                                                                      (1, 5, 'Arregló la fuga en un momento. Muy recomendable.',  '2026-10-10 14:00:00'),
                                                                                      (2, 4, 'Explica muy bien, mi hijo lo entendió todo.',        '2026-10-12 19:30:00'),
                                                                                      (3, 5, 'El portátil va como nuevo.',                         '2026-10-15 20:00:00');

-- ? Saldos de los seeds = 50 de bienvenida ± trueques completados:
-- ?   Laura  50 + 30 - 12 = 68
-- ?   Ana    50 - 30 + 15 = 35
-- ?   David  50 - 15 + 12 = 47
-- ?   Carlos 50 (aún no tiene ninguno completado)