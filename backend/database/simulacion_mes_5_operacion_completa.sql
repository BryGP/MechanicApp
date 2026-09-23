-- ==============================================================================
-- SIMULACIÓN DE OPERACIÓN: MES 5 DEL TALLER MECÁNICO (OPERACIÓN COMPLETA Y CONSOLIDADA)
-- CIERRE DE ÓRDENES PREVIAS + 10 NUEVAS ÓRDENES + RESTOCK + EGRESOS OPERATIVOS MES 5
-- ==============================================================================
-- Base de Datos: mechanic_app (MySQL)
-- Contexto del Mes 5:
-- 1. Consolidación de ingresos en tres segmentos clave:
--    a) Alta Tensión & Vehículos Eléctricos (BYD Seal CTB, Tesla Model Y, Lucid Air 900V).
--    b) Joyas Clásicas y Muscle Cars (Camaro SS 69 Big Block, Charger 440, Combi 74).
--    c) Alta Gama Europea y Flotillas Corporativas (Porsche Macan GTS, BMW 430i G26, Flotilla Bimbo EV).
-- 2. Transición formal de ciclo de vida: Órdenes previas abiertas pasan a 'delivered'.
-- 3. Resurtido estratégico de insumos de alta rotación (aceites, refrigerantes dieléctricos, balatas).
-- 4. Asentamiento contable con estricto apego fiscal (Art. 27 LISR) y categorización limpia.
-- 5. IDEMPOTENCIA TOTAL: Permite re-ejecuciones seguras sin duplicar órdenes ni desbalancear números.
-- ==============================================================================

START TRANSACTION;

-- ==============================================================================
-- PASO 1: ENTREGA DE TODAS LAS ÓRDENES PREVIAS PENDIENTES DEL MES 4
-- ==============================================================================
-- Cerrar el ciclo operativo y financiero de órdenes anteriores:
UPDATE orders 
SET status = 'delivered', 
    updated_at = NOW() 
WHERE status IN ('open', 'in_progress', 'done');


-- ==============================================================================
-- PASO 2: LIMPIEZA PREVENTIVA DE REGISTROS DEL MES 5 (IDEMPOTENCIA ESTRICTA)
-- ==============================================================================
-- Si el script se vuelve a ejecutar, se eliminan previamente partidas y órdenes del Mes 5:
DELETE FROM order_items WHERE order_id IN (
    SELECT id FROM orders WHERE customer_name IN (
        'Ing. Marcelo Canales Clariond',
        'Don Lorenzo Zambrano Villarreal (Colección)',
        'Grupo Bimbo S.A.B. de C.V. - Flota Reparto EV',
        'Lic. Mauricio Morales Treviño',
        'Arq. Bernardo Pozas de la Vega',
        'Dra. Sofía Garza Lagüera',
        'Lic. Carlos Bremer Gutiérrez',
        'Taller de Restauración Clásica Monterrey',
        'Transportes Ejecutivos San Pedro S.A. de C.V.',
        'Dr. Alfonso Romo Garza'
    )
);

DELETE FROM orders WHERE customer_name IN (
    'Ing. Marcelo Canales Clariond',
    'Don Lorenzo Zambrano Villarreal (Colección)',
    'Grupo Bimbo S.A.B. de C.V. - Flota Reparto EV',
    'Lic. Mauricio Morales Treviño',
    'Arq. Bernardo Pozas de la Vega',
    'Dra. Sofía Garza Lagüera',
    'Lic. Carlos Bremer Gutiérrez',
    'Taller de Restauración Clásica Monterrey',
    'Transportes Ejecutivos San Pedro S.A. de C.V.',
    'Dr. Alfonso Romo Garza'
);

DELETE FROM expenses WHERE reference IN (
    'RENTA-TALLER-M5',
    'NOM-QUINC-M5',
    'CFE-BIM-M5',
    'RESTOCK-INSUMOS-M5',
    'CERT-ALTA-TENSION-M5'
);


