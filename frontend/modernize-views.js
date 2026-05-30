// modernize-views.js
// This script scans all Vue view components under src/views and applies
// simple language simplifications and adds premium Tailwind CSS class tweaks.
// It is intended to be run with Node.js (v20+).

const fs = require('fs');
const path = require('path');
const glob = require('glob');

const VIEWS_DIR = path.resolve(__dirname, 'src', 'views');

// Define simple text replacements for jargon -> plain English
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
  [/\bHelp Desk\b/g, 'Help Desk'], // keep as is if already simple
];

// Helper to apply replacements to file content
function applyReplacements(content) {
  let updated = content;
  for (const [regex, replacement] of replacements) {
    updated = updated.replace(regex, replacement);
  }
  return updated;
}

// Add a premium Tailwind wrapper if a top‑level <div> is missing the "card" class.
function ensureCardClass(content) {
  // Find the first opening <div> inside the <template> block.
  const templateMatch = content.match(/<template>[\s\S]*?<div([^>]*?)>/);
  if (!templateMatch) return content;
  const originalAttrs = templateMatch[1];
  if (/\bcard\b/.test(originalAttrs)) return content; // already has card
  const newAttrs = originalAttrs.trim() ? `${originalAttrs.trim()} card` : 'card';
  return content.replace(/<div([^>]*?)>/, `<div${newAttrs}>`);
}

// Process each .vue file
glob('**/*.vue', { cwd: VIEWS_DIR, absolute: true }, (err, files) => {
  if (err) {
    console.error('Glob error:', err);
    process.exit(1);
  }
  console.log(`Found ${files.length} Vue view files.`);
  files.forEach((file) => {
    let content = fs.readFileSync(file, 'utf8');
    const original = content;
    content = applyReplacements(content);
    content = ensureCardClass(content);
    if (content !== original) {
      fs.writeFileSync(file, content, 'utf8');
      console.log(`Updated ${path.relative(VIEWS_DIR, file)}`);
    }
  });
  console.log('All view files processed.');
});
