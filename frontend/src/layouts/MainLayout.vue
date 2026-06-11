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
      <div class="topbar-context-shell" v-if="showContextSwitcher">
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
        <div v-if="isGuestOnly" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl text-yellow-800 text-sm font-medium flex items-start gap-3">
          <span class="text-xl">⚠️</span>
          <div>
            <p class="font-bold mb-0.5">Limited Access</p>
            <p>You have limited access as a Guest. Contact <a :href="'mailto:' + contactEmail" class="underline font-bold">{{ contactEmail }}</a> to upgrade your role.</p>
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
    const uRes = await api.get('/universities')
    const cRes = await api.get('/campuses')
    const fRes = await api.get('/faculties')
    const dRes = await api.get('/departments')
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
  const s = role.replace(/_/g, ' ')
  return s.charAt(0).toUpperCase() + s.slice(1)
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

const isSuperAdminOnly = computed(() => {
  if (!auth.userRoles) return false
  // Only pure super_admin — not if they also hold an institutional role
  return auth.userRoles.includes('super_admin') &&
    !['research_admin','campus_admin','faculty_admin','department_head','director',
      'researcher','reviewer','student','finance_officer','ethics_officer'].some(r => auth.userRoles.includes(r))
})

const isGuestOnly = computed(() => {
  if (!auth.userRoles) return true
  return auth.userRoles.length === 1 && auth.userRoles[0] === 'guest'
})

const showContextSwitcher = computed(() => {
  if (isGuestOnly.value) return false
  if (auth.userRoles?.includes('super_admin')) return false
  return true
})

const contactEmail = computed(() => lookupStore.getSetting('contact_email', 'admin@rdrims.local'))

