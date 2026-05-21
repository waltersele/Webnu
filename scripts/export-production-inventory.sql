-- Inventario pre-migración (ejecutar en MySQL de producción, solo lectura)
-- Guardar salida: companies-inventory-YYYYMMDD.csv

SELECT
    id,
    name,
    slug,
    menu_type,
    enabled,
    template,
    user_id,
    logo,
    menu_type_2_pdf
FROM companies
ORDER BY slug;

SELECT
    u.id AS user_id,
    u.email,
    u.stripe_id,
    u.plan AS plan_db,
    c.id AS company_id,
    c.name AS company_name,
    c.slug,
    CONCAT('https://webnu.es/carta/', c.slug) AS public_url
FROM users u
INNER JOIN companies c ON c.user_id = u.id
ORDER BY u.email;

-- Contenido de carta por negocio (prioridad migración: platos publicados)
SELECT
    c.id,
    c.slug,
    c.name,
    c.menu_type,
    COUNT(DISTINCT s.id) AS sections_count,
    COUNT(p.id) AS products_count
FROM companies c
LEFT JOIN sections s ON s.company_id = c.id
LEFT JOIN products p ON p.section_id = s.id
GROUP BY c.id, c.slug, c.name, c.menu_type
ORDER BY c.slug;

-- Opcional: suscripciones Stripe activas (puede devolver 0 filas)
SELECT
    u.id AS user_id,
    u.email,
    s.name AS subscription_name,
    s.stripe_status,
    c.slug
FROM users u
INNER JOIN subscriptions s ON s.user_id = u.id
INNER JOIN companies c ON c.user_id = u.id
WHERE s.stripe_status IN ('active', 'trialing')
  AND (s.ends_at IS NULL OR s.ends_at > NOW())
ORDER BY u.email;
