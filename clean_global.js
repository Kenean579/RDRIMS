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
        } else if (full.endsWith('.vue') || full.endsWith('.css')) {
            let content = fs.readFileSync(full, 'utf8');
            const original = content;
            let count = 0;

            // 1. Remove colored button shadows: shadow-lg shadow-blue-500/20, shadow-lg shadow-brand/30, etc.
            content = content.replace(/\s*shadow-lg\s+shadow-[\w-]+\/\d+/g, () => { count++; return ''; });

            // 2. Remove card-hover class usage from card containers (keep card itself)
            // Not removing the class definition, just usage in templates alongside .card
            // Actually let's keep card-hover, it's now toned down

            // 3. Remove bg-linear-to-br gradient blocks (the big icon squares already removed from main cards)
            content = content.replace(/bg-linear-to-br\s+from-[\w-]+\s+to-[\w-]+/g, () => { count++; return 'bg-slate-100'; });

            // 4. Remove "font-bold" from tiny labels (text-[9px], text-[10px], text-[11px])
            // Replace font-bold on text-[9px] / text-[10px] / text-[11px] labels with font-medium
            content = content.replace(/(text-\[\d+px\]\s+)font-semibold/g, (m, p1) => { count++; return p1 + 'font-medium'; });
            
            // 5. Remove excessive shadow classes on non-button elements
            content = content.replace(/\s*shadow-xs/g, () => { count++; return ''; });
            content = content.replace(/\s*shadow-inner/g, () => { count++; return ''; });

            // 6. Clean up double/triple spaces in class strings
            content = content.replace(/class="([^"]*?)"/g, (match, p1) => {
                const cleaned = p1.replace(/\s+/g, ' ').trim();
                if (cleaned !== p1.trim()) count++;
                return `class="${cleaned}"`;
            });

            if (content !== original) {
                fs.writeFileSync(full, content);
                filesChanged++;
                totalReplacements += count;
            }
        }
    }
}

processDir(path.join('d:', 'proje', 'qelemeda', 'RDRIMS', 'frontend', 'src'));
console.log(`Done. ${filesChanged} files changed, ${totalReplacements} replacements made.`);
