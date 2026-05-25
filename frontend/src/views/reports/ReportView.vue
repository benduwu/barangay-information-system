<template>
  <div class="reports-dashboard">
    <!-- Filters & Main Export Trigger Row -->
    <div class="row align-items-center mb-4">
      <div class="col-md-8">
        <p class="text-muted mb-0 small">
          Compile operational activities, request revenues, residential demographics, and case statistics.
        </p>
      </div>
      <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <!-- Reusable Export Button (Only for Admins) -->
        <ExportBtn
          v-if="authStore.role === 'admin'"
          :report-type="activeTab"
          :filters="currentFilters"
        />
        <div v-else class="badge bg-secondary p-2 small">
          <i class="bi bi-shield-lock me-1"></i>Export (Admin Only)
        </div>
      </div>
    </div>

    <!-- Filter Panel Integration -->
    <FilterPanel
      v-if="activeTab !== 'residents'"
      @filter="handleFilterChange"
    />

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-0 p-0">
        <ul class="nav nav-tabs nav-fill border-0" id="reportsTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button
              class="nav-link py-3 border-0 rounded-0 d-flex align-items-center justify-content-center gap-2"
              :class="{ active: activeTab === 'residents' }"
              @click="switchTab('residents')"
              type="button"
            >
              <i class="bi bi-people-fill text-primary"></i>
              <span>Residents Demographic</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link py-3 border-0 rounded-0 d-flex align-items-center justify-content-center gap-2"
              :class="{ active: activeTab === 'documents' }"
              @click="switchTab('documents')"
              type="button"
            >
              <i class="bi bi-file-earmark-text-fill text-success"></i>
              <span>Clearances & Revenue</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link py-3 border-0 rounded-0 d-flex align-items-center justify-content-center gap-2"
              :class="{ active: activeTab === 'blotters' }"
              @click="switchTab('blotters')"
              type="button"
            >
              <i class="bi bi-journal-text text-danger"></i>
              <span>Incident Blotters</span>
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link py-3 border-0 rounded-0 d-flex align-items-center justify-content-center gap-2"
              :class="{ active: activeTab === 'monthly' }"
              @click="switchTab('monthly')"
              type="button"
            >
              <i class="bi bi-calendar-check text-warning"></i>
              <span>Monthly Activity Summary</span>
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Tab Contents -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="text-muted mt-3 small">Compiling analytics reports...</p>
    </div>

    <div v-else class="tab-content" id="reportsTabContent">
      <!-- 1. Residents Registry Tab -->
      <div v-if="activeTab === 'residents'" class="tab-pane fade show active">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-primary">{{ residentData.summary.total_residents }}</h3>
              <span class="text-muted small uppercase font-medium">Total Registered</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-info">{{ residentData.summary.male_count }}</h3>
              <span class="text-muted small uppercase font-medium">Male Residents</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-danger">{{ residentData.summary.female_count }}</h3>
              <span class="text-muted small uppercase font-medium">Female Residents</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-warning">{{ residentData.summary.senior_count }}</h3>
              <span class="text-muted small uppercase font-medium">Senior Citizens (60+)</span>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Residents per Purok</div>
              <div class="card-body">
                <BarChart
                  type="bar"
                  :labels="residentPurokLabels"
                  :datasets="[
                    {
                      label: 'Resident Count',
                      data: residentPurokValues,
                      backgroundColor: '#1e3a5f',
                      borderRadius: 4
                    }
                  ]"
                />
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Age Groups Distribution</div>
              <div class="card-body">
                <BarChart
                  type="doughnut"
                  :labels="['Seniors (60+)', 'Adults (18-59)', 'Youth (12-17)', 'Children (<12)']"
                  :datasets="[
                    {
                      data: [
                        residentData.summary.senior_count,
                        residentData.summary.adult_count,
                        residentData.summary.youth_count,
                        residentData.summary.child_count
                      ],
                      backgroundColor: ['#eab308', '#2563eb', '#6366f1', '#f43f5e']
                    }
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Clearances Registry Tab -->
      <div v-if="activeTab === 'documents'" class="tab-pane fade show active">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-primary">{{ docData.total_requests }}</h3>
              <span class="text-muted small uppercase font-medium">Total Requests Filed</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-success">Php {{ docData.revenue.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</h3>
              <span class="text-muted small uppercase font-medium">Revenue Collected</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-info">
                {{ docData.status_breakdown.find(s => s.status === 'released')?.count || 0 }}
              </h3>
              <span class="text-muted small uppercase font-medium">Certificates Released</span>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Requests by Certificate Type</div>
              <div class="card-body">
                <BarChart
                  type="bar"
                  :labels="docTypeLabels"
                  :datasets="[
                    {
                      label: 'Documents Count',
                      data: docTypeValues,
                      backgroundColor: '#10b981',
                      borderRadius: 4
                    }
                  ]"
                />
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Request Status Breakdown</div>
              <div class="card-body">
                <BarChart
                  type="doughnut"
                  :labels="docStatusLabels"
                  :datasets="[
                    {
                      data: docStatusValues,
                      backgroundColor: ['#f59e0b', '#3b82f6', '#ef4444', '#10b981']
                    }
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Blotters Registry Tab -->
      <div v-if="activeTab === 'blotters'" class="tab-pane fade show active">
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-primary">{{ blotterData.total_cases }}</h3>
              <span class="text-muted small uppercase font-medium">Total Cases Filed</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-success">
                {{ blotterData.status_breakdown.find(s => s.status === 'settled')?.count || 0 }}
              </h3>
              <span class="text-muted small uppercase font-medium">Cases Resolved / Settled</span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-danger">
                {{ blotterData.status_breakdown.find(s => s.status === 'escalated')?.count || 0 }}
              </h3>
              <span class="text-muted small uppercase font-medium">Cases Escalated</span>
            </div>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Filing Trends (sqlite months)</div>
              <div class="card-body">
                <BarChart
                  type="line"
                  :labels="blotterTrendLabels"
                  :datasets="[
                    {
                      label: 'Cases Filed',
                      data: blotterTrendValues,
                      borderColor: '#ef4444',
                      tension: 0.3,
                      fill: false
                    }
                  ]"
                />
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white py-3">Incident Categories</div>
              <div class="card-body">
                <BarChart
                  type="bar"
                  :labels="blotterTypeLabels"
                  :datasets="[
                    {
                      label: 'Incident Type Count',
                      data: blotterTypeValues,
                      backgroundColor: '#6366f1',
                      borderRadius: 4
                    }
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Monthly Summary Tab -->
      <div v-if="activeTab === 'monthly'" class="tab-pane fade show active">
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-primary">{{ monthlyData.summary.new_residents }}</h3>
              <span class="text-muted small uppercase font-medium">New Residents</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-success">{{ monthlyData.summary.documents_processed }}</h3>
              <span class="text-muted small uppercase font-medium">Certificates Issued</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-info">Php {{ monthlyData.summary.revenue_generated.toLocaleString('en-US') }}</h3>
              <span class="text-muted small uppercase font-medium">Fees Collected</span>
            </div>
          </div>
          <div class="col-md-3">
            <div class="metric-card bg-white p-3 border-0 shadow-sm rounded-3 text-center">
              <h3 class="mb-1 fw-bold text-danger">{{ monthlyData.summary.blotters_filed }}</h3>
              <span class="text-muted small uppercase font-medium">Blotters Filed</span>
            </div>
          </div>
        </div>

        <!-- Dynamic Activity Summary Breakdown Table -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white py-3">Sub-registry Operations Overview</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="table-light small text-uppercase">
                  <tr>
                    <th class="ps-4">Subsystem Category</th>
                    <th>Activity Indicator</th>
                    <th class="pe-4 text-end">Count / Metrics</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="ps-4 fw-bold">Residents Management</td>
                    <td>New Registrants enrolled in purok registries</td>
                    <td class="pe-4 text-end fw-bold text-primary">{{ monthlyData.summary.new_residents }} residents</td>
                  </tr>
                  <tr>
                    <td class="ps-4 fw-bold">Documents & Clearance</td>
                    <td>Certificates approved and officially released</td>
                    <td class="pe-4 text-end fw-bold text-success">{{ monthlyData.summary.documents_processed }} certificates</td>
                  </tr>
                  <tr>
                    <td class="ps-4 fw-bold">Financial Operations</td>
                    <td>Clearance processing fees accumulated</td>
                    <td class="pe-4 text-end fw-bold text-info">Php {{ monthlyData.summary.revenue_generated.toLocaleString('en-US', {minimumFractionDigits:2}) }}</td>
                  </tr>
                  <tr>
                    <td class="ps-4 fw-bold">Incident & Disputes</td>
                    <td>Disputes filed vs disputes officially settled</td>
                    <td class="pe-4 text-end fw-bold text-danger">{{ monthlyData.summary.blotters_filed }} filed / {{ monthlyData.summary.blotters_settled }} settled</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

