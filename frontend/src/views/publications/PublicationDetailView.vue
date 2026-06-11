<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/app/publications" class="flex items-center gap-2 text-brand font-bold text-xs mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Publications
        </router-link>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-tight max-w-3xl">{{ pub.title || 'Publication Detail' }}</h1>
        <div class="flex items-center gap-3 mt-3">
          <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 text-xs font-medium rounded-2xl">Publication</span>
          <span v-if="pub.doi" class="text-xs text-slate-400 font-medium">DOI: {{ pub.doi }}</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-bold">
        <div class="lg:col-span-2 space-y-8">
          <!-- Main Details -->
          <div class="card p-8-500/20">
            <h2 class="text-xs font-medium text-slate-400 mb-5 flex items-center gap-2">
              <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
              Core Information
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
              <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Full Title</dt>
                <dd class="text-base font-bold text-slate-800 bg-slate-50 p-5 rounded-2xl border border-slate-100 leading-snug">{{ pub.title }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Source Venue</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 text-slate-700">
                   <div class="flex items-center gap-2 mb-1">
                      <i class="fas fa-scroll text-indigo-400"></i>
                      <span class="font-bold text-slate-900">{{ pub.journal || 'N/A' }}</span>
                   </div>
                   <p class="text-xs opacity-60">Published: {{ pub.publication_date }}</p>
                </dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Impact & Mentions</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100">
                   <div class="flex items-center gap-2">
                      <i class="fas fa-quote-left text-slate-300"></i>
                      <span class="text-slate-700">Citations: <span class="text-brand font-bold">{{ pub.citation_count || 0 }}</span></span>
                   </div>
                </dd>
              </div>
            </dl>
          </div>

          <!-- Abstract -->
          <div v-if="pub.abstract" class="card p-8">
             <h2 class="text-xs font-medium text-slate-400 mb-5 flex items-center gap-2">
               <span class="w-1 h-3 bg-slate-300 rounded-full"></span>
               Abstract Summary
             </h2>
             <div class="p-6 bg-slate-50 rounded-2xl text-slate-600 text-sm leading-relaxed font-medium italic">
                {{ pub.abstract }}
             </div>
          </div>
        </div>

        <!-- Sidebar: Authors -->
        <div class="space-y-8">
          <div class="card p-8 bg-slate-50/50">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xs font-medium text-slate-400">Authorship Team</h2>
              <button @click="showAddAuthor = true" class="text-xs font-medium text-brand hover:underline">+ Add Member</button>
            </div>
            
            <div v-if="pub.authors?.length" class="space-y-4">
              <div v-for="author in pub.authors" :key="author.id" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 group shadow-sm">
                <div class="min-w-0">
                  <p class="text-xs font-bold text-slate-800 truncate mb-0.5">{{ author.user?.name || author.external_author_name }}</p>
                  <p class="text-xs text-slate-400">Priority Order: {{ author.author_order }}</p>
                </div>
                <button @click="removeAuthor(author)" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-full transition-all">
                  <i class="fas fa-times text-xs"></i>
                </button>
              </div>
            </div>
            <div v-else class="p-5 text-center bg-white rounded-2xl border border-dashed border-slate-100">
               <p class="text-xs font-medium text-slate-400">No authors added.</p>
            </div>
          </div>
          
          <div v-if="pub.url" class="card p-8 bg-slate-100 text-white shadow-xl shadow-indigo-500/20">
             <h3 class="text-xs font-bold mb-4 opacity-80">Online Access</h3>
             <p class="text-sm font-medium mb-6 leading-snug">Full document is available at the publisher's site.</p>
             <a :href="pub.url" target="_blank" class="btn w-full bg-white text-indigo-700 text-xs font-medium h-11 border-0 shadow-lg">Open Full Text</a>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showAddAuthor" title="Add Author" @close="showAddAuthor = false">
      <form @submit.prevent="addAuthor" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">User (or leave empty for external)</label><select v-model="authorForm.user_id" class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">External</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></div>
        <div v-if="!authorForm.user_id"><label class="block text-sm font-medium text-gray-700 mb-1">External Name *</label><input v-model="authorForm.external_author_name" type="text" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Author Order *</label><input v-model.number="authorForm.author_order" type="number" required min="1" class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="flex justify-end gap-3"><button type="button" @click="showAddAuthor = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Add</button></div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import Modal from '@/components/Modal.vue'
import { formatDate } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const pub = ref({}); const loading = ref(true)
const showAddAuthor = ref(false)
const authorForm = ref({ user_id: '', external_author_name: '', external_institution: '', author_order: 1 })
const users = ref([])

async function fetchPub() {
  loading.value = true
  try { const { data } = await api.get(`/publications/${route.params.id}`); pub.value = data }
  catch (e) {} finally { loading.value = false }
}

async function addAuthor() {
  try { await api.post(`/publications/${pub.value.id}/authors`, authorForm.value); notif.success('Author added!'); showAddAuthor.value = false; fetchPub() }
  catch (err) { notif.error('Failed') }
}

async function removeAuthor(author) {
  try { await api.delete(`/publications/${pub.value.id}/authors/${author.id}`); notif.success('Removed!'); fetchPub() }
  catch (err) { notif.error('Failed') }
}

onMounted(async () => {
  await fetchPub()
  try { const { data } = await api.get('/users',{params:{per_page:200}}); users.value = data.data || data } catch (e) {}
})
</script>
