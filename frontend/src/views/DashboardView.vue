<template>
  <div class="space-y-8 animate-fade pb-16">
    <!-- Header with Breadcrumbs & Date Selection -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
      <div class="absolute right-0 top-0 w-64 h-64 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Research Pulse</h1>
        <p class="text-slate-500 max-w-xl text-sm leading-relaxed">
          Welcome back, <span class="text-brand font-black">{{ auth.user?.name }}</span>. Here's a real-time overview of the institutional research ecosystem.
        </p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3 relative z-10">
        <div class="relative group min-w-[200px]">
          <select 
            v-model="selectedYear" 
            @change="fetchDashboard" 
            class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-700 focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all appearance-none cursor-pointer"
          >
            <option value="">Aggregate All-Time</option>
            <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}{{ y.is_current ? ' (Current)' : '' }}</option>
          </select>
          <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
          </div>
        </div>
        
        <button 
          @click="fetchDashboard" 
          class="bg-brand text-white p-3 rounded-2xl shadow-lg shadow-brand/20 hover:scale-105 active:scale-95 transition-all"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        </button>
      </div>
    </div>

    <!-- Tier 1: Primary KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="(stat, i) in stats" :key="i" 
        class="group bg-white rounded-3xl p-6 border border-slate-200 hover:border-brand/30 hover:shadow-2xl hover:shadow-brand/5 transition-all relative overflow-hidden"
      >
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>
        
        <div class="flex items-center justify-between mb-4 relative z-10">
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-6 transition-transform" 
            :style="{ background: stat.iconBg + '20', color: stat.iconColor }">
            <span v-html="stat.svgIcon"></span>
          </div>
          <div class="px-2 py-1 bg-slate-50 text-[10px] font-black text-slate-400 rounded-lg border border-slate-100 italic">
            {{ stat.sub }}
          </div>
        </div>
        
        <div class="relative z-10">
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ stat.label }}</p>
          <h2 class="text-4xl font-black text-slate-800 tracking-tighter">{{ stat.value }}</h2>
        </div>
        
        <!-- Subtle Trend Line Concept -->
        <div class="mt-4 h-1 w-full bg-slate-50 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-1000" :style="{ width: '60%', background: stat.iconColor }"></div>
        </div>
      </div>
    </div>

    <!-- Tier 2: Main Content Split -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
      
      <!-- Submissions Breakdown -->
      <div class="xl:col-span-8 bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
          <div>
            <h3 class="text-lg font-black text-slate-800">Operational Breakdown</h3>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Lifecycle Status Statistics</p>
          </div>
          <router-link to="/app/proposals" class="group flex items-center gap-2 text-xs font-black text-brand uppercase tracking-widest hover:translate-x-1 transition-transform">
            View Register
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
          </router-link>
        </div>

        <div v-if="loadingStats" class="p-10 space-y-6">
          <div v-for="i in 5" :key="i" class="h-12 bg-slate-50/50 rounded-2xl animate-pulse"></div>
        </div>
        
        <div v-else class="p-8">
          <div class="grid grid-cols-1 gap-4">
            <div 
              v-for="(item, i) in proposalStatuses" :key="i" 
              class="group flex items-center gap-6 p-4 rounded-3xl border border-slate-100 hover:border-brand/20 hover:bg-brand/[0.02] transition-all"
            >
              <div class="w-3 h-10 rounded-full" :style="{ background: item.color }"></div>
              
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-black text-slate-700 tracking-tight">{{ item.name }}</span>
                  <span class="text-[10px] font-black text-slate-400">{{ item.count }} units ({{ item.percent }}%)</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner flex">
                   <div 
                    class="h-full rounded-full transition-all duration-1000 ease-out" 
                    :style="{ width: item.percent + '%', background: item.color }"
                   ></div>
                </div>
              </div>

              <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 font-black text-xs group-hover:bg-brand group-hover:text-white transition-all">
                {{ item.count }}
              </div>
            </div>
            
            <div v-if="proposalStatuses.length === 0" class="py-20 text-center">
               <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                 <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5a2 2 0 00.586-1.414V5L8 4z" /></svg>
               </div>
               <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Zero activity detected in this cycle</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions & Deadlines -->
      <div class="xl:col-span-4 space-y-8">
        <!-- Shortcuts Grid -->
        <div class="bg-slate-900 rounded-[40px] p-8 shadow-2xl shadow-slate-900/30 relative overflow-hidden group">
          <div class="absolute right-0 bottom-0 w-32 h-32 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3 group-hover:scale-150 transition-transform duration-1000"></div>
          
          <h3 class="text-sm font-black text-white/50 uppercase tracking-[0.2em] mb-6 relative z-10 flex items-center gap-3">
             Command Center <span class="w-8 h-px bg-white/20"></span>
          </h3>
          
          <div class="grid grid-cols-2 gap-4 relative z-10">
            <router-link
              v-for="action in quickActions" :key="action.label"
              :to="action.to"
              class="flex flex-col gap-4 p-5 bg-white/5 border border-white/10 rounded-3xl hover:bg-brand hover:border-brand-light transition-all group/item"
            >
              <div class="w-10 h-10 rounded-2xl bg-white/5 flex items-center justify-center text-white shrink-0 shadow-lg group-hover/item:rotate-12 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="action.icon" /></svg>
              </div>
              <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ action.label }}</span>
            </router-link>
          </div>
        </div>

        <!-- System Alerts / Upcoming -->
        <div class="bg-white rounded-[40px] border border-slate-200 p-8 shadow-sm">
          <h3 class="text-xs font-black text-slate-800 uppercase tracking-[0.2em] mb-6 flex items-center justify-between">
            System Pulse
            <span class="flex items-center gap-1.5 px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded-full text-[9px]">
               <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ACTIVE
            </span>
          </h3>
          
          <div class="space-y-4">
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex gap-4">
              <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/30 font-black text-xs">
                !
              </div>
              <div>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-0.5">Urgent Milestone</p>
                <p class="text-xs font-black text-slate-700 leading-tight">Q2 Fiscal Report due in 3 days</p>
              </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex gap-4 grayscale group hover:grayscale-0 transition-all">
              <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-500 flex items-center justify-center shrink-0 font-black text-xs">
                i
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Policy Update</p>
                <p class="text-xs font-black text-slate-500 leading-tight group-hover:text-slate-700">Open Access guidelines revised</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tier 3: Recent Activity Table -->
    <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden">
      <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/20">
        <h3 class="text-lg font-black text-slate-800 tracking-tight">Recent Research Activity</h3>
        <button class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-brand transition-colors">See Live Feed</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50">
              <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Project Reference</th>
              <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Principal Author</th>
              <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-center">Status Tracking</th>
              <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Modified</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="p in recentProposals" :key="p.id" 
              @click="$router.push(`/app/proposals/${p.id}`)"
              class="group hover:bg-brand/[0.03] transition-all cursor-pointer"
            >
              <td class="px-8 py-5">
                <div class="flex items-center gap-4">
                   <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center font-black group-hover:bg-brand/10 group-hover:text-brand transition-all">
                     #{{ p.id }}
                   </div>
                   <div>
                     <p class="text-sm font-black text-slate-800 group-hover:text-brand transition-colors line-clamp-1 max-w-sm">{{ p.title }}</p>
                     <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ p.type?.name || 'Research Unit' }}</p>
                   </div>
                </div>
              </td>
              <td class="px-8 py-5">
                <p class="text-xs font-black text-slate-600 mb-0.5">{{ p.submitted_by?.name || 'Researcher Identity Protected' }}</p>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Faculty Investigator</p>
              </td>
              <td class="px-8 py-5 text-center">
                <StatusBadge :status="p.status?.name || 'draft'" />
              </td>
              <td class="px-8 py-5 text-right">
                <p class="text-xs font-black text-slate-800 tracking-tight">{{ formatDate(p.updated_at || p.created_at) }}</p>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Last Sync</p>
              </td>
            </tr>
            <tr v-if="recentProposals.length === 0">
              <td colspan="4" class="px-8 py-20 text-center">
                 <p class="text-[11px] font-black text-slate-300 uppercase tracking-[0.2em] italic">Archive empty for current selection</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import { formatDate } from '@/utils/formatters'

