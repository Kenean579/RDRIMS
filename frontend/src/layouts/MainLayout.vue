<template>
  <div class="app-shell">
    <!-- ── Top Bar ────────────────────────────────────── -->
    <header class="topbar">
      <div class="topbar-left gap-4">
        <!-- Hamburger -->
        <button class="icon-btn mr-2" @click="sidebarOpen = !sidebarOpen" title="Toggle sidebar">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>

        <!-- Logo -->
        <router-link to="/" class="brand">
          <div class="brand-icon">R</div>
          <div class="brand-text">
            <span class="brand-name">RDRIMS</span>
            <span class="brand-sub">{{ appName }}</span>
          </div>
        </router-link>
      </div>

      <!-- Search -->
      <div class="topbar-search">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search items…"
          class="search-input"
          @keyup.enter="goSearch"
        />
        <kbd class="search-kbd">⌵</kbd>
      </div>

      <!-- Hierarchical Context Switcher -->
      <div class="topbar-context-shell" v-if="!isGuestOnly">
        <label class="context-label">Context</label>
        <div class="context-breadcrumb">
          <!-- University -->
          <div class="context-item">
            <select :value="context.university_id" @change="e => context.setUniversity(e.target.value)" class="context-select">
              <option value="">All Universities</option>
              <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          
          <!-- Campus -->
          <div class="context-item" v-if="context.university_id">
            <span class="context-sep">/</span>
            <select :value="context.campus_id" @change="e => context.setCampus(e.target.value)" class="context-select">
              <option value="">All Campuses</option>
              <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Faculty -->
          <div class="context-item" v-if="context.campus_id">
            <span class="context-sep">/</span>
            <select :value="context.faculty_id" @change="e => context.setFaculty(e.target.value)" class="context-select">
              <option value="">All Faculties</option>
              <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
            </select>
          </div>

          <!-- Department -->
          <div class="context-item" v-if="context.faculty_id">
            <span class="context-sep">/</span>
            <select :value="context.department_id" @change="e => context.setDepartment(e.target.value)" class="context-select">
              <option value="">All Departments</option>
              <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
        
        <button v-if="context.university_id" @click="context.resetContext" class="context-reset" title="Reset Context">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <div class="topbar-right">
        <!-- Notifications -->
        <router-link to="/app/notifications" class="icon-btn notif-btn" title="Notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span v-if="unreadCount > 0" class="notif-dot">{{ unreadCount > 9 ? '9+' : unreadCount }}</span>
        </router-link>

        <!-- Divider -->
        <div class="topbar-divider"></div>

        <!-- Profile -->
        <router-link to="/app/profile" class="profile-btn" title="My Profile">
          <div class="avatar">{{ getInitials(auth.user?.name) }}</div>
          <div class="profile-info">
            <span class="profile-name">{{ auth.user?.name || 'Administrator' }}</span>
            <span class="profile-role">{{ formatRole(auth.primaryRole) }}</span>
          </div>
        </router-link>

        <!-- Logout -->
        <button class="icon-btn text-red-400 hover:text-red-300" @click="logout" title="Sign out">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </header>

    <!-- ── Body ──────────────────────────────────────── -->
    <div class="app-body">

      <!-- Overlay for mobile -->
      <div v-if="sidebarOpen && isMobile" class="sidebar-overlay" @click="sidebarOpen = false"></div>

       <!-- ── Sidebar ──────────────────────────────── -->
      <aside class="sidebar" :class="{ 'sidebar-closed': !sidebarOpen }">
        <nav class="sidebar-nav">
          <div v-for="(group, gi) in navigation" :key="gi" class="nav-group">
            <!-- Group Header (Closable) -->
            <button 
              v-if="group.title" 
              class="nav-group-header" 
              @click="toggleGroup(group.title)"
            >
              <span class="nav-group-label">{{ group.title }}</span>
              <svg 
                class="w-3.5 h-3.5 transition-transform duration-200" 
                :class="{ 'rotate-180': openGroups[group.title] }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Group Items -->
            <div 
              v-show="!group.title || openGroups[group.title]" 
              class="nav-group-items-wrapper"
              :class="{ 'has-title': group.title }"
            >
              <router-link
                v-for="item in group.items"
                :key="item.path"
                :to="item.path"
                class="sidebar-link"
                :class="{ 'exact-active': $route.path === item.path }"
                @click="isMobile && (sidebarOpen = false)"
              >
                <span class="nav-icon" v-html="item.icon"></span>
                <span>{{ item.name }}</span>
              </router-link>
            </div>
          </div>
        </nav>

        <div class="sidebar-footer">
          <div class="sidebar-footer-inner">
            <span class="footer-dot"></span>
            System Online
          </div>
        </div>
      </aside>

      <!-- ── Main Content ─────────────────────────── -->
      <main class="main-content" :class="{ 'main-expanded': !sidebarOpen }">
        <div v-if="isGuestOnly" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm font-medium flex items-start gap-3">
          <span class="text-xl">⚠️</span>
          <div>
            <p class="font-bold mb-0.5">Limited Access</p>
            <p>You have limited access as a Guest. Contact your university administrator to upgrade your role.</p>
          </div>
        </div>

        <router-view :key="viewKey" v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLookupStore } from '@/stores/lookup'
