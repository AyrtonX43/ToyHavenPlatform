-- Fix Philippine text encoding issues (Ã± to n) in database
-- Run this SQL script in your MySQL database

USE toyhaven_local;

-- Fix Sellers table
UPDATE sellers SET region = REPLACE(region, 'Ã±', 'n') WHERE region LIKE '%Ã±%';
UPDATE sellers SET region = REPLACE(region, 'Ã', 'N') WHERE region LIKE '%Ã%';
UPDATE sellers SET province = REPLACE(province, 'Ã±', 'n') WHERE province LIKE '%Ã±%';
UPDATE sellers SET province = REPLACE(province, 'Ã', 'N') WHERE province LIKE '%Ã%';
UPDATE sellers SET city = REPLACE(city, 'Ã±', 'n') WHERE city LIKE '%Ã±%';
UPDATE sellers SET city = REPLACE(city, 'Ã', 'N') WHERE city LIKE '%Ã%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ã±', 'n') WHERE barangay LIKE '%Ã±%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ã', 'N') WHERE barangay LIKE '%Ã%';
UPDATE sellers SET address = REPLACE(address, 'Ã±', 'n') WHERE address LIKE '%Ã±%';
UPDATE sellers SET address = REPLACE(address, 'Ã', 'N') WHERE address LIKE '%Ã%';
UPDATE sellers SET business_name = REPLACE(business_name, 'Ã±', 'n') WHERE business_name LIKE '%Ã±%';
UPDATE sellers SET business_name = REPLACE(business_name, 'Ã', 'N') WHERE business_name LIKE '%Ã%';
UPDATE sellers SET description = REPLACE(description, 'Ã±', 'n') WHERE description LIKE '%Ã±%';
UPDATE sellers SET description = REPLACE(description, 'Ã', 'N') WHERE description LIKE '%Ã%';

-- Fix other common encoding issues in Sellers
UPDATE sellers SET region = REPLACE(region, 'Ã¡', 'a') WHERE region LIKE '%Ã¡%';
UPDATE sellers SET region = REPLACE(region, 'Ã©', 'e') WHERE region LIKE '%Ã©%';
UPDATE sellers SET region = REPLACE(region, 'Ã­', 'i') WHERE region LIKE '%Ã­%';
UPDATE sellers SET region = REPLACE(region, 'Ã³', 'o') WHERE region LIKE '%Ã³%';
UPDATE sellers SET region = REPLACE(region, 'Ãº', 'u') WHERE region LIKE '%Ãº%';

UPDATE sellers SET province = REPLACE(province, 'Ã¡', 'a') WHERE province LIKE '%Ã¡%';
UPDATE sellers SET province = REPLACE(province, 'Ã©', 'e') WHERE province LIKE '%Ã©%';
UPDATE sellers SET province = REPLACE(province, 'Ã­', 'i') WHERE province LIKE '%Ã­%';
UPDATE sellers SET province = REPLACE(province, 'Ã³', 'o') WHERE province LIKE '%Ã³%';
UPDATE sellers SET province = REPLACE(province, 'Ãº', 'u') WHERE province LIKE '%Ãº%';

UPDATE sellers SET city = REPLACE(city, 'Ã¡', 'a') WHERE city LIKE '%Ã¡%';
UPDATE sellers SET city = REPLACE(city, 'Ã©', 'e') WHERE city LIKE '%Ã©%';
UPDATE sellers SET city = REPLACE(city, 'Ã­', 'i') WHERE city LIKE '%Ã­%';
UPDATE sellers SET city = REPLACE(city, 'Ã³', 'o') WHERE city LIKE '%Ã³%';
UPDATE sellers SET city = REPLACE(city, 'Ãº', 'u') WHERE city LIKE '%Ãº%';

UPDATE sellers SET barangay = REPLACE(barangay, 'Ã¡', 'a') WHERE barangay LIKE '%Ã¡%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ã©', 'e') WHERE barangay LIKE '%Ã©%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ã­', 'i') WHERE barangay LIKE '%Ã­%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ã³', 'o') WHERE barangay LIKE '%Ã³%';
UPDATE sellers SET barangay = REPLACE(barangay, 'Ãº', 'u') WHERE barangay LIKE '%Ãº%';

UPDATE sellers SET address = REPLACE(address, 'Ã¡', 'a') WHERE address LIKE '%Ã¡%';
UPDATE sellers SET address = REPLACE(address, 'Ã©', 'e') WHERE address LIKE '%Ã©%';
UPDATE sellers SET address = REPLACE(address, 'Ã­', 'i') WHERE address LIKE '%Ã­%';
UPDATE sellers SET address = REPLACE(address, 'Ã³', 'o') WHERE address LIKE '%Ã³%';
UPDATE sellers SET address = REPLACE(address, 'Ãº', 'u') WHERE address LIKE '%Ãº%';

