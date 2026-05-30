<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-slate-500 font-medium mt-1">A quick summary of your research data.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative group">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-brand transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <select v-model="selectedYear" @change="fetchDashboard" class="input pl-10 h-11 font-bold text-slate-700" style="width:220px;">
            <option value="">All Years</option>
            <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}{{ y.is_current ? ' (Now)' : '' }}</option>
          </select>
        </div>
        <button @click="fetchDashboard" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      <div v-for="(stat, i) in stats" :key="i" class="card card-hover p-6 flex flex-col justify-between group overflow-hidden relative border-t-4" :style="{ borderTopColor: stat.barColor }">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 shadow-md" :style="{ background: stat.iconBg, color: stat.iconColor }">
            <span v-html="stat.svgIcon"></span>
          </div>
          <div class="text-right">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ stat.label }}</p>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter mt-1">{{ stat.value }}</h2>
          </div>
        </div>
        <div class="flex items-center justify-between pt-4 border-t border-slate-50">
          <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ stat.sub }}</span>
          <svg class="w-4 h-4 text-slate-300 group-hover:text-brand transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Analytics Row -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

      <!-- Submissions Row (2/3 width) -->
      <div class="card flex flex-col xl:col-span-2 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h3 class="text-base font-black text-slate-900">Your Submissions</h3>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Live status of research proposals.</p>
          </div>
          <router-link to="/proposals" class="btn btn-secondary py-2 px-4 text-[11px] font-black uppercase tracking-widest">Show All</router-link>
        </div>
        
        <div v-if="loadingStats" class="p-8 space-y-4">
           <div v-for="i in 4" :key="i" class="h-10 bg-slate-50/50 rounded-xl animate-pulse"></div>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table-auto">
            <thead>
              <tr>
                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">Status</th>
                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">Count</th>
                <th style="width:40%" class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">Scale</th>
                <th class="text-right px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="(item, i) in proposalStatuses" :key="i" class="group transition-colors hover:bg-slate-50/30">
                <td class="px-8 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full shadow-sm" :style="{ background: item.color }"></div>
                    <span class="font-bold text-slate-800 text-sm tracking-tight group-hover:text-brand transition-colors">{{ item.name }}</span>
                  </div>
                </td>
                <td class="py-4">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-black" :style="{ background: item.color + '10', color: item.color }">
                    {{ item.count }}
                  </span>
                </td>
                <td class="py-4">
                  <div class="flex items-center gap-3 pr-4">
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden shadow-inner">
                      <div class="h-full rounded-full transition-all duration-1000 ease-out" :style="{ width: item.percent + '%', background: item.color }"></div>
                    </div>
                  </div>
                </td>
                <td class="text-right px-8 py-4">
                  <span class="text-sm font-black text-slate-400 group-hover:text-slate-600 transition-colors">{{ item.percent }}%</span>
                </td>
              </tr>
              <tr v-if="proposalStatuses.length === 0">
                <td colspan="4" class="py-20 text-center text-slate-400 text-xs font-black uppercase tracking-widest italic">No data found for this year.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Stats Summary (1/3 width) -->
      <div class="card p-6 flex flex-col">
        <div class="mb-6">
          <h3 class="text-base font-black text-slate-900">Summary</h3>
          <p class="text-xs text-slate-500 font-medium mt-0.5">Visual breakdown of data.</p>
        </div>

        <div class="flex items-center justify-center flex-1 relative bg-slate-50/50 rounded-2xl mb-6 border border-slate-100 shadow-inner">
          <apexchart
            type="donut"
            :options="chartOptions"
            :series="chartSeries"
            height="260"
            class="w-full"
          />
        </div>

        <div class="flex flex-col gap-3 px-1">
          <div v-for="(item, i) in proposalStatuses.slice(0, 5)" :key="i" class="flex items-center justify-between group">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full" :style="{ background: item.color }"></div>
              <span class="text-[11px] font-black text-slate-500 group-hover:text-brand transition-colors uppercase tracking-widest">{{ item.name }}</span>
            </div>
            <span class="text-sm font-black text-slate-800 tracking-tighter">{{ item.count }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Latest Work -->
      <div class="card flex flex-col overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/20">
          <h3 class="text-base font-black text-slate-900">Latest Work</h3>
          <router-link to="/proposals" class="text-[11px] font-black text-brand uppercase tracking-widest hover:underline underline-offset-4">See All</router-link>
        </div>
        <div v-if="loadingStats" class="p-8 space-y-4">
           <div v-for="i in 5" :key="i" class="h-12 bg-slate-50/50 rounded-xl animate-pulse"></div>
        </div>
        <div v-else-if="recentProposals.length === 0" class="py-20 text-center text-slate-400 font-black uppercase tracking-widest text-xs italic">No latest work found.</div>
        <div v-else class="divide-y divide-slate-50">
          <div
            v-for="p in recentProposals" :key="p.id"
            @click="$router.push(`/proposals/${p.id}`)"
            class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/80 cursor-pointer transition-all group"
          >
            <div class="w-11 h-11 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center shrink-0 group-hover:bg-brand-light transition-all duration-300">
              <svg class="w-5 h-5 text-slate-400 group-hover:text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-black text-slate-800 truncate group-hover:text-brand transition-colors leading-tight">{{ p.title }}</p>
              <div class="flex items-center gap-2 mt-1.5">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ formatDate(p.created_at) }}</span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ p.submitted_by?.name || 'Researcher' }}</span>
              </div>
            </div>
            <StatusBadge :status="p.status?.name || 'draft'" />
          </div>
        </div>
      </div>

      <!-- Shortcuts -->
      <div class="card p-6 flex flex-col gap-6">
        <div>
          <h3 class="text-base font-black text-slate-900">Shortcuts</h3>
          <p class="text-xs text-slate-500 font-medium mt-0.5">Quickly access common tasks.</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <router-link
            v-for="action in quickActions" :key="action.label"
            :to="action.to"
            class="flex flex-col gap-3 p-5 rounded-2xl border border-slate-100 hover:border-brand hover:bg-brand-light/20 transition-all group relative overflow-hidden shadow-sm"
          >
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shrink-0 shadow-lg group-hover:scale-110 transition-transform duration-300" :class="action.color">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" :d="action.icon" /></svg>
            </div>
            <span class="text-[11px] font-black text-slate-800 group-hover:text-brand transition-colors uppercase tracking-widest">{{ action.label }}</span>
            <div class="absolute -right-2 -bottom-2 w-12 h-12 bg-slate-50 rounded-full opacity-50 group-hover:bg-brand-light transition-colors"></div>
          </router-link>
        </div>

        <div class="mt-auto pt-6 border-t border-slate-100">
          <div class="bg-slate-900 rounded-2xl p-5 flex items-center justify-between shadow-2xl shadow-slate-900/40 relative overflow-hidden group">
            <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            <div class="flex gap-4 items-center relative z-10">
              <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center text-emerald-400 border border-white/5 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-0.5">Status</p>
                <h4 class="text-sm font-black text-white tracking-tight">System Online</h4>
              </div>
            </div>
            <div class="flex items-center gap-2 relative z-10">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_12px_rgba(16,185,129,0.8)] border border-emerald-400/50"></span>
              <span class="text-[10px] font-black text-white tracking-widest">LIVE</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDate } from '@/utils/formatters'

