<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">

            <!-- Header -->
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Management</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>

            <!-- Body -->
            <div class="xbody">
              <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.name"
                  placeholder="Enter name"
                />
                <span v-if="errors.name" class="text-danger small">{{ errors.name }}</span>
              </div>
            </div>

            <!-- Footer -->
            <div class="xfoot d-flex justify-content-end gap-2">
              <button class="btn btn-secondary" type="button" @click="emitClose">Cancel</button>
              <button class="btn btn-primary" type="button" :disabled="saving" @click="submitForm">
                <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                <span v-else>Save</span>
              </button>
            </div>

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
  name: "CreateManagementForm",
  props: {
    show: { type: Boolean, default: false },
  },
  emits: ["close", "created"],
  data() {
    return {
      saving: false,
      form: {
        name: "",
      },
      errors: {},
    };
  },
  computed: {
    url() {
      return this.$store.state.url;
    },
  },
  methods: {
    emitClose() {
      this.form = { name: "" };
      this.errors = {};
      this.$emit("close");
    },
    validate() {
      this.errors = {};
      if (!this.form.name.trim()) {
        this.errors.name = "Name is required.";
      }
      return Object.keys(this.errors).length === 0;
    },
    async submitForm() {
      if (!this.validate()) return;
      this.saving = true;
      try {
        await axios.post(`${this.url}management-store`, this.form);
        Toastify({
          text: "Created successfully!",
          duration: 3000,
          gravity: "top",
          position: "right",
          style: { background: "linear-gradient(to right, #22c55e, #16a34a)" },
        }).showToast();
        this.$emit("created");
        this.emitClose();
      } catch (err) {
        const msg = err?.response?.data?.message || "Something went wrong.";
        Toastify({
          text: msg,
          duration: 3000,
          gravity: "top",
          position: "right",
          style: { background: "linear-gradient(to right, #ef4444, #dc2626)" },
        }).showToast();
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
  z-index: 99999;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.xwrap { width: 100%; max-width: 480px; }
.xbox {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.xhead {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
}
.xbody {
  padding: 20px;
}
.xfoot {
  padding: 14px 20px;
  border-top: 1px solid #eef2f7;
  background: #fafafa;
}
.slide-fade-enter-active, .slide-fade-leave-active {
  transition: all 0.25s ease;
}
.slide-fade-enter-from, .slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>