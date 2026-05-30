<template>
  <div card>
    <div class="mb-6">
      <router-link to="/publications" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Publications</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ pub.title || 'Publication Detail' }}</h1>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div class="sm:col-span-2"><dt class="text-gray-500">Title</dt><dd class="font-medium text-gray-800 mt-0.5">{{ pub.title }}</dd></div>
              <div><dt class="text-gray-500">Journal</dt><dd class="text-gray-800 mt-0.5">{{ pub.journal }}</dd></div>
              <div><dt class="text-gray-500">Publication Date</dt><dd class="text-gray-800 mt-0.5">{{ formatDate(pub.publication_date) }}</dd></div>
              <div v-if="pub.doi"><dt class="text-gray-500">DOI</dt><dd class="text-blue-600 mt-0.5">{{ pub.doi }}</dd></div>
              <div><dt class="text-gray-500">Citations</dt><dd class="text-gray-800 mt-0.5">{{ pub.citation_count }}</dd></div>
              <div v-if="pub.project"><dt class="text-gray-500">Project</dt><dd class="text-gray-800 mt-0.5">{{ pub.project?.title }}</dd></div>
            </dl>
            <div v-if="pub.keywords" class="mt-4"><p class="text-gray-500 text-sm mb-1">Keywords</p><p class="text-sm text-gray-800">{{ pub.keywords }}</p></div>
            <div v-if="pub.abstract" class="mt-4"><p class="text-gray-500 text-sm mb-1">Abstract</p><p class="text-sm text-gray-800 whitespace-pre-line">{{ pub.abstract }}</p></div>
          </div>
        </div>

        <div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-base font-semibold text-gray-800">Authors</h2>
              <button @click="showAddAuthor = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add</button>
            </div>
            <div v-if="pub.authors?.length" class="space-y-2">
              <div v-for="author in pub.authors" :key="author.id" class="flex items-center justify-between p-2 rounded hover:bg-gray-50">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ author.user?.name || author.external_author_name }}</p>
                  <p class="text-xs text-gray-500">Order: {{ author.author_order }}</p>
                </div>
                <button @click="removeAuthor(author)" class="text-red-500 text-xs hover:underline">Remove</button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No authors listed.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showAddAuthor" title="Add Author" @close="showAddAuthor = false">
      <form @submit.prevent="addAuthor" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">User (or leave empty for external)</label><select v-model="authorForm.user_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">External</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></div>
        <div v-if="!authorForm.user_id"><label class="block text-sm font-medium text-gray-700 mb-1">External Name *</label><input v-model="authorForm.external_author_name" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Author Order *</label><input v-model.number="authorForm.author_order" type="number" required min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="flex justify-end gap-3"><button type="button" @click="showAddAuthor = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Add</button></div>
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