// ═══════════════════════════════════════════════════════════════
// FIXED: Guest navigation now has Dashboard & Notifications
// as a separate group above "Community & Public"
// ═══════════════════════════════════════════════════════════════
const navigation = computed(() => {
  // ── PLATFORM LAYER: Pure Super Admin ─────────────────────────────────
  if (isSuperAdminOnly.value) {
    return [
      { items: [
        { name: 'Dashboard',    path: '/app/dashboard',    icon: icons.home },
        { name: 'Notifications', path: '/app/notifications', icon: icons.events },
      ]},
      { title: 'Platform Management', items: [
        { name: 'Universities',  path: '/app/universities', icon: icons.academic },
        { name: 'System Settings', path: '/app/settings',   icon: icons.perms },
      ]},
      { title: 'Access Control', items: [
        { name: 'Roles',       path: '/app/roles',       icon: icons.roles },
        { name: 'Permissions', path: '/app/permissions', icon: icons.perms },
      ]},
      { title: 'Administration', items: [
        { name: 'System Logs', path: '/app/audit-logs',  icon: icons.audit },
      ]},
    ]
  }

  // ── GUEST LAYER ──────────────────────────────────────────────────────
  if (isGuestOnly.value) {
    return [
      // First group: Dashboard & Notifications (no title = no collapsible header)
      {
        items: [
          { name: 'Dashboard', path: '/app/dashboard', icon: icons.home },
          { name: 'Notifications', path: '/app/notifications', icon: icons.events },
        ]
      },
      // Second group: Community & Public (with collapsible title)
      {
        title: 'Community & Public',
        items: [
          { name: 'Call for Proposals', path: '/app/calls', icon: icons.calls },
          { name: 'Publications', path: '/app/publications', icon: icons.publications },
          { name: 'Events', path: '/app/events', icon: icons.events },
          { name: 'Community Problems', path: '/app/community-problems', icon: icons.community },
        ]
      }
    ]
  }

  // ── INSTITUTIONAL LAYER ───────────────────────────────────────────
  // (researcher, reviewer, student, department_head, director,
  //  faculty_admin, campus_admin, research_admin, finance_officer, ethics_officer)
  const institutionalAdmins = ['research_admin','campus_admin','faculty_admin','department_head','director']
  const isInstitutionalAdmin = auth.userRoles?.some(r => institutionalAdmins.includes(r))

  const nav = [
    {
      items: [
        { name: 'Dashboard', path: '/app/dashboard', icon: icons.home },
        { name: 'Notifications', path: '/app/notifications', icon: icons.events },
      ]
    },
    {
      title: 'Research',
      items: [
        { name: 'Call for Proposals', path: '/app/calls', icon: icons.calls },
        { name: 'Proposals',     path: '/app/proposals',    icon: icons.proposals },
        { name: 'Projects',      path: '/app/projects',     icon: icons.projects },
        { name: 'Outputs',       path: '/app/outputs',      icon: icons.outputs },
        { name: 'Publications',  path: '/app/publications', icon: icons.publications },
        { name: 'Patents & IP',  path: '/app/patents',      icon: icons.patents },
      ]
    },
    {
      title: 'Community',
      items: [
        { name: 'Partners',            path: '/app/partners',           icon: icons.partners },
        { name: 'Community Problems',  path: '/app/community-problems',  icon: icons.community },
        { name: 'Events',              path: '/app/events',             icon: icons.events },
      ]
    }
  ]

  // Reviews: any user can be a reviewer, so show for all institutional users
  {
    const researchGroup = nav.find(g => g.title === 'Research')
    if (researchGroup) {
      const proposalsIdx = researchGroup.items.findIndex(i => i.name === 'Proposals')
      if (proposalsIdx !== -1) {
        researchGroup.items.splice(proposalsIdx + 1, 0, { name: 'Reviews', path: '/app/reviewer/proposals', icon: icons.criteria })
      }
    }
  }
  
  // Rules group for ethics and detection (institutional only — no super_admin)
  if (auth.userRoles?.some(r => ['research_admin', 'campus_admin', 'faculty_admin', 'ethics_officer'].includes(r))) {
    nav.push({
      title: 'Rules',
      items: [
        { name: 'Ethics Review',  path: '/app/ethics-requests',    icon: icons.ethics },
        { name: 'Writing Check',  path: '/app/detection-requests', icon: icons.detect },
      ]
    })
  }

  // Finance group (institutional only — no super_admin)
  if (auth.userRoles?.some(r => ['research_admin', 'campus_admin', 'faculty_admin', 'finance_officer'].includes(r))) {
    nav.push({
      title: 'Finance',
      items: [
        { name: 'Funding Check', path: '/app/finance-checks', icon: icons.finance },
      ]
    })
  }

  // Reports for institutional admins only (no super_admin)
  if (auth.userRoles?.some(r => ['research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head', 'finance_officer'].includes(r))) {
    nav.push({
      title: 'Reports',
      items: [
        { name: 'Reports', path: '/app/reports', icon: icons.files },
      ]
    })
  }

  // Administration group (institutional only — no super_admin)
  if (auth.userRoles?.some(r => ['research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director'].includes(r))) {
    nav.push({
      title: 'Administration',
      items: [
        { name: 'Roles',  path: '/app/institution/roles', icon: icons.roles },
        { name: 'Users',  path: '/app/users',             icon: icons.users },
      ]
    })
  }
    
  // Settings: institutional hierarchy management (research_admin only, not super_admin)
  if (auth.userRoles?.some(r => ['research_admin'].includes(r))) {
    nav.push({
      title: 'Settings',
      items: [
        { name: 'Academic Years',   path: '/app/academic-years',   icon: icons.academic },
        { name: 'Research Centers', path: '/app/research-centers', icon: icons.centers },
        { name: 'Expertise Tags',   path: '/app/expertise',        icon: icons.criteria },
        { name: 'Review Criteria',  path: '/app/review-criteria',  icon: icons.criteria },
        { name: 'Departments',      path: '/app/departments',      icon: icons.dept },
        { name: 'Faculties',        path: '/app/faculties',        icon: icons.dept },
        { name: 'Campuses',         path: '/app/campuses',         icon: icons.centers },
        { name: 'Files',            path: '/app/files',            icon: icons.files },
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
  background: var(--color-surface-2);
}

/* ── Top Bar ─────────────────────────────────── */
.topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: var(--topbar-h);
  background: var(--color-glass-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  z-index: 50;
  gap: 20px;
  box-shadow: var(--shadow-sm);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-shrink: 0;
}

.icon-btn {
  width: 40px; height: 40px;
  display: flex; align-items: center; justify-content: center;
  border-radius: 12px;
  border: 1px solid transparent;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.icon-btn:hover { 
  background: #f1f5f9; 
  color: var(--color-brand);
  border-color: var(--color-border);
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
}
.brand-icon {
  width: 36px; height: 36px;
  background: var(--color-brand);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 18px;
}
.brand-text {
  display: flex; flex-direction: column; line-height: 1.1;
}
.brand-name {
  font-size: 16px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em;
}
.brand-sub {
  font-size: 10px; color: var(--text-muted); font-weight: 600; margin-top: 1px;
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
  position: absolute; left: 16px;
  color: var(--text-muted); pointer-events: none;
}
.search-input {
  width: 100%;
  padding: 12px 42px 12px 46px;
  border: 1px solid var(--color-border);
  border-radius: 14px;
  font-size: 13.5px;
  color: var(--text-primary);
  background: #f1f5f9;
  outline: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.search-input:focus {
  border-color: var(--color-brand);
  background: #fff;
  box-shadow: 0 0 0 4px var(--color-brand-light);
}
.search-kbd {
  position: absolute; right: 14px;
  font-size: 10px; color: var(--text-muted);
  background: #fff; border: 1px solid var(--color-border);
  border-radius: 6px; padding: 3px 7px;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
}

/* Context Switcher */
.topbar-context-shell {
  display: flex;
  align-items: center;
  gap: 12px;
  background: white;
  border: 1px solid var(--color-border);
  border-radius: 14px;
  padding: 4px 14px;
  margin-left: 20px;
  margin-right: 0;
  height: 44px;
  box-shadow: var(--shadow-sm);
  max-width: 600px;
  overflow: hidden;
}
.context-label {
  font-size: 10px;
  font-weight: 700;
  color: var(--color-brand);
  background: var(--color-brand-light);
  padding: 3px 8px;
  border-radius: 6px;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.context-breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
}
.context-item {
  display: flex;
  align-items: center;
  gap: 6px;
}
.context-sep {
  color: var(--text-muted);
  font-size: 16px;
  opacity: 0.4;
  font-weight: 300;
}
.context-select {
  border: none;
  background: transparent;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-primary);
  outline: none;
  cursor: pointer;
  max-width: 140px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  padding-right: 4px;
}
.context-select:hover {
  color: var(--color-brand);
}
.context-reset {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: none;
  background: #fef2f2;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.context-reset:hover {
  background: #fee2e2;
  color: #dc2626;
  transform: scale(1.1);
}

.topbar-right {
  display: flex; align-items: center; gap: 12px; flex-shrink: 0;
}
.topbar-divider {
  width: 1px; height: 36px;
  background: var(--color-border); margin: 0 8px;
}

.profile-btn {
  display: flex; align-items: center; gap: 12px;
  padding: 6px 16px 6px 6px;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
  border: 1px solid transparent;
}
.profile-btn:hover { 
  background: #fff; 
  border-color: var(--color-border);
  box-shadow: var(--shadow-sm); 
}

.avatar {
  width: 38px; height: 38px;
  background: var(--color-brand);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; font-weight: 700;
  color: white; border: 2px solid #fff;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.profile-info {
  display: flex; flex-direction: column; line-height: 1.2;
}
.profile-name {
  font-size: 14px; font-weight: 800; color: var(--text-primary);
}
.profile-role {
  font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;
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
  border-right: 1px solid var(--color-border);
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
  padding: 24px 0;
  scrollbar-width: none;
}
.sidebar-nav::-webkit-scrollbar { display: none; }

.nav-group { margin-bottom: 28px; }
.nav-group-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 32px;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--text-muted);
}
.nav-group-label {
  font-size: 10px;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.sidebar-link {
  display: flex !important;
  align-items: center;
  gap: 14px;
  padding: 12px 32px;
  margin: 2px 14px;
  border-radius: 12px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-link:hover {
  background: #f8fafc;
  color: var(--color-brand);
}
.sidebar-link.router-link-exact-active {
  background: var(--color-brand-light);
  color: var(--color-brand);
  font-weight: 800;
}
.sidebar-link.router-link-exact-active .nav-icon {
  color: var(--color-brand);
}

.nav-icon {
  color: var(--text-muted);
  width: 20px;
  display: flex;
  justify-content: center;
}

.sidebar-footer {
  padding: 24px 32px;
  border-top: 1px solid var(--color-border);
  background: #fcfdfe;
}
.sidebar-footer-inner {
  display: flex; align-items: center; gap: 12px;
  font-size: 12px; color: var(--text-secondary); font-weight: 700;
}
.footer-dot {
  width: 10px; height: 10px;
  background: var(--color-success);
  border-radius: 50%;
  box-shadow: 0 0 0 4px rgba(16,185,129,0.1);
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
  background: var(--color-surface-2);
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
  .main-content { padding: 32px; }
  .topbar { padding: 0 24px; }
}

@media (max-width: 767px) {
  .topbar-search { display: none; }
  .profile-info  { display: none; }
  .sidebar { width: 280px; }
  .sidebar-closed { transform: translateX(-100%); }
  .main-content { margin-left: 0 !important; padding: 24px; }
  .brand-text { display: none; }
  .topbar-context-shell { display: none; }
}
</style>