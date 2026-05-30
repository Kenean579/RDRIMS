// modernize-views.cjs
// Batch modernizes all Vue view components under src/views.
// Works with the project’s "type": "module" setting by using CommonJS (.cjs).

const fs = require('fs');
const path = require('path');

// Root directory for Vue view files
const VIEWS_DIR = path.resolve(__dirname, 'src', 'views');

// Simple text replacements: jargon -> plain English
const replacements = [
  [/\bBasic Information\b/g, 'Quick Summary'],
  [/\bInvestigators\b/g, 'Team Members'],
  [/\bReviewers\b/g, 'Reviews'],
  [/\bSystem Configurations\b/g, 'Settings'],
  [/\bInstitutional Parameters\b/g, 'Control'],
  [/\bEthics Review\b/g, 'Ethics Approval'],
  [/\bDecision Note\b/g, 'Comments'],
  [/\bRequest Revision\b/g, 'Ask for Changes'],
  [/\bSubmit\b/g, 'Submit Final'],
  [/\bApprove\b/g, 'Approve'],
  [/\bReject\b/g, 'Reject'],
  [/\bAssign Reviewers\b/g, 'Find Reviewers'],
];

function applyReplacements(content) {
  let updated = content;
  for (const [regex, repl] of replacements) {
    updated = updated.replace(regex, repl);
  }
  return updated;
}

// Ensure the first <div> inside <template> has the "card" class.
function ensureCardClass(content) {
  const match = content.match(/<template>[\s\S]*?<div([^>]*?)>/);
  if (!match) return content;
  const attrs = match[1];
  if (/\bcard\b/.test(attrs)) return content; // already present
  const newAttrs = attrs.trim() ? `${attrs.trim()} card` : ' card';
  return content.replace(/<div([^>]*?)>/, `<div${newAttrs}>`);
}

// Recursively collect all .vue files under VIEWS_DIR
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
console.log(`Found ${vueFiles.length} Vue view files.`);

vueFiles.forEach((file) => {
  const original = fs.readFileSync(file, 'utf8');
  let updated = applyReplacements(original);
  updated = ensureCardClass(updated);
  if (updated !== original) {
    fs.writeFileSync(file, updated, 'utf8');
    console.log(`Updated ${path.relative(VIEWS_DIR, file)}`);
  }
});

console.log('All view files processed.');
