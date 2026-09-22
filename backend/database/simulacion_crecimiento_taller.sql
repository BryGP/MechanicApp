-- ==============================================================================
-- SIMULACIÓN DE NEGOCIO: RESTOCK MASIVO, LOTE CLÁSICOS/BMW Y SERVICIOS AVANZADOS
-- ==============================================================================
-- Base de Datos: mechanic_app
-- Contexto:
-- 1. Restock masivo de piezas de alta rotación (aceites, filtros, bujías, frenos).
-- 2. Refacciones para autos populares clásicos (Vocho, Tsuru, Chevy C2, Pointer).
-- 3. Refacciones de alta gama para BMW modernos (Serie 3, X3, Motores TwinPower Turbo).
-- 4. Nuevos servicios especializados tras capacitación y cursos de los mecánicos.
-- 5. Registro contable de egresos de capacitación y compras a distribuidor.
-- 6. Órdenes de trabajo reales en vivo (entregadas, en proceso y abiertas) con deducción.
-- ==============================================================================

START TRANSACTION;

-- ==============================================================================
-- PASO 1: RESTOCK MASIVO DE INVENTARIO ESENCIAL (PIEZAS DE ALTA ROTACIÓN)
-- ==============================================================================
-- Resurtido de consumibles y fluidos que tenían existencias bajas o agotándose:

UPDATE products SET stock = stock + 40, updated_at = NOW() WHERE sku = 'ACE-10W30';
UPDATE products SET stock = stock + 50, updated_at = NOW() WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock + 35, updated_at = NOW() WHERE sku = 'FILT-AIR';
UPDATE products SET stock = stock + 60, updated_at = NOW() WHERE sku = 'BUJ-COBRE-1P';
UPDATE products SET stock = stock + 30, updated_at = NOW() WHERE sku = 'LIQ-FREN-DOT4';
UPDATE products SET stock = stock + 45, updated_at = NOW() WHERE sku = 'BRAKE-CLEAN-400';
UPDATE products SET stock = stock + 25, updated_at = NOW() WHERE sku = 'ANT-5050-GAL';


-- ==============================================================================
-- PASO 2: NUEVAS REFACCIONES PARA "CARRITOS VIEJITOS" (CLÁSICOS POPULARES)
-- ==============================================================================
-- Refacciones para VW Vocho 1600, Nissan Tsuru III / B13, Chevy 1.6L y Pointer:

INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Juego Platino y Condensador Bosch (VW Vocho / Datsun)', 'PLAT-COND-BOSCH', 145.00, 20, 5, 0, NOW(), NOW()),
('Chicote de Clutch Reforzado (Tsuru III / Sentra B13)', 'CHIC-CLUTCH-TSURU', 190.00, 15, 4, 0, NOW(), NOW()),
('Bomba de Gasolina Mecánica de Diafragma (VW Sedán 1600)', 'BOMB-GAS-MEC-VCH', 380.00, 12, 3, 0, NOW(), NOW()),
('Kit Juntas y Empaques Carburador Bocar 1-2 Gargantas', 'KIT-JUNTAS-CARB', 260.00, 14, 3, 0, NOW(), NOW()),
('Juego Cables de Bujía Silicón 8mm (Chevy 1.6L / Corsa)', 'CAB-BUJ-COBRE-CHEV', 340.00, 18, 4, 0, NOW(), NOW()),
('Termostato y Toma de Agua Metálica (Pointer 1.8L)', 'TERMOSTATO-POINTER', 285.00, 16, 4, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    price = VALUES(price), 
    stock = stock + VALUES(stock), 
    updated_at = NOW();


-- ==============================================================================
-- PASO 3: NUEVAS REFACCIONES PARA "BMW MODERNOS Y ALTA GAMA"
-- ==============================================================================
-- Refacciones para BMW Serie 3 (G20), Serie 5, X3 (G01) y motores B48/B58 Turbo:

INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Aceite Sintético BMW TwinPower Turbo LL-04 5W-30 (1L)', 'ACE-BMW-LL04-5W30', 420.00, 36, 8, 0, NOW(), NOW()),
('Pastillas de Freno Cerámicas Delanteras BMW Serie 3 / X3 G20', 'FREN-BMW-CER-G20', 2450.00, 8, 2, 0, NOW(), NOW()),
('Sensor Electrónico de Desgaste de Frenos Delantero BMW', 'SENS-DESG-BMW-FREN', 480.00, 14, 4, 0, NOW(), NOW()),
('Filtro de Aceite Ecológico Cartucho (BMW Motores B48/B58)', 'FILT-OIL-BMW-ECO', 380.00, 20, 5, 0, NOW(), NOW()),
('Bobina de Encendido Individual Bosch High Output (BMW Turbo)', 'BOB-IGN-BOSCH-BMW', 1150.00, 16, 4, 0, NOW(), NOW()),
('Líquido de Frenos Sintético DOT 5.1 Low Viscosity ESP (BMW/Euro)', 'LIQ-FREN-DOT51-ESP', 310.00, 24, 6, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    price = VALUES(price), 
    stock = stock + VALUES(stock), 
    updated_at = NOW();


-- ==============================================================================
-- PASO 4: NUEVOS SERVICIOS TRAS CAPACITACIÓN Y CURSOS DE MECÁNICOS
-- ==============================================================================
-- Mano de obra especializada (is_service = 1, sin control de inventario):

INSERT INTO products (name, sku, price, stock, min_stock, is_service, created_at, updated_at) VALUES
('Diagnóstico Avanzado y Escaneo Telemático BMW / Mini (ISTA & INPA)', 'SERV-DIAG-BMW-ISTA', 1200.00, 0, 0, 1, NOW(), NOW()),
('Afinación y Puesta a Tiempo con Lámpara Estroboscópica (Clásicos)', 'SERV-CALIB-CARB-TIM', 650.00, 0, 0, 1, NOW(), NOW()),
('Limpieza de Inyectores en Laboratorio por Ultrasonido y Probeta', 'SERV-LAB-INY-ULTRA', 850.00, 0, 0, 1, NOW(), NOW()),
('Purga Presurizada Electrónica de Sistema de Frenos ABS / DSC / ESP', 'SERV-PURGA-ABS-ESP', 750.00, 0, 0, 1, NOW(), NOW()),
('Mantenimiento y Cambio de Fluido Transmisión DSG / Steptronic', 'SERV-TRANS-DSG-STEP', 2800.00, 0, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    price = VALUES(price), 
    is_service = 1, 
    updated_at = NOW();


-- ==============================================================================
-- PASO 5: REGISTRO CONTABLE DE EGRESOS (CAPACITACIÓN + LOTE DE REFACCIONES)
-- ==============================================================================
-- Asentamiento en el módulo de contabilidad de la inversión y costos de operación:

INSERT INTO expenses (concept, category, amount, payment_method, reference, expense_date, created_at, updated_at) VALUES
('Certificación Técnica y Curso Diagnóstico Electrónico Europeo BMW (ISTA)', 'Capacitación y Cursos', 8500.00, 'transferencia', 'CURSO-BMW-2026', CURDATE(), NOW(), NOW()),
('Factura A-4892 Distribuidora Mayorista (Restock masivo + lote Clásicos y BMW)', 'Refacciones', 28450.00, 'transferencia', 'FAC-A4892-DIS', CURDATE(), NOW(), NOW()),
('Scanner Interface BMW ICOM / K+DCAN para laboratorio de diagnóstico', 'Herramientas', 4200.00, 'tarjeta', 'TC-HERR-8831', CURDATE(), NOW(), NOW());


-- ==============================================================================
-- PASO 6: SIMULACIÓN DE ÓRDENES DE VENTA EN VIVO (CLIENTES Y VEHÍCULOS REALES)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- ORDEN A: BMW 330i 2021 (G20) - Servicio Mayor y Frenos (ESTATUS: DELIVERED)
-- Cliente: Lic. Fernando Sada (Flotilla Corporativa)
-- Total: $7,360.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, status, total, created_at, updated_at)
VALUES ('Lic. Fernando Sada', 'BMW 330i 2021 (G20) - Placas PYC-8821', 'delivered', 7360.00, DATE_SUB(NOW(), INTERVAL 2 DAY), NOW());
SET @ord_bmw1 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'SERV-DIAG-BMW-ISTA' LIMIT 1), 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'ACE-BMW-LL04-5W30' LIMIT 1), 5, 420.00, 2100.00, NOW(), NOW()),
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'FILT-OIL-BMW-ECO' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'FREN-BMW-CER-G20' LIMIT 1), 1, 2450.00, 2450.00, NOW(), NOW()),
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'SENS-DESG-BMW-FREN' LIMIT 1), 1, 480.00, 480.00, NOW(), NOW()),
(@ord_bmw1, (SELECT id FROM products WHERE sku = 'SERV-PURGA-ABS-ESP' LIMIT 1), 1, 750.00, 750.00, NOW(), NOW());

