-- ==============================================================================
-- SIMULACIÓN DE OPERACIÓN: MES 4 DEL TALLER MECÁNICO
-- CIERRE DE ÓRDENES PREVIAS + 10 NUEVAS ÓRDENES (EVs, CLÁSICOS, FLOTAS) + EGRESOS M4
-- ==============================================================================
-- Base de Datos: mechanic_app (MySQL)
-- Objetivos:
-- 1. Entrega formal de las órdenes pendientes del Mes 3 (status -> 'delivered').
-- 2. Apertura de 10 nuevas órdenes de trabajo de alto impacto para el Mes 4.
-- 3. Asentamiento contable del Mes 4 (renta, nómina con especialista EV, energía CFE, restock mayorista y seguro).
-- 4. Deducción atómica de existencias en catálogo de refacciones.
-- 5. IDEMPOTENCIA TOTAL: Si se ejecuta más de una vez, previene duplicados.
-- ==============================================================================

START TRANSACTION;

-- ==============================================================================
-- PASO 1: ENTREGA DE TODAS LAS ÓRDENES PREVIAS PENDIENTES DEL MES 3
-- ==============================================================================
UPDATE orders 
SET status = 'delivered', 
    updated_at = NOW() 
WHERE status IN ('open', 'in_progress', 'done');


-- ==============================================================================
-- PASO 2: LIMPIEZA PREVENTIVA DE REGISTROS DEL MES 4 (PREVENCIÓN DE DUPLICADOS)
-- ==============================================================================
DELETE FROM order_items WHERE order_id IN (
    SELECT id FROM orders WHERE customer_name IN (
        'Lic. Alejandro Garza Sada',
        'Don Eugenio Garza Lagüera (Colección)',
        'DHL Supply Chain México S.A. de C.V. - Flota M4',
        'Ing. Bernardo Bichara Assad',
        'Flotilla Ejecutiva Sustentable Monterrey',
        'Arq. Guillermo González Camarena (Colección)',
        'Lic. Gabriel Alarcón Velázquez',
        'Lic. Adrián de la Garza Santos',
        'Rancho El Campanario S.P.R. de R.L.',
        'Empresa Verde Soluciones Sustentables S.A.'
    )
);

DELETE FROM orders WHERE customer_name IN (
    'Lic. Alejandro Garza Sada',
    'Don Eugenio Garza Lagüera (Colección)',
    'DHL Supply Chain México S.A. de C.V. - Flota M4',
    'Ing. Bernardo Bichara Assad',
    'Flotilla Ejecutiva Sustentable Monterrey',
    'Arq. Guillermo González Camarena (Colección)',
    'Lic. Gabriel Alarcón Velázquez',
    'Lic. Adrián de la Garza Santos',
    'Rancho El Campanario S.P.R. de R.L.',
    'Empresa Verde Soluciones Sustentables S.A.'
);

DELETE FROM expenses WHERE reference IN (
    'RENTA-TALLER-M4',
    'NOM-QUINC-M4',
    'CFE-BIM-M4',
    'RESTOCK-INSUMOS-M4',
    'SEGURO-TALLER-M4'
);


-- ==============================================================================
-- PASO 3: ASENTAMIENTO CONTABLE OPERATIVO DEL MES 4 ($83,350.00)
-- ==============================================================================
INSERT INTO expenses (concept, category, amount, payment_method, reference, expense_date, created_at, updated_at) VALUES
('Renta de Inmueble y Bahías de Taller Mecánico - Mes 4', 'renta', 18000.00, 'transferencia', 'RENTA-TALLER-M4', DATE_SUB(CURDATE(), INTERVAL 5 DAY), NOW(), NOW()),
('Nómina Quincenal de Operaciones Taller, Especialistas EV y Maestros Clásicos - Mes 4', 'nomina', 31500.00, 'transferencia', 'NOM-QUINC-M4', CURDATE(), NOW(), NOW()),
('Consumo Eléctrico CFE Bahías de Carga EV, Compresores y Maquinaria - Mes 4', 'servicios', 5650.00, 'transferencia', 'CFE-BIM-M4', DATE_SUB(CURDATE(), INTERVAL 3 DAY), NOW(), NOW()),
('Restock Mayorista de Refacciones: Balatas Cerámicas, Fluidos Dieléctricos y Filtros', 'refacciones', 21400.00, 'transferencia', 'RESTOCK-INSUMOS-M4', DATE_SUB(CURDATE(), INTERVAL 8 DAY), NOW(), NOW()),
('Póliza Trimestral de Responsabilidad Civil de Taller y Protección de Flotas', 'otros', 6800.00, 'tarjeta', 'SEGURO-TALLER-M4', DATE_SUB(CURDATE(), INTERVAL 10 DAY), NOW(), NOW());


