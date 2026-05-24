#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { parse } from 'csv-parse/sync';
import { config, assertWorkerConfig } from './config.js';
import { processOne } from './pipeline/processOne.js';
import { createLogger, printSummary } from './report/batchReport.js';

function parseArgs(argv) {
  const args = {
    input: null,
    dryRun: false,
    limit: null,
    fromRow: 1,
    concurrency: config.concurrency,
    batchId: new Date().toISOString().replace(/[:.]/g, '-'),
  };

  for (let i = 2; i < argv.length; i++) {
    const arg = argv[i];
    if (arg === '--dry-run') {
      args.dryRun = true;
    } else if (arg === '--input' && argv[i + 1]) {
      args.input = argv[++i];
    } else if (arg === '--limit' && argv[i + 1]) {
      args.limit = Number.parseInt(argv[++i], 10);
    } else if (arg === '--from-row' && argv[i + 1]) {
      args.fromRow = Number.parseInt(argv[++i], 10);
    } else if (arg === '--concurrency' && argv[i + 1]) {
      args.concurrency = Math.max(1, Number.parseInt(argv[++i], 10));
    } else if (arg === '--batch-id' && argv[i + 1]) {
      args.batchId = argv[++i];
    } else if (arg === '--help' || arg === '-h') {
      printHelp();
      process.exit(0);
    }
  }

  return args;
}

function printHelp() {
  console.log(`Uso: npm run capture -- --input urls.csv [opciones]

Opciones:
  --input <archivo.csv>   CSV: restaurant_name,source_url,logo_url
  --dry-run               Extrae + IA sin POST a Webnu
  --limit <n>             Máximo de filas a procesar
  --from-row <n>          Fila inicial (1 = primera fila de datos)
  --concurrency <n>       Paralelismo (default: 1)
  --batch-id <id>         Identificador en source_meta
`);
}

function loadCsv(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const records = parse(content, {
    columns: true,
    skip_empty_lines: true,
    trim: true,
  });

  return records.map((row, index) => ({
    row: index + 1,
    restaurant_name: row.restaurant_name || row.name || '',
    source_url: row.source_url || row.url || '',
    logo_url: row.logo_url || '',
  }));
}

async function runPool(items, concurrency, worker) {
  const results = [];
  let index = 0;

  async function runner() {
    while (index < items.length) {
      const current = items[index++];
      results.push(await worker(current));
    }
  }

  const runners = Array.from({ length: concurrency }, () => runner());
  await Promise.all(runners);
  return results;
}

async function main() {
  const args = parseArgs(process.argv);

  if (!args.input) {
    printHelp();
    process.exit(1);
  }

  const csvPath = path.isAbsolute(args.input) ? args.input : path.join(config.rootDir, args.input);
  if (!fs.existsSync(csvPath)) {
    console.error(`No existe el CSV: ${csvPath}`);
    process.exit(1);
  }

  assertWorkerConfig({ dryRun: args.dryRun });

  let rows = loadCsv(csvPath).filter((r) => r.row >= args.fromRow);
  if (args.limit !== null && Number.isFinite(args.limit)) {
    rows = rows.slice(0, args.limit);
  }

  if (rows.length === 0) {
    console.log('No hay filas que procesar.');
    process.exit(0);
  }

  const logger = createLogger();
  console.log(`Procesando ${rows.length} fila(s). Log: ${logger.logPath}`);
  console.log(`Modo: ${args.dryRun ? 'dry-run' : 'push Webnu'} | concurrencia: ${args.concurrency}`);

  const results = await runPool(rows, args.concurrency, async (row) => {
    try {
      const result = await processOne({
        ...row,
        batchId: args.batchId,
        dryRun: args.dryRun,
      });
      logger.write({ ...result, row: row.row });
      return result;
    } catch (error) {
      const failed = {
        status: 'failed',
        restaurant_name: row.restaurant_name,
        source_url: row.source_url,
        message: error instanceof Error ? error.message : String(error),
        row: row.row,
      };
      logger.write(failed);
      console.error(`[FAIL] fila ${row.row} ${row.restaurant_name}: ${failed.message}`);
      return failed;
    }
  });

  printSummary(results);
  const failedCount = results.filter((r) => r.status === 'failed').length;
  process.exit(failedCount > 0 ? 1 : 0);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