// Components
import FilterPanel from '../../components/FilterPanel.vue';
import ExportBtn from '../../components/ExportBtn.vue';
import BarChart from '../../components/BarChart.vue';

const authStore = useAuthStore();

const activeTab = ref('residents');
const isLoading = ref(false);

const currentFilters = ref({
  start_date: '',
  end_date: ''
});

// 1. Residents Report States
const residentData = ref({
  summary: {
    total_residents: 0,
    male_count: 0,
    female_count: 0,
    senior_count: 0,
    adult_count: 0,
    youth_count: 0,
    child_count: 0
  },
  purok_breakdown: []
});

const residentPurokLabels = computed(() => residentData.value.purok_breakdown.map(p => p.purok_name));
const residentPurokValues = computed(() => residentData.value.purok_breakdown.map(p => p.residents_count));

// 2. Documents Report States
const docData = ref({
  total_requests: 0,
  revenue: 0,
  type_breakdown: [],
  status_breakdown: []
});

const docTypeLabels = computed(() => docData.value.type_breakdown.map(t => t.document_type.toUpperCase()));
const docTypeValues = computed(() => docData.value.type_breakdown.map(t => t.count));

const docStatusLabels = computed(() => docData.value.status_breakdown.map(s => s.status.toUpperCase()));
const docStatusValues = computed(() => docData.value.status_breakdown.map(s => s.count));