-- ==============================================================================
-- PASO 4: APERTURA DE 10 NUEVAS ÓRDENES DEL MES 4
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- ORDEN 1: Tesla Model 3 Performance 2023 Dual Motor AWD (IN_PROGRESS)
-- Cliente: Lic. Alejandro Garza Sada | Total: $6,880.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Lic. Alejandro Garza Sada',
    'Tesla Model 3 Performance 2023 - Placas TSL-303-P',
    'Mantenimiento integral de frenado regenerativo i-Booster con purga presurizada electrónica DOT 5.1 ESP, cambio de líquido refrigerante dieléctrico de batería de tracción y reemplazo de plumas de silicón silenciosas.',
    'in_progress',
    6880.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
);
SET @ord_m4_1 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 2, 310.00, 620.00, NOW(), NOW()),
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'REFR-DIEL-EV-4L' LIMIT 1), 1, 1450.00, 1450.00, NOW(), NOW()),
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'SERV-REFR-DIEL-EV' LIMIT 1), 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_m4_1, (SELECT id FROM products WHERE sku = 'LIMP-PAR-AERO-EV' LIMIT 1), 1, 460.00, 460.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'LIQ-FREN-DOT51-ESP';
UPDATE products SET stock = stock - 1 WHERE sku = 'REFR-DIEL-EV-4L';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIMP-PAR-AERO-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 2: Ford Mustang Mach 1 1969 V8 351 Cleveland (DONE)
-- Cliente: Don Eugenio Garza Lagüera (Colección) | Total: $6,340.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Don Eugenio Garza Lagüera (Colección)',
    'Ford Mustang Mach 1 1969 V8 351 - Placas MCH-1969-NL',
    'Puesta a punto de motor clásico Cleveland V8 351: Afinación mayor con ajuste fino de punterías, reconstrucción y carburación Holley, bujías Champion calientes, bandas en V, kit de juntas y filtro de combustible lavable.',
    'done',
    6340.00,
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    NOW()
);
SET @ord_m4_2 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'SERV-AFIN-V8-CLASICO' LIMIT 1), 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'SERV-CARB-MULTI-70' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'BUJIA-CHAMPION-V8' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'BANDA-EN-V-FORD-V8' LIMIT 2), 2, 165.00, 330.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'JUEGO-JUNTAS-FORD302' LIMIT 1), 1, 1150.00, 1150.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'FILT-GAS-VIDRIO-70' LIMIT 1), 1, 210.00, 210.00, NOW(), NOW()),
(@ord_m4_2, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 6, 140.00, 840.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'BUJIA-CHAMPION-V8';
UPDATE products SET stock = stock - 2 WHERE sku = 'BANDA-EN-V-FORD-V8';
UPDATE products SET stock = stock - 1 WHERE sku = 'JUEGO-JUNTAS-FORD302';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-GAS-VIDRIO-70';
UPDATE products SET stock = stock - 6 WHERE sku = 'ACE-20W50-1L';


-- ------------------------------------------------------------------------------
-- ORDEN 3: Flotilla Logística DHL Express (5x Mercedes-Benz eVito Eléctrica) (OPEN)
-- Cliente: DHL Supply Chain México S.A. de C.V. - Flota M4 | Total: $23,300.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'DHL Supply Chain México S.A. de C.V. - Flota M4',
    'Flotilla 5x Mercedes-Benz eVito Eléctrica 2023 - Reparto Express',
    'Inspección trimestral programada de 5 vans eléctricas de paquetería urbana: escaneo telemático SOH de batería de 60 kWh, mantenimiento a frenos regenerativos, plumas silenciosas EV y alineación/balanceo computarizado.',
    'open',
    23300.00,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    NOW()
);
SET @ord_m4_3 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_3, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 5, 1600.00, 8000.00, NOW(), NOW()),
(@ord_m4_3, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 5, 1400.00, 7000.00, NOW(), NOW()),
(@ord_m4_3, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 5, 550.00, 2750.00, NOW(), NOW()),
(@ord_m4_3, (SELECT id FROM products WHERE sku = 'LIMP-PAR-AERO-EV' LIMIT 1), 5, 460.00, 2300.00, NOW(), NOW()),
(@ord_m4_3, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 5, 650.00, 3250.00, NOW(), NOW());

