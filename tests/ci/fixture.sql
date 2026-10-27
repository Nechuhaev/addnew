-- Мінімальні гео-дані для smoke-тесту в CI: сайт очікує країну id=62 (Україна, AdCountry::getCurrentCountry)
INSERT INTO ad_countries (id, name, name_uk, image, slug, created_at, updated_at)
VALUES (62, 'Украина', 'Україна', '', 'ukraina', NOW(), NOW());
UPDATE ad_regions SET country_id = 62 WHERE country_id = (SELECT id FROM (SELECT MIN(id) AS id FROM ad_countries WHERE id <> 62) t);