const auth = useAuthStore()
const selectedYear    = ref('')
const academicYears   = ref([])
const loadingStats    = ref(true)
const recentProposals = ref([])
const proposalStatuses = ref([])

const PALETTE = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316']

const quickActions = [
  { label: 'Submit Proposal', icon: 'M12 4v16m8-8H4', to: '/app/proposals/create' },
  { label: 'Track Projects', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', to: '/app/projects' },
  { label: 'Active Calls', icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', to: '/app/calls' },
  { label: 'Data Reports', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', to: '/app/reports' },
]

const stats = ref([
  {
    label: 'Total Proposals', value: '0', sub: 'Cumulative',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    iconBg: '#2563eb', iconColor: '#2563eb'
  },
  {
    label: 'Active Centers', value: '0', sub: 'Live Units',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
    iconBg: '#10b981', iconColor: '#10b981'
  },
  {
    label: 'Research Staff', value: '0', sub: 'Verified',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    iconBg: '#f59e0b', iconColor: '#f59e0b'
  },
  {
    label: 'Funded Work', value: '0', sub: 'Ongoing',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
    iconBg: '#8b5cf6', iconColor: '#8b5cf6'
  },
])

async function fetchDashboard() {
  loadingStats.value = true
  try {
    const params = selectedYear.value ? { academic_year_id: selectedYear.value } : {}
    const [dashRes, proposalsRes] = await Promise.all([
      api.get('/dashboard', { params }).catch(() => ({ data: {} })),
      api.get('/proposals', { params: { ...params, per_page: 8 } }).catch(() => ({ data: { data: [] } }))
    ])
    const d = dashRes.data || {}
    const isAdmin = auth.hasRole?.('super_admin', 'research_admin')

    stats.value[0].label = isAdmin ? 'Total Proposals' : 'My Submissions'
    stats.value[0].value = String(d.proposals_count ?? d.active_proposals ?? 0)

    stats.value[1].label = isAdmin ? 'Research Centers' : 'Institutional Units'
    stats.value[1].value = String(d.centers_count ?? d.research_centers ?? 0)

    stats.value[2].label = isAdmin ? 'System Users' : 'Draft Backlog'
    stats.value[2].value = isAdmin 
      ? String(d.users_count ?? d.active_researchers ?? 0)
      : String((d.recent_proposals || []).filter(p => !p.submitted_at).length || 0)

    stats.value[3].label = isAdmin ? 'Total Projects' : 'Active Grants'
    stats.value[3].value = String(d.projects_count ?? d.ongoing_projects ?? 0)

    const statusData = d.status_breakdown || d.proposal_statuses || []
    if (Array.isArray(statusData) && statusData.length > 0) {
      const total = statusData.reduce((s, i) => s + (i.count || 0), 0) || 1
      proposalStatuses.value = statusData.map((s, i) => ({
        name: (s.name || s.status || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()),
        count: s.count || 0,
        percent: Math.round(((s.count || 0) / total) * 100),
        color: PALETTE[i % PALETTE.length]
      }))
    }
    recentProposals.value = (d.recent_proposals || proposalsRes.data?.data || proposalsRes.data || []).slice(0, 8)
  } catch (e) {
    console.error('Dashboard synchronization error:', e)
  } finally {
    loadingStats.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/academic-years')
    academicYears.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) { }
  fetchDashboard()
})
</script>