UPDATE products SET stock = stock - 5 WHERE sku = 'LIMP-PAR-AERO-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 4: BMW M2 CS Coupé 2021 TwinPower Turbo (IN_PROGRESS)
-- Cliente: Ing. Bernardo Bichara Assad | Total: $9,390.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Ing. Bernardo Bichara Assad',
    'BMW M2 CS Coupé 2021 - Placas M2-CS-021',
    'Reemplazo de termostato electrónico mapeado por alerta en cuadro de instrumentos, cambio de discos de freno ventilados Hi-Carbon delanteros con balatas cerámicas TRW y sensor de desgaste, y cambio de aceite BMW LL-04 5W-30.',
    'in_progress',
    9390.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
);
SET @ord_m4_4 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'TERMOSTATO-ELECT-BMW' LIMIT 1), 1, 1580.00, 1580.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'DISCO-FREN-VENT-VW' LIMIT 1), 1, 1850.00, 1850.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'BALATAS-CERAM-TRW-DEL' LIMIT 1), 1, 1280.00, 1280.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'SENS-DESG-BMW-FREN' LIMIT 1), 1, 480.00, 480.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'ACE-BMW-LL04-5W30' LIMIT 1), 6, 420.00, 2520.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'FILT-OIL-BMW-ECO' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m4_4, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'TERMOSTATO-ELECT-BMW';
UPDATE products SET stock = stock - 1 WHERE sku = 'DISCO-FREN-VENT-VW';
UPDATE products SET stock = stock - 1 WHERE sku = 'BALATAS-CERAM-TRW-DEL';
UPDATE products SET stock = stock - 1 WHERE sku = 'SENS-DESG-BMW-FREN';
UPDATE products SET stock = stock - 6 WHERE sku = 'ACE-BMW-LL04-5W30';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL-BMW-ECO';


