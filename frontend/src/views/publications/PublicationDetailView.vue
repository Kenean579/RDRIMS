<template>
  <div class="space-y-8 animate-fade pb-16">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/publications" class="flex items-center gap-2 text-brand font-black uppercase tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Publications
        </router-link>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight leading-tight max-w-3xl">
          {{ pub.title || 'Publication Detail' }}
        </h1>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
          {{ pub.journal || 'Journal Not Specified' }}
          <span v-if="pub.publication_date"> · Published {{ formatDate(pub.publication_date) }}</span>
        </p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl p-8 border border-slate-100 h-64 animate-pulse shadow-sm"></div>
        <div class="bg-white rounded-3xl p-8 border border-slate-100 h-40 animate-pulse shadow-sm"></div>
      </div>
      <div class="bg-white rounded-3xl p-8 border border-slate-100 h-64 animate-pulse shadow-sm"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Left: Main Details -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Abstract -->
          <div v-if="pub.abstract" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-40 h-40 bg-brand/5 rounded-full translate-x-1/3 -translate-y-1/3"></div>
            <h2 class="text-[11px] font-black text-brand uppercase tracking-[0.2em] mb-5 flex items-center gap-3">
              <span class="w-8 h-px bg-brand/30"></span> Abstract
            </h2>
            <p class="text-slate-600 leading-relaxed text-base italic relative z-10 whitespace-pre-line">
              {{ pub.abstract }}
            </p>
          </div>

          <!-- Metadata Grid -->
          <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
            <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span> Bibliographic Details
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Journal</p>
                <div class="font-black text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ pub.journal || 'N/A' }}</div>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Publication Date</p>
                <div class="font-black text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ formatDate(pub.publication_date) }}</div>
              </div>
              <div v-if="pub.doi">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">DOI</p>
                <a :href="`https://doi.org/${pub.doi}`" target="_blank" class="block font-black text-brand bg-brand/5 p-3 rounded-xl border border-brand/10 hover:underline truncate">
                  {{ pub.doi }}
                </a>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Citations</p>
                <div class="font-black text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ pub.citation_count ?? 0 }}</div>
              </div>
              <div v-if="pub.project">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Related Project</p>
                <router-link :to="`/projects/${pub.project.id}`" class="block font-black text-brand bg-brand/5 p-3 rounded-xl border border-brand/10 hover:underline truncate">
                  {{ pub.project.title }}
                </router-link>
              </div>
            </div>

            <!-- Keywords -->
            <div v-if="pub.keywords" class="mt-6 pt-6 border-t border-slate-100">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Thematic Keywords</p>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="tag in String(pub.keywords).split(',')"
                  :key="tag"
                  class="px-3 py-1.5 bg-slate-50 text-slate-600 rounded-xl border border-slate-200 text-xs font-black"
                >
                  {{ tag.trim() }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Authors -->
        <div class="space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
              <div>
                <h2 class="text-sm font-black text-slate-800">Authors</h2>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ pub.authors?.length || 0 }} contributors</p>
              </div>
              <button @click="showAddAuthor = true" class="flex items-center gap-1.5 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-brand bg-brand/10 rounded-xl hover:bg-brand/20 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add
              </button>
            </div>

            <div v-if="pub.authors?.length" class="divide-y divide-slate-50">
              <div
                v-for="author in pub.authors"
                :key="author.id"
                class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/80 group transition-all"
              >
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-black text-[11px] uppercase shrink-0 group-hover:bg-brand group-hover:text-white transition-all">
                    {{ (author.user?.name || author.external_author_name || '?').charAt(0) }}
                  </div>
                  <div>
                    <p class="text-sm font-black text-slate-800">{{ author.user?.name || author.external_author_name }}</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Order {{ author.author_order }}</p>
                  </div>
                </div>
                <button
                  @click="removeAuthor(author)"
                  class="text-[10px] font-black text-rose-400 hover:text-rose-600 uppercase tracking-widest transition-colors opacity-0 group-hover:opacity-100"
                >
                  Remove
                </button>
              </div>
            </div>
            <div v-else class="px-6 py-10 text-center">
              <p class="text-[11px] font-black text-slate-300 uppercase tracking-widest">No authors listed</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Add Author Modal -->
    <Modal :show="showAddAuthor" title="Add Author" @close="showAddAuthor = false">
      <form @submit.prevent="addAuthor" class="space-y-5 p-1">
        <div>
          <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">System User <span class="italic normal-case text-slate-300">(leave blank for external)</span></label>
          <select v-model="authorForm.user_id" class="input">
            <option value="">External Author</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div v-if="!authorForm.user_id">
          <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">External Full Name *</label>
          <input v-model="authorForm.external_author_name" type="text" required class="input" placeholder="e.g. Dr. Jane Doe" />
        </div>
        <div>
          <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Author Order *</label>
          <input v-model.number="authorForm.author_order" type="number" required min="1" class="input" placeholder="1" />
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="showAddAuthor = false" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Author</button>
        </div>
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

const route = useRoute()
const notif = useNotificationStore()
const pub = ref({})
const loading = ref(true)
const showAddAuthor = ref(false)
const authorForm = ref({ user_id: '', external_author_name: '', external_institution: '', author_order: 1 })
const users = ref([])

async function fetchPub() {
  loading.value = true
  try {
    const { data } = await api.get(`/publications/${route.params.id}`)
    pub.value = data
  } catch (e) {}
  finally { loading.value = false }
}

async function addAuthor() {
  try {
    await api.post(`/publications/${pub.value.id}/authors`, authorForm.value)
    notif.success('Author added!')
    showAddAuthor.value = false
    fetchPub()
  } catch (err) { notif.error('Failed to add author') }
}

async function removeAuthor(author) {
  try {
    await api.delete(`/publications/${pub.value.id}/authors/${author.id}`)
    notif.success('Author removed!')
    fetchPub()
  } catch (err) { notif.error('Failed to remove author') }
}

onMounted(async () => {
  await fetchPub()
  try {
    const { data } = await api.get('/users', { params: { per_page: 200 } })
    users.value = data.data || data
  } catch (e) {}
})
</script>
