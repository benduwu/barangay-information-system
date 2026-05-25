<template>
  <div class="purok-select-container">
    <label class="form-label d-block mb-2 font-semibold text-slate-700">
      Target Audiences (Select Puroks)
    </label>
    <div v-if="loading" class="text-muted py-2 d-flex align-items-center gap-2">
      <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
      <span>Loading Puroks...</span>
    </div>
    <div v-else-if="error" class="text-danger py-2">
      <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ error }}
    </div>
    <div v-else class="purok-grid">
      <div 
        v-for="purok in puroks" 
        :key="purok.id" 
        class="purok-checkbox-card"
        :class="{ checked: isChecked(purok.id) }"
        @click="togglePurok(purok.id)"
      >
        <div class="form-check m-0">
          <input 
            type="checkbox" 
            class="form-check-input" 
            :id="'purok-' + purok.id"
            :checked="isChecked(purok.id)"
            @click.stop
            @change="togglePurok(purok.id)"
          />
          <label class="form-check-label ms-1 cursor-pointer font-medium" :for="'purok-' + purok.id">
            {{ purok.purok_name }}
          </label>
        </div>
        <small class="text-xs text-muted d-block mt-1">
          {{ purok.zone ? 'Zone ' + purok.zone : 'No zone specified' }}
        </small>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const puroks = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchPuroks = async () => {
  try {
    loading.value = true;
    const response = await api.get('/puroks');
    puroks.value = response.data;
  } catch (err) {
    console.error('Failed to load puroks:', err);
    error.value = 'Failed to load Purok list. Please try again.';
  } finally {
    loading.value = false;
  }
};

const isChecked = (id) => {
  return props.modelValue.includes(id);
};

const togglePurok = (id) => {
  const current = [...props.modelValue];
  const index = current.indexOf(id);
  if (index === -1) {
    current.push(id);
  } else {
    current.splice(index, 1);
  }
  emit('update:modelValue', current);
};

onMounted(() => {
  fetchPuroks();
});
</script>

<style scoped>
.purok-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.purok-checkbox-card {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  padding: 0.75rem;
  background-color: #fff;
  transition: all 0.2s ease-in-out;
  cursor: pointer;
  user-select: none;
}

.purok-checkbox-card:hover {
  border-color: #cbd5e1;
  background-color: #f8fafc;
}

.purok-checkbox-card.checked {
  border-color: #3b82f6;
  background-color: #eff6ff;
  box-shadow: 0 0 0 1px #3b82f6;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