-- Deducción de inventario para Orden A:
UPDATE products SET stock = stock - 5 WHERE sku = 'ACE-BMW-LL04-5W30';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL-BMW-ECO';
UPDATE products SET stock = stock - 1 WHERE sku = 'FREN-BMW-CER-G20';
UPDATE products SET stock = stock - 1 WHERE sku = 'SENS-DESG-BMW-FREN';


-- ------------------------------------------------------------------------------
-- ORDEN B: Nissan Tsuru 2008 - Puesta a punto y embrague (ESTATUS: DELIVERED)
-- Cliente: Don Ramón Mendoza (Taxi Sitio 14)
-- Total: $1,675.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, status, total, created_at, updated_at)
VALUES ('Don Ramón Mendoza', 'Nissan Tsuru 2008 - Placas A-514-TME', 'delivered', 1675.00, DATE_SUB(NOW(), INTERVAL 1 DAY), NOW());
SET @ord_tsuru = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_tsuru, (SELECT id FROM products WHERE sku = 'SERV-CALIB-CARB-TIM' LIMIT 1), 1, 650.00, 650.00, NOW(), NOW()),
(@ord_tsuru, (SELECT id FROM products WHERE sku = 'ACE-20W50-1L' LIMIT 1), 4, 140.00, 560.00, NOW(), NOW()),
(@ord_tsuru, (SELECT id FROM products WHERE sku = 'FILT-OIL' LIMIT 1), 1, 95.00, 95.00, NOW(), NOW()),
(@ord_tsuru, (SELECT id FROM products WHERE sku = 'BUJ-COBRE-1P' LIMIT 1), 4, 45.00, 180.00, NOW(), NOW()),
(@ord_tsuru, (SELECT id FROM products WHERE sku = 'CHIC-CLUTCH-TSURU' LIMIT 1), 1, 190.00, 190.00, NOW(), NOW());

-- Deducción de inventario para Orden B:
UPDATE products SET stock = stock - 4 WHERE sku = 'ACE-20W50-1L';
UPDATE products SET stock = stock - 1 WHERE sku = 'FILT-OIL';
UPDATE products SET stock = stock - 4 WHERE sku = 'BUJ-COBRE-1P';
UPDATE products SET stock = stock - 1 WHERE sku = 'CHIC-CLUTCH-TSURU';


