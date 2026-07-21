<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">
            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-0">Create Residence Overview</h5>
                <small class="text-muted">Add a new residence overview with images and description</small>
              </div>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="createOverview">
              <div class="xbody">
                <div class="row g-3">
                  <!-- Title -->
                  <div class="col-12">
                    <label class="form-label fw-semibold">
                      Title <code class="req">*</code>
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="form.title"
                      placeholder="Enter overview title"
                    />
                    <small v-if="errors.title" class="text-danger d-block mt-1">
                      {{ errors.title }}
                    </small>
                  </div>

                  <!-- Description -->
                  <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea
                      class="form-control"
                      rows="4"
                      v-model="form.description"
                      placeholder="Write short description..."
                    ></textarea>
                    <small v-if="errors.description" class="text-danger d-block mt-1">
                      {{ errors.description }}
                    </small>
                  </div>

                  <!-- Back Image -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Back Image <code class="req">*</code>
                    </label>
                    <div class="upload-card-wrapper">
                      <label class="upload-card" for="img_back_input">
                        <input
                          id="img_back_input"
                          type="file"
                          class="d-none"
                          accept="image/*"
                          @change="onImageChange($event, 'img_back')"
                        />
                        <template v-if="previewBack">
                          <img :src="previewBack" class="upload-preview-img" alt="Back Preview" />
                          <div class="upload-overlay">
                            <span class="change-btn">
                              <i class="fa fa-camera me-1"></i> Change Photo
                            </span>
                          </div>
                        </template>
                        <template v-else>
                          <div class="upload-placeholder-content">
                            <div class="icon-circle mb-2">
                              <i class="fa fa-cloud-upload-alt text-primary fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">Upload Photo</span>
                            <small class="text-muted">Click to browse files</small>
                          </div>
                        </template>
                      </label>
                    </div>
                    <small v-if="errors.img_back" class="text-danger d-block mt-1">
                      {{ errors.img_back }}
                    </small>
                  </div>

                  <!-- Front Image -->
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">
                      Front Image <code class="req">*</code>
                    </label>
                    <div class="upload-card-wrapper">
                      <label class="upload-card" for="img_front_input">
                        <input
                          id="img_front_input"
                          type="file"
                          class="d-none"
                          accept="image/*"
                          @change="onImageChange($event, 'img_front')"
                        />
                        <template v-if="previewFront">
                          <img :src="previewFront" class="upload-preview-img" alt="Front Preview" />
                          <div class="upload-overlay">
                            <span class="change-btn">
                              <i class="fa fa-camera me-1"></i> Change Photo
                            </span>
                          </div>
                        </template>
                        <template v-else>
                          <div class="upload-placeholder-content">
                            <div class="icon-circle mb-2">
                              <i class="fa fa-cloud-upload-alt text-primary fs-4"></i>
                            </div>
                            <span class="fw-bold text-dark">Upload Photo</span>
                            <small class="text-muted">Click to browse files</small>
                          </div>
                        </template>
                      </label>
                    </div>
                    <small v-if="errors.img_front" class="text-danger d-block mt-1">
                      {{ errors.img_front }}
                    </small>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">
                  Cancel
                </button>
                <button type="submit" class="btn btn-success" :disabled="saving">
                  <span v-if="saving">
                    <i class="fa fa-spinner fa-spin me-1"></i> Saving...
                  </span>
                  <span v-else>
                    <i class="fa fa-save me-1"></i> Submit
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script>
import axios from "axios";
import Toastify from "toastify-js";
import "toastify-js/src/toastify.css";