-- Fix Users table
UPDATE users SET region = REPLACE(region, 'Ã±', 'n') WHERE region LIKE '%Ã±%';
UPDATE users SET region = REPLACE(region, 'Ã', 'N') WHERE region LIKE '%Ã%';
UPDATE users SET province = REPLACE(province, 'Ã±', 'n') WHERE province LIKE '%Ã±%';
UPDATE users SET province = REPLACE(province, 'Ã', 'N') WHERE province LIKE '%Ã%';
UPDATE users SET city = REPLACE(city, 'Ã±', 'n') WHERE city LIKE '%Ã±%';
UPDATE users SET city = REPLACE(city, 'Ã', 'N') WHERE city LIKE '%Ã%';
UPDATE users SET barangay = REPLACE(barangay, 'Ã±', 'n') WHERE barangay LIKE '%Ã±%';
UPDATE users SET barangay = REPLACE(barangay, 'Ã', 'N') WHERE barangay LIKE '%Ã%';
UPDATE users SET address = REPLACE(address, 'Ã±', 'n') WHERE address LIKE '%Ã±%';
UPDATE users SET address = REPLACE(address, 'Ã', 'N') WHERE address LIKE '%Ã%';
UPDATE users SET name = REPLACE(name, 'Ã±', 'n') WHERE name LIKE '%Ã±%';
UPDATE users SET name = REPLACE(name, 'Ã', 'N') WHERE name LIKE '%Ã%';

-- Fix other common encoding issues in Users
UPDATE users SET region = REPLACE(region, 'Ã¡', 'a') WHERE region LIKE '%Ã¡%';
UPDATE users SET region = REPLACE(region, 'Ã©', 'e') WHERE region LIKE '%Ã©%';
UPDATE users SET region = REPLACE(region, 'Ã­', 'i') WHERE region LIKE '%Ã­%';
UPDATE users SET region = REPLACE(region, 'Ã³', 'o') WHERE region LIKE '%Ã³%';
UPDATE users SET region = REPLACE(region, 'Ãº', 'u') WHERE region LIKE '%Ãº%';

UPDATE users SET province = REPLACE(province, 'Ã¡', 'a') WHERE province LIKE '%Ã¡%';
UPDATE users SET province = REPLACE(province, 'Ã©', 'e') WHERE province LIKE '%Ã©%';
UPDATE users SET province = REPLACE(province, 'Ã­', 'i') WHERE province LIKE '%Ã­%';
UPDATE users SET province = REPLACE(province, 'Ã³', 'o') WHERE province LIKE '%Ã³%';
UPDATE users SET province = REPLACE(province, 'Ãº', 'u') WHERE province LIKE '%Ãº%';

UPDATE users SET city = REPLACE(city, 'Ã¡', 'a') WHERE city LIKE '%Ã¡%';
UPDATE users SET city = REPLACE(city, 'Ã©', 'e') WHERE city LIKE '%Ã©%';
UPDATE users SET city = REPLACE(city, 'Ã­', 'i') WHERE city LIKE '%Ã­%';
UPDATE users SET city = REPLACE(city, 'Ã³', 'o') WHERE city LIKE '%Ã³%';
UPDATE users SET city = REPLACE(city, 'Ãº', 'u') WHERE city LIKE '%Ãº%';

UPDATE users SET barangay = REPLACE(barangay, 'Ã¡', 'a') WHERE barangay LIKE '%Ã¡%';
UPDATE users SET barangay = REPLACE(barangay, 'Ã©', 'e') WHERE barangay LIKE '%Ã©%';
UPDATE users SET barangay = REPLACE(barangay, 'Ã­', 'i') WHERE barangay LIKE '%Ã­%';
UPDATE users SET barangay = REPLACE(barangay, 'Ã³', 'o') WHERE barangay LIKE '%Ã³%';
UPDATE users SET barangay = REPLACE(barangay, 'Ãº', 'u') WHERE barangay LIKE '%Ãº%';

UPDATE users SET address = REPLACE(address, 'Ã¡', 'a') WHERE address LIKE '%Ã¡%';
UPDATE users SET address = REPLACE(address, 'Ã©', 'e') WHERE address LIKE '%Ã©%';
UPDATE users SET address = REPLACE(address, 'Ã­', 'i') WHERE address LIKE '%Ã­%';
UPDATE users SET address = REPLACE(address, 'Ã³', 'o') WHERE address LIKE '%Ã³%';
UPDATE users SET address = REPLACE(address, 'Ãº', 'u') WHERE address LIKE '%Ãº%';

