const fs = require('fs');
const path = require('path');

const VIEWS_DIR = path.resolve(__dirname, 'src', 'views');

function collectVueFiles(dir) {
  let results = [];
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      results = results.concat(collectVueFiles(fullPath));
    } else if (entry.isFile() && entry.name.endsWith('.vue')) {
      results.push(fullPath);
    }
  }
  return results;
}

const vueFiles = collectVueFiles(VIEWS_DIR);
let count = 0;

vueFiles.forEach((file) => {
  const content = fs.readFileSync(file, 'utf8');
  const updated = content.replace(/<divclass="([^"]+)" card>/g, '<div class="$1 card">');
  if (content !== updated) {
    fs.writeFileSync(file, updated, 'utf8');
    count++;
  }
});

console.log(`Fixed ${count} files.`);
