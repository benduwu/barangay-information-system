<template>
  <div class="household-group-container">
    <label for="household-head-select" class="form-label">
      Head of Household
      <span class="text-muted" style="font-size: 0.75rem;">(optional)</span>
    </label>
    <select
      id="household-head-select"
      class="form-select"
      :value="modelValue"
      @change="handleSelect"
      :disabled="!purokId || isLoading"
    >
      <option value="">-- No Head (This Resident is Head of Household) --</option>
      <option v-if="isLoading" disabled>Loading household heads...</option>
      <option
        v-for="head in potentialHeads"
        :key="head.id"
        :value="head.id"
      >
        {{ head.last_name }}, {{ head.first_name }}
      </option>
    </select>
    <div v-if="!purokId" class="form-text text-muted" style="font-size: 0.75rem;">
      Please select a Purok first to load potential heads of household.
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import api from '../services/api';

const props = defineProps({
  modelValue: { type: [Number, String], default: '' },
  purokId: { type: [Number, String], default: '' },
  currentResidentId: { type: Number, default: null },
});

const emit = defineEmits(['update:modelValue']);

const potentialHeads = ref([]);
const isLoading = ref(false);

watch(
  () => props.purokId,
  (newPurokId) => {
    if (newPurokId) {
      fetchHeads(newPurokId);
    } else {
      potentialHeads.value = [];
      emit('update:modelValue', '');
    }
  }
);

async function fetchHeads(purokId) {
  isLoading.value = true;
  try {
    // Fetch residents in this purok
    // For household heads, we fetch unpaginated or high limit
    const response = await api.get('/residents', {
      params: {
        purok_id: purokId,
        per_page: 150,
      },
    });

    let list = response.data.residents || [];

    // Filter out the current resident themselves (cannot be head under themselves)
    if (props.currentResidentId) {
      list = list.filter((r) => r.id !== props.currentResidentId);
    }

    // Filter to only show people who are heads themselves (no parent head_of_household_id)
    // or just show all residents so they can select anyone as head in that Purok.
    // Spec says: "link resident to household head (same purok)"
    // We'll list all residents in the same Purok to keep it simple and highly flexible.
    potentialHeads.value = list;
  } catch (error) {
    console.error('Failed to load household heads:', error);
  } finally {
    isLoading.value = false;
  }
}

function handleSelect(event) {
  const val = event.target.value;
  emit('update:modelValue', val ? Number(val) : '');
}

// Initial fetch if purokId is already set
onMounted(() => {
  if (props.purokId) {
    fetchHeads(props.purokId);
  }
});
</script>
