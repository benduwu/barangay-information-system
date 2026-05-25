<template>
  <div class="d-flex flex-column h-100 min-h-preview">
    <!-- Header/ActionBar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h4 class="mb-1" style="font-weight: 700;">Certificate Document Preview</h4>
        <p class="mb-0 text-muted" style="font-size: 0.8125rem;">
          Previewing print specifications for document request.
        </p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" @click="navigateBack">
          <i class="bi bi-arrow-left me-1"></i> Back to Registry
        </button>
        <button class="btn btn-primary" @click="triggerPrint">
          <i class="bi bi-printer-fill me-1"></i> Print / Download
        </button>
      </div>
    </div>

    <!-- PDF Iframe Container -->
    <div class="card flex-grow-1 border-0 shadow-sm overflow-hidden position-relative" style="min-height: 600px;">
      <!-- Loading Spinner Overlay -->
      <div v-if="isPdfLoading" class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-white z-3">
        <div class="spinner-border text-primary mb-3" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <div class="text-muted small">Generating official PDF stream...</div>
      </div>

      <!-- PDF Frame -->
      <iframe
        v-if="pdfUrl"
        ref="pdfIframe"
        :src="pdfUrl"
        class="w-100 h-100 border-0 flex-grow-1"
        @load="handleIframeLoaded"
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// --- State ---
const isPdfLoading = ref(true);
const pdfIframe = ref(null);

// --- Computed ---
const pdfUrl = computed(() => {
  const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
  // Include the sanctum auth token in query string since iframes cannot attach headers
  return `${apiBase}/documents/${route.params.id}/pdf?token=${authStore.token}`;
});

// --- Methods ---
function navigateBack() {
  router.push(`/${authStore.role}/documents`);
}

function handleIframeLoaded() {
  isPdfLoading.value = false;
}

function triggerPrint() {
  if (pdfIframe.value) {
    try {
      // Focus on iframe and trigger its print dialog
      pdfIframe.value.contentWindow.focus();
      pdfIframe.value.contentWindow.print();
    } catch (e) {
      // In case cross-origin or other policies block direct calling print(),
      // fall back to opening in a new tab which standard browser controls handle.
      window.open(pdfUrl.value, '_blank');
    }
  }
}

onMounted(() => {
  // Safe-guard: start loading fallback timeout
  setTimeout(() => {
    isPdfLoading.value = false;
  }, 10000);
});
</script>

<style scoped>
.min-h-preview {
  min-height: calc(100vh - 120px);
}

.z-3 {
  z-index: 3;
}

iframe {
  display: block;
}
</style>
