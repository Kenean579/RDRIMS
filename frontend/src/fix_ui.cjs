const fs = require('fs');
const path = require('path');

function processFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  let original = content;

  // Replace excessively large padding/margin classes
  content = content.replace(/\bp-24\b/g, 'p-8');
  content = content.replace(/\bp-20\b/g, 'p-6');
  content = content.replace(/\bp-16\b/g, 'p-6');
  content = content.replace(/\bp-12\b/g, 'p-6');
  content = content.replace(/\bp-10\b/g, 'p-5');
  content = content.replace(/\bp-8\b/g, 'p-5');

  content = content.replace(/\bpy-16\b/g, 'py-6');
  content = content.replace(/\bpy-12\b/g, 'py-6');
  content = content.replace(/\bpy-10\b/g, 'py-5');
  content = content.replace(/\bpy-8\b/g, 'py-4');

  content = content.replace(/\bpx-16\b/g, 'px-8');
  content = content.replace(/\bpx-12\b/g, 'px-6');
  content = content.replace(/\bpx-10\b/g, 'px-5');
  content = content.replace(/\bpx-8\b/g, 'px-5');

  content = content.replace(/\bpb-16\b/g, 'pb-8');
  content = content.replace(/\bpb-12\b/g, 'pb-6');
  content = content.replace(/\bpb-10\b/g, 'pb-5');
  content = content.replace(/\bpb-8\b/g, 'pb-4');

  content = content.replace(/\bpt-16\b/g, 'pt-8');
  content = content.replace(/\bpt-12\b/g, 'pt-6');
  content = content.replace(/\bpt-10\b/g, 'pt-5');
  content = content.replace(/\bpt-8\b/g, 'pt-4');

  content = content.replace(/\bmt-16\b/g, 'mt-8');
  content = content.replace(/\bmt-12\b/g, 'mt-6');
  content = content.replace(/\bmt-10\b/g, 'mt-5');
  content = content.replace(/\bmt-8\b/g, 'mt-5');
  
  content = content.replace(/\bmb-16\b/g, 'mb-8');
  content = content.replace(/\bmb-12\b/g, 'mb-6');
  content = content.replace(/\bmb-10\b/g, 'mb-5');
  content = content.replace(/\bmb-8\b/g, 'mb-5');

  content = content.replace(/\bm-16\b/g, 'm-8');
  content = content.replace(/\bm-12\b/g, 'm-6');
  content = content.replace(/\bm-10\b/g, 'm-5');
  content = content.replace(/\bm-8\b/g, 'm-4');
  
  content = content.replace(/\bgap-10\b/g, 'gap-5');
  content = content.replace(/\bgap-12\b/g, 'gap-6');
  content = content.replace(/\bgap-8\b/g, 'gap-5');
  
  // Replace large text sizes
  content = content.replace(/\btext-7xl\b/g, 'text-4xl');
  content = content.replace(/\btext-6xl\b/g, 'text-3xl');
  content = content.replace(/\btext-5xl\b/g, 'text-2xl');
  content = content.replace(/\btext-4xl\b/g, 'text-2xl');
  content = content.replace(/\btext-3xl\b/g, 'text-xl');
  
  // Font weight
  content = content.replace(/\bfont-black\b/g, 'font-bold');

  if (content !== original) {
    fs.writeFileSync(filePath, content, 'utf8');
    console.log('Updated', filePath);
  }
}

function traverseDir(dir) {
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const fullPath = path.join(dir, file);
    if (fs.statSync(fullPath).isDirectory()) {
      traverseDir(fullPath);
    } else if (fullPath.endsWith('.vue')) {
      processFile(fullPath);
    }
  }
}

traverseDir(__dirname);