const selectedYear    = ref('')
const academicYears   = ref([])
const loadingStats    = ref(true)
const recentProposals = ref([])

const PALETTE = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316']

const quickActions = [
  { label: 'New Proposal', icon: 'M12 4v16m8-8H4', to: '/proposals/create', color: 'bg-blue-600' },
  { label: 'Projects', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', to: '/projects', color: 'bg-emerald-600' },
  { label: 'Funding Calls', icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', to: '/calls', color: 'bg-amber-600' },
  { label: 'Reports', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', to: '/reports', color: 'bg-indigo-600' },
]

const stats = ref([
  {
    label: 'Proposals', value: '0', sub: 'overall',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    iconBg: '#eff6ff', iconColor: '#2563eb', barColor: '#2563eb'
  },
  {
    label: 'Centers', value: '0', sub: 'active',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
    iconBg: '#f0fdf4', iconColor: '#16a34a', barColor: '#10b981'
  },
  {
    label: 'Researchers', value: '0', sub: 'users',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    iconBg: '#fffbeb', iconColor: '#d97706', barColor: '#f59e0b'
  },
  {
    label: 'Projects', value: '0', sub: 'running',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
    iconBg: '#f5f3ff', iconColor: '#7c3aed', barColor: '#8b5cf6'
  },
])

const proposalStatuses = ref([])

const chartOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
  labels: proposalStatuses.value.map(s => s.name),
  colors: PALETTE.slice(0, Math.max(proposalStatuses.value.length, 1)),
  legend: { show: false },
  dataLabels: { enabled: false },
  stroke: { width: 6, colors: ['#ffffff'] },
  plotOptions: {
    pie: {
      donut: {
        size: '82%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '11px',
            fontWeight: '900',
            color: '#94a3b8',
            formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
          }
        }
      }
    }
  }
}))

const chartSeries = computed(() => {
  const s = proposalStatuses.value.map(x => x.count || 0)
  return s.length > 0 ? s : [1]
})

async function fetchDashboard() {
  loadingStats.value = true
  try {
    const params = selectedYear.value ? { academic_year_id: selectedYear.value } : {}
    const [dashRes, proposalsRes] = await Promise.all([
      api.get('/dashboard', { params }).catch(() => ({ data: {} })),
      api.get('/proposals', { params: { ...params, per_page: 6 } }).catch(() => ({ data: { data: [] } }))
    ])
    const d = dashRes.data || {}
    const auth = useAuthStore()
    const isAdmin = auth.hasRole?.('super_admin', 'research_admin')

    stats.value[0].label = isAdmin ? 'All Proposals' : 'My Proposals'
    stats.value[0].value = String(d.proposals_count ?? d.active_proposals ?? 0)

    stats.value[1].label = isAdmin ? 'Centers' : 'My Center'
    stats.value[1].value = String(d.centers_count ?? d.research_centers ?? 0)

    stats.value[2].label = isAdmin ? 'Researchers' : 'Drafts'
    stats.value[2].value = isAdmin 
      ? String(d.users_count ?? d.active_researchers ?? 0)
      : String((d.recent_proposals || []).filter(p => !p.submitted_at).length || 0) // rough estimation for drafts

    stats.value[3].label = isAdmin ? 'Total Projects' : 'My Projects'
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
    recentProposals.value = (d.recent_proposals || proposalsRes.data?.data || proposalsRes.data || []).slice(0, 5)
  } catch (e) {
    console.error('Dashboard error:', e)
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