import { useContextStore } from '@/stores/context'
import { getInitials } from '@/utils/formatters'
import api from '@/services/api'

const router  = useRouter()
const route   = useRoute()
const auth    = useAuthStore()
const lookupStore = useLookupStore()
const appName = computed(() => lookupStore.getSetting('app_name', 'Research Portal'))

const sidebarOpen  = ref(true)
const unreadCount  = ref(0)
const searchQuery  = ref('')
const isMobile     = ref(false)
const openGroups   = reactive({})
const context = useContextStore()

const universities = ref([])
const campuses     = ref([])
const faculties    = ref([])
const departments  = ref([])
const viewKey      = ref(0)

const filteredCampuses = computed(() => campuses.value.filter(c => String(c.university_id) === String(context.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(f => String(f.campus_id) === String(context.campus_id)))
const filteredDepartments = computed(() => departments.value.filter(d => String(d.faculty_id) === String(context.faculty_id)))

// Watch context to refresh views
watch(() => [context.university_id, context.campus_id, context.faculty_id, context.department_id], () => {
  viewKey.value++
})

async function fetchContextOptions() {
  try {
    const [uRes, cRes, fRes, dRes] = await Promise.all([
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments')
    ])
    universities.value = uRes.data
    campuses.value     = cRes.data
    faculties.value    = fRes.data
    departments.value  = dRes.data
  } catch (e) {}
}

function toggleGroup(title) {
  openGroups[title] = !openGroups[title]
}

function checkMobile() {
  isMobile.value = window.innerWidth < 768
  if (isMobile.value) sidebarOpen.value = false
  else sidebarOpen.value = true
}

function goSearch() {
  if (searchQuery.value.trim()) {
    router.push({ path: '/app/search', query: { q: searchQuery.value } })
    searchQuery.value = ''
  }
}

function formatRole(role) {
  if (!role) return 'User'
  return role.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

function logout() {
  auth.logout?.()
  router.push('/login')
}

async function fetchUnreadCount() {
  try {
    const { data } = await api.get('/notifications?unread=1')
    unreadCount.value = data.total || data.length || 0
  } catch (e) {}
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  fetchUnreadCount()
  fetchContextOptions()
  
  // Initialize all groups to open by default
  navigation.value.forEach(g => {
    if (g.title) openGroups[g.title] = true
  })
})
onUnmounted(() => window.removeEventListener('resize', checkMobile))

// ── SVG Icons (inline) ──────────────────────────────────────
const icons = {
  home:     `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
  calls:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9.81a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 7.6 7.6l.87-1.87a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>`,
  proposals:`<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  projects: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
  publications:`<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
  patents:  `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  outputs:  `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>`,
  partners: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  community:`<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
  events:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  ethics:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  detect:   `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>`,
  finance:  `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`,
  users:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  roles:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`,
  perms:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 1 0 21 12h-1"/></svg>`,
  academic: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`,
  thematic: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>`,
  centers:  `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
  criteria: `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>`,
  dept:     `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>`,
  files:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
  audit:    `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
}

const isGuestOnly = computed(() => {
  if (!auth.userRoles) return true
  return auth.userRoles.length === 1 && auth.userRoles[0] === 'guest'
})

const navigation = computed(() => {
  if (isGuestOnly.value) {
    return [
      {
        title: 'Community & Public',
        items: [
          { name: 'Funding Calls', path: '/app/calls', icon: icons.calls },
          { name: 'Publications', path: '/app/publications', icon: icons.publications },
          { name: 'News & Events', path: '/app/events', icon: icons.events },
          { name: 'Community', path: '/app/community-problems', icon: icons.community },
        ]
      }
    ]
  }

  const nav = [
    {
      items: [{ name: 'Dashboard', path: '/app/dashboard', icon: icons.home }]
    },
    {
      title: 'Research',
      items: [
        { name: 'Funding Calls', path: '/app/calls',        icon: icons.calls },
        { name: 'Proposals',     path: '/app/proposals',    icon: icons.proposals },
        { name: 'Projects',      path: '/app/projects',     icon: icons.projects },
        { name: 'Publications',   path: '/app/publications', icon: icons.publications },
      ]
    },
    {
      title: 'Patents',
      items: [
        { name: 'Patents & IP',   path: '/app/patents',  icon: icons.patents },
        { name: 'Innovation',     path: '/app/outputs',  icon: icons.outputs },
      ]
    },
    {
      title: 'Community',
      items: [
        { name: 'Partners',    path: '/app/partners',          icon: icons.partners },
        { name: 'Issues',      path: '/app/community-problems',  icon: icons.community },
        { name: 'News',        path: '/app/events',            icon: icons.events },
      ]
    }
  ]
  
  if (auth.hasRole?.('reviewer')) {
    const researchGroup = nav.find(g => g.title === 'Research')
    const proposalsIdx = researchGroup.items.findIndex(i => i.name === 'Proposals')
    researchGroup.items.splice(proposalsIdx + 1, 0, { name: 'Reviews', path: '/app/reviewer/proposals', icon: icons.criteria })
  }
  
  if (auth.hasRole?.('director')) {
    nav.splice(1, 0, {
      items: [{ name: 'My Research Center', path: '/app/research-centers/my-center', icon: icons.centers }]
    })
  }

  if (auth.hasRole?.('department_head')) {
    nav.splice(1, 0, {
      items: [{ name: 'My Department', path: '/app/departments/my-department', icon: icons.dept }]
    })
  }

  if (auth.hasRole?.('super_admin', 'research_admin', 'ethics_officer')) {
    nav.push({
      title: 'Rules',
      items: [
        { name: 'Ethics Review',  path: '/app/ethics-requests',    icon: icons.ethics },
        { name: 'Writing Check',  path: '/app/detection-requests', icon: icons.detect },
      ]
    })
  }

  if (auth.hasRole?.('super_admin', 'research_admin', 'finance_officer')) {
    nav.push({
      title: 'Finance',
      items: [
        { name: 'Funding Check', path: '/app/finance-checks', icon: icons.finance },
      ]
    })
  }

  if (auth.hasRole?.('super_admin', 'research_admin')) {
    nav.push({
      title: 'Users',
      items: [
        { name: 'All Users',   path: '/app/users',       icon: icons.users },
        { name: 'Roles',       path: '/app/roles',       icon: icons.roles },
        { name: 'Permissions', path: '/app/permissions', icon: icons.perms },
      ]
    })
    nav.push({
      title: 'Settings',
      items: [
        { name: 'Academic Years',   path: '/app/academic-years',   icon: icons.academic },
        { name: 'Research Areas',   path: '/app/thematic-areas',   icon: icons.thematic },
        { name: 'Research Centers', path: '/app/research-centers', icon: icons.centers },
        { name: 'Tags',             path: '/app/expertise',        icon: icons.criteria },
        { name: 'Review Rules',     path: '/app/review-criteria',  icon: icons.criteria },
        { name: 'Departments',      path: '/app/departments',      icon: icons.dept },
        { name: 'Faculties',        path: '/app/faculties',        icon: icons.dept },
        { name: 'Campuses',         path: '/app/campuses',         icon: icons.centers },
        { name: 'Universities',     path: '/app/universities',     icon: icons.academic },
        { name: 'Files',            path: '/app/files',            icon: icons.files },
        { name: 'Settings',         path: '/app/settings',         icon: icons.perms },
        { name: 'Lookups',          path: '/app/settings/lookups', icon: icons.criteria },
        { name: 'System Logs',      path: '/app/audit-logs',       icon: icons.audit },
      ]
    })
  }

  return nav
})
</script>

<style scoped>
/* ── App Shell ───────────────────────────────── */
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--surface-2);
}