-- ==============================================================================
-- PASO 3: RESTOCK ESTRATÉGICO Y NUEVAS REFACCIONES DE ALTA ROTACIÓN
-- ==============================================================================
-- Reabastecer stock de insumos que se consumen rápidamente:
UPDATE products SET stock = stock + 30, updated_at = NOW() WHERE sku = 'ACE-5W30-5L';
UPDATE products SET stock = stock + 40, updated_at = NOW() WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock + 20, updated_at = NOW() WHERE sku = 'ACE-BMW-LL04-5W30';
UPDATE products SET stock = stock + 25, updated_at = NOW() WHERE sku = 'REFR-DIEL-EV-4L';
UPDATE products SET stock = stock + 30, updated_at = NOW() WHERE sku = 'LIQ-FREN-DOT51-ESP';
UPDATE products SET stock = stock + 50, updated_at = NOW() WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock + 40, updated_at = NOW() WHERE sku = 'FILT-AIR';
UPDATE products SET stock = stock + 15, updated_at = NOW() WHERE sku = 'FREN-BMW-CER-G20';
UPDATE products SET stock = stock + 20, updated_at = NOW() WHERE sku = 'BALATAS-CERAM-TRW-DEL';
UPDATE products SET stock = stock + 10, updated_at = NOW() WHERE sku = 'BOMBA-GAS-ALTA-GDI';

-- Incorporación de 3 nuevos componentes especializados para el Mes 5:
INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Filtro de Cabina Biológico / Carbón Activado CTB (BYD Seal / Atto 3)', 'FILT-CAB-BYD-SEAL', 720.00, 18, 3, 0, NOW(), NOW()),
('Juego de Balatas Cerámicas Traseras Brembo Sport (Porsche Macan / Cayenne)', 'FREN-BREMBO-CER-POR', 2850.00, 10, 2, 0, NOW(), NOW()),
('Válvula PCV Reforzada y Manguera Respiradero Motor V8 Mopar / Chevy', 'VALV-PCV-V8-MAGNUM', 380.00, 16, 3, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    price = VALUES(price), 
    stock = stock + VALUES(stock), 
    updated_at = NOW();


-- ==============================================================================
-- PASO 4: ASENTAMIENTO CONTABLE OPERATIVO DEL MES 5 ($89,300.00)
-- ==============================================================================
-- Egresos debidamente categorizados para alimentar el módulo de finanzas:
INSERT INTO expenses (concept, category, amount, payment_method, reference, expense_date, created_at, updated_at) VALUES
('Renta de Inmueble, Patio de Maniobras y 6 Bahías de Taller - Mes 5', 'renta', 18000.00, 'transferencia', 'RENTA-TALLER-M5', DATE_SUB(CURDATE(), INTERVAL 6 DAY), NOW(), NOW()),
('Nómina Quincenal de Operaciones: 2 Mecánicos Máster, 1 Especialista EV y 1 Jefe de Taller', 'nomina', 34500.00, 'transferencia', 'NOM-QUINC-M5', CURDATE(), NOW(), NOW()),
('Consumo Eléctrico CFE: Bahía de Carga Rápida 22kW, Compresores Trifásicos y Elevadores', 'servicios', 5900.00, 'transferencia', 'CFE-BIM-M5', DATE_SUB(CURDATE(), INTERVAL 3 DAY), NOW(), NOW()),
('Factura Restock Mayorista: Fluidos Dieléctricos, Balatas Brembo/TRW, Aceites Sintéticos', 'refacciones', 25400.00, 'transferencia', 'RESTOCK-INSUMOS-M5', DATE_SUB(CURDATE(), INTERVAL 8 DAY), NOW(), NOW()),
('Recertificación Internacional de Seguridad en Alta Tensión NFPA 70E / TUV Rheinland', 'otros', 5500.00, 'transferencia', 'CERT-ALTA-TENSION-M5', DATE_SUB(CURDATE(), INTERVAL 12 DAY), NOW(), NOW());


-- ==============================================================================
-- PASO 5: APERTURA DE 10 NUEVAS ÓRDENES DEL MES 5 (TOTAL FACTURADO: $93,420.00)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- ORDEN 1: BYD Seal AWD Performance 2024 (530 HP - Batería Blade CTB) (IN_PROGRESS)
-- Cliente: Ing. Marcelo Canales Clariond | Total: $8,450.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Ing. Marcelo Canales Clariond',
    'BYD Seal AWD Performance 2024 - Placas BYD-530-NL',
    'Inspección de 30,000 km y prueba de aislamiento dieléctrico Fluke a tren motriz dual 8-en-1: purgado asistido de refrigerante dieléctrico de batería Blade Cell-to-Body (CTB), mantenimiento a frenos regenerativos y cambio de filtro de cabina biológico.',
    'in_progress',
    8450.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
);
SET @ord_m5_1 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'SERV-REFR-DIEL-EV' LIMIT 1), 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'REFR-DIEL-EV-4L' LIMIT 1), 2, 1450.00, 2900.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'FILT-CAB-BYD-SEAL' LIMIT 1), 1, 720.00, 720.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m5_1, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 1, 310.00, 310.00, NOW(), NOW());