export default {
  name: "ResidenceOverviewCreateModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  data() {
    return {
      form: {
        title: "",
        description: "",
        img_back: null,
        img_front: null,
      },
      previewBack: "",
      previewFront: "",
      saving: false,
      errors: {},
    };
  },

  computed: {
    url() {
      return this.$store.state.url;
    },
  },

  watch: {
    show(v) {
      document.body.style.overflow = v ? "hidden" : "";
      if (v) this.resetForm();
    },
  },

  beforeUnmount() {
    document.body.style.overflow = "";
    if (this.previewBack) URL.revokeObjectURL(this.previewBack);
    if (this.previewFront) URL.revokeObjectURL(this.previewFront);
  },

  methods: {
    toast(text, type = "success") {
      const bg =
        type === "success"
          ? "linear-gradient(to right, #00b09b, #96c93d)"
          : type === "warning"
          ? "linear-gradient(to right, #f59e0b, #fbbf24)"
          : "linear-gradient(to right, #ff5f6d, #ffc371)";

      Toastify({
        text: text || "Something went wrong",
        duration: 2500,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: bg,
      }).showToast();
    },

    emitClose() {
      this.$emit("close");
    },

    resetForm() {
      this.errors = {};
      this.form = {
        title: "",
        description: "",
        img_back: null,
        img_front: null,
      };
      if (this.previewBack) URL.revokeObjectURL(this.previewBack);
      if (this.previewFront) URL.revokeObjectURL(this.previewFront);
      this.previewBack = "";
      this.previewFront = "";
    },

    onImageChange(e, field) {
      const file = e.target?.files?.[0] || null;
      if (!file) return;

      this.form[field] = file;
      this.errors[field] = null;

      if (field === "img_back") {
        if (this.previewBack) URL.revokeObjectURL(this.previewBack);
        this.previewBack = URL.createObjectURL(file);
      }

      if (field === "img_front") {
        if (this.previewFront) URL.revokeObjectURL(this.previewFront);
        this.previewFront = URL.createObjectURL(file);
      }
    },

    validate() {
      this.errors = {};
      if (!this.form.title) this.errors.title = "Title is required";
      if (!this.form.img_back) this.errors.img_back = "Back image is required";
      if (!this.form.img_front) this.errors.img_front = "Front image is required";
      return Object.keys(this.errors).length === 0;
    },

    async createOverview() {
      if (!this.validate()) {
        this.toast("Please check required fields", "warning");
        return;
      }

      this.saving = true;
      try {
        const fd = new FormData();
        fd.append("title", this.form.title);
        fd.append("description", this.form.description || "");
        fd.append("img_back", this.form.img_back);
        fd.append("img_front", this.form.img_front);

        const res = await axios.post(
          this.url + "residence-overview-create",
          fd,
          { headers: { "Content-Type": "multipart/form-data" } }
        );

        this.toast(res.data?.message || "Residence overview created successfully", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k]) ? data.errors[k][0] : data.errors[k];
          });
          this.toast(data?.message || "Validation error", "error");
        } else {
          this.toast(data?.message || "Create failed", "error");
        }
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
.xmask {
  position: fixed;
  inset: 0;
  z-index: 30000;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
}

.xwrap {
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.xbox {
  width: min(96vw, 760px);
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 18px 55px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}

.xbody {
  padding: 20px;
  max-height: calc(100vh - 190px);
  overflow: auto;
}

.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

.req { color: #dc3545; }

.form-control,
textarea {
  border-radius: 10px;
  border: 1px solid #d7dde5;
  padding: 10px 12px;
  box-shadow: none;
}

.form-control:focus,
textarea:focus {
  border-color: #6f63f6;
  box-shadow: 0 0 0 0.2rem rgba(111, 99, 246, 0.12);
}

.upload-card-wrapper { width: 100%; }

.upload-card {
  width: 100%;
  height: 210px;
  border: 2px dashed #d8dbe7;
  border-radius: 16px;
  background: #f7f7fb;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  position: relative;
  transition: all 0.25s ease;
}

.upload-card:hover {
  border-color: #7c6cff;
  background: #f3f1ff;
}

.upload-placeholder-content {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.icon-circle {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #e9e4ff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.upload-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.28);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: 0.25s ease;
}

.upload-card:hover .upload-overlay { opacity: 1; }

.change-btn {
  background: #fff;
  color: #111827;
  padding: 8px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all 0.18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}

@media (max-width: 576px) {
  .xmask { padding: 10px; }
  .xbox { width: 100%; border-radius: 14px; }
  .xbody { max-height: calc(100vh - 170px); padding: 14px; }
  .upload-card { height: 180px; }
}
</style>
