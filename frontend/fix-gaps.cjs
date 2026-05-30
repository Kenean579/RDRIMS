const fs = require('fs');
const path = require('path');

// 1. CreateProposalView.vue
const createPropFile = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src\\views\\proposals\\CreateProposalView.vue';
if (fs.existsSync(createPropFile)) {
    let content = fs.readFileSync(createPropFile, 'utf8');
    
    // Add file input to the form
    if (!content.includes('form.proposal_file')) {
        content = content.replace(
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">',
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">\n          <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Proposal Document (PDF) *</label><input type="file" @change="e => form.proposal_file = e.target.files[0]" required accept=".pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white" /></div>'
        );

        content = content.replace(
            "const form = reactive({ call_id: '', type_id: '', academic_year_id: '', title: '', keywords: '', abstract: '', objectives: '', methodology: '', budget: null, investigators: [] })",
            "const form = reactive({ call_id: '', type_id: '', academic_year_id: '', title: '', keywords: '', abstract: '', objectives: '', methodology: '', budget: null, proposal_file: null, investigators: [] })"
        );

        // Replace payload creation for formdata
        content = content.replace(
            /const payload = { call_id[\s\S]*?};/,
            `const payload = new FormData();
        if(form.call_id) payload.append('call_id', form.call_id);
        payload.append('type_id', form.type_id);
        if(form.academic_year_id) payload.append('academic_year_id', form.academic_year_id);
        payload.append('title', form.title);
        payload.append('keywords', form.keywords);
        payload.append('abstract', form.abstract);
        payload.append('objectives', form.objectives);
        payload.append('methodology', form.methodology);
        payload.append('budget', form.budget);
        if (form.proposal_file) payload.append('proposal_file', form.proposal_file);
        payload.append('investigators', JSON.stringify(form.investigators.map(inv => ({ user_id: inv.user_id || null, name: inv.name || null, email: inv.email || null, role_id: inv.role_id }))));`
        );
        
        fs.writeFileSync(createPropFile, content);
        console.log('Fixed CreateProposalView.vue');
    }
}

// 2. ProposalDetailView.vue
const propDetailFile = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src\\views\\proposals\\ProposalDetailView.vue';
if (fs.existsSync(propDetailFile)) {
    let content = fs.readFileSync(propDetailFile, 'utf8');

    // Add buttons
    if (!content.includes('Convert To Project')) {
        content = content.replace(
            '<button v-if="proposal.status?.name === \'draft\' && isOwner"',
            `<button v-if="auth.hasRole('super_admin','research_admin')" @click="checkOriginality" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-600/20">Check Originality</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && proposal.status?.name === 'approved'" @click="convertToProject" class="btn bg-teal-500 hover:bg-teal-600 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-teal-500/20">Convert To Project</button>
        <button v-if="proposal.status?.name === 'draft' && isOwner"`
        );
        
        // Setup logic inside script
        content = content.replace(
            'const isOwner = computed(() => auth.user?.id === proposal.value.submitted_by?.id)',
            `const isOwner = computed(() => auth.user?.id === proposal.value.submitted_by?.id)
            import { useRouter } from 'vue-router'
            const router = useRouter()
            async function convertToProject() {
              try {
                const {data} = await api.post('/proposals/' + proposal.value.id + '/create-project', {});
                notif.success('Converted to Project successfully!');
                router.push('/projects/' + data.id);
              } catch(e) {
                notif.error(e.response?.data?.message || 'Failed to convert');
              }
            }
            async function checkOriginality() {
              try {
                await api.post('/detection-requests', { detectable_type: 'App\\\\Models\\\\Proposal', detectable_id: proposal.value.id });
                notif.success('Detection request submitted!');
              } catch(e) {
                notif.error('Failed to submit detection check');
              }
            }`
        );

        // Fix suggest reviewers api
        content = content.replace(
            "const { data } = await api.get('/users', { params: { role: 'reviewer', per_page: 100 } });",
            "const { data } = await api.get(`/proposals/${route.params.id}/suggest-reviewers`);"
        );
        
        fs.writeFileSync(propDetailFile, content);
        console.log('Fixed ProposalDetailView.vue');
    }
}

// 3. ProjectDetailView.vue
const projDetailFile = 'd:\\proje\\qelemeda\\RDRIMS\\frontend\\src\\views\\projects\\ProjectDetailView.vue';
if (fs.existsSync(projDetailFile)) {
    let content = fs.readFileSync(projDetailFile, 'utf8');

    if (!content.includes('TaskManagement')) {
        content = content.replace(
            '<div class="flex flex-col items-end gap-1">\n                    <StatusBadge :status="m.status?.name" />\n                    <button @click="openTasks(m)" class="text-[10px] text-blue-600 font-bold hover:underline">TASKS</button>\n                 </div>',
            `<div class="flex flex-col items-end gap-1">
                    <StatusBadge :status="m.status?.name" />
                    <button @click="m.showTasks = !m.showTasks" class="text-[10px] text-blue-600 font-bold hover:underline">TASKS ({{ m.tasks?.length || 0 }})</button>
                 </div>
              </div>
              <div v-if="m.showTasks" class="mt-3 pl-12 pr-4 pb-3 space-y-2 border-t border-slate-100 pt-3">
                 <div v-for="t in (m.tasks || [])" :key="t.id" class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-3">
                       <input type="checkbox" :checked="t.status?.name === 'done'" @change="toggleTask(t)" class="rounded text-brand focus:ring-brand w-4 h-4 cursor-pointer" />
                       <span class="text-xs font-bold text-slate-700" :class="{'line-through text-slate-400': t.status?.name === 'done'}">{{ t.title }}</span>
                    </div>
                    <span class="text-[9px] font-black uppercase text-slate-400">{{ t.assigned_to?.name || 'Unassigned' }}</span>
                 </div>
                 <div class="flex items-center gap-2 mt-2">
                    <input v-model="newTaskTitles[m.id]" type="text" placeholder="New task..." class="flex-1 border border-slate-200 rounded px-2 py-1 text-xs outline-none focus:border-brand" @keyup.enter="addTask(m)" />
                    <button @click="addTask(m)" class="btn btn-secondary px-3 py-1 text-[10px] font-black uppercase">Add</button>
                 </div>`
        );
        
        content = content.replace(
            "const milestoneForm = reactive({ title: '', due_date: '', percentage: 10 })",
            `const milestoneForm = reactive({ title: '', due_date: '', percentage: 10 })
             const newTaskTitles = reactive({})
             
             async function toggleTask(task) {
               try {
                 const newStatus = task.status?.name === 'done' ? 6 : 7; // Status ID mapping (dummy)
                 await api.put('/tasks/' + task.id, { status_id: newStatus });
                 fetchProject();
               } catch(e) { notif.error('Failed modifying task'); }
             }
             
             async function addTask(m) {
               if (!newTaskTitles[m.id]) return;
               try {
                 await api.post('/tasks', { milestone_id: m.id, title: newTaskTitles[m.id], status_id: 6 }); // 6 = not_started
                 newTaskTitles[m.id] = '';
                 fetchProject();
               } catch(e) { notif.error('Failed generating task'); }
             }`
        );

        fs.writeFileSync(projDetailFile, content);
        console.log('Fixed ProjectDetailView.vue');
    }
}
