<template>
  <div class="space-y-4 animate-fade pb-4">
    <div class="h-1"></div>

    <!-- Tier 1: Primary KPIs (dynamic per role) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <div
        v-for="(stat, i) in visibleStats"
        :key="i"
        class="group bg-white rounded-2xl p-4 border border-slate-200 hover:border-brand/30 hover:shadow-xl hover:shadow-brand/5 transition-all relative overflow-hidden"
      >
        <div class="absolute -right-2 -bottom-2 w-12 h-12 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>

        <div class="flex items-center justify-between mb-2 relative z-10">
          <div
            class="w-8 h-8 rounded-lg flex items-center justify-center shadow-md group-hover:rotate-6 transition-transform"
            :style="{ background: stat.iconBg + '15', color: stat.iconColor }"
          >
            <span v-html="stat.svgIcon"></span>
          </div>
          <div class="px-2 py-0.5 text-[8px] font-bold text-slate-400 rounded-md border border-slate-100 italic">
            {{ stat.sub }}
          </div>
        </div>

        <div class="relative z-10">
          <p class="text-[8px] font-bold text-slate-400 tracking-widest mb-0.5">{{ stat.label }}</p>
          <h2 class="text-lg font-bold text-slate-800 tracking-tight">{{ stat.value }}</h2>
        </div>

        <div class="mt-2 h-1 w-full border-t border-slate-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-1000" :style="{ width: '60%', background: stat.iconColor }"></div>
        </div>
      </div>
    </div>

    <!-- Tier 2: Charts and Detailed Breakdowns (conditional per role) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- University Distribution (only for super_admin / research_admin) -->
      <div v-if="showUniversityDistribution" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col p-4">
        <h3 class="text-xs font-bold text-slate-800 tracking-widest mb-1">University Distribution</h3>
        <p class="text-[10px] text-slate-400 font-bold mb-4">Proposals by Institution</p>

        <div v-if="loadingStats" class="flex-1 min-h-[200px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
        <div v-else class="flex-1 min-h-[200px] flex items-center justify-center">
          <apexchart type="donut" height="220" width="100%" :options="universityOptions" :series="universitySeries"></apexchart>
        </div>
      </div>

      <!-- Proposal Status (for most roles) -->
      <div v-if="showProposalStatus" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col p-4">
        <h3 class="text-xs font-bold text-slate-800 tracking-widest mb-1">Proposal Status</h3>
        <p class="text-[10px] text-slate-400 font-bold mb-4">Current workflow distribution</p>

        <div v-if="loadingStats" class="flex-1 min-h-[200px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
        <div v-else class="flex-1 min-h-[200px] flex items-center justify-center -ml-4">
          <apexchart type="donut" height="220" width="100%" :options="donutOptions" :series="donutSeries"></apexchart>
        </div>
      </div>

      <!-- Monthly Trend (for most roles) -->
      <div v-if="showMonthlyTrend" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col p-4">
        <h3 class="text-xs font-bold text-slate-800 tracking-widest mb-1">Monthly Trend</h3>
        <p class="text-[10px] text-slate-400 font-bold mb-4">Proposal submission activity</p>

        <div v-if="loadingStats" class="flex-1 min-h-[200px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
        <div v-else class="flex-1 min-h-[200px] flex items-center justify-center">
          <apexchart type="area" height="220" width="100%" :options="trendOptions" :series="trendSeries"></apexchart>
        </div>
      </div>
    </div>

    <!-- Tier 3: Detailed University/Department Breakdown (only for super_admin / research_admin) -->
    <div v-if="showUniversityBreakdown" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-4">
        <h3 class="text-xs font-bold text-slate-800 tracking-widest mb-1">University Breakdown</h3>
        <p class="text-[10px] text-slate-400 font-bold mb-4">Detailed statistics by institution</p>

        <div v-if="loadingStats" class="flex-1 min-h-[200px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
        <div v-else class="space-y-3 max-h-[200px] overflow-y-auto">
          <div
            v-for="(uni, i) in universityStats"
            :key="i"
            class="flex items-center justify-between p-2 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold"
                :style="{ background: PALETTE[i % PALETTE.length] + '20', color: PALETTE[i % PALETTE.length] }"
              >
                {{ uni.name?.charAt(0) || 'U' }}
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800">{{ uni.name }}</p>
                <p class="text-[10px] text-slate-500">{{ uni.code }}</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-brand">{{ uni.proposals_count || 0 }}</p>
              <p class="text-[10px] text-slate-400">proposals</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Calls Summary -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-4">
        <h3 class="text-xs font-bold text-slate-800 tracking-widest mb-1">Active Calls</h3>
        <p class="text-[10px] text-slate-400 font-bold mb-4">Open call for proposals</p>

        <div v-if="loadingStats" class="flex-1 min-h-[200px] flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand"></div>
        </div>
        <div v-else class="space-y-3 max-h-[200px] overflow-y-auto">
          <div
            v-for="(call, i) in activeCalls"
            :key="i"
            class="p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors border-l-4 border-brand"
          >
            <div class="flex justify-between items-start mb-2">
              <p class="text-xs font-bold text-slate-800 line-clamp-1">{{ call.title }}</p>
              <span class="text-[10px] px-2 py-0.5 bg-brand/10 text-brand rounded-full">{{ call.status || 'Open' }}</span>
            </div>
            <div class="flex items-center gap-4 text-[10px] text-slate-500">
              <span>{{ call.university?.name || 'Central' }}</span>
              <span>•</span>
              <span>{{ formatDate(call.deadline) }}</span>
              <span>•</span>
              <span>{{ call.proposals_count || 0 }} proposals</span>
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