-- Fix Addresses table
UPDATE addresses SET city = REPLACE(city, 'Ã±', 'n') WHERE city LIKE '%Ã±%';
UPDATE addresses SET city = REPLACE(city, 'Ã', 'N') WHERE city LIKE '%Ã%';
UPDATE addresses SET province = REPLACE(province, 'Ã±', 'n') WHERE province LIKE '%Ã±%';
UPDATE addresses SET province = REPLACE(province, 'Ã', 'N') WHERE province LIKE '%Ã%';
UPDATE addresses SET address = REPLACE(address, 'Ã±', 'n') WHERE address LIKE '%Ã±%';
UPDATE addresses SET address = REPLACE(address, 'Ã', 'N') WHERE address LIKE '%Ã%';
UPDATE addresses SET label = REPLACE(label, 'Ã±', 'n') WHERE label LIKE '%Ã±%';
UPDATE addresses SET label = REPLACE(label, 'Ã', 'N') WHERE label LIKE '%Ã%';

-- Fix other common encoding issues in Addresses
UPDATE addresses SET city = REPLACE(city, 'Ã¡', 'a') WHERE city LIKE '%Ã¡%';
UPDATE addresses SET city = REPLACE(city, 'Ã©', 'e') WHERE city LIKE '%Ã©%';
UPDATE addresses SET city = REPLACE(city, 'Ã­', 'i') WHERE city LIKE '%Ã­%';
UPDATE addresses SET city = REPLACE(city, 'Ã³', 'o') WHERE city LIKE '%Ã³%';
UPDATE addresses SET city = REPLACE(city, 'Ãº', 'u') WHERE city LIKE '%Ãº%';

UPDATE addresses SET province = REPLACE(province, 'Ã¡', 'a') WHERE province LIKE '%Ã¡%';
UPDATE addresses SET province = REPLACE(province, 'Ã©', 'e') WHERE province LIKE '%Ã©%';
UPDATE addresses SET province = REPLACE(province, 'Ã­', 'i') WHERE province LIKE '%Ã­%';
UPDATE addresses SET province = REPLACE(province, 'Ã³', 'o') WHERE province LIKE '%Ã³%';
UPDATE addresses SET province = REPLACE(province, 'Ãº', 'u') WHERE province LIKE '%Ãº%';

UPDATE addresses SET address = REPLACE(address, 'Ã¡', 'a') WHERE address LIKE '%Ã¡%';
UPDATE addresses SET address = REPLACE(address, 'Ã©', 'e') WHERE address LIKE '%Ã©%';
UPDATE addresses SET address = REPLACE(address, 'Ã­', 'i') WHERE address LIKE '%Ã­%';
UPDATE addresses SET address = REPLACE(address, 'Ã³', 'o') WHERE address LIKE '%Ã³%';
UPDATE addresses SET address = REPLACE(address, 'Ãº', 'u') WHERE address LIKE '%Ãº%';

-- Fix Orders table
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã±', 'n') WHERE shipping_address LIKE '%Ã±%';
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã', 'N') WHERE shipping_address LIKE '%Ã%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã±', 'n') WHERE shipping_city LIKE '%Ã±%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã', 'N') WHERE shipping_city LIKE '%Ã%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã±', 'n') WHERE shipping_province LIKE '%Ã±%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã', 'N') WHERE shipping_province LIKE '%Ã%';
UPDATE orders SET shipping_notes = REPLACE(shipping_notes, 'Ã±', 'n') WHERE shipping_notes LIKE '%Ã±%';
UPDATE orders SET shipping_notes = REPLACE(shipping_notes, 'Ã', 'N') WHERE shipping_notes LIKE '%Ã%';

-- Fix other common encoding issues in Orders
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã¡', 'a') WHERE shipping_address LIKE '%Ã¡%';
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã©', 'e') WHERE shipping_address LIKE '%Ã©%';
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã­', 'i') WHERE shipping_address LIKE '%Ã­%';
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ã³', 'o') WHERE shipping_address LIKE '%Ã³%';
UPDATE orders SET shipping_address = REPLACE(shipping_address, 'Ãº', 'u') WHERE shipping_address LIKE '%Ãº%';

UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã¡', 'a') WHERE shipping_city LIKE '%Ã¡%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã©', 'e') WHERE shipping_city LIKE '%Ã©%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã­', 'i') WHERE shipping_city LIKE '%Ã­%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ã³', 'o') WHERE shipping_city LIKE '%Ã³%';
UPDATE orders SET shipping_city = REPLACE(shipping_city, 'Ãº', 'u') WHERE shipping_city LIKE '%Ãº%';

UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã¡', 'a') WHERE shipping_province LIKE '%Ã¡%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã©', 'e') WHERE shipping_province LIKE '%Ã©%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã­', 'i') WHERE shipping_province LIKE '%Ã­%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ã³', 'o') WHERE shipping_province LIKE '%Ã³%';
UPDATE orders SET shipping_province = REPLACE(shipping_province, 'Ãº', 'u') WHERE shipping_province LIKE '%Ãº%';

SELECT 'Philippine text encoding fix completed!' AS status;
