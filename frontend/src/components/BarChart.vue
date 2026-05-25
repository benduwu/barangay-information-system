<template>
  <div class="chart-container" style="position: relative; width: 100%; height: 100%;">
    <canvas ref="canvasRef"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register all Chart.js components
Chart.register(...registerables);

const props = defineProps({
  type: {
    type: String,
    default: 'bar' // bar, line, doughnut, pie
  },
  labels: {
    type: Array,
    required: true
  },
  datasets: {
    type: Array,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const canvasRef = ref(null);
let chartInstance = null;

function buildChart() {
  if (chartInstance) {
    chartInstance.destroy();
  }

  if (!canvasRef.value) return;

  const ctx = canvasRef.value.getContext('2d');
  
  // Custom curated palettes to look premium
  const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: props.type === 'doughnut' || props.type === 'pie',
        position: 'bottom',
        labels: {
          boxWidth: 12,
          usePointStyle: true,
          font: {
            size: 11,
            family: "'Inter', sans-serif"
          }
        }
      },
      tooltip: {
        backgroundColor: 'rgba(30, 58, 95, 0.95)',
        titleFont: { size: 12, family: "'Inter', sans-serif", weight: 'bold' },
        bodyFont: { size: 12, family: "'Inter', sans-serif" },
        padding: 10,
        cornerRadius: 6,
        displayColors: props.type === 'doughnut' || props.type === 'pie'
      }
    },
    scales: props.type === 'doughnut' || props.type === 'pie' ? {} : {
      x: {
        grid: { display: false },
        ticks: {
          font: { size: 10, family: "'Inter', sans-serif" },
          color: '#64748b'
        }
      },
      y: {
        border: { dash: [4, 4] },
        grid: { color: '#f1f5f9' },
        ticks: {
          font: { size: 10, family: "'Inter', sans-serif" },
          color: '#64748b',
          precision: 0
        }
      }
    }
  };

  // Merge custom user options
  const finalOptions = {
    ...defaultOptions,
    ...props.options,
    plugins: {
      ...defaultOptions.plugins,
      ...(props.options.plugins || {})
    },
    scales: props.type === 'doughnut' || props.type === 'pie' ? {} : {
      ...defaultOptions.scales,
      ...(props.options.scales || {})
    }
  };

  chartInstance = new Chart(ctx, {
    type: props.type,
    data: {
      labels: props.labels,
      datasets: props.datasets
    },
    options: finalOptions
  });
}

// Watch data updates and reconstruct chart
watch(
  () => [props.labels, props.datasets, props.type],
  () => {
    buildChart();
  },
  { deep: true }
);

onMounted(() => {
  buildChart();
});

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.chart-container {
  min-height: 260px;
}
</style>
