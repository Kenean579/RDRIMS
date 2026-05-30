const fs = require('fs');
const path = require('path');

const { execSync } = require('child_process');

const frontendDir = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src';
const backendDir = 'd:\\proje\\qelemeda\\RDRIMS\\backend';

let backendRoutes = [];
try {
  const stdout = execSync('php artisan route:list --path=api --json', { cwd: backendDir, encoding: 'utf-8' });
  const data = JSON.parse(stdout.trim());
  backendRoutes = data.map(r => ({
    method: r.method.split('|'),
    uri: r.uri.replace(/^api\//, '/'),
    raw: r.uri
  }));
} catch (e) {
  console.error("Failed to read backend routes: ", e.message);
  process.exit(1);
}

// Recursively find all vue and js files
function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = path.join(dir, file);
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(file));
    } else if (file.endsWith('.js') || file.endsWith('.vue')) {
      results.push(file);
    }
  });
  return results;
}

const files = walk(frontendDir);
let discrepancies = [];

function convertLaravelRouteToRegex(uri) {
  // convert /users/{user} to /users/[^/]+
  // make trailing slash optional
  const regexStr = '^' + uri.replace(/{[^}]+}/g, '[^/]+') + '/?$';
  return new RegExp(regexStr);
}

files.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  // Simple regex to match api.get('/path/...'), api.post(`/path/${id}`), axios.get(...)
  const regex = /(?:api|axios)\.(get|post|put|patch|delete)\s*\(\s*[`'"]([^`'"]+)[`'"]/g;
  let match;
  while ((match = regex.exec(content)) !== null) {
    const method = match[1].toUpperCase();
    let url = match[2];
    
    // Normalize url variable interpolations for regex matching
    // Replace ${...} with something generic
    url = url.replace(/\$\{[^}]+\}/g, '123'); // supply dummy ID
    url = url.replace(/\?.*/, ''); // strip query string
    if (!url.startsWith('/')) {
        url = '/' + url;
    }

    // Check if url matches any backend route
    let found = false;
    for (const route of backendRoutes) {
      if (route.method.includes(method) || route.method.includes('ANY')) {
        const routeRegex = convertLaravelRouteToRegex(route.uri);
        if (routeRegex.test(url)) {
          found = true;
          break;
        }
      }
    }

    if (!found) {
      discrepancies.push({ file: file.replace(frontendDir, ''), method, url, originalMatch: match[2] });
    }
  }
});

if (discrepancies.length > 0) {
  console.log("== DISCREPANCIES FOUND ==");
  console.log("These frontend calls do not seem to match any backend route (or could not be parsed dynamically):");
  discrepancies.forEach(d => {
    console.log(`[${d.method}] ${d.url} -> Found in: ${d.file}`);
  });
} else {
  console.log("== ALL GOOD ==");
  console.log("All statically analyzable API calls in the frontend map to defined backend routes!");
}
