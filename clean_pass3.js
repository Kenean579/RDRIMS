const fs = require('fs');
const path = require('path');

let filesChanged = 0;
let totalReplacements = 0;

function processDir(dir) {
    const items = fs.readdirSync(dir);
    for (const item of items) {
        const full = path.join(dir, item);
        const stat = fs.statSync(full);
        if (stat.isDirectory()) {
            processDir(full);
        } else if (full.endsWith('.vue')) {
            let content = fs.readFileSync(full, 'utf8');
            const original = content;
            let count = 0;

            // 1. Strip left/top heavy borders and their hover states
            // Matches: border-l-4 border-l-brand, border-t-4 border-t-brand, with optional /20 or variants
            content = content.replace(/\s*border-(l|t)-4\s+border-(l|t)-(?:brand|indigo|rose|[\w-]+[/-]\d+)/g, () => { count++; return ''; });
            content = content.replace(/\s*hover:border-(l|t)-[\w-]+/g, () => { count++; return ''; });

            // 2. Remove font-bold on specific list containers
            content = content.replace(/(class="[^"]*?card[^"]*?)\s+font-bold([^"]*?")/g, (match, p1, p2) => {
                count++;
                return p1 + p2;
            });

            // 3. One more check for capitalize tracking-widest (just in case)
            content = content.replace(/\s*(?:capitalize|uppercase)\s+tracking-widest/g, () => { count++; return ''; });

            if (content !== original) {
                fs.writeFileSync(full, content);
                filesChanged++;
                totalReplacements += count;
            }
        }
    }
}

processDir(path.join('d:', 'proje', 'qelemeda', 'RDRIMS', 'frontend', 'src', 'views'));
console.log(`Cleanup pass 3 done. ${filesChanged} files changed, ${totalReplacements} replacements.`);