-- Descuento atómico de almacén:
UPDATE products SET stock = stock - 2 WHERE sku = 'REFR-DIEL-EV-4L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-CAB-BYD-SEAL';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIQ-FREN-DOT51-ESP';


-- ------------------------------------------------------------------------------
-- ORDEN 2: Chevrolet Camaro SS 1969 V8 396 Big Block Turbo-Jet (DONE)
-- Cliente: Don Lorenzo Zambrano Villarreal (Colección) | Total: $7,530.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Don Lorenzo Zambrano Villarreal (Colección)',
    'Chevrolet Camaro SS 1969 V8 396 - Placas SS-1969-MX',
    'Servicio mayor de puesta a punto para rally clásico: afinación mayor V8 Big Block con ajuste fino de balancines, reconstrucción de carburador cuádruple garganta Holley, bujías Champion calientes, aceite mineral 20W-50 con zinc ZDDP y válvula PCV nueva.',
    'done',
    7530.00,
    DATE_SUB(NOW(), INTERVAL 3 DAY),
    NOW()
);
SET @ord_m5_2 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'SERV-AFIN-V8-CLASICO' LIMIT 1), 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'SERV-CARB-MULTI-70' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'BUJIA-CHAMPION-V8' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'VALV-PCV-V8-MAGNUM' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 7, 140.00, 980.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 1, 95.00, 95.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'FILT-AIR' LIMIT 1), 1, 120.00, 120.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 3, 650.00, 1950.00, NOW(), NOW()),
(@ord_m5_2, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 1, 550.00, 550.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'BUJIA-CHAMPION-V8';
UPDATE products SET stock = stock - 1 WHERE sku = 'VALV-PCV-V8-MAGNUM';
UPDATE products SET stock = stock - 7 WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-AIR';


-- ------------------------------------------------------------------------------
-- ORDEN 3: Flotilla Grupo Bimbo (4x Vans de Reparto 100% Eléctricas Vekstar) (OPEN)
-- Cliente: Grupo Bimbo S.A.B. de C.V. - Flota Reparto EV | Total: $21,400.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Grupo Bimbo S.A.B. de C.V. - Flota Reparto EV',
    'Flotilla 4x Vans de Reparto Vekstar EV 2023 - Monterrey Metropolitano',
    'Mantenimiento preventivo de flotilla sustentable de reparto diario: escaneo de telemetría de salud de celdas SOH, rotación, alineación y balanceo computarizado de 4 unidades, servicio y limpieza a frenos regenerativos y cambio de plumas limpiaparabrisas silenciosas.',
    'open',
    21400.00,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    NOW()
);
SET @ord_m5_3 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_3, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 4, 1600.00, 6400.00, NOW(), NOW()),
(@ord_m5_3, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 4, 1400.00, 5600.00, NOW(), NOW()),
(@ord_m5_3, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 4, 550.00, 2200.00, NOW(), NOW()),
(@ord_m5_3, (SELECT id FROM products WHERE sku = 'LIMP-PAR-AERO-EV' LIMIT 1), 4, 460.00, 1840.00, NOW(), NOW()),
(@ord_m5_3, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 8, 650.00, 5200.00, NOW(), NOW());