/* ── Top Bar ─────────────────────────────────── */
.topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: var(--topbar-h);
  background: var(--glass-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  z-index: 50;
  gap: 16px;
  box-shadow: var(--shadow-sm);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-shrink: 0;
}

.icon-btn {
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 10px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.icon-btn:hover { 
  background: #f1f5f9; 
  color: var(--brand);
  border-color: var(--border);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}
.brand-icon {
  width: 34px; height: 34px;
  background: linear-gradient(135deg, var(--brand), #4f46e5);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 800; font-size: 16px;
  box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25);
}
.brand-text {
  display: flex; flex-direction: column; line-height: 1;
}
.brand-name {
  font-size: 15px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;
}
.brand-sub {
  font-size: 10.5px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;
}

/* Search Box Enhancement */
.topbar-search {
  flex: 1;
  max-width: 500px;
  position: relative;
  display: flex;
  align-items: center;
}
.search-icon {
  position: absolute; left: 14px;
  color: var(--text-muted); pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 10px 42px 10px 42px;
  border: 1px solid var(--border);
  border-radius: 12px;
  font-size: 13.5px;
  color: var(--text-primary);
  background: #f1f5f9;
  outline: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.search-input:focus {
  border-color: var(--brand);
  background: #fff;
  box-shadow: 0 0 0 4px var(--brand-light);
}
.search-kbd {
  position: absolute; right: 12px;
  font-size: 11px; color: var(--text-muted);
  background: #fff; border: 1px solid var(--border);
  border-radius: 6px; padding: 2px 6px;
  display: flex; align-items: center; justify-content: center;
}

/* Context Switcher */
.topbar-context-shell {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 4px 12px;
  margin-left: 20px;
  margin-right: -10px;
  height: 40px;
  box-shadow: var(--shadow-sm);
  max-width: 600px;
  overflow: hidden;
}
.context-label {
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--brand);
  background: var(--brand-light);
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
}
.context-breadcrumb {
  display: flex;
  align-items: center;
  gap: 4px;
}
.context-item {
  display: flex;
  align-items: center;
  gap: 4px;
}
.context-sep {
  color: var(--text-muted);
  font-size: 14px;
  opacity: 0.5;
}
.context-select {
  border: none;
  background: transparent;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--text-primary);
  outline: none;
  cursor: pointer;
  max-width: 130px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 4px;
}
.context-select:hover {
  color: var(--brand);
}
.context-reset {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: none;
  background: #fee2e2;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.context-reset:hover {
  background: #ef4444;
  color: white;
  transform: scale(1.1);
}

