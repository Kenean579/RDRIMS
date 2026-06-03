<template>
  <div class="space-y-8 animate-fade pb-4">
    <!-- Header with Breadcrumbs & Date Selection -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
      <div class="absolute right-0 top-0 w-64 h-64 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight mb-2">Research Pulse</h1>
        <p class="text-slate-500 max-w-xl text-sm leading-relaxed">
          Welcome back, <span class="text-brand font-bold">{{ auth.user?.name }}</span>. Here's a real-time overview of the institutional research ecosystem.
        </p>
      </div>
      
      <div class="flex flex-wrap items-center gap-3 relative z-10">
        <div class="relative group min-w-[200px]">
          <select 
            v-model="selectedYear" 
            @change="fetchDashboard" 
            class="w-full bg-white border-2 border-slate-200 rounded-2xl px-5 py-3 text-xs font-bold capitalize tracking-widest text-slate-700 focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all appearance-none cursor-pointer"
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
          <div class="px-2 py-1 text-[10px] font-bold text-slate-500 rounded-lg border border-slate-200 italic">
            {{ stat.sub }}
          </div>
        </div>
        
        <div class="relative z-10">
          <p class="text-[10px] font-bold text-slate-400 capitalize tracking-widest mb-1">{{ stat.label }}</p>
          <h2 class="text-2xl font-bold text-slate-800 tracking-tighter">{{ stat.value }}</h2>
        </div>
        
        <!-- Subtle Trend Line Concept -->
        <div class="mt-4 h-1 w-full border-t border-slate-200 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-1000" :style="{ width: '60%', background: stat.iconColor }"></div>
        </div>
      </div>
    </div>

    <!-- Tier 2: Main Content Split -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      
      <!-- Chart Distribution -->
      <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col p-5 relative">
        <h3 class="text-sm font-bold text-slate-800 tracking-widest capitalize mb-2">Status Distribution</h3>
        <p class="text-xs text-slate-400 font-bold mb-6">Proportional split of current workload</p>
        
        <div v-if="loadingStats" class="flex-1 min-h-[300px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
        </div>
        <div v-else-if="donutSeries.length > 0" class="flex-1 min-h-[300px] flex items-center justify-center -ml-4">
          <apexchart type="donut" height="320" width="100%" :options="donutOptions" :series="donutSeries"></apexchart>
        </div>
        <div v-else class="flex-1 min-h-[300px] flex items-center justify-center text-xs font-bold text-slate-300 capitalize tracking-widest italic">
          No data available
        </div>
      </div>

      <!-- General Activity Overview -->
      <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden flex flex-col p-5">
        <h3 class="text-sm font-bold text-slate-800 tracking-widest capitalize mb-2">Progress Overview</h3>
        <p class="text-xs text-slate-400 font-bold mb-6">Metrics tracked against active lifecycle</p>
        
        <div v-if="loadingStats" class="flex-1 min-h-[300px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
        </div>
        <div v-else class="flex-1 min-h-[300px] flex items-center justify-center -ml-4">
          <apexchart type="bar" height="320" width="100%" :options="barOptions" :series="barSeries"></apexchart>
        </div>
      </div>
    </div>


  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const auth = useAuthStore()
const selectedYear    = ref('')
const academicYears   = ref([])
const loadingStats    = ref(true)
const proposalStatuses = ref([])

const PALETTE = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316']

const donutSeries = ref([])
const donutOptions = ref({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
  labels: [],
  colors: PALETTE,
  stroke: { width: 0 },
  plotOptions: { donut: { size: '75%' } },
  dataLabels: { enabled: false },
  legend: { show: true, position: 'bottom', fontSize: '11px', fontWeight: 800 }
})

