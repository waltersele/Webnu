-- Inventario pre-deploy: plantillas retiradas vs activas
-- Ejecutar en MySQL de producción (phpMyAdmin o SSH) antes del deploy.
-- Ver docs/MIGRACION-PRODUCCION.md

SELECT template, COUNT(*) AS n
FROM companies
WHERE template IN ('oriental', 'basic', 'visual', 'atelier', 'bistro', 'velvet')
GROUP BY template
ORDER BY n DESC;

-- Slugs afectados por migración (solo retiradas; velvet NO se migra)
SELECT slug, template, name
FROM companies
WHERE template IN ('oriental', 'basic', 'visual', 'atelier', 'bistro')
ORDER BY template, slug;
