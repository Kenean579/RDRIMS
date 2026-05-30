const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

const DB = { host: '127.0.0.1', port: 3306, user: 'root', password: '', database: 'rdrims_db' };
const BACKEND = 'd:\\proje\\qelemeda\\RDRIMS\\backend\\app';

async function main() {
  // 1. Get actual tables from DB
  const conn = await mysql.createConnection(DB);
  const [rows] = await conn.query('SHOW TABLES');
  const actualTables = rows.map(r => Object.values(r)[0]).sort();
  await conn.end();

  // 2. Get Models
  const models = fs.readdirSync(path.join(BACKEND, 'Models'))
    .filter(f => f.endsWith('.php'))
    .map(f => f.replace('.php', ''));

  // 3. Get Controllers
  const controllers = fs.readdirSync(path.join(BACKEND, 'Http', 'Controllers'))
    .filter(f => f.endsWith('Controller.php') && f !== 'Controller.php')
    .map(f => f.replace('Controller.php', ''));

  // 4. Get Policies
  const policies = fs.readdirSync(path.join(BACKEND, 'Policies'))
    .filter(f => f.endsWith('Policy.php'))
    .map(f => f.replace('Policy.php', ''));

  // 5. Get Services
  const services = fs.readdirSync(path.join(BACKEND, 'Services'))
    .filter(f => f.endsWith('Service.php'))
    .map(f => f.replace('Service.php', ''));

  // 6. Cross-check: which tables DON'T have a model
  function toModelName(table) {
    // simple: strip trailing s, capitalize each word
    return table.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('');
  }

  const IGNORE_TABLES = ['migrations', 'cache', 'cache_locks', 'jobs', 'job_batches',
    'failed_jobs', 'personal_access_tokens', 'sessions', 'password_reset_tokens'];

  const businessTables = actualTables.filter(t => !IGNORE_TABLES.includes(t));

  console.log(`\n========================================`);
  console.log(` RDRIMS Backend ↔ DB Schema Gap Report`);
  console.log(`========================================\n`);
  console.log(`✅ Total DB tables: ${actualTables.length} (${businessTables.length} business tables)`);
  console.log(`✅ Models: ${models.length}`);
  console.log(`✅ Controllers: ${controllers.length}`);
  console.log(`✅ Policies: ${policies.length}`);
  console.log(`✅ Services: ${services.length}`);

  console.log(`\n--- Tables WITHOUT a matching Model ---`);
  let missingModels = [];
  for (const t of businessTables) {
    const singular = toModelName(t);
    // try singular and remove trailing 's' forms
    const candidates = [
      singular,
      toModelName(t.endsWith('s') ? t.slice(0,-1) : t),
      toModelName(t.replace(/_statuses$/, '_status').replace(/_types$/, '_type')),
    ];
    const found = models.some(m => candidates.includes(m));
    if (!found) missingModels.push({ table: t, tried: candidates[0] });
  }
  if (missingModels.length === 0) console.log('  None — all tables have a model ✅');
  else missingModels.forEach(m => console.log(`  ❌ Table: ${m.table}  (expected model: ${m.tried})`));

  console.log(`\n--- Controllers WITHOUT a matching Route (not in api.php) ---`);
  const apiPhp = fs.readFileSync('d:\\proje\\qelemeda\\RDRIMS\\backend\\routes\\api.php', 'utf8');
  const missingRouted = controllers.filter(c => !apiPhp.includes(c + 'Controller'));
  if (missingRouted.length === 0) console.log('  None — all controllers are routed ✅');
  else missingRouted.forEach(c => console.log(`  ❌ ${c}Controller has no route`));

  console.log(`\n--- Controllers WITHOUT a Policy ---`);
  // Main entity controllers should have a policy
  const skipPolicy = ['Auth', 'Dashboard', 'Lookup', 'Public', 'Search', 'Report',
    'ReviewerProposal', 'UserRole', 'UserExpertise', 'UserResearchCenter',
    'ProposalFile', 'OutputFile', 'PatentFile', 'ProjectFile', 'OutputParticipant',
    'ProposalInvestigator', 'ProjectInvestigator', 'ProposalReviewer', 'PublicationAuthor',
    'FinanceCheck', 'RolePermission', 'LanguagePreference', 'Notification', 'AgreementFile',
    'EventRegistration', 'ThematicArea'];
  const missingPolicy = controllers.filter(c => !skipPolicy.includes(c) && !policies.includes(c));
  if (missingPolicy.length === 0) console.log('  None — all entity controllers have a policy ✅');
  else missingPolicy.forEach(c => console.log(`  ❌ ${c}Controller has no policy`));

  console.log('\n');
}

main().catch(e => console.error('Error:', e.message));