const barSeries = ref([{ name: 'Count', data: [] }])
const barOptions = ref({
  chart: { type: 'bar', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
  colors: ['#2563eb'],
  plotOptions: { bar: { borderRadius: 6, horizontal: false, columnWidth: '40%' } },
  dataLabels: { enabled: false },
  xaxis: { categories: [], labels: { style: { fontSize: '10px', fontWeight: 800, colors: '#94a3b8' } } },
  yaxis: { labels: { style: { fontSize: '10px', fontWeight: 800, colors: '#94a3b8' } } },
  grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
})

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
    const { data } = await api.get('/dashboard', { params }).catch(() => ({ data: {} }))
    const d = data || {}
    
    // Dynamic Role-based mapping
    if (auth.hasRole('super_admin', 'research_admin')) {
      stats.value[0].label = 'Total Proposals'; stats.value[0].value = String(d.proposals_count || 0)
      stats.value[1].label = 'Research Centers'; stats.value[1].value = String(d.centers_count || 0)
      stats.value[2].label = 'System Users'; stats.value[2].value = String(d.users_count || 0)
      stats.value[3].label = 'Total Projects'; stats.value[3].value = String(d.projects_count || 0)
    } else if (auth.hasRole('reviewer')) {
      stats.value[0].label = 'Pending Reviews'; stats.value[0].value = String(d.pending_reviews || 0)
      stats.value[1].label = 'Completed Reviews'; stats.value[1].value = String(d.completed_reviews || 0)
      stats.value[2].label = 'Average Score'; stats.value[2].value = String(d.average_score || 'N/A')
      stats.value[3].label = 'Active Assignments'; stats.value[3].value = String((d.pending_reviews || 0) + (d.completed_reviews || 0))
    } else if (auth.hasRole('finance_officer')) {
      stats.value[0].label = 'Pending Checks'; stats.value[0].value = String(d.pending_finance_checks || 0)
      stats.value[1].label = 'Approved Budgets'; stats.value[1].value = String(d.approved_budgets || 0)
      stats.value[2].label = 'Total Expenses'; stats.value[2].value = String(d.total_expenses || 0)
      stats.value[3].label = 'Active Grants'; stats.value[3].value = String(d.active_grants || 0)
    } else if (auth.hasRole('ethics_officer')) {
      stats.value[0].label = 'Pending Ethics'; stats.value[0].value = String(d.pending_ethics || 0)
      stats.value[1].label = 'Cleared Projects'; stats.value[1].value = String(d.cleared_ethics || 0)
      stats.value[2].label = 'Rejected Requests'; stats.value[2].value = String(d.rejected_ethics || 0)
      stats.value[3].label = 'Total Processed'; stats.value[3].value = String(d.total_ethics || 0)
    } else if (auth.hasRole('department_head')) {
      stats.value[0].label = 'Dept Proposals'; stats.value[0].value = String(d.proposals_count || 0)
      stats.value[1].label = 'Dept Projects'; stats.value[1].value = String(d.projects_count || 0)
      stats.value[2].label = 'Dept Staff'; stats.value[2].value = String(d.staff_count || 0)
      stats.value[3].label = 'Publications'; stats.value[3].value = String(d.publications_count || 0)
    } else if (auth.hasRole('director')) {
      stats.value[0].label = 'Center Proposals'; stats.value[0].value = String(d.proposals_count || 0)
      stats.value[1].label = 'Center Projects'; stats.value[1].value = String(d.projects_count || 0)
      stats.value[2].label = 'Centers Managed'; stats.value[2].value = String(d.centers_managed || 0)
      stats.value[3].label = 'Publications'; stats.value[3].value = String(d.publications_count || 0)
    } else {
      // General Researcher / Student
      stats.value[0].label = 'My Submissions'; stats.value[0].value = String(d.proposals_count || 0)
      stats.value[1].label = 'Active Projects'; stats.value[1].value = String(d.projects_count || 0)
      stats.value[2].label = 'Draft Backlog'; stats.value[2].value = String(d.draft_count || 0)
      stats.value[3].label = 'Publications'; stats.value[3].value = String(d.publications_count || 0)
    }

    const statusData = d.status_breakdown || d.proposal_statuses || []
    
    // Process donut chart
    donutLabels = []
    donutValues = []
    if (Array.isArray(statusData) && statusData.length > 0) {
      statusData.forEach(s => {
        donutLabels.push((s.name || s.status || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()))
        donutValues.push(s.count || 0)
      })
    }
    donutSeries.value = donutValues
    donutOptions.value = { ...donutOptions.value, labels: donutLabels }

    // Process bar chart
    barOptions.value = { ...barOptions.value, xaxis: { categories: [stats.value[0].label, stats.value[1].label, stats.value[2].label, stats.value[3].label] } }
    barSeries.value = [{ name: 'Volume', data: [Number(stats.value[0].value), Number(stats.value[1].value), Number(stats.value[2].value), Number(stats.value[3].value)] }]


  } catch (e) {
    console.error('Dashboard synchronization error:', e)
  } finally {
    loadingStats.value = false
  }
}

let donutLabels = []
let donutValues = []

onMounted(async () => {
  try {
    const { data } = await api.get('/academic-years')
    academicYears.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) { }
  fetchDashboard()
})
</script>

