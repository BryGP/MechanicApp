-- ==============================================================================
-- SIMULACIÓN DE OPERACIÓN: MES 3 DEL TALLER MECÁNICO
-- CIERRE DE ÓRDENES PENDIENTES + NUEVAS ÓRDENES (EVs, CLÁSICOS, FLOTILLA) + EGRESOS M3
-- ==============================================================================
-- Base de Datos: mechanic_app (MySQL)
-- Objetivos:
-- 1. Entrega formal de las órdenes que estaban abiertas / en proceso / terminadas.
-- 2. Apertura de 8 nuevas órdenes representativas del Mes 3 (Rivian R1T, Corvette 74,
--    Mercado Libre Flotilla EV, Datsun 240Z, Panamera E-Hybrid, Combi Westfalia 78,
--    Ioniq 5 800V, BMW M340i G20).
-- 3. Asentamiento contable de operación del Mes 3 (renta, nóminas, energía CFE).
-- 4. Deducción atómica de stock de refacciones utilizadas.
-- 5. IDEMPOTENCIA TOTAL: Si se ejecuta más de una vez, previene duplicados.
-- ==============================================================================

START TRANSACTION;

-- ==============================================================================
-- PASO 1: ENTREGA DE TODAS LAS ÓRDENES PREVIAS PENDIENTES
-- ==============================================================================
-- Las órdenes con estatus 'open', 'in_progress' o 'done' pasan a 'delivered'
UPDATE orders 
SET status = 'delivered', 
    updated_at = NOW() 
WHERE status IN ('open', 'in_progress', 'done');


-- ==============================================================================
-- PASO 2: LIMPIEZA PREVENTIVA DE REGISTROS DEL MES 3 (PREVENCIÓN DE DUPLICADOS)
-- ==============================================================================
-- Si el script ya se había corrido antes, se limpian las partidas del Mes 3:
DELETE FROM order_items WHERE order_id IN (
    SELECT id FROM orders WHERE customer_name IN (
        'Ing. Patricio Zambrano Treviño',
        'Dr. Mauricio Fernández Garza',
        'Mercado Libre Logística Urbana S.A. de C.V.',
        'Arq. Rodrigo de la Peña',
        'Lic. Fernando Senderos',
        'Sra. Paulina Rubio Morales (Westfalia)',
        'Ing. David Martinez (Tech Mobility)',
        'Lic. Carlos Slim Domit (M340i)'
    )
);

DELETE FROM orders WHERE customer_name IN (
    'Ing. Patricio Zambrano Treviño',
    'Dr. Mauricio Fernández Garza',
    'Mercado Libre Logística Urbana S.A. de C.V.',
    'Arq. Rodrigo de la Peña',
    'Lic. Fernando Senderos',
    'Sra. Paulina Rubio Morales (Westfalia)',
    'Ing. David Martinez (Tech Mobility)',
    'Lic. Carlos Slim Domit (M340i)'
);

DELETE FROM expenses WHERE reference IN ('RENTA-TALLER-M3', 'NOM-QUINC-M3', 'CFE-BIM-M3');


-- ==============================================================================
-- PASO 3: ASENTAMIENTO CONTABLE OPERATIVO DEL MES 3
-- ==============================================================================
INSERT INTO expenses (concept, category, amount, payment_method, reference, expense_date, created_at, updated_at) VALUES
('Renta de Inmueble y Bahías de Taller Mecánico - Mes 3', 'renta', 18000.00, 'transferencia', 'RENTA-TALLER-M3', DATE_SUB(CURDATE(), INTERVAL 2 DAY), NOW(), NOW()),
('Nómina Quincenal Operativa de Mecánicos y Especialistas EV/Clásicos - Mes 3', 'nomina', 29000.00, 'transferencia', 'NOM-QUINC-M3', CURDATE(), NOW(), NOW()),
('Consumo Eléctrico CFE Bahía de Carga Rápida EV y Compresores Trifásicos - Mes 3', 'servicios', 5200.00, 'transferencia', 'CFE-BIM-M3', DATE_SUB(CURDATE(), INTERVAL 4 DAY), NOW(), NOW());