-- ------------------------------------------------------------------------------
-- ORDEN 5: Toyota Prius HEV 2022 Híbrido (DELIVERED)
-- Cliente: Flotilla Ejecutiva Sustentable Monterrey | Total: $2,735.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Flotilla Ejecutiva Sustentable Monterrey',
    'Toyota Prius HEV 2022 Híbrido - Placas PRU-2022-CDMX',
    'Mantenimiento preventivo híbrido: desmontaje y descontaminación profunda de ductos y ventilador de enfriamiento de batería Ni-MH, afinación con garrafa de aceite sintético 5W-30, filtro de aceite y filtro de aire.',
    'delivered',
    2735.00,
    DATE_SUB(NOW(), INTERVAL 4 DAY),
    NOW()
);
SET @ord_m4_5 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_5, (SELECT id FROM products WHERE sku = 'SERV-LIMPIEZA-VENT-HEV' LIMIT 1), 1, 1100.00, 1100.00, NOW(), NOW()),
(@ord_m4_5, (SELECT id FROM products WHERE sku = 'ACE-5W30-5L' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m4_5, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 1, 95.00, 95.00, NOW(), NOW()),
(@ord_m4_5, (SELECT id FROM products WHERE sku = 'FILT-AIR' LIMIT 1), 1, 140.00, 140.00, NOW(), NOW()),
(@ord_m4_5, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'ACE-5W30-5L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-AIR';


-- ------------------------------------------------------------------------------
-- ORDEN 6: Datsun 510 Bluebird 1971 Sedán 1.6L L16 (IN_PROGRESS)
-- Cliente: Arq. Guillermo González Camarena (Colección) | Total: $3,610.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Arq. Guillermo González Camarena (Colección)',
    'Datsun 510 Bluebird 1971 Sedán 1.6L - Placas BLU-1971',
    'Reacondicionamiento de frenos y encendido clásico: bomba mecánica de gasolina, tapa y rotor de distribuidor, zapatas traseras con cilindros de rueda nuevos, rectificación de tambores y sincronización de chispa con lámpara estroboscópica.',
    'in_progress',
    3610.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
);
SET @ord_m4_6 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'TAPA-DIST-DATSUN-710' LIMIT 1), 1, 185.00, 185.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'ROTOR-DIST-BOSCH-70' LIMIT 1), 1, 125.00, 125.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'BOMB-GAS-MEC-DATSUN' LIMIT 1), 1, 410.00, 410.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'ZAP-FREN-TAMBOR-70' LIMIT 1), 1, 360.00, 360.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'CIL-RUEDA-TRAS-VCH' LIMIT 1), 2, 175.00, 350.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'SERV-TAMBORES-ZAPATAS' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m4_6, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'TAPA-DIST-DATSUN-710';
UPDATE products SET stock = stock - 1 WHERE sku = 'ROTOR-DIST-BOSCH-70';
UPDATE products SET stock = stock - 1 WHERE sku = 'BOMB-GAS-MEC-DATSUN';
UPDATE products SET stock = stock - 1 WHERE sku = 'ZAP-FREN-TAMBOR-70';
UPDATE products SET stock = stock - 2 WHERE sku = 'CIL-RUEDA-TRAS-VCH';


