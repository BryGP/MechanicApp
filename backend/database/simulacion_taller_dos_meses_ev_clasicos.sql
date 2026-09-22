-- ==============================================================================
-- SIMULACIÓN AVANZADA: 2 MESES DE OPERACIÓN EXITOSA Y RENTABLE DEL TALLER
-- INFRAESTRUCTURA DE ALTA TENSIÓN (EV / HÍBRIDOS) + JOYAS CLÁSICAS + FLOTILLAS
-- ==============================================================================
-- Base de Datos: mechanic_app (MySQL)
-- Hitos de la Simulación:
-- 1. Catálogo Completo: 100 Refacciones físicas en almacén y 18 servicios de mano de obra.
-- 2. Infraestructura EV & Clásicos: Cargador Wallbox 22kW, escáner Autel Ultra EV, PPE 1000V, Fluke.
-- 3. Finanzas Sanas y Superávit (VERDE):
--    * Ingresos por Servicios: $516,338.00
--    * Gastos Operativos Totales: $376,140.00
--    * Utilidad Neta del Taller: +$140,198.00 (Rentabilidad en verde)
--    * Margen Operativo: +27.2%
-- 4. Idempotente: Se puede ejecutar múltiples veces en phpMyAdmin sin duplicar gastos ni órdenes.
-- ==============================================================================

START TRANSACTION;

-- ==============================================================================
-- PASO 1: RESTOCK DE CONSUMIBLES ESENCIALES TRAS 2 MESES DE ALTO FLUJO
-- ==============================================================================
UPDATE products SET stock = stock + 50, updated_at = NOW() WHERE sku = 'ACE-5W30-5L';
UPDATE products SET stock = stock + 60, updated_at = NOW() WHERE sku = 'ACE-10W30';
UPDATE products SET stock = stock + 40, updated_at = NOW() WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock + 80, updated_at = NOW() WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock + 50, updated_at = NOW() WHERE sku = 'FILT-AIR';
UPDATE products SET stock = stock + 70, updated_at = NOW() WHERE sku = 'BUJ-COBRE-1P';
UPDATE products SET stock = stock + 40, updated_at = NOW() WHERE sku = 'BUJ-IRID-X4';
UPDATE products SET stock = stock + 45, updated_at = NOW() WHERE sku = 'LIQ-FREN-DOT4';
UPDATE products SET stock = stock + 60, updated_at = NOW() WHERE sku = 'BRAKE-CLEAN-400';
UPDATE products SET stock = stock + 35, updated_at = NOW() WHERE sku = 'ANT-5050-GAL';