-- ==============================================================================
-- PASO 4: APERTURA DE 8 NUEVAS ÓRDENES DEL MES 3
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- ORDEN 1: Rivian R1T Launch Edition 2023 Quad-Motor AWD (IN_PROGRESS)
-- Cliente: Ing. Patricio Zambrano Treviño | Total: $8,530.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Ing. Patricio Zambrano Treviño', 'Rivian R1T Launch Edition 2023 - Placas RVN-2023-NL', 'Revisión preventiva de 40,000 km y prueba de aislamiento dieléctrico Fluke a paquete cuádruple de motores (Quad-Motor AWD). Purgado de refrigerante dieléctrico de baja conductividad y purga electrónica de frenos con líquido DOT 5.1 ESP.', 'in_progress', 8530.00, DATE_SUB(NOW(), INTERVAL 3 DAY), NOW());
SET @ord_m3_1 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'SERV-REFR-DIEL-EV' LIMIT 1), 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'REFR-DIEL-EV-4L' LIMIT 1), 2, 1450.00, 2900.00, NOW(), NOW()),
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 2, 310.00, 620.00, NOW(), NOW()),
(@ord_m3_1, (SELECT id FROM products WHERE sku = 'LIMP-PAR-AERO-EV' LIMIT 1), 1, 460.00, 460.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'REFR-DIEL-EV-4L';
UPDATE products SET stock = stock - 2 WHERE sku = 'LIQ-FREN-DOT51-ESP';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIMP-PAR-AERO-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 2: Chevrolet Corvette Stingray C3 1974 V8 350 Small Block (IN_PROGRESS)
-- Cliente: Dr. Mauricio Fernández Garza | Total: $4,955.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Dr. Mauricio Fernández Garza', 'Chevrolet Corvette Stingray C3 1974 V8 350 - Placas VET-1974-MX', 'Puesta a punto de motor V8 350 para exhibición: desmonte y reconstrucción de carburador, cambio de aceite mineral 20W-50 con filtro de aceite y gasolina lavable, bujías Champion calientes y calibración estroboscópica a 8° BTDC.', 'in_progress', 4955.00, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());
SET @ord_m3_2 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'SERV-AFIN-V8-CLASICO' LIMIT 1), 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'SERV-CARB-MULTI-70' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 6, 140.00, 840.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 1, 95.00, 95.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'FILT-GAS-VIDRIO-70' LIMIT 1), 1, 210.00, 210.00, NOW(), NOW()),
(@ord_m3_2, (SELECT id FROM products WHERE sku = 'BUJIA-CHAMPION-V8' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW());

UPDATE products SET stock = stock - 6 WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-GAS-VIDRIO-70';
UPDATE products SET stock = stock - 1 WHERE sku = 'BUJIA-CHAMPION-V8';


-- ------------------------------------------------------------------------------
-- ORDEN 3: Flotilla Última Milla 6x Renault Kangoo E-Tech EV 2023 (OPEN)
-- Cliente: Mercado Libre Logística Urbana S.A. de C.V. | Total: $27,960.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Mercado Libre Logística Urbana S.A. de C.V.', 'Flotilla Última Milla 6x Renault Kangoo E-Tech EV 2023', 'Contrato de flota de reparto eléctrico: inspección telemática de salud de paquete de batería (SOH al 98.9%), mantenimiento preventivo de frenos regenerativos i-Booster, plumas limpiaparabrisas silenciosas y alineación/balanceo.', 'open', 27960.00, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW());
SET @ord_m3_3 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_3, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 6, 1600.00, 9600.00, NOW(), NOW()),
(@ord_m3_3, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 6, 1400.00, 8400.00, NOW(), NOW()),
(@ord_m3_3, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 6, 550.00, 3300.00, NOW(), NOW()),
(@ord_m3_3, (SELECT id FROM products WHERE sku = 'LIMP-PAR-AERO-EV' LIMIT 1), 6, 460.00, 2760.00, NOW(), NOW()),
(@ord_m3_3, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 6, 650.00, 3900.00, NOW(), NOW());

UPDATE products SET stock = stock - 6 WHERE sku = 'LIMP-PAR-AERO-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 4: Datsun 240Z Fairlady 1973 2.4L L24 6 Cilindros (DONE)
-- Cliente: Arq. Rodrigo de la Peña | Total: $3,260.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Arq. Rodrigo de la Peña', 'Datsun 240Z Fairlady 1973 2.4L L24 - Placas DTZ-240-Z', 'Restauración del tren de encendido y frenado: reemplazo de bomba mecánica de gasolina Datsun, tapa y rotor de distribuidor de encendido, rectificación de tambores con juego nuevo de zapatas y sincronización estroboscópica.', 'done', 3260.00, DATE_SUB(NOW(), INTERVAL 4 DAY), NOW());
SET @ord_m3_4 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'SERV-TAMBORES-ZAPATAS' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'ZAP-FREN-TAMBOR-70' LIMIT 1), 1, 360.00, 360.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'BOMB-GAS-MEC-DATSUN' LIMIT 1), 1, 410.00, 410.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'TAPA-DIST-DATSUN-710' LIMIT 1), 1, 185.00, 185.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'ROTOR-DIST-BOSCH-70' LIMIT 1), 1, 125.00, 125.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m3_4, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'ZAP-FREN-TAMBOR-70';
UPDATE products SET stock = stock - 1 WHERE sku = 'BOMB-GAS-MEC-DATSUN';
UPDATE products SET stock = stock - 1 WHERE sku = 'TAPA-DIST-DATSUN-710';
UPDATE products SET stock = stock - 1 WHERE sku = 'ROTOR-DIST-BOSCH-70';