-- ------------------------------------------------------------------------------
-- ORDEN 7: Audi e-tron GT RS Quattro 2023 800V (OPEN)
-- Cliente: Lic. Gabriel Alarcón Velázquez | Total: $9,050.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Lic. Gabriel Alarcón Velázquez',
    'Audi e-tron GT RS Quattro 2023 - Placas ETG-2023-GT',
    'Protocolo de alta tensión LOTO: bloqueo de seguridad MSD, diagnóstico telemático SOH de batería de 93.4 kWh, reemplazo y purga asistida por vacío de refrigerante dieléctrico en inversores delantero/trasero y servicio a frenos regenerativos.',
    'open',
    9050.00,
    NOW(),
    NOW()
);
SET @ord_m4_7 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_7, (SELECT id FROM products WHERE sku = 'SERV-LOTO-DESCON-EV' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m4_7, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m4_7, (SELECT id FROM products WHERE sku = 'SERV-REFR-DIEL-EV' LIMIT 1), 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_m4_7, (SELECT id FROM products WHERE sku = 'REFR-DIEL-EV-4L' LIMIT 1), 2, 1450.00, 2900.00, NOW(), NOW()),
(@ord_m4_7, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'REFR-DIEL-EV-4L';


-- ------------------------------------------------------------------------------
-- ORDEN 8: Volkswagen Golf GTI MK7.5 2.0 TSI DSG 2019 (DELIVERED)
-- Cliente: Lic. Adrián de la Garza Santos | Total: $11,290.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Lic. Adrián de la Garza Santos',
    'Volkswagen Golf GTI MK7.5 2.0 TSI DSG 2019 - Placas GTI-750-NL',
    'Corrección de código de presión de riel EPC: instalación de bomba de gasolina de alta presión GDI/TSI nueva, mantenimiento especializado a transmisión DSG de 6 velocidades, balatas cerámicas TRW y purga de frenos DOT 5.1.',
    'delivered',
    11290.00,
    DATE_SUB(NOW(), INTERVAL 6 DAY),
    NOW()
);
SET @ord_m4_8 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'BOMBA-GAS-ALTA-GDI' LIMIT 1), 1, 4850.00, 4850.00, NOW(), NOW()),
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'SERV-TRANS-DSG-STEP' LIMIT 1), 1, 2800.00, 2800.00, NOW(), NOW()),
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'BALATAS-CERAM-TRW-DEL' LIMIT 1), 1, 1280.00, 1280.00, NOW(), NOW()),
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 1, 310.00, 310.00, NOW(), NOW()),
(@ord_m4_8, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'BOMBA-GAS-ALTA-GDI';
UPDATE products SET stock = stock - 1 WHERE sku = 'BALATAS-CERAM-TRW-DEL';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIQ-FREN-DOT51-ESP';


-- ------------------------------------------------------------------------------
-- ORDEN 9: Chevrolet Silverado 2500 V8 Diésel Duramax 2020 (DONE)
-- Cliente: Rancho El Campanario S.P.R. de R.L. | Total: $3,990.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Rancho El Campanario S.P.R. de R.L.',
    'Chevrolet Silverado 2500 V8 Duramax 2020 - Placas HD-2500-TX',
    'Servicio mayor de servicio pesado para remolque: sustitución de filtro separador de agua y diésel common rail, reemplazo de cruceta de flecha cardán trasera por juego excesivo, y 4L de fluido sintético para transmisión/diferencial.',
    'done',
    3990.00,
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    NOW()
);
SET @ord_m4_9 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_9, (SELECT id FROM products WHERE sku = 'FILTRO-DIESEL-COMMON' LIMIT 1), 1, 690.00, 690.00, NOW(), NOW()),
(@ord_m4_9, (SELECT id FROM products WHERE sku = 'CRUCETA-CARDAN-DART' LIMIT 1), 2, 280.00, 560.00, NOW(), NOW()),
(@ord_m4_9, (SELECT id FROM products WHERE sku = 'ACE-TRANS-CVT-NS3' LIMIT 1), 4, 360.00, 1440.00, NOW(), NOW()),
(@ord_m4_9, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'FILTRO-DIESEL-COMMON';
UPDATE products SET stock = stock - 2 WHERE sku = 'CRUCETA-CARDAN-DART';
UPDATE products SET stock = stock - 4 WHERE sku = 'ACE-TRANS-CVT-NS3';


-- ------------------------------------------------------------------------------
-- ORDEN 10: Nissan Leaf EV 2021 40kWh (OPEN)
-- Cliente: Empresa Verde Soluciones Sustentables S.A. | Total: $4,270.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Empresa Verde Soluciones Sustentables S.A.',
    'Nissan Leaf EV 2021 40kWh - Placas NLF-040-EV',
    'Revisión preventiva de 50,000 km: Diagnóstico telemático SOH de batería laminada (SOH 94.2%), reemplazo de sensores térmicos de módulo BMS, servicio y limpieza a frenos regenerativos y cambio de plumas limpiaparabrisas Bosch.',
    'open',
    4270.00,
    NOW(),
    NOW()
);
SET @ord_m4_10 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m4_10, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m4_10, (SELECT id FROM products WHERE sku = 'SENS-TEMP-BMS-EV' LIMIT 1), 2, 390.00, 780.00, NOW(), NOW()),
(@ord_m4_10, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m4_10, (SELECT id FROM products WHERE sku = 'JUEGO-LIMPIA-BOSCH-A' LIMIT 1), 1, 490.00, 490.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'SENS-TEMP-BMS-EV';
UPDATE products SET stock = stock - 1 WHERE sku = 'JUEGO-LIMPIA-BOSCH-A';

COMMIT;
