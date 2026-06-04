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

            // 1. Replace font-bold on tiny text with font-medium
            // Match text-[9px] or text-[10px] or text-[11px] followed later in same class by font-bold
            content = content.replace(/text-\[(9|10|11)px\](\s+[\w\/\[\].-]+)*\s+font-bold/g, (match) => {
                count++;
                return match.replace('font-bold', 'font-medium');
            });

            // 2. Replace .toUpperCase() on type badge text with proper casing
            content = content.replace(/\.toUpperCase\(\)/g, () => { count++; return ''; });

            // 3. Remove hover:shadow-xl (too aggressive for clean cards)
            content = content.replace(/hover:shadow-xl/g, () => { count++; return 'hover:shadow-md'; });

            // 4. Reduce remaining font-bold on text-xs and text-sm labels to font-medium
            // (only inside class attributes, not on headings/h-tags)
            content = content.replace(/(text-(?:xs|sm)\s+[\w\/\[\].-]*\s*)font-bold(\s+text-slate-(?:400|500))/g, (match, p1, p2) => {
                count++;
                return p1 + 'font-medium' + p2;
            });

            // 5. Clean up hover:bg-brand group-hover:bg-brand on small buttons (keep subtle)
            // Actually these are fine for interactive elements

            // 6. Clean up double spaces
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
console.log(`Pass 2 done. ${filesChanged} files changed, ${totalReplacements} replacements.`);
