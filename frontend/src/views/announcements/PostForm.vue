<template>
  <div class="post-form-container">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h2 class="fw-bold text-slate-800 mb-1">{{ isEditing ? 'Edit Announcement' : 'Create Announcement' }}</h2>
        <p class="text-slate-500 mb-0">{{ isEditing ? 'Update this announcement draft or publish it.' : 'Compose a new announcement for your community.' }}</p>
      </div>
      <button class="btn btn-outline-secondary btn-sm" @click="goBack">
        <i class="bi bi-arrow-left me-1"></i> Back
      </button>
    </div>

    <form @submit.prevent="handleSubmit" class="announcement-form">
      <div class="row g-4">
        <!-- Left Column: Content -->
        <div class="col-lg-8">
          <div class="form-card p-4">
            <h5 class="form-section-title mb-3">
              <i class="bi bi-pencil-square me-2 text-primary"></i> Content
            </h5>

            <div class="mb-3">
              <label for="ann-title" class="form-label fw-semibold">Headline <span class="text-danger">*</span></label>
              <input
                id="ann-title"
                type="text"
                class="form-control form-control-lg"
                v-model="form.title"
                placeholder="Enter announcement headline..."
                :class="{ 'is-invalid': errors.title }"
                required
              />
              <div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
            </div>

            <div class="mb-3">
              <label for="ann-content" class="form-label fw-semibold">Body <span class="text-danger">*</span></label>
              <textarea
                id="ann-content"
                class="form-control"
                v-model="form.content"
                rows="8"
                placeholder="Write the announcement content here..."
                :class="{ 'is-invalid': errors.content }"
                required
              ></textarea>
              <div v-if="errors.content" class="invalid-feedback">{{ errors.content }}</div>
            </div>

            <div class="mb-3">
              <label for="ann-image" class="form-label fw-semibold">Image (Optional)</label>
              <div class="image-upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleDrop">
                <input
                  ref="fileInput"
                  type="file"
                  id="ann-image"
                  accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                  class="d-none"
                  @change="handleFileSelect"
                />
                <div v-if="imagePreview" class="image-preview-wrapper">
                  <img :src="imagePreview" alt="Preview" class="image-preview" />
                  <button type="button" class="btn btn-danger btn-sm remove-image-btn" @click.stop="removeImage">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
                <div v-else class="upload-placeholder">
                  <i class="bi bi-cloud-arrow-up fs-1 text-slate-300"></i>
                  <p class="text-slate-500 mb-1 fw-medium">Click or drag image here</p>
                  <small class="text-slate-400">JPG, PNG, GIF, or WebP — Max 4 MB</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Settings -->
        <div class="col-lg-4">
          <div class="form-card p-4 mb-4">
            <h5 class="form-section-title mb-3">
              <i class="bi bi-sliders me-2 text-primary"></i> Settings
            </h5>

            <div class="mb-3">
              <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
              <div class="d-flex gap-2">
                <button
                  v-for="p in ['low', 'normal', 'high']"
                  :key="p"
                  type="button"
                  class="btn btn-priority flex-fill"
                  :class="{ active: form.priority === p, [`priority-${p}`]: form.priority === p }"
                  @click="form.priority = p"
                >
                  {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                </button>
              </div>
            </div>

            <div class="mb-3">
              <label for="ann-publish-date" class="form-label fw-semibold">Publish Date</label>
              <input
                id="ann-publish-date"
                type="datetime-local"
                class="form-control"
                v-model="form.publish_date"
              />
              <small class="text-muted">Leave blank for immediate publish</small>
            </div>

            <div class="mb-3">
              <label for="ann-expiry-date" class="form-label fw-semibold">Expiry Date</label>
              <input
                id="ann-expiry-date"
                type="datetime-local"
                class="form-control"
                v-model="form.expiry_date"
              />
              <small class="text-muted">Leave blank for no expiry</small>
            </div>
          </div>

          <div class="form-card p-4 mb-4">
            <h5 class="form-section-title mb-3">
              <i class="bi bi-geo-alt me-2 text-primary"></i> Audience
            </h5>

            <div class="form-check form-switch mb-3">
              <input
                class="form-check-input"
                type="checkbox"
                id="barangay-wide-toggle"
                v-model="form.is_barangay_wide"
              />
              <label class="form-check-label fw-semibold" for="barangay-wide-toggle">
                Barangay-wide
              </label>
              <small class="d-block text-muted mt-1">
                {{ form.is_barangay_wide ? 'Visible to all residents' : 'Visible only to selected Puroks' }}
              </small>
            </div>

            <PurokSelect
              v-if="!form.is_barangay_wide"
              v-model="form.purok_ids"
            />
            <div v-if="errors.purok_ids" class="text-danger mt-1" style="font-size: 0.875rem;">{{ errors.purok_ids }}</div>
          </div>

          <!-- Actions -->
          <div class="form-card p-4">
            <div class="d-grid gap-2">
              <button
                type="submit"
                class="btn btn-primary btn-lg fw-semibold"
                :disabled="submitting"
                @click="publishOnSubmit = true"
              >
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-send-fill me-2"></i>
                {{ isEditing ? 'Save & Publish' : 'Publish Now' }}
              </button>
              <button
                type="submit"
                class="btn btn-outline-secondary fw-semibold"
                :disabled="submitting"
                @click="publishOnSubmit = false"
              >
                <i class="bi bi-file-earmark me-2"></i>
                Save as Draft
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';
import PurokSelect from '../../components/PurokSelect.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isEditing = computed(() => !!route.params.id);
const submitting = ref(false);
const publishOnSubmit = ref(false);
const fileInput = ref(null);
const imagePreview = ref(null);
const selectedFile = ref(null);

const form = ref({
  title: '',
  content: '',
  priority: 'normal',
  is_barangay_wide: true,
  publish_date: '',
  expiry_date: '',
  purok_ids: [],
});

const errors = ref({});

const goBack = () => {
  const prefix = authStore.role === 'admin' ? 'admin' : 'staff';
  router.push({ name: `${prefix}-announcements` });
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (e) => {
  const file = e.target.files?.[0];
  if (file) {
    selectedFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const handleDrop = (e) => {
  const file = e.dataTransfer.files?.[0];
  if (file && file.type.startsWith('image/')) {
    selectedFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  selectedFile.value = null;
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';
};

const loadAnnouncement = async () => {
  if (!isEditing.value) return;
  try {
    const { data } = await api.get(`/admin/announcements/${route.params.id}`);
    const ann = data.announcement;
    form.value.title = ann.title;
    form.value.content = ann.content;
    form.value.priority = ann.priority;
    form.value.is_barangay_wide = ann.is_barangay_wide;
    form.value.publish_date = ann.publish_date ? ann.publish_date.slice(0, 16) : '';
    form.value.expiry_date = ann.expiry_date ? ann.expiry_date.slice(0, 16) : '';
    form.value.purok_ids = ann.targets?.map(t => t.purok_id) || [];
    if (ann.image_path) {
      const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1').replace('/api/v1', '');
      imagePreview.value = `${base}/storage/${ann.image_path}`;
    }
  } catch (err) {
    console.error('Failed to load announcement:', err);
  }
};

const handleSubmit = async () => {
  errors.value = {};
  submitting.value = true;

  try {
    const formData = new FormData();
    formData.append('title', form.value.title);
    formData.append('content', form.value.content);
    formData.append('priority', form.value.priority);
    formData.append('is_barangay_wide', form.value.is_barangay_wide ? '1' : '0');
    formData.append('is_published', publishOnSubmit.value ? '1' : '0');

    if (form.value.publish_date) {
      formData.append('publish_date', form.value.publish_date);
    }
    if (form.value.expiry_date) {
      formData.append('expiry_date', form.value.expiry_date);
    }
    if (!form.value.is_barangay_wide) {
      form.value.purok_ids.forEach((id, i) => {
        formData.append(`purok_ids[${i}]`, id);
      });
    }
    if (selectedFile.value) {
      formData.append('image', selectedFile.value);
    }

    if (isEditing.value) {
      formData.append('_method', 'PUT');
      await api.post(`/admin/announcements/${route.params.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await api.post('/admin/announcements', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }

    goBack();
  } catch (err) {
    if (err.response?.status === 422) {
      const serverErrors = err.response.data.errors || {};
      Object.keys(serverErrors).forEach((key) => {
        errors.value[key] = serverErrors[key][0];
      });
    } else {
      console.error('Submit error:', err);
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  loadAnnouncement();
});
</script>

<style scoped>
.post-form-container {
  max-width: 1100px;
}

.form-card {
  background: #ffffff;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.form-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}

.image-upload-area {
  border: 2px dashed #cbd5e1;
  border-radius: 0.75rem;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #f8fafc;
}

.image-upload-area:hover {
  border-color: #93c5fd;
  background-color: #eff6ff;
}

.image-preview-wrapper {
  position: relative;
  display: inline-block;
}

.image-preview {
  max-height: 240px;
  max-width: 100%;
  border-radius: 0.5rem;
  object-fit: cover;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  border-radius: 50%;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.btn-priority {
  padding: 0.5rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  border: 1px solid #e2e8f0;
  background: #fff;
  color: #64748b;
  transition: all 0.15s ease;
}

.btn-priority:hover {
  background: #f1f5f9;
}

.btn-priority.active.priority-low {
  background: #d1fae5;
  color: #059669;
  border-color: #6ee7b7;
}

.btn-priority.active.priority-normal {
  background: #dbeafe;
  color: #2563eb;
  border-color: #93c5fd;
}

.btn-priority.active.priority-high {
  background: #fee2e2;
  color: #dc2626;
  border-color: #fca5a5;
}

.text-slate-800 {
  color: #1e293b;
}

.text-slate-500 {
  color: #64748b;
}

.text-slate-400 {
  color: #94a3b8;
}

.text-slate-300 {
  color: #cbd5e1;
}
</style>