.topbar-right {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.topbar-divider {
  width: 1px; height: 32px;
  background: var(--border); margin: 0 8px;
}

.profile-btn {
  display: flex; align-items: center; gap: 12px;
  padding: 6px 14px 6px 6px;
  border-radius: 12px;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: 1px solid transparent;
}
.profile-btn:hover { 
  background: #fff; 
  border-color: var(--border);
  box-shadow: var(--shadow-sm); 
}

.avatar {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--brand), var(--accent));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800;
  color: white; border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.profile-info {
  display: flex; flex-direction: column; line-height: 1.25;
}
.profile-name {
  font-size: 13.5px; font-weight: 700; color: var(--text-primary);
}
.profile-role {
  font-size: 11px; color: var(--text-muted); font-weight: 500;
}

/* ── App Body ────────────────────────────────── */
.app-body {
  display: flex;
  flex: 1;
  padding-top: var(--topbar-h);
  position: relative;
}

/* ── Sidebar ─────────────────────────────────── */
.sidebar {
  width: var(--sidebar-w);
  flex-shrink: 0;
  background: #fff;
  position: fixed;
  top: var(--topbar-h);
  left: 0;
  bottom: 0;
  z-index: 40;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-closed {
  width: 0;
  transform: translateX(-100%);
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 20px 0;
  scrollbar-width: none;
}
.sidebar-nav::-webkit-scrollbar { display: none; }

.nav-group { margin-bottom: 24px; }
.nav-group-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 24px;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
}
.nav-group-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.sidebar-link {
  display: flex !important;
  align-items: center;
  gap: 12px;
  padding: 10px 24px;
  margin: 2px 12px;
  border-radius: 10px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-link:hover {
  background: #f8fafc;
  color: var(--brand);
}
.sidebar-link.router-link-exact-active {
  background: var(--brand-light);
  color: var(--brand);
  font-weight: 700;
}
.sidebar-link.router-link-exact-active .nav-icon {
  color: var(--brand);
}

.nav-icon {
  color: var(--text-muted);
}

.sidebar-footer {
  padding: 20px 24px;
  border-top: 1px solid var(--border);
  background: #fcfdfe;
}
.sidebar-footer-inner {
  display: flex; align-items: center; gap: 10px;
  font-size: 12.5px; color: var(--text-secondary); font-weight: 700;
}
.footer-dot {
  width: 10px; height: 10px;
  background: var(--success);
  border-radius: 50%;
  box-shadow: 0 0 0 3px rgba(16,185,129,0.1);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 0.7; transform: scale(0.9); }
  50% { opacity: 1; transform: scale(1.1); }
  100% { opacity: 0.7; transform: scale(0.9); }
}

/* ── Main Content ─────────────────────────────── */
.main-content {
  flex: 1;
  margin-left: var(--sidebar-w);
  padding: 40px;
  min-height: calc(100vh - var(--topbar-h));
  background: var(--surface-2);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 0;
}
.main-expanded { margin-left: 0; }

/* ── Page Transition ──────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: all 0.2s ease; }
.fade-enter-from { opacity: 0; transform: translateY(8px); }
.fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Responsive ───────────────────────────────── */
@media (max-width: 1024px) {
  .main-content { padding: 24px; }
}

@media (max-width: 767px) {
  .topbar-search { display: none; }
  .profile-info  { display: none; }
  .sidebar { width: 260px; }
  .sidebar-closed { transform: translateX(-100%); }
  .main-content { margin-left: 0 !important; padding: 20px; }
  .brand-text { display: none; }
}
</style>