-- ------------------------------------------------------------------------------
-- ORDEN C: VW Sedán 1994 (Vocho) - Falla de combustible y encendido (ESTATUS: IN_PROGRESS)
-- Cliente: Mateo Solís (Restaurador Clásico)
-- Total: $1,060.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, status, total, created_at, updated_at)
VALUES ('Mateo Solís', 'VW Sedán Vocho 1994 - Placas 412-ZUX', 'in_progress', 1060.00, DATE_SUB(NOW(), INTERVAL 8 HOUR), NOW());
SET @ord_vocho = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_vocho, (SELECT id FROM products WHERE sku = 'SERV-MO-MEC' LIMIT 1), 1, 450.00, 450.00, NOW(), NOW()),
(@ord_vocho, (SELECT id FROM products WHERE sku = 'BOMB-GAS-MEC-VCH' LIMIT 1), 1, 380.00, 380.00, NOW(), NOW()),
(@ord_vocho, (SELECT id FROM products WHERE sku = 'PLAT-COND-BOSCH' LIMIT 1), 1, 145.00, 145.00, NOW(), NOW()),
(@ord_vocho, (SELECT id FROM products WHERE sku = 'BRAKE-CLEAN-400' LIMIT 1), 1, 85.00, 85.00, NOW(), NOW());

-- Deducción de inventario para Orden C:
UPDATE products SET stock = stock - 1 WHERE sku = 'BOMB-GAS-MEC-VCH';
UPDATE products SET stock = stock - 1 WHERE sku = 'PLAT-COND-BOSCH';
UPDATE products SET stock = stock - 1 WHERE sku = 'BRAKE-CLEAN-400';


-- ------------------------------------------------------------------------------
-- ORDEN D: BMW X3 xDrive30i 2020 - Falla de cilindro / Check Engine (ESTATUS: OPEN)
-- Cliente: Dra. Valeria Montes
-- Total: $6,650.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, status, total, created_at, updated_at)
VALUES ('Dra. Valeria Montes', 'BMW X3 xDrive30i 2020 - Placas NBL-3390', 'open', 6650.00, DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW());
SET @ord_bmwx3 = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_bmwx3, (SELECT id FROM products WHERE sku = 'SERV-DIAG-BMW-ISTA' LIMIT 1), 1, 1200.00, 1200.00, NOW(), NOW()),
(@ord_bmwx3, (SELECT id FROM products WHERE sku = 'BOB-IGN-BOSCH-BMW' LIMIT 1), 4, 1150.00, 4600.00, NOW(), NOW()),
(@ord_bmwx3, (SELECT id FROM products WHERE sku = 'SERV-LAB-INY-ULTRA' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW());

-- Deducción de inventario para Orden D:
UPDATE products SET stock = stock - 4 WHERE sku = 'BOB-IGN-BOSCH-BMW';


-- ------------------------------------------------------------------------------
-- ORDEN E: Chevrolet Chevy C2 2007 - Mantenimiento de Inyección (ESTATUS: OPEN)
-- Cliente: Sr. Ignacio Barajas
-- Total: $1,370.00
-- ------------------------------------------------------------------------------
INSERT INTO orders (customer_name, vehicle, status, total, created_at, updated_at)
VALUES ('Sr. Ignacio Barajas', 'Chevrolet Chevy C2 2007 - Placas JKL-9912', 'open', 1370.00, NOW(), NOW());
SET @ord_chevy = LAST_INSERT_ID();

INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
(@ord_chevy, (SELECT id FROM products WHERE sku = 'CAB-BUJ-COBRE-CHEV' LIMIT 1), 1, 340.00, 340.00, NOW(), NOW()),
(@ord_chevy, (SELECT id FROM products WHERE sku = 'BUJ-COBRE-1P' LIMIT 1), 4, 45.00, 180.00, NOW(), NOW()),
(@ord_chevy, (SELECT id FROM products WHERE sku = 'SERV-LAB-INY-ULTRA' LIMIT 1), 1, 850.00, 850.00, NOW(), NOW());

-- Deducción de inventario para Orden E:
UPDATE products SET stock = stock - 1 WHERE sku = 'CAB-BUJ-COBRE-CHEV';
UPDATE products SET stock = stock - 4 WHERE sku = 'BUJ-COBRE-1P';

COMMIT;
