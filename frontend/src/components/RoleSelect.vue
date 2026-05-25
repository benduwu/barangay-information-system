<template>
  <select
    class="form-select"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    :id="id"
    :required="required"
  >
    <option value="" disabled>Select a role</option>
    <option value="admin">Admin</option>
    <option value="staff">Staff</option>
  </select>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
  id: { type: String, default: 'role-select' },
  required: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>