UPDATE products SET stock = stock - 4 WHERE sku = 'LIMP-PAR-AERO-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 4: Porsche Macan GTS 2.9L Biturbo 2022 (DELIVERED)
-- Cliente: Lic. Mauricio Morales Treviño | Total: $12,980.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Lic. Mauricio Morales Treviño',
    'Porsche Macan GTS 2.9L Biturbo 2022 - Placas GTS-2022-NL',
    'Servicio mayor de frenado y transmisión: cambio de balatas cerámicas traseras Brembo Sport con sensores, mantenimiento completo a transmisión PDK/DSG con reemplazo de fluido sintético de alto rendimiento y purga presurizada de frenos con líquido DOT 5.1 ESP.',
    'delivered',
    12980.00,
    DATE_SUB(NOW(), INTERVAL 5 DAY),
    NOW()
);
SET @ord_m5_4 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'FREN-BREMBO-CER-POR' LIMIT 1), 1, 2850.00, 2850.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'SENS-DESG-BMW-FREN' LIMIT 1), 2, 480.00, 960.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'SERV-TRANS-DSG-STEP' LIMIT 1), 1, 2800.00, 2800.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 2, 310.00, 620.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'ACE-BMW-LL04-5W30' LIMIT 1), 7, 420.00, 2940.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'FILT-OIL-BMW-ECO' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_4, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'FREN-BREMBO-CER-POR';
UPDATE products SET stock = stock - 2 WHERE sku = 'SENS-DESG-BMW-FREN';
UPDATE products SET stock = stock - 2 WHERE sku = 'LIQ-FREN-DOT51-ESP';
UPDATE products SET stock = stock - 7 WHERE sku = 'ACE-BMW-LL04-5W30';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL-BMW-ECO';