const auth = useAuthStore()
const loadingStats = ref(true)

// Role-based visibility flags
const showUniversityDistribution = computed(() =>
  auth.hasRole('super_admin', 'research_admin')
)
const showProposalStatus = computed(() =>
  auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director', 'researcher')
)
const showMonthlyTrend = computed(() =>
  auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director')
)
const showUniversityBreakdown = computed(() =>
  auth.hasRole('super_admin', 'research_admin')
)

// ---- Dynamic stats definition ----
const baseStats = [
  // 0
  {
    label: 'Total Proposals', value: '0', sub: 'Cumulative',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    iconBg: '#2563eb', iconColor: '#2563eb'
  },
  // 1
  {
    label: 'Universities', value: '0', sub: 'Institutions',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>`,
    iconBg: '#10b981', iconColor: '#10b981'
  },
  // 2
  {
    label: 'Campuses', value: '0', sub: 'Locations',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><path d="M3.21 10a9 9 0 0 1 17.58 0"/></svg>`,
    iconBg: '#f59e0b', iconColor: '#f59e0b'
  },
  // 3
  {
    label: 'Faculties', value: '0', sub: 'Academic',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>`,
    iconBg: '#8b5cf6', iconColor: '#8b5cf6'
  },
  // 4
  {
    label: 'Active Calls', value: '0', sub: 'Open',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 5.882V19.24a1.76 1.76 0 0 1-3.417.592l-2.147-6.15M18 13a3 3 0 1 0 0-6M7.83 16.917l3.298-2.164"/></svg>`,
    iconBg: '#14b8a6', iconColor: '#14b8a6'
  },
  // 5
  {
    label: 'Research Centers', value: '0', sub: 'Live Units',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>`,
    iconBg: '#f97316', iconColor: '#f97316'
  },
  // 6
  {
    label: 'System Users', value: '0', sub: 'Verified',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    iconBg: '#ec4899', iconColor: '#ec4899'
  },
  // 7
  {
    label: 'Total Projects', value: '0', sub: 'Active',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>`,
    iconBg: '#6366f1', iconColor: '#6366f1'
  },
  // 8
  {
    label: 'Completed', value: '0', sub: 'Proposals',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
    iconBg: '#22c55e', iconColor: '#22c55e'
  },
  // 9
  {
    label: 'In Progress', value: '0', sub: 'Proposals',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>`,
    iconBg: '#eab308', iconColor: '#eab308'
  },
  // 10
  {
    label: 'Pending', value: '0', sub: 'Proposals',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
    iconBg: '#3b82f6', iconColor: '#3b82f6'
  },
  // 11
  {
    label: 'Publications', value: '0', sub: 'Published',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
    iconBg: '#06b6d4', iconColor: '#06b6d4'
  },
  // 12 (for reviewer)
  {
    label: 'Pending Reviews', value: '0', sub: 'Assignments',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
    iconBg: '#f59e0b', iconColor: '#f59e0b'
  },
  // 13
  {
    label: 'Completed Reviews', value: '0', sub: 'Reviewed',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
    iconBg: '#10b981', iconColor: '#10b981'
  },
  // 14 (for finance_officer)ca
  {
    label: 'Pending Checks', value: '0', sub: 'Finance',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>`,
    iconBg: '#14b8a6', iconColor: '#14b8a6'
  },
  // 15
  {
    label: 'Approved Budgets', value: '0', sub: 'Approved',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
    iconBg: '#22c55e', iconColor: '#22c55e'
  },
  // 16 (for ethics_officer)
  {
    label: 'Pending Ethics', value: '0', sub: 'Requests',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
    iconBg: '#f59e0b', iconColor: '#f59e0b'
  },
  // 17
  {
    label: 'Cleared Projects', value: '0', sub: 'Ethics',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    iconBg: '#10b981', iconColor: '#10b981'
  },
  // 18 (for student)
  {
    label: 'My Outputs', value: '0', sub: 'Submissions',
    svgIcon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`,
    iconBg: '#2563eb', iconColor: '#2563eb'
  }
]

// Reactive stats that will be shown on the page
const visibleStats = ref([])

// Chart data
const universityStats = ref([])
const activeCalls = ref([])
const universitySeries = ref([])
const universityOptions = ref({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
  labels: [],
  colors: ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'],
  stroke: { width: 0 },
  plotOptions: { donut: { size: '70%' } },
  dataLabels: { enabled: false },
  legend: { show: true, position: 'bottom', fontSize: '10px', fontWeight: 800 }
})

const trendSeries = ref([{ name: 'Proposals', data: [] }])
const trendOptions = ref({
  chart: { type: 'area', toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
  colors: ['#2563eb'],
  fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.9, stops: [0, 90, 100] } },
  plotOptions: { bar: { borderRadius: 6, horizontal: false, columnWidth: '40%' } },
  dataLabels: { enabled: false },
  xaxis: { categories: ['Jan','Feb','Mar','Apr','May','Jun'], labels: { style: { fontSize: '10px', fontWeight: 800, colors: '#94a3b8' } } },
  yaxis: { labels: { style: { fontSize: '10px', fontWeight: 800, colors: '#94a3b8' } } },
  grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
})

const donutSeries = ref([])
const donutOptions = ref({
  chart: { type: 'donut', fontFamily: 'Inter, sans-serif' },
  labels: [],
  colors: ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'],
  stroke: { width: 0 },
  plotOptions: { donut: { size: '70%' } },
  dataLabels: { enabled: false },
  legend: { show: true, position: 'bottom', fontSize: '10px', fontWeight: 800 }
})

const PALETTE = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899']

// Helper to set visible stats based on role and fetched data
function applyRoleStats(d, actualCounts) {
  const statsArr = []
  if (auth.hasRole('super_admin', 'research_admin')) {
    statsArr.push({ ...baseStats[0], value: String(d.proposals_count || 0) })
    statsArr.push({ ...baseStats[1], value: String(actualCounts.universities || d.universities_count || 0) })
    statsArr.push({ ...baseStats[2], value: String(actualCounts.campuses || d.campuses_count || 0) })
    statsArr.push({ ...baseStats[3], value: String(actualCounts.faculties || d.faculties_count || 0) })
    statsArr.push({ ...baseStats[4], value: String(d.calls_count || 0) })
    statsArr.push({ ...baseStats[5], value: String(actualCounts.researchCenters || d.centers_count || 0) })
    statsArr.push({ ...baseStats[6], value: String(d.users_count || 0) })
    statsArr.push({ ...baseStats[7], value: String(d.projects_count || 0) })
    statsArr.push({ ...baseStats[8], value: String(d.completed_count || 0) })
    statsArr.push({ ...baseStats[9], value: String(d.in_progress_count || 0) })
    statsArr.push({ ...baseStats[10], value: String(d.pending_count || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
  } else if (auth.hasRole('reviewer')) {
    statsArr.push({ ...baseStats[12], value: String(d.pending_reviews || 0) })
    statsArr.push({ ...baseStats[13], value: String(d.completed_reviews || 0) })
    statsArr.push({ ...baseStats[7], value: String(d.assigned_reviews || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.average_score ?? 0) })
  } else if (auth.hasRole('finance_officer')) {
    statsArr.push({ ...baseStats[14], value: String(d.pending_finance_checks || 0) })
    statsArr.push({ ...baseStats[15], value: String(d.approved_budgets || 0) })
    statsArr.push({ ...baseStats[7], value: String(d.projects_count || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
  } else if (auth.hasRole('ethics_officer')) {
    statsArr.push({ ...baseStats[16], value: String(d.pending_ethics || 0) })
    statsArr.push({ ...baseStats[17], value: String(d.cleared_ethics || 0) })
    statsArr.push({ ...baseStats[7], value: String(d.projects_count || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
  } else if (auth.hasRole('student')) {
    statsArr.push({ ...baseStats[18], value: String(d.outputs_count || 0) })
    statsArr.push({ ...baseStats[4], value: String(d.calls_count || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
  } else {
    // researcher, department_head, director, campus_admin, faculty_admin, guest etc.
    statsArr.push({ ...baseStats[0], value: String(d.proposals_count || 0) })
    statsArr.push({ ...baseStats[7], value: String(d.projects_count || 0) })
    statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
    statsArr.push({ ...baseStats[4], value: String(d.calls_count || 0) })
    // For guest, show limited stats
    if (auth.hasRole('guest')) {
      statsArr.length = 0
      statsArr.push({ ...baseStats[1], value: String(actualCounts.universities || d.universities_count || 0) })
      statsArr.push({ ...baseStats[4], value: String(d.calls_count || 0) })
      statsArr.push({ ...baseStats[11], value: String(d.publications_count || 0) })
    }
  }
  visibleStats.value = statsArr
}

async function fetchDashboard() {
  loadingStats.value = true
  try {
    // Fetch dashboard data and essential counts in parallel
    const [dashRes, callsRes] = await Promise.all([
      api.get('/dashboard'),
      api.get('/calls', { params: { status: 'open' } })
    ])

    const d = dashRes.data || {}

    // Use counts from dashboard API where possible, fallback to actual lists
    const actualCounts = {
      universities: d.universities_count || (d.university_stats?.length) || 0,
      researchCenters: d.centers_count || d.centers_managed || 0,
      campuses: d.campuses_count || 0,
      faculties: d.faculties_count || 0
    }

    // Apply role-specific stats with actual counts
    applyRoleStats(d, actualCounts)

    // University distribution - use optimized stats from dashboard
    if (showUniversityDistribution.value) {
      universityStats.value = (d.university_stats || []).map(u => ({
        name: u.name,
        code: u.code,
        proposals_count: u.proposals_count || 0
      }))
      universityOptions.value = {
        ...universityOptions.value,
        labels: universityStats.value.map(u => u.name)
      }
      universitySeries.value = universityStats.value.map(u => u.proposals_count)
    }

    // Active calls
    const calls = callsRes.data?.data || []
    activeCalls.value = calls.slice(0, 3).map(c => ({
      title: c.title,
      university: c.university || { name: 'Central' },
      deadline: c.deadline,
      status: c.status?.name || 'Open',
      proposals_count: c.proposals_count || 0
    }))

    // Proposal status chart
    if (showProposalStatus.value) {
      const statuses = d.status_breakdown || []
      donutOptions.value = {
        ...donutOptions.value,
        labels: statuses.map(s => (s.name || 'Unknown').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()))
      }
      donutSeries.value = statuses.map(s => s.count || 0)
    }

    // Monthly trend
    if (showMonthlyTrend.value) {
      const monthly = d.monthly_trend || []
      trendSeries.value = [{
        name: 'Proposals',
        data: monthly.length ? monthly.map(m => m.count || 0) : [0, 0, 0, 0, 0, 0]
      }]
      trendOptions.value = {
        ...trendOptions.value,
        xaxis: {
          categories: monthly.length ? monthly.map(m => m.month) : ['Jan','Feb','Mar','Apr','May','Jun'],
          labels: { style: { fontSize: '10px', fontWeight: 800, colors: '#94a3b8' } }
        }
      }
    }

  } catch (e) {
    console.error('Dashboard load error:', e)
    applyRoleStats({}, { universities: 0, researchCenters: 0, campuses: 0, faculties: 0 })
  } finally {
    loadingStats.value = false
  }
}

onMounted(() => fetchDashboard())

function formatDate(val) {
  if (!val) return 'N/A'
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}
</script>