// 3. Blotters Report States
const blotterData = ref({
  total_cases: 0,
  status_breakdown: [],
  type_breakdown: [],
  monthly_trend: []
});

const blotterTypeLabels = computed(() => blotterData.value.type_breakdown.map(t => t.incident_type));
const blotterTypeValues = computed(() => blotterData.value.type_breakdown.map(t => t.count));

const blotterTrendLabels = computed(() => blotterData.value.monthly_trend.map(m => m.month));
const blotterTrendValues = computed(() => blotterData.value.monthly_trend.map(m => m.count));

// 4. Monthly consolidated stats
const monthlyData = ref({
  summary: {
    new_residents: 0,
    documents_processed: 0,
    revenue_generated: 0,
    blotters_filed: 0,
    blotters_settled: 0,
    blotters_escalated: 0
  },
  purok_stats: [],
  document_stats: []
});

// Fetch reports data based on filters
async function fetchReportsData() {
  isLoading.value = true;
  try {
    const params = { ...currentFilters.value };
    
    if (activeTab.value === 'residents') {
      const response = await api.get('/reports/residents');
      residentData.value = response.data;
    } else if (activeTab.value === 'documents') {
      const response = await api.get('/reports/documents', { params });
      docData.value = response.data;
    } else if (activeTab.value === 'blotters') {
      const response = await api.get('/reports/blotters', { params });
      blotterData.value = response.data;
    } else if (activeTab.value === 'monthly') {
      const response = await api.get('/reports/monthly', { params });
      monthlyData.value = response.data;
    }
  } catch (error) {
    console.error('Failed to load reports analytics:', error);
  } finally {
    isLoading.value = false;
  }
}

function handleFilterChange(newFilters) {
  currentFilters.value = newFilters;
  fetchReportsData();
}

function switchTab(tab) {
  activeTab.value = tab;
  fetchReportsData();
}

onMounted(() => {
  fetchReportsData();
});
</script>

<style scoped>
.nav-tabs .nav-link {
  color: #64748b;
  border-bottom: 2px solid transparent !important;
  font-weight: 500;
  transition: all 0.2s;
}
.nav-tabs .nav-link:hover {
  background-color: #f8fafc;
  color: var(--bs-primary);
}
.nav-tabs .nav-link.active {
  color: var(--bs-primary);
  border-bottom-color: var(--bs-primary) !important;
  background-color: #fff;
  font-weight: 600;
}
.metric-card {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}
</style>
