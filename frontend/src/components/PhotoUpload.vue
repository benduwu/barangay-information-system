<template>
  <div class="photo-upload-container">
    <div
      class="photo-preview-wrapper"
      :class="{ dragging: isDragging }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <!-- Real File Input -->
      <input
        type="file"
        ref="fileInput"
        class="d-none"
        accept="image/jpeg,image/png,image/jpg"
        @change="handleFileChange"
      />

      <!-- Existing Photo or Preview -->
      <img v-if="previewUrl" :src="previewUrl" alt="Preview" class="photo-preview" />
      
      <!-- Placeholder -->
      <div v-else class="upload-placeholder">
        <i class="bi bi-camera fs-1 text-muted"></i>
        <span class="upload-text">Upload Photo</span>
        <span class="upload-help">JPG, PNG up to 2MB</span>
      </div>

      <!-- Hover Overlay -->
      <div class="hover-overlay">
        <i class="bi bi-cloud-arrow-up-fill fs-3 mb-1"></i>
        <span>Change Photo</span>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="text-danger mt-1 text-center small">
      <i class="bi bi-exclamation-circle me-1"></i>{{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  photoUrl: { type: String, default: null },
});

const emit = defineEmits(['change']);

const isDragging = ref(false);
const previewUrl = ref(props.photoUrl);
const fileInput = ref(null);
const errorMessage = ref('');

// Watch for initial prop load
watch(
  () => props.photoUrl,
  (newUrl) => {
    previewUrl.value = newUrl;
  }
);

function triggerFileInput() {
  fileInput.value.click();
}

function processFile(file) {
  errorMessage.value = '';

  if (!file) return;

  // Validate format
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  if (!validTypes.includes(file.type)) {
    errorMessage.value = 'Only JPG, JPEG, and PNG files are allowed.';
    return;
  }

  // Validate size (2MB max)
  const maxSize = 2 * 1024 * 1024;
  if (file.size > maxSize) {
    errorMessage.value = 'File size must not exceed 2MB.';
    return;
  }

  // Local object URL for instant premium preview
  if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(previewUrl.value);
  }
  previewUrl.value = URL.createObjectURL(file);

  // Emit event
  emit('change', file);
}

function handleFileChange(event) {
  const file = event.target.value ? event.target.files[0] : null;
  processFile(file);
}

function handleDrop(event) {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  processFile(file);
}

// Clean up object URLs to avoid memory leaks
onBeforeUnmount(() => {
  if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(previewUrl.value);
  }
});
</script>

<style scoped>
.photo-upload-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.photo-preview-wrapper {
  position: relative;
  width: 140px;
  height: 140px;
  border-radius: 50%;
  border: 2px dashed #cbd5e1;
  background-color: #f8fafc;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}

.photo-preview-wrapper:hover,
.photo-preview-wrapper.dragging {
  border-color: var(--bs-primary);
  background-color: rgba(30, 58, 95, 0.02);
}

.photo-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 10px;
}

.upload-text {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #475569;
  margin-top: 4px;
}

.upload-help {
  font-size: 0.625rem;
  color: #94a3b8;
  margin-top: 2px;
}

.hover-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  opacity: 0;
  transition: opacity 0.2s ease-in-out;
  font-size: 0.75rem;
  font-weight: 500;
}

.photo-preview-wrapper:hover .hover-overlay {
  opacity: 1;
}
</style>
