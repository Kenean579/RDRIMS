const fs = require('fs');
const path = require('path');

// 1. Fix MainLayout.vue (Router Active Link & Spacing)
const layoutPath = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src\\layouts\\MainLayout.vue';
if (fs.existsSync(layoutPath)) {
  let layout = fs.readFileSync(layoutPath, 'utf8');
  // Fix router link active
  layout = layout.replace(/\.sidebar-link\.router-link-active \{/g, '.sidebar-link.router-link-exact-active {');
  layout = layout.replace(/\.sidebar-link\.router-link-active \.nav-icon \{/g, '.sidebar-link.router-link-exact-active .nav-icon {');
  
  // Fix header cramp
  layout = layout.replace(/<div class="topbar-left">(\s*)<!-- Hamburger -->(\s*)<button class="icon-btn"/g, '<div class="topbar-left gap-4">$1<!-- Hamburger -->$2<button class="icon-btn mr-2"');
  
  fs.writeFileSync(layoutPath, layout);
}

// 2. Fix List Views alignment
const viewsDir = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src\\views';
function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    file = path.join(dir, file);
    const stat = fs.statSync(file);
    if (stat && stat.isDirectory()) {
      results = results.concat(walk(file));
    } else if (file.endsWith('ListView.vue') || file.endsWith('Publications.vue') || file.endsWith('FundingCalls.vue') || file.endsWith('View.vue')) {
      results.push(file);
    }
  });
  return results;
}

const files = walk(viewsDir);
let fixedCount = 0;
files.forEach(file => {
  let content = fs.readFileSync(file, 'utf8');
  const size = content.length;
  
  // Replace `items-end` on the filter row with `items-start` or none, and make sure search/status match heights.
  // Actually, standardizing the flex container alignment:
  content = content.replace(/<div class="flex flex-col sm:flex-row gap-5 items-end">/g, '<div class="flex flex-col sm:flex-row gap-5 items-start">');
  
  if (content.length !== size || content !== fs.readFileSync(file, 'utf8')) {
    fs.writeFileSync(file, content);
    fixedCount++;
  }
});

console.log(`Fixed Active Link and Header spacing in MainLayout.`);
console.log(`Fixed filter misalignments in ${fixedCount} list views.`);
