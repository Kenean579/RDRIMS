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

            // 1. Convert ad-hoc white cards to standard .card
            content = content.replace(/bg-white\s+rounded-lg\s+shadow-sm\s+p-6/g, () => { count++; return 'card p-8'; });
            content = content.replace(/bg-white\s+rounded-lg\s+shadow-sm\s+p-5/g, () => { count++; return 'card p-8'; });
            content = content.replace(/bg-white\s+rounded-lg\s+shadow-sm\s+p-4/g, () => { count++; return 'card p-6'; });
            
            // 2. Fix potential typo <div card>
            content = content.replace(/<div card>/g, () => { count++; return '<div class="card p-8">'; });

            // 3. Rounding standardization
            content = content.replace(/rounded-lg/g, () => { count++; return 'rounded-2xl'; });
            content = content.replace(/rounded-xl/g, () => { count++; return 'rounded-2xl'; });

            // 4. Border soften
            content = content.replace(/border-gray-(?:100|200)/g, () => { count++; return 'border-slate-100'; });
            content = content.replace(/border-slate-200/g, () => { count++; return 'border-slate-100'; });

            // 5. Upgrade standard p-5/p-6 padding to p-8 for main views
            // We look for class="...card...p-5" or similar
            content = content.replace(/(class="[^"]*?card[^"]*?)\s+p-[56]([^"]*?")/g, (match, p1, p2) => {
                count++;
                return p1 + ' p-8' + p2;
            });

            if (content !== original) {
                fs.writeFileSync(full, content);
                filesChanged++;
                totalReplacements += count;
            }
        }
    }
}

processDir(path.join('d:', 'proje', 'qelemeda', 'RDRIMS', 'frontend', 'src', 'views'));
processDir(path.join('d:', 'proje', 'qelemeda', 'RDRIMS', 'frontend', 'src', 'components'));
processDir(path.join('d:', 'proje', 'qelemeda', 'RDRIMS', 'frontend', 'src', 'layouts'));
console.log(`Cleanup pass 4 done. ${filesChanged} files changed, ${totalReplacements} replacements.`);
