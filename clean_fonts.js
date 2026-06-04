const fs = require('fs');
const path = require('path');

function processDir(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        if (fs.statSync(fullPath).isDirectory()) {
            processDir(fullPath);
        } else if (fullPath.endsWith('.vue')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            // Remove 'capitalize tracking-widest'
            content = content.replace(/capitalize\s+tracking-widest/g, '');
            // Remove 'uppercase tracking-widest'
            content = content.replace(/uppercase\s+tracking-widest/g, '');
            // Reduce 'font-black' to 'font-semibold'
            content = content.replace(/font-black/g, 'font-semibold');
            
            // Clean up extra spaces in class attributes
            content = content.replace(/class="([^"]*?)"/g, (match, p1) => {
                return `class="${p1.replace(/\s+/g, ' ').trim()}"`;
            });

            fs.writeFileSync(fullPath, content);
        }
    }
}

processDir('d:\\proje\\qelemeda\\RDRIMS\\frontend\\src');
console.log("Styling cleaned up globally.");