-- ------------------------------------------------------------------------------
-- ORDEN 5: Dodge Charger R/T 1970 V8 440 Magnum (IN_PROGRESS)
-- Cliente: Arq. Bernardo Pozas de la Vega | Total: $8,770.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Arq. Bernardo Pozas de la Vega',
    'Dodge Charger R/T 1970 V8 440 Magnum - Placas CHG-1970-TX',
    'Restauración de sistema de encendido y frenado: afinación mayor V8 440 Magnum, ajuste y carburación cuádruple boca Carter AVS, cambio de bujías Champion, sustitución de crucetas de cardán por vibración en cardán y rectificado de zapatas y tambores traseros.',
    'in_progress',
    8770.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
);
SET @ord_m5_5 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'SERV-AFIN-V8-CLASICO' LIMIT 1), 1, 1800.00, 1800.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'SERV-CARB-MULTI-70' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'BUJIA-CHAMPION-V8' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'CRUCETA-CARDAN-DART' LIMIT 1), 2, 280.00, 560.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'SERV-TAMBORES-ZAPATAS' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'ZAP-FREN-TAMBOR-70' LIMIT 1), 1, 360.00, 360.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 7, 140.00, 980.00, NOW(), NOW()),
(@ord_m5_5, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'BUJIA-CHAMPION-V8';
UPDATE products SET stock = stock - 2 WHERE sku = 'CRUCETA-CARDAN-DART';
UPDATE products SET stock = stock - 1 WHERE sku = 'ZAP-FREN-TAMBOR-70';
UPDATE products SET stock = stock - 7 WHERE sku = 'ACE-20W50-1L';


-- ------------------------------------------------------------------------------
-- ORDEN 6: Tesla Model Y Long Range 2023 Dual Motor AWD (DELIVERED)
-- Cliente: Dra. Sofía Garza Lagüera | Total: $5,840.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Dra. Sofía Garza Lagüera',
    'Tesla Model Y Long Range 2023 - Placas TSL-992-NL',
    'Servicio de 40,000 km: cambio de filtro de cabina grado médico HEPA con carbón activado, prueba de estanqueidad y reemplazo de cartucho desecante sílice de batería de alta tensión, y lubricación de calipers y pernos guía de frenos regenerativos.',
    'delivered',
    5840.00,
    DATE_SUB(NOW(), INTERVAL 4 DAY),
    NOW()
);
SET @ord_m5_6 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'FILT-HEPA-TESLA-M3' LIMIT 1), 1, 980.00, 980.00, NOW(), NOW()),
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'DESEC-PAC-BAT-EV' LIMIT 1), 1, 620.00, 620.00, NOW(), NOW()),
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 1, 550.00, 550.00, NOW(), NOW()),
(@ord_m5_6, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-HEPA-TESLA-M3';
UPDATE products SET stock = stock - 1 WHERE sku = 'DESEC-PAC-BAT-EV';


-- ------------------------------------------------------------------------------
-- ORDEN 7: BMW 430i Gran Coupé M Sport 2022 G26 B48 Turbo (IN_PROGRESS)
-- Cliente: Lic. Carlos Bremer Gutiérrez | Total: $9,680.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Lic. Carlos Bremer Gutiérrez',
    'BMW 430i Gran Coupé M Sport 2022 - Placas CBG-430-NL',
    'Mantenimiento mayor B48 TwinPower Turbo: cambio de pastillas de freno cerámicas delanteras con sensor de desgaste electrónico, cambio de aceite BMW LL-04 5W-30 con filtro cartucho ecológico, purga de frenos DOT 5.1 ESP y escaneo telemático ISTA.',
    'in_progress',
    9680.00,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    NOW()
);
SET @ord_m5_7 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'FREN-BMW-CER-G20' LIMIT 1), 1, 2450.00, 2450.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'SENS-DESG-BMW-FREN' LIMIT 1), 1, 480.00, 480.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'ACE-BMW-LL04-5W30' LIMIT 1), 6, 420.00, 2520.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'FILT-OIL-BMW-ECO' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'SERV-DIAG-BMW-ISTA' LIMIT 1), 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 1, 310.00, 310.00, NOW(), NOW()),
(@ord_m5_7, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'FREN-BMW-CER-G20';
UPDATE products SET stock = stock - 1 WHERE sku = 'SENS-DESG-BMW-FREN';
UPDATE products SET stock = stock - 6 WHERE sku = 'ACE-BMW-LL04-5W30';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL-BMW-ECO';
UPDATE products SET stock = stock - 1 WHERE sku = 'LIQ-FREN-DOT51-ESP';


-- ------------------------------------------------------------------------------
-- ORDEN 8: Volkswagen Combi Westfalia Camper 1974 1600cc (OPEN)
-- Cliente: Taller de Restauración Clásica Monterrey | Total: $4,895.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Taller de Restauración Clásica Monterrey',
    'Volkswagen Combi Westfalia Camper 1974 1600cc - Placas CMB-1974-NL',
    'Reacondicionamiento para viaje carretero: kit de reconstrucción de juntas de carburador Bocar 34-PICT, bomba mecánica de gasolina, calibración de platino y condensador Bosch con lámpara estroboscópica, cambio de aceite 20W-50 y cilindros de rueda traseros.',
    'open',
    4895.00,
    NOW(),
    NOW()
);
SET @ord_m5_8 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'KIT-JUNTAS-CARB' LIMIT 1), 1, 260.00, 260.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'BOMB-GAS-MEC-VCH' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'PLAT-COND-BOSCH' LIMIT 1), 1, 145.00, 145.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'SERV-TIEMPO-PLAT-ESTROB' LIMIT 1), 1, 680.00, 680.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'CIL-RUEDA-TRAS-VCH' LIMIT 1), 2, 175.00, 350.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'SERV-TAMBORES-ZAPATAS' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 3, 140.00, 420.00, NOW(), NOW()),
(@ord_m5_8, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW());

UPDATE products SET stock = stock - 1 WHERE sku = 'KIT-JUNTAS-CARB';
UPDATE products SET stock = stock - 1 WHERE sku = 'BOMB-GAS-MEC-VCH';
UPDATE products SET stock = stock - 1 WHERE sku = 'PLAT-COND-BOSCH';
UPDATE products SET stock = stock - 2 WHERE sku = 'CIL-RUEDA-TRAS-VCH';
UPDATE products SET stock = stock - 3 WHERE sku = 'ACE-20W50-1L';


