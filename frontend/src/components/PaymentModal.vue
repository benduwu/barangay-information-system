<template>
  <div class="modal fade" id="paymentModal" tabindex="-1" ref="modalRef" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0">
          <h6 class="modal-title fw-bold text-primary">
            <i class="bi bi-cash-coin me-2"></i>Process Document Release
          </h6>
          <button type="button" class="btn-close" @click="close" aria-label="Close" style="font-size: 0.75rem;"></button>
        </div>
        <form @submit.prevent="handleSubmit">
          <div class="modal-body py-3">
            <p class="text-muted small mb-3">
              Enter payment details to release <strong>{{ documentControlNumber }}</strong>.
            </p>
            
            <div class="mb-3">
              <label for="amount" class="form-label text-muted small fw-semibold">Amount Paid (PHP)</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0">₱</span>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control border-start-0 ps-1"
                  id="amount"
                  v-model.number="form.amount"
                  required
                />
              </div>
            </div>

            <div class="mb-2">
              <label for="receipt" class="form-label text-muted small fw-semibold">Official Receipt (OR) #</label>
              <input
                type="text"
                class="form-control form-control-sm"
                id="receipt"
                placeholder="e.g. OR-9876543"
                v-model="form.official_receipt_no"
                required
              />
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="close">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Confirm & Release
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { Modal } from 'bootstrap';

const props = defineProps({
  documentControlNumber: {
    type: String,
    default: ''
  },
  isSubmitting: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'close']);

const modalRef = ref(null);
let modalInstance = null;

const form = reactive({
  amount: 50.00, // Standard default clearance fee
  official_receipt_no: ''
});

function open() {
  form.official_receipt_no = '';
  modalInstance?.show();
}

function close() {
  modalInstance?.hide();
  emit('close');
}

function handleSubmit() {
  if (form.amount === '' || !form.official_receipt_no.trim()) {
    return;
  }
  emit('submit', {
    amount: form.amount,
    official_receipt_no: form.official_receipt_no.trim()
  });
}

onMounted(() => {
  if (modalRef.value) {
    modalInstance = new Modal(modalRef.value, {
      backdrop: 'static',
      keyboard: false
    });
  }
});

defineExpose({ open, close });
</script>