-- ------------------------------------------------------------------------------
-- ORDEN 5: Porsche Panamera 4 E-Hybrid 2022 (OPEN)
-- Cliente: Lic. Fernando Senderos | Total: $7,880.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Lic. Fernando Senderos', 'Porsche Panamera 4 E-Hybrid 2022 - Placas PNH-400-P', 'Servicio mayor de 50,000 km: Mantenimiento a caja de doble embrague PDK/DSG, purga presurizada electrónica de frenos con líquido DOT 5.1 ESP, refrigerante OAT para inversor y escaneo con protocolo de seguridad LOTO.', 'open', 7880.00, NOW(), NOW());
SET @ord_m3_5 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'SERV-TRANS-DSG-STEP' LIMIT 1), 1, 2800.00, 2800.00, NOW(), NOW()),
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 2, 310.00, 620.00, NOW(), NOW()),
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'ANT-INVERSOR-OAT' LIMIT 1), 2, 580.00, 1160.00, NOW(), NOW()),
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'SERV-LOTO-DESCON-EV' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m3_5, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'LIQ-FREN-DOT51-ESP';
UPDATE products SET stock = stock - 2 WHERE sku = 'ANT-INVERSOR-OAT';


-- ------------------------------------------------------------------------------
-- ORDEN 6: VW Combi Westfalia Camper 1978 1600cc (IN_PROGRESS)
-- Cliente: Sra. Paulina Rubio Morales (Westfalia) | Total: $4,855.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Sra. Paulina Rubio Morales (Westfalia)', 'VW Combi Westfalia Camper 1978 1600cc - Placas WST-1978', 'Preparación mecánica para viaje de ruta clásica: carburador nuevo Bocar 34-PICT con calibración fina de flotador, cilindros de rueda trasera de frenos de tambor, cambio de aceite 20W-50 y filtro.', 'in_progress', 4855.00, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());
SET @ord_m3_6 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'CARB-BOCAR-1G-34PICT' LIMIT 1), 1, 2250.00, 2250.00, NOW(), NOW()),
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'SERV-CARB-MULTI-70' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'CIL-RUEDA-TRAS-VCH' LIMIT 1), 2, 175.00, 350.00, NOW(), NOW()),
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 4, 140.00, 560.00, NOW(), NOW()),
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 1, 95.00, 95.00, NOW(), NOW()),
(@ord_m3_6, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'CARB-BOCAR-1G-34PICT';
UPDATE products SET stock = stock - 2 WHERE sku = 'CIL-RUEDA-TRAS-VCH';
UPDATE products SET stock = stock - 4 WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL';


-- ------------------------------------------------------------------------------
-- ORDEN 7: Hyundai Ioniq 5 Limited EV 800V 2023 (OPEN)
-- Cliente: Ing. David Martinez (Tech Mobility) | Total: $4,570.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Ing. David Martinez (Tech Mobility)', 'Hyundai Ioniq 5 Limited EV 800V 2023 - Placas IQ5-800-V', 'Inspección programada de arquitectura eléctrica de 800V: telemetría telemática de celdas de batería de 77.4 kWh, calibración de sensores térmicos de módulo BMS, mantenimiento de frenado regenerativo y filtro de cabina.', 'open', 4570.00, NOW(), NOW());
SET @ord_m3_7 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_7, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m3_7, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m3_7, (SELECT id FROM products WHERE sku = 'SENS-TEMP-BMS-EV' LIMIT 1), 2, 390.00, 780.00, NOW(), NOW()),
(@ord_m3_7, (SELECT id FROM products WHERE sku = 'FILT-AIR' LIMIT 1), 1, 140.00, 140.00, NOW(), NOW()),
(@ord_m3_7, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'SENS-TEMP-BMS-EV';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-AIR';


-- ------------------------------------------------------------------------------
-- ORDEN 8: BMW M340i xDrive G20 2021 (DONE)
-- Cliente: Lic. Carlos Slim Domit (M340i) | Total: $5,840.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES ('Lic. Carlos Slim Domit (M340i)', 'BMW M340i xDrive G20 2021 - Placas G20-340-M', 'Servicio de frenos deportivos y diagnóstico telemático: escaneo con software ISTA/INPA, sustitución de pastillas cerámicas delanteras con sensor de desgaste y purga electrónica con fluido DOT 5.1 ESP.', 'done', 5840.00, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW());
SET @ord_m3_8 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'SERV-DIAG-BMW-ISTA' LIMIT 1), 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'FREN-BMW-CER-G20' LIMIT 1), 1, 2450.00, 2450.00, NOW(), NOW()),
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'SENS-DESG-BMW-FREN' LIMIT 1), 1, 480.00, 480.00, NOW(), NOW()),
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 1, 310.00, 310.00, NOW(), NOW()),
(@ord_m3_8, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'FREN-BMW-CER-G20';
UPDATE products SET stock = stock - 1 WHERE sku = 'SENS-DESG-BMW-FREN';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIQ-FREN-DOT51-ESP';

COMMIT;

-- ==============================================================================
-- VERIFICACIÓN INFORMATIVA DEL MES 3:
-- SELECT status, COUNT(*) AS total_ordenes FROM orders GROUP BY status;
-- SELECT SUM(total) AS Ingresos_Totales FROM orders;
-- SELECT SUM(amount) AS Gastos_Totales FROM expenses;
-- SELECT (SELECT SUM(total) FROM orders) - (SELECT SUM(amount) FROM expenses) AS Superavit_Acumulado;
-- ==============================================================================