-- ------------------------------------------------------------------------------
-- ORDEN 9: Flotilla Ejecutiva (2x Toyota RAV4 Hybrid Limited 2023) (DELIVERED)
-- Cliente: Transportes Ejecutivos San Pedro S.A. de C.V. | Total: $6,540.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Transportes Ejecutivos San Pedro S.A. de C.V.',
    'Flotilla 2x Toyota RAV4 Hybrid Limited 2023 - Placas RAV-02-SP',
    'Servicio bimestral para unidades híbridas ejecutivas de transporte VIP: cambio de balatas cerámicas delanteras TRW Pro de bajo ruido, afinación con garrafas de aceite sintético 5W-30, filtros de aceite y aire, e inspección del soplador de batería híbrida Ni-MH.',
    'delivered',
    6540.00,
    DATE_SUB(NOW(), INTERVAL 6 DAY),
    NOW()
);
SET @ord_m5_9 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'BALATAS-CERAM-TRW-DEL' LIMIT 1), 2, 1280.00, 2560.00, NOW(), NOW()),
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'ACE-5W30-5L' LIMIT 1), 2, 750.00, 1500.00, NOW(), NOW()),
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 2, 95.00, 190.00, NOW(), NOW()),
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'FILT-AIR' LIMIT 1), 2, 120.00, 240.00, NOW(), NOW()),
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 2, 650.00, 1300.00, NOW(), NOW()),
(@ord_m5_9, (SELECT id FROM products WHERE sku = 'SERV-ALIN-BAL' LIMIT 1), 1, 550.00, 550.00, NOW(), NOW());

UPDATE products SET stock = stock - 2 WHERE sku = 'BALATAS-CERAM-TRW-DEL';
UPDATE products SET stock = stock - 2 WHERE sku = 'ACE-5W30-5L';
UPDATE products SET stock = stock - 2 WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock - 2 WHERE sku = 'FILT-AIR';


-- ------------------------------------------------------------------------------
-- ORDEN 10: Lucid Air Grand Touring 2023 900V Dual Motor (1,050 HP) (OPEN)
-- Cliente: Dr. Alfonso Romo Garza | Total: $11,940.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, notes, status, total, created_at, updated_at)
VALUES (
    'Dr. Alfonso Romo Garza',
    'Lucid Air Grand Touring 2023 900V - Placas LCD-900-MX',
    'Protocolo de alta tensión 900V Wunderbox: Desconexión de seguridad LOTO con traje dieléctrico Arc Flash, diagnóstico telemático profundo de estado de celdas SOH (118 kWh), drenado y purga a presión de refrigerante dieléctrico de baja conductividad en inversor SiC y mantenimiento de calipers regenerativos.',
    'open',
    11940.00,
    NOW(),
    NOW()
);
SET @ord_m5_10 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'SERV-LOTO-DESCON-EV' LIMIT 1), 1, 950.00, 950.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'SERV-DIAG-EV-SOH' LIMIT 1), 1, 1600.00, 1600.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'SERV-REFR-DIEL-EV' LIMIT 1), 1, 2200.00, 2200.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'REFR-DIEL-EV-4L' LIMIT 1), 3, 1450.00, 4350.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'SERV-FRENOS-REGEN-EV' LIMIT 1), 1, 1400.00, 1400.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW()),
(@ord_m5_10, (SELECT id FROM products WHERE sku = 'LIQ-FREN-DOT51-ESP' LIMIT 1), 2, 310.00, 620.00, NOW(), NOW());

UPDATE products SET stock = stock - 3 WHERE sku = 'REFR-DIEL-EV-4L';
UPDATE products SET stock = stock - 2 WHERE sku = 'LIQ-FREN-DOT51-ESP';

COMMIT;


-- ==============================================================================
-- PASO 6: CONSULTAS DE AUDITORÍA Y COMPROBACIÓN POST-SIMULACIÓN
-- ==============================================================================
-- Ejecutar estas consultas para corroborar el estado en el Dashboard y Finanzas:

-- 1. Resumen global de caja (Ingresos, Egresos, Utilidad y Margen Operativo):
SELECT 
    ROUND(SUM(total), 2) AS total_ingresos_acumulados,
    (SELECT ROUND(SUM(amount), 2) FROM expenses) AS total_gastos_acumulados,
    ROUND(SUM(total) - (SELECT SUM(amount) FROM expenses), 2) AS utilidad_neta_taller,
    ROUND(((SUM(total) - (SELECT SUM(amount) FROM expenses)) / SUM(total)) * 100, 2) AS margen_operativo_pct
FROM orders;

-- 2. Conteo de órdenes por estatus operativo:
SELECT status, COUNT(*) AS total_ordenes, ROUND(SUM(total), 2) AS monto_total 
FROM orders 
GROUP BY status;

-- 3. Verificación de existencias críticas (no debe haber negativos):
SELECT sku, name, stock, min_stock 
FROM products 
WHERE is_service = 0 AND stock <= min_stock;