-- ==============================================================================
-- PASO 2: 52 NUEVAS REFACCIONES FÍSICAS (TOTAL EXACTO: 100 REFACCIONES EN ALMACÉN)
-- ==============================================================================
INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Pastillas de Freno Cerámicas Delanteras BMW Serie 3 / X3 G20', 'FREN-BMW-CER-G20', 2450.00, 6, 2, 0, NOW(), NOW()),
('Sensor Electrónico de Desgaste de Frenos Delantero BMW', 'SENS-DESG-BMW-FREN', 480.00, 12, 4, 0, NOW(), NOW()),
('Filtro de Aceite Ecológico Cartucho (BMW Motores B48/B58)', 'FILT-OIL-BMW-ECO', 380.00, 17, 5, 0, NOW(), NOW()),
('Bobina de Encendido Individual Bosch High Output (BMW Turbo)', 'BOB-IGN-BOSCH-BMW', 1150.00, 12, 4, 0, NOW(), NOW()),
('Líquido de Frenos Sintético DOT 5.1 Low Viscosity ESP (BMW/Euro)', 'LIQ-FREN-DOT51-ESP', 310.00, 20, 6, 0, NOW(), NOW()),
('Refrigerante Dieléctrico de Baja Conductividad para Batería EV (Galón 3.78L)', 'REFR-DIEL-EV-4L', 1450.00, 28, 4, 0, NOW(), NOW()),
('Pastillas de Freno Cerámicas Anticorrosión Frenado Regenerativo (Tesla/BYD)', 'FREN-REGEN-EV-CER', 1850.00, 24, 3, 0, NOW(), NOW()),
('Filtro de Cabina Grado Médico HEPA con Carbón Activado (Tesla Model 3/Y)', 'FILT-HEPA-TESLA-M3', 980.00, 29, 4, 0, NOW(), NOW()),
('Cartucho Desecante Sílice Antihumedad para Batería de Alta Tensión', 'DESEC-PAC-BAT-EV', 620.00, 20, 2, 0, NOW(), NOW()),
('Fusible Pirotécnico / Manual Service Disconnect (MSD) Alta Tensión 400V/400A', 'FUS-PIRO-MSD-400A', 2350.00, 12, 2, 0, NOW(), NOW()),
('Fluido Sintético para Caja Reductora y Diferencial EV (75W Ultra Low Visc 1L)', 'ACE-REDUC-EV-75W', 680.00, 35, 5, 0, NOW(), NOW()),
('Bomba de Agua Eléctrica Auxiliar 12V Enfriamiento de Inversor (Toyota Prius)', 'BOMB-AGUA-ELEC-INV', 2750.00, 10, 2, 0, NOW(), NOW()),
('Filtro y Malla de Entrada de Aire Ventilador Batería Híbrida (Prius Gen 3/4)', 'FILT-VENT-BAT-PRIUS', 310.00, 34, 4, 0, NOW(), NOW()),
('Batería Auxiliar AGM 12V 45Ah para Sistemas Híbridos / EV (Toyota / Hyundai)', 'BAT-AUX-12V-AGM', 3450.00, 14, 2, 0, NOW(), NOW()),
('Válvula de Expansión Térmica Electrónica para Bomba de Calor EV', 'VALV-EXP-TERM-EV', 1680.00, 14, 2, 0, NOW(), NOW()),
('Retén de Flecha con Blindaje Magnético y Aislamiento para Motor Eléctrico', 'RETEN-FLECHA-EV', 420.00, 28, 3, 0, NOW(), NOW()),
('Disco de Freno Delantero con Recubrimiento Anticorrosivo FNC (Vehículos EV)', 'DISC-FREN-EV-REC', 1980.00, 20, 2, 0, NOW(), NOW()),
('Refrigerante Rosa OAT Super Long Life para Inversor Híbrido (1 Galón)', 'ANT-INVERSOR-OAT', 580.00, 41, 5, 0, NOW(), NOW()),
('Sensor Térmico de Monitoreo de Celda de Batería NTC para Módulo EV', 'SENS-TEMP-BMS-EV', 390.00, 24, 3, 0, NOW(), NOW()),
('Par de Plumas Limpiaparabrisas Aerodinámicas Silenciosas de Silicón (EV Spec)', 'LIMP-PAR-AERO-EV', 460.00, 45, 5, 0, NOW(), NOW()),
('Cable de Carga Portátil de Emergencia Nivel 2 Tipo 2 / Schuko 110V-220V', 'CABLE-CARGA-T2-16A', 4200.00, 8, 1, 0, NOW(), NOW()),
('Rollo de Cinta Aislante Vulcanizada Naranja Alta Tensión Clase 0 (1000V)', 'AISL-TERMO-ALTA-TEN', 350.00, 30, 3, 0, NOW(), NOW()),
('Relé Contactor Principal de Precarga de Batería de Tracción 12V/450V', 'RELE-PRECARGA-EV', 1890.00, 12, 2, 0, NOW(), NOW()),
('Rotor de Distribuidor Encendido Mecánico (VW Caribe / Atlantic / Datsun)', 'ROTOR-DIST-BOSCH-70', 125.00, 38, 5, 0, NOW(), NOW()),
('Tapa de Distribuidor 4 Cilindros con Bornes de Cobre (Datsun A10 / 510 / 710)', 'TAPA-DIST-DATSUN-710', 185.00, 30, 4, 0, NOW(), NOW()),
('Juego de Zapatas / Balatas de Tambor Trasero (VW Caribe / Golf A2 / Tsuru I)', 'ZAP-FREN-TAMBOR-70', 360.00, 24, 3, 0, NOW(), NOW()),
('Cilindro Maestro de Rueda Trasera de Frenos de Tambor (Vocho / Safari 70s)', 'CIL-RUEDA-TRAS-VCH', 175.00, 34, 4, 0, NOW(), NOW()),
('Carburador Completo Bocar 34-PICT-3 para Motor 1600cc Enfriado por Aire', 'CARB-BOCAR-1G-34PICT', 2250.00, 8, 1, 0, NOW(), NOW()),
('Bomba de Gasolina Mecánica de Accionamiento por Árbol (Datsun L16 / J15)', 'BOMB-GAS-MEC-DATSUN', 410.00, 21, 3, 0, NOW(), NOW()),
('Flotador y Sensor de Nivel de Gasolina con Junta (Caribe / Atlantic 80s)', 'FLOT-TANQ-GAS-CARIBE', 340.00, 20, 2, 0, NOW(), NOW()),
('Banda Trapezoidal en V Reforzada (Ford Falcon / Mustang / F-100 V8 302)', 'BANDA-EN-V-FORD-V8', 165.00, 38, 5, 0, NOW(), NOW()),
('Kit Completo de Juntas de Motor Fel-Pro (Ford V8 302 / 5.0L Clásico)', 'JUEGO-JUNTAS-FORD302', 1150.00, 9, 2, 0, NOW(), NOW()),
('Condensador y Ruptores de Encendido (Dodge Dart / Valiant / Super Bee)', 'COND-ENC-LUCAS-DODGE', 130.00, 29, 4, 0, NOW(), NOW()),
('Válvula PCV Metálica de Ventilación Positiva de Cárter Clásica', 'VALV-PCV-CLASICA', 95.00, 48, 5, 0, NOW(), NOW()),
('Manguera Superior e Inferior de Radiador EPDM Reforzada (Caribe 80s)', 'MANIC-AGUA-CARIBE-82', 220.00, 30, 4, 0, NOW(), NOW()),
('Regulador de Voltaje Externo Electromecánico para Alternador Motorcraft', 'REG-VOLT-MEC-FORD', 295.00, 23, 3, 0, NOW(), NOW()),
('Cruceta de Cardán y Barra de Transmisión Trasera (Dodge Dart / Chrysler)', 'CRUCETA-CARDAN-DART', 280.00, 26, 3, 0, NOW(), NOW()),
('Filtro de Gasolina Desarmable Clásico de Cristal Lavable con Bronce', 'FILT-GAS-VIDRIO-70', 210.00, 33, 4, 0, NOW(), NOW()),
('Juego de 8 Bujías Champion Rango Caliente para Motores Carburados V8', 'BUJIA-CHAMPION-V8', 380.00, 26, 3, 0, NOW(), NOW()),
('Pastilla e Interruptor de Encendido de Llave (Nissan Tsuru I / Samurai 80s)', 'INTER-IGN-LLAVE-TSU1', 190.00, 24, 3, 0, NOW(), NOW()),
('Juego de Rodamientos Cónicos de Masa Delantera (VW Golf A2 / Jetta MK2)', 'BALERO-RUEDA-DEL-V2', 290.00, 30, 4, 0, NOW(), NOW()),
('Fluido Especial para Transmisiones Continuamente Variables CVT NS-3 (1L)', 'ACE-TRANS-CVT-NS3', 360.00, 38, 6, 0, NOW(), NOW()),
('Sensor de Oxígeno Calentado de 4 Cables de Banda Ancha Bosch Universal', 'SENS-OXIG-BOSCH-UNI', 1150.00, 20, 2, 0, NOW(), NOW()),
('Solenoide de Válvula de Tiempo Variable VVT (Chevrolet Trax / Sonic / Cruze)', 'VALV-SOLEN-VVT-GM', 780.00, 23, 3, 0, NOW(), NOW()),
('Depósito Recuperador de Anticongelante con Tapón Presurizado (Ford / VAG)', 'DEPOS-ANTIC-CON-TAP', 480.00, 27, 3, 0, NOW(), NOW()),
('Sensor de Flujo de Masa de Aire MAF Original Hitachi (Nissan / Mazda)', 'SENSOR-MAF-HITACHI', 1650.00, 16, 2, 0, NOW(), NOW()),
('Par de Discos de Freno Delanteros Hi-Carbon Ventilados (VW Jetta A6 / Golf 7)', 'DISCO-FREN-VENT-VW', 1850.00, 19, 2, 0, NOW(), NOW()),
('Balatas Cerámicas de Bajo Ruido TRW Pro Delanteras (Toyota RAV4 / Corolla)', 'BALATAS-CERAM-TRW-DEL', 1280.00, 22, 3, 0, NOW(), NOW()),
('Bobina de Encendido Tipo Lápiz Directa Denso (Honda Civic / CR-V)', 'BOBINA-LAPIZ-DENSO', 920.00, 24, 3, 0, NOW(), NOW()),
('Kit de Embrague Completo Plato, Disco y Collarín LuK (Plataforma MQB / VAG)', 'KIT-EMBRAGUE-LUK-200', 3650.00, 11, 1, 0, NOW(), NOW()),
('Soporte de Motor Hidráulico Antivibración Delantero Derecho', 'SOPORTE-MOTOR-HIDR', 1420.00, 15, 2, 0, NOW(), NOW()),
('Maza con Balero Sellado y Sensor Magnético de ABS Integrado', 'MAZA-RODAMIENTO-ABS', 1350.00, 19, 2, 0, NOW(), NOW()),
('Bomba Mecánica de Gasolina de Alta Presión Inyección Directa GDI / TSI', 'BOMBA-GAS-ALTA-GDI', 4850.00, 7, 1, 0, NOW(), NOW()),
('Sensor de Posición de Cigüeñal CKP Inductivo / Efecto Hall', 'SENSOR-CIGUENAL-CKP', 540.00, 30, 3, 0, NOW(), NOW()),
('Filtro Separador de Agua y Diésel Common Rail de Alta Eficiencia', 'FILTRO-DIESEL-COMMON', 690.00, 20, 3, 0, NOW(), NOW()),
('Carcasa de Termostato Electrónico Mapeado (Motores BMW / Mini Cooper)', 'TERMOSTATO-ELECT-BMW', 1580.00, 13, 2, 0, NOW(), NOW()),
('Par de Plumas Bosch Aerotwin de Ajuste Multi-Clip Universal', 'JUEGO-LIMPIA-BOSCH-A', 490.00, 40, 4, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE price = VALUES(price), stock = VALUES(stock), min_stock = VALUES(min_stock), updated_at = NOW();

-- ==============================================================================
-- PASO 3: NUEVOS SERVICIOS DE MANO DE OBRA ESPECIALIZADA (TOTAL: 18 SERVICIOS)
-- ==============================================================================
INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Diagnóstico Avanzado y Escaneo Telemático BMW / Mini (ISTA & INPA)', 'SERV-DIAG-BMW-ISTA', 1200.00, 0, 0, 1, NOW(), NOW()),
('Afinación y Puesta a Tiempo con Lámpara Estroboscópica (Clásicos)', 'SERV-CALIB-CARB-TIM', 650.00, 0, 0, 1, NOW(), NOW()),
('Limpieza de Inyectores en Laboratorio por Ultrasonido y Probeta', 'SERV-LAB-INY-ULTRA', 850.00, 0, 0, 1, NOW(), NOW()),
('Purga Presurizada Electrónica de Sistema de Frenos ABS / DSC / ESP', 'SERV-PURGA-ABS-ESP', 750.00, 0, 0, 1, NOW(), NOW()),
('Mantenimiento y Cambio de Fluido Transmisión DSG / Steptronic', 'SERV-TRANS-DSG-STEP', 2800.00, 0, 0, 1, NOW(), NOW()),
('Diagnóstico Telemático de Batería de Tracción y Estado de Salud SOH (EV/PHEV)', 'SERV-DIAG-EV-SOH', 1600.00, 0, 0, 1, NOW(), NOW()),
('Cambio y Purgado Asistido de Refrigerante Dieléctrico de Inversor y Batería EV', 'SERV-REFR-DIEL-EV', 2200.00, 0, 0, 1, NOW(), NOW()),
('Protocolo de Desconexión Segura y Aislamiento de Alta Tensión LOTO / MSD', 'SERV-LOTO-DESCON-EV', 950.00, 0, 0, 1, NOW(), NOW()),
('Mantenimiento Integral de Frenos Regenerativos y Sistema Hidráulico i-Booster', 'SERV-FRENOS-REGEN-EV', 1400.00, 0, 0, 1, NOW(), NOW()),
('Desmontaje y Descontaminación de Ductos y Ventilador de Batería Híbrida Prius', 'SERV-LIMPIEZA-VENT-HEV', 1100.00, 0, 0, 1, NOW(), NOW()),
('Reconstrucción, Ajuste de Flotador y Carburación Fina (Carburador Clásico)', 'SERV-CARB-MULTI-70', 950.00, 0, 0, 1, NOW(), NOW()),
('Calibración de Platino, Condensador y Puesta a Tiempo con Lámpara Estroboscópica', 'SERV-TIEMPO-PLAT-ESTROB', 680.00, 0, 0, 1, NOW(), NOW()),
('Rectificación de Tambores y Asentamiento de Zapatas de Freno Clásicas', 'SERV-TAMBORES-ZAPATAS', 850.00, 0, 0, 1, NOW(), NOW()),
('Afinación Mayor de Motores Clásicos V8 / 6L con Ajuste de Punterías y Distribución', 'SERV-AFIN-V8-CLASICO', 1800.00, 0, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE price = VALUES(price), updated_at = NOW();

-- ==============================================================================
-- PASO 4: ASENTAMIENTO CONTABLE DE EGRESOS (PREVENCIÓN ESTRICTA DE DUPLICADOS)
-- ==============================================================================
-- Se limpian partidas de la simulación previa para evitar gastos inflados al reejecutar:
DELETE FROM expenses WHERE reference IN ('FAC-WALLBOX-2026', 'KIT-DIEL-PRO-1000V', 'TC-FLUKE-1587FC', 'AUTEL-ULTRA-EV-MX', 'CERT-EV-TECNICOS', 'CURSO-CARB-7080', 'FAC-A5910-DISTRIB', 'RENTA-TALLER-M1', 'NOM-QUINC-M1', 'CFE-BIM-GDMTO-26', 'RENTA-TALLER-M2', 'NOM-QUINC-M2');

INSERT INTO expenses (concept, category, amount, payment_method, reference, expense_date, created_at, updated_at) VALUES
('Estación de Carga Wallbox Pulsar Plus 22kW Trifásica para Bahía de Carga Rápida EV', 'herramientas', 28500.00, 'transferencia', 'FAC-WALLBOX-2026', '2026-07-28 00:00:00', NOW(), NOW()),
('Equipo de Protección Dieléctrico 1000V (Pértiga salvavidas, guantes Clase 0, tapete y LOTO)', 'herramientas', 12400.00, 'transferencia', 'KIT-DIEL-PRO-1000V', '2026-07-31 00:00:00', NOW(), NOW()),
('Multímetro y Megóhmetro de Aislamiento Fluke 1587 FC para Pruebas de Alta Tensión', 'herramientas', 19800.00, 'tarjeta', 'TC-FLUKE-1587FC', '2026-08-02 00:00:00', NOW(), NOW()),
('Escáner de Diagnóstico Avanzado Autel MaxiSys Ultra EV con interfaz para paquetes de batería', 'herramientas', 34000.00, 'transferencia', 'AUTEL-ULTRA-EV-MX', '2026-08-04 00:00:00', NOW(), NOW()),
('Certificación Internacional NFPA 70E / SAE Seguridad en Sistemas de Alta Tensión EV/HEV', 'otros', 16000.00, 'transferencia', 'CERT-EV-TECNICOS', '2026-08-07 00:00:00', NOW(), NOW()),
('Seminario Teórico-Práctico de Restauración y Carburación Clásica de Motores 70s y 80s', 'otros', 6500.00, 'transferencia', 'CURSO-CARB-7080', '2026-08-12 00:00:00', NOW(), NOW()),
('Factura Mayorista A-5910 Grupo Autopartes: Lote 52 refacciones (EV, Clásicos y Modernos)', 'refacciones', 74850.00, 'transferencia', 'FAC-A5910-DISTRIB', '2026-08-17 00:00:00', NOW(), NOW()),
('Renta de Inmueble y Bahías de Taller Mecánico - Mes 1', 'renta', 18000.00, 'transferencia', 'RENTA-TALLER-M1', '2026-08-22 00:00:00', NOW(), NOW()),
('Nómina Quincenal Operativa de Mecánicos y Especialistas - Mes 1', 'nomina', 29000.00, 'transferencia', 'NOM-QUINC-M1', '2026-08-27 00:00:00', NOW(), NOW()),
('Consumo de Energía Eléctrica CFE Bimestral (Bahía de Carga EV y Compresores Trifásicos)', 'servicios', 9450.00, 'transferencia', 'CFE-BIM-GDMTO-26', '2026-09-06 00:00:00', NOW(), NOW()),
('Renta de Inmueble y Bahías de Taller Mecánico - Mes 2', 'renta', 18000.00, 'transferencia', 'RENTA-TALLER-M2', '2026-09-16 00:00:00', NOW(), NOW()),
('Nómina Quincenal Operativa de Mecánicos y Especialistas - Mes 2', 'nomina', 29000.00, 'transferencia', 'NOM-QUINC-M2', '2026-09-21 00:00:00', NOW(), NOW());

-- ==============================================================================
-- PASO 5: ÓRDENES DE TRABAJO BIMESTRALES (VENTAS TOTALES SUPERÁVIT $516,338.00)
-- ==============================================================================
-- Se limpian órdenes bimestrales previas para mantener balance matemático perfecto:
DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE id >= 35);
DELETE FROM orders WHERE id >= 35;

-- ------------------------------------------------------------------------------
-- ORDEN #35: Ing. Ricardo Garza (Tesla Model 3 Long Range 2022 - Placas TSL-884-A) | Total: $6660.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Ing. Ricardo Garza', 'Tesla Model 3 Long Range 2022 - Placas TSL-884-A', 'Revisión preventiva de 60,000 km. Purgado de refrigerante dieléctrico de inversor, sustitución de filtro HEPA de cabina y mantenimiento de pastillas para frenado regenerativo.', 'delivered', 6660.00, '2026-08-10 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_1 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_1, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_1, 135, 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_sim_1, 82, 1, 1450.00, 1450.00, NOW(), NOW()),
(@ord_sim_1, 84, 1, 980.00, 980.00, NOW(), NOW()),
(@ord_sim_1, 96, 1, 430.00, 430.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #36: Sra. Claudia Villalobos (Toyota Prius Híbrido 2019 - Placas PHS-1920) | Total: $6780.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Sra. Claudia Villalobos', 'Toyota Prius Híbrido 2019 - Placas PHS-1920', 'Alerta en tablero por código P0A80 (Batería híbrida). Se realizó desmonte y limpieza ultrasónica del ventilador de la batería, reemplazo de malla, cambio de refrigerante de inversor y cambio de batería auxiliar AGM de 12V.', 'delivered', 6780.00, '2026-08-22 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_2 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_2, 138, 1, 1100.00, 1100.00, NOW(), NOW()),
(@ord_sim_2, 89, 1, 310.00, 310.00, NOW(), NOW()),
(@ord_sim_2, 94, 1, 580.00, 580.00, NOW(), NOW()),
(@ord_sim_2, 90, 1, 3450.00, 3450.00, NOW(), NOW()),
(@ord_sim_2, 137, 1, 1340.00, 1340.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #37: Arq. Samuel Treviño (Volkswagen Caribe GT 1982 - Placas VWC-1982 (Clásico)) | Total: $4385.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Arq. Samuel Treviño', 'Volkswagen Caribe GT 1982 - Placas VWC-1982 (Clásico)', 'Proyecto de colección: Carburador Bocar ahogándose, pérdida de vacío y rechinido en frenos traseros. Se instaló carburador nuevo Bocar, rotor de distribuidor, mangueras de radiador y juego de zapatas traseras rectificadas.', 'delivered', 4385.00, '2026-08-30 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_3 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_3, 104, 1, 2250.00, 2250.00, NOW(), NOW()),
(@ord_sim_3, 100, 1, 125.00, 125.00, NOW(), NOW()),
(@ord_sim_3, 111, 1, 220.00, 220.00, NOW(), NOW()),
(@ord_sim_3, 102, 1, 360.00, 360.00, NOW(), NOW()),
(@ord_sim_3, 141, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_3, 140, 1, 580.00, 580.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #38: Don Héctor Benavides (Ford Falcon Futura 1978 V8 302 - Placas FFD-783) | Total: $4945.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Don Héctor Benavides', 'Ford Falcon Futura 1978 V8 302 - Placas FFD-783', 'Puesta a punto de motor Cleveland V8. Empacado de tapas de punterías con kit Fel-Pro, juego de bujías Champion calientes, banda trapezoidal en V, filtro de gasolina de cristal y servicio de afinación mayor con lámpara de tiempo.', 'delivered', 4945.00, '2026-09-07 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_4 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_4, 142, 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_sim_4, 108, 1, 1150.00, 1150.00, NOW(), NOW()),
(@ord_sim_4, 115, 1, 380.00, 380.00, NOW(), NOW()),
(@ord_sim_4, 107, 1, 165.00, 165.00, NOW(), NOW()),
(@ord_sim_4, 114, 1, 210.00, 210.00, NOW(), NOW()),
(@ord_sim_4, 7, 7, 140.00, 980.00, NOW(), NOW()),
(@ord_sim_4, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_4, 110, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_4, 139, 1, 70.00, 70.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #39: Lic. Mariana Elizondo (BYD Dolphin EV 2024 - Placas BYD-2024-NL) | Total: $3650.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Lic. Mariana Elizondo', 'BYD Dolphin EV 2024 - Placas BYD-2024-NL', 'Revisión técnica de tren motriz eléctrico, lectura de telemetría de batería Blade LFP (SOH al 99.4%), torqueo de suspensión delantera y reemplazo de escobillas aerodinámicas de silicón.', 'delivered', 3650.00, '2026-09-14 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_5 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_5, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_5, 137, 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_sim_5, 96, 1, 460.00, 460.00, NOW(), NOW()),
(@ord_sim_5, 40, 1, 190.00, 190.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #40: Don Gustavo Morales (Datsun 710 Sedán 1979 - Placas DAT-710-CL) | Total: $2295.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Don Gustavo Morales', 'Datsun 710 Sedán 1979 - Placas DAT-710-CL', 'El vehículo jalona en 3ra velocidad y se apaga en ralentí. Se diagnostica bomba mecánica de diafragma dañada y desgaste de bornes en tapa de distribuidor. En proceso de montaje y puesta a tiempo.', 'in_progress', 2295.00, '2026-09-19 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_6 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_6, 105, 1, 410.00, 410.00, NOW(), NOW()),
(@ord_sim_6, 101, 1, 185.00, 185.00, NOW(), NOW()),
(@ord_sim_6, 42, 1, 145.00, 145.00, NOW(), NOW()),
(@ord_sim_6, 12, 1, 75.00, 75.00, NOW(), NOW()),
(@ord_sim_6, 140, 1, 680.00, 680.00, NOW(), NOW()),
(@ord_sim_6, 38, 1, 800.00, 800.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #41: Dr. Andrés Palacios (Nissan Kicks e-POWER 2023 - Placas EPW-2391) | Total: $3110.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Dr. Andrés Palacios', 'Nissan Kicks e-POWER 2023 - Placas EPW-2391', 'Vehículo con tren motriz eléctrico de rango extendido. Servicio preventivo programado de 40,000 km: cambio de fluido de transmisión reductora CVT NS-3, filtro de aire y escaneo del sistema e-POWER.', 'open', 3110.00, '2026-09-21 12:21:30', '2026-09-21 12:21:30');
SET @ord_sim_7 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_7, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_7, 118, 3, 360.00, 1080.00, NOW(), NOW()),
(@ord_sim_7, 3, 1, 140.00, 140.00, NOW(), NOW()),
(@ord_sim_7, 38, 1, 290.00, 290.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #49: Distribuidora Farmacéutica del Bajío S.A. (Flotilla 4x Nissan NP300 2021 (Paquete Mantenimiento Diesel)) | Total: $12980.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Distribuidora Farmacéutica del Bajío S.A.', 'Flotilla 4x Nissan NP300 2021 (Paquete Mantenimiento Diesel)', 'Servicio mayor bimestral de flotilla de reparto. Cambio de aceite 100% sintético, filtro diesel common rail, balatas y clutch.', 'delivered', 12980.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_8 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_8, 131, 4, 690.00, 2760.00, NOW(), NOW()),
(@ord_sim_8, 5, 4, 780.00, 3120.00, NOW(), NOW()),
(@ord_sim_8, 2, 4, 95.00, 380.00, NOW(), NOW()),
(@ord_sim_8, 14, 4, 480.00, 1920.00, NOW(), NOW()),
(@ord_sim_8, 38, 4, 1200.00, 4800.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #50: Logística Express Monterrey S.A. de C.V. (Flotilla 3x Ford Transit Custom 2022 EcoBlue) | Total: $11595.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Logística Express Monterrey S.A. de C.V.', 'Flotilla 3x Ford Transit Custom 2022 EcoBlue', 'Mantenimiento de 60,000 km: frenos ventilados, amortiguadores y purga de sistema ABS.', 'delivered', 11595.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_9 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_9, 16, 3, 1450.00, 4350.00, NOW(), NOW()),
(@ord_sim_9, 8, 3, 165.00, 495.00, NOW(), NOW()),
(@ord_sim_9, 57, 3, 750.00, 2250.00, NOW(), NOW()),
(@ord_sim_9, 38, 3, 1500.00, 4500.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #51: Dr. Fernando Kalifa (Tesla Model Y Performance 2023 - Placas TSY-990-NL) | Total: $4600.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Dr. Fernando Kalifa', 'Tesla Model Y Performance 2023 - Placas TSY-990-NL', 'Servicio preventivo anual. Cambio de líquido de frenos DOT 5.1 ESP, reemplazo de filtro HEPA grado médico y diagnóstico de celdas SOH.', 'delivered', 4600.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_10 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_10, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_10, 84, 1, 980.00, 980.00, NOW(), NOW()),
(@ord_sim_10, 53, 2, 310.00, 620.00, NOW(), NOW()),
(@ord_sim_10, 137, 1, 1400.00, 1400.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #52: Lic. Regina Zambrano (BMW i4 eDrive40 Gran Coupé 2023 - Placas I4-7721) | Total: $5530.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Lic. Regina Zambrano', 'BMW i4 eDrive40 Gran Coupé 2023 - Placas I4-7721', 'Inspección de alto voltaje y prueba de aislamiento 1000V. Protocolo LOTO, alineación computarizada y pastillas cerámicas.', 'delivered', 5530.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_11 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_11, 136, 1, 950.00, 950.00, NOW(), NOW()),
(@ord_sim_11, 54, 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_sim_11, 49, 1, 2450.00, 2450.00, NOW(), NOW()),
(@ord_sim_11, 50, 1, 480.00, 480.00, NOW(), NOW()),
(@ord_sim_11, 40, 1, 450.00, 450.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #53: Ing. Alejandro Maiz (Toyota RAV4 Hybrid 2021 - Placas RVH-4412) | Total: $4385.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Ing. Alejandro Maiz', 'Toyota RAV4 Hybrid 2021 - Placas RVH-4412', 'Mantenimiento de sistema híbrido dual. Limpieza de conductos de batería, cambio de refrigerante de inversor OAT y afinación motor Atkinson.', 'delivered', 4385.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_12 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_12, 138, 1, 1100.00, 1100.00, NOW(), NOW()),
(@ord_sim_12, 94, 1, 580.00, 580.00, NOW(), NOW()),
(@ord_sim_12, 18, 1, 880.00, 880.00, NOW(), NOW()),
(@ord_sim_12, 5, 1, 780.00, 780.00, NOW(), NOW()),
(@ord_sim_12, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_12, 38, 1, 950.00, 950.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #54: Sra. Beatriz Garza Lagüera (Mercedes-Benz EQC 400 4MATIC 2022 - Placas EQC-302) | Total: $5040.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Sra. Beatriz Garza Lagüera', 'Mercedes-Benz EQC 400 4MATIC 2022 - Placas EQC-302', 'Reemplazo de fluido reductor de diferenciales delantero y trasero (75W EV) y escaneo de telemetría de batería de 80kWh.', 'delivered', 5040.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_13 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_13, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_13, 87, 3, 680.00, 2040.00, NOW(), NOW()),
(@ord_sim_13, 137, 1, 1400.00, 1400.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #55: Don Manuel Sada Rivero (Chevrolet Corvette Stingray 1974 V8 350 - Placas VET-1974) | Total: $4605.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Don Manuel Sada Rivero', 'Chevrolet Corvette Stingray 1974 V8 350 - Placas VET-1974', 'Proyecto clásico: Carburador Holley 4 gargantas ahogado y falla de chispa. Afinación mayor V8, bujías Champion, juntas de punterías y puesta a tiempo con estroboscópica.', 'delivered', 4605.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_14 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_14, 142, 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_sim_14, 139, 1, 950.00, 950.00, NOW(), NOW()),
(@ord_sim_14, 115, 1, 380.00, 380.00, NOW(), NOW()),
(@ord_sim_14, 7, 6, 140.00, 840.00, NOW(), NOW()),
(@ord_sim_14, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_14, 114, 1, 210.00, 210.00, NOW(), NOW()),
(@ord_sim_14, 8, 2, 165.00, 330.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #56: Sr. Rodolfo Elizondo (Volkswagen Golf GTI MK2 1991 - Placas MK2-910) | Total: $4005.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Sr. Rodolfo Elizondo', 'Volkswagen Golf GTI MK2 1991 - Placas MK2-910', 'Clásico de los 90s: Cambio de baleros cónicos delanteros, zapatas traseras con rectificado de tambores y afinación con bujías Bosch platino.', 'delivered', 4005.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_15 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_15, 117, 2, 290.00, 580.00, NOW(), NOW()),
(@ord_sim_15, 102, 1, 360.00, 360.00, NOW(), NOW()),
(@ord_sim_15, 141, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_15, 36, 1, 320.00, 320.00, NOW(), NOW()),
(@ord_sim_15, 37, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_15, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_15, 38, 1, 950.00, 950.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #57: Arq. Bernardo Pozas (Dodge Dart Swinger 1976 6 Cilindros en Línea (Slant-6 225)) | Total: $2515.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Arq. Bernardo Pozas', 'Dodge Dart Swinger 1976 6 Cilindros en Línea (Slant-6 225)', 'Reemplazo de cruceta de flecha cardán, regulador de voltaje electromecánico y condensador de distribuidor.', 'delivered', 2515.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_16 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_16, 113, 2, 280.00, 560.00, NOW(), NOW()),
(@ord_sim_16, 112, 1, 295.00, 295.00, NOW(), NOW()),
(@ord_sim_16, 109, 1, 130.00, 130.00, NOW(), NOW()),
(@ord_sim_16, 140, 1, 680.00, 680.00, NOW(), NOW()),
(@ord_sim_16, 38, 1, 850.00, 850.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #58: Don Antonio Chapa (Datsun Bluebird 510 Sedán 1972 - Placas DAT-510) | Total: $3065.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Don Antonio Chapa', 'Datsun Bluebird 510 Sedán 1972 - Placas DAT-510', 'Restauración completa de frenos y encendido: bomba mecánica de gasolina, platino y condensador, cilindro maestro de rueda y zapatas.', 'delivered', 3065.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_17 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_17, 105, 1, 410.00, 410.00, NOW(), NOW()),
(@ord_sim_17, 42, 1, 145.00, 145.00, NOW(), NOW()),
(@ord_sim_17, 103, 2, 175.00, 350.00, NOW(), NOW()),
(@ord_sim_17, 102, 1, 360.00, 360.00, NOW(), NOW()),
(@ord_sim_17, 141, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_17, 38, 1, 950.00, 950.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #59: Ing. David Martinez (Audi S3 2.0 TFSI Quattro 2020 - Placas AUD-330-NL) | Total: $10175.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Ing. David Martinez', 'Audi S3 2.0 TFSI Quattro 2020 - Placas AUD-330-NL', 'Mantenimiento mayor: Bomba de gasolina mecánica de alta presión (GDI/TSI), bobinas directas, termostato electrónico y cambio de fluido de transmisión DSG.', 'delivered', 10175.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_18 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_18, 129, 1, 4850.00, 4850.00, NOW(), NOW()),
(@ord_sim_18, 132, 1, 1580.00, 1580.00, NOW(), NOW()),
(@ord_sim_18, 58, 1, 2800.00, 2800.00, NOW(), NOW()),
(@ord_sim_18, 37, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_18, 2, 1, 95.00, 95.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #60: Dra. Gabriela Santos (Porsche Macan 2.0T 2019 - Placas PRM-881) | Total: $7220.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Dra. Gabriela Santos', 'Porsche Macan 2.0T 2019 - Placas PRM-881', 'Servicio de frenos Brembo Hi-Carbon y afinación completa con bujías de iridio.', 'delivered', 7220.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_19 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_19, 35, 1, 1890.00, 1890.00, NOW(), NOW()),
(@ord_sim_19, 123, 1, 1850.00, 1850.00, NOW(), NOW()),
(@ord_sim_19, 53, 2, 310.00, 620.00, NOW(), NOW()),
(@ord_sim_19, 57, 1, 750.00, 750.00, NOW(), NOW()),
(@ord_sim_19, 18, 1, 880.00, 880.00, NOW(), NOW()),
(@ord_sim_19, 37, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_19, 51, 1, 380.00, 380.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #61: Lic. Gerardo Clariond (BMW Serie 5 530i 2021 (G30) - Placas CLR-530) | Total: $5830.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Lic. Gerardo Clariond', 'BMW Serie 5 530i 2021 (G30) - Placas CLR-530', 'Afinación ejecutiva BMW TwinPower Turbo, escaneo con software ISTA y lavado ultrasónico de inyectores piezoeléctricos.', 'delivered', 5830.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_20 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_20, 54, 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_sim_20, 48, 6, 420.00, 2520.00, NOW(), NOW()),
(@ord_sim_20, 51, 1, 380.00, 380.00, NOW(), NOW()),
(@ord_sim_20, 56, 1, 850.00, 850.00, NOW(), NOW()),
(@ord_sim_20, 18, 1, 880.00, 880.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #62: Prof. Raúl Espinoza (Nissan Sentra B17 2018 - Placas NST-819) | Total: $2230.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Prof. Raúl Espinoza', 'Nissan Sentra B17 2018 - Placas NST-819', 'Servicio de caja CVT: cambio de fluido NS-3, filtro de aire y lavado de cuerpo de aceleración.', 'delivered', 2230.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_21 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_21, 118, 4, 360.00, 1440.00, NOW(), NOW()),
(@ord_sim_21, 3, 1, 140.00, 140.00, NOW(), NOW()),
(@ord_sim_21, 38, 1, 650.00, 650.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #63: C.P. Mónica Tamez (Honda CR-V 1.5 Turbo 2020 - Placas CRV-512) | Total: $4425.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('C.P. Mónica Tamez', 'Honda CR-V 1.5 Turbo 2020 - Placas CRV-512', 'Reemplazo de soporte de motor hidráulico derecho, balatas cerámicas TRW y cambio de aceite sintético.', 'delivered', 4425.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_22 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_22, 127, 1, 1420.00, 1420.00, NOW(), NOW()),
(@ord_sim_22, 124, 1, 1280.00, 1280.00, NOW(), NOW()),
(@ord_sim_22, 5, 1, 780.00, 780.00, NOW(), NOW()),
(@ord_sim_22, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_22, 38, 1, 850.00, 850.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #64: Ing. Mauricio Fernández (Toyota Hilux 2.7L 2019 - Placas HLX-994) | Total: $6550.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Ing. Mauricio Fernández', 'Toyota Hilux 2.7L 2019 - Placas HLX-994', 'Kit de clutch LuK nuevo, rectificado de volante motriz, cambio de retén de cigüeñal y afinación.', 'delivered', 6550.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_23 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_23, 126, 1, 3650.00, 3650.00, NOW(), NOW()),
(@ord_sim_23, 41, 1, 380.00, 380.00, NOW(), NOW()),
(@ord_sim_23, 1, 5, 165.00, 825.00, NOW(), NOW()),
(@ord_sim_23, 2, 1, 95.00, 95.00, NOW(), NOW()),
(@ord_sim_23, 38, 1, 1600.00, 1600.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #65: Srita. Paulina Rubio Morales (Kia Rio 1.6L 2021 - Placas KIO-312) | Total: $2350.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Srita. Paulina Rubio Morales', 'Kia Rio 1.6L 2021 - Placas KIO-312', 'Maza delantera con sensor ABS cambiada por zumbido en rodamiento, alineación y balanceo.', 'delivered', 2350.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_24 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_24, 128, 1, 1350.00, 1350.00, NOW(), NOW()),
(@ord_sim_24, 40, 1, 450.00, 450.00, NOW(), NOW()),
(@ord_sim_24, 38, 1, 550.00, 550.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #66: Sr. Ernesto Cordero (Chevrolet Sonic 1.6L 2017 - Placas SON-882) | Total: $2445.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Sr. Ernesto Cordero', 'Chevrolet Sonic 1.6L 2017 - Placas SON-882', 'Solenoide VVT de admisión cambiado por código P0011, depósito de anticongelante y termostato.', 'delivered', 2445.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_25 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_25, 120, 1, 780.00, 780.00, NOW(), NOW()),
(@ord_sim_25, 121, 1, 480.00, 480.00, NOW(), NOW()),
(@ord_sim_25, 24, 1, 195.00, 195.00, NOW(), NOW()),
(@ord_sim_25, 9, 1, 240.00, 240.00, NOW(), NOW()),
(@ord_sim_25, 38, 1, 750.00, 750.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #67: Dra. Patricia Lankenau (Volvo XC40 Recharge Pure Electric 2023 - Placas VOL-402) | Total: $3460.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Dra. Patricia Lankenau', 'Volvo XC40 Recharge Pure Electric 2023 - Placas VOL-402', 'Inspección de 30,000 km vehículo 100% eléctrico. Purgado de frenos i-Booster, filtro de cabina y revisión de batería 78 kWh.', 'delivered', 3460.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_26 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_26, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_26, 137, 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_sim_26, 96, 1, 460.00, 460.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #68: Lic. Carlos Slim Domit (Club Clásicos) (Ford Mustang Hardtop 1969 V8 302 - Placas MUS-1969) | Total: $5215.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Lic. Carlos Slim Domit (Club Clásicos)', 'Ford Mustang Hardtop 1969 V8 302 - Placas MUS-1969', 'Afinación premium de colección: juego completo de juntas Fel-Pro, carburador ajustado, bujías Champion y aceite de alto zinc.', 'delivered', 5215.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_27 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_27, 142, 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_sim_27, 139, 1, 950.00, 950.00, NOW(), NOW()),
(@ord_sim_27, 108, 1, 1150.00, 1150.00, NOW(), NOW()),
(@ord_sim_27, 115, 1, 380.00, 380.00, NOW(), NOW()),
(@ord_sim_27, 7, 6, 140.00, 840.00, NOW(), NOW()),
(@ord_sim_27, 2, 1, 95.00, 95.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #69: Flotilla Taxis Aeropuerto Monterrey (Contrato Mantenimiento 5x Toyota Corolla 1.8L 2021) | Total: $16625.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Flotilla Taxis Aeropuerto Monterrey', 'Contrato Mantenimiento 5x Toyota Corolla 1.8L 2021', 'Mantenimiento preventivo por contrato: cambio de aceite 5W-30 sintético, bujías platino y balatas cerámicas.', 'delivered', 16625.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_28 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_28, 5, 5, 780.00, 3900.00, NOW(), NOW()),
(@ord_sim_28, 2, 5, 95.00, 475.00, NOW(), NOW()),
(@ord_sim_28, 36, 5, 320.00, 1600.00, NOW(), NOW()),
(@ord_sim_28, 124, 5, 1280.00, 6400.00, NOW(), NOW()),
(@ord_sim_28, 38, 5, 850.00, 4250.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #70: Ing. Eugenio Garza T. (Porsche Taycan 4S 2022 - Placas TAY-400) | Total: $5310.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Ing. Eugenio Garza T.', 'Porsche Taycan 4S 2022 - Placas TAY-400', 'Servicio de bahía eléctrica: Protocolo de alta tensión LOTO, telemetría de batería Performance Plus 93.4 kWh y fluido reductor.', 'delivered', 5310.00, '2026-09-21 18:30:21', '2026-09-21 18:30:21');
SET @ord_sim_29 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_29, 136, 1, 950.00, 950.00, NOW(), NOW()),
(@ord_sim_29, 134, 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_sim_29, 87, 2, 680.00, 1360.00, NOW(), NOW()),
(@ord_sim_29, 137, 1, 1400.00, 1400.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #71: DHL Supply Chain México S.A. de C.V. (Contrato Bimestral Flotilla 10x Ford Transit 2022 EcoBlue Diesel) | Total: $112950.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('DHL Supply Chain México S.A. de C.V.', 'Contrato Bimestral Flotilla 10x Ford Transit 2022 EcoBlue Diesel', 'Servicio mayor bimestral de flota de reparto metropolitana: sustitución de filtros diesel common rail, cambio de aceite sintético, balatas cerámicas y purga de sistema de frenos ABS.', 'delivered', 112950.00, '2026-09-21 18:30:41', '2026-09-21 18:30:41');
SET @ord_sim_30 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_30, 131, 10, 690.00, 6900.00, NOW(), NOW()),
(@ord_sim_30, 5, 10, 780.00, 7800.00, NOW(), NOW()),
(@ord_sim_30, 2, 10, 95.00, 950.00, NOW(), NOW()),
(@ord_sim_30, 14, 10, 480.00, 4800.00, NOW(), NOW()),
(@ord_sim_30, 57, 10, 750.00, 7500.00, NOW(), NOW()),
(@ord_sim_30, 38, 10, 8500.00, 85000.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #72: Club de Autos Clásicos y de Colección Monterrey A.C. (Restauración Integral: Ford Mustang Mach 1 1971 & Corvette Stingray 1974) | Total: $90420.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Club de Autos Clásicos y de Colección Monterrey A.C.', 'Restauración Integral: Ford Mustang Mach 1 1971 & Corvette Stingray 1974', 'Reconstrucción de motores clásicos V8 351 Cleveland y V8 350. Carburación fina, empacado completo con juntas Fel-Pro, suspensión delantera, rectificación de tambores y encendido con lámpara estroboscópica.', 'delivered', 90420.00, '2026-09-21 18:30:41', '2026-09-21 18:30:41');
SET @ord_sim_31 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_31, 142, 2, 1800.00, 3600.00, NOW(), NOW()),
(@ord_sim_31, 139, 2, 950.00, 1900.00, NOW(), NOW()),
(@ord_sim_31, 108, 2, 1150.00, 2300.00, NOW(), NOW()),
(@ord_sim_31, 141, 2, 850.00, 1700.00, NOW(), NOW()),
(@ord_sim_31, 32, 4, 980.00, 3920.00, NOW(), NOW()),
(@ord_sim_31, 38, 2, 38500.00, 77000.00, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- ORDEN #73: Financiera y Arrendadora Global Mobility S.A. (Póliza Mantenimiento Flotilla Ejecutiva 6x Tesla Model 3 & BYD Seal EV) | Total: $78540.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at) VALUES
('Financiera y Arrendadora Global Mobility S.A.', 'Póliza Mantenimiento Flotilla Ejecutiva 6x Tesla Model 3 & BYD Seal EV', 'Mantenimiento preventivo anual de flota ejecutiva eléctrica: inspección y telemetría de baterías de alta tensión (SOH 99.1%), mantenimiento de frenado regenerativo electromecánico, filtros HEPA y rotación.', 'delivered', 78540.00, '2026-09-21 18:30:41', '2026-09-21 18:30:41');
SET @ord_sim_32 = LAST_INSERT_ID();
INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_sim_32, 134, 6, 1600.00, 9600.00, NOW(), NOW()),
(@ord_sim_32, 137, 6, 1400.00, 8400.00, NOW(), NOW()),
(@ord_sim_32, 84, 6, 980.00, 5880.00, NOW(), NOW()),
(@ord_sim_32, 96, 6, 460.00, 2760.00, NOW(), NOW()),
(@ord_sim_32, 40, 6, 450.00, 2700.00, NOW(), NOW()),
(@ord_sim_32, 38, 6, 8200.00, 49200.00, NOW(), NOW());

COMMIT;

-- ==============================================================================
-- VERIFICACIÓN DE SALUD FINANCIERA EN VIVO:
-- SELECT SUM(total) AS Ingresos_Totales FROM orders;       -- Retorna: $516,338.00
-- SELECT SUM(amount) AS Egresos_Totales FROM expenses;    -- Retorna: $376,140.00
-- SELECT (SELECT SUM(total) FROM orders) - (SELECT SUM(amount) FROM expenses) AS Utilidad_Neta; -- +$140,198.00
-- ==============================================================================