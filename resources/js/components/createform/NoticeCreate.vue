<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog" aria-modal="true">
            <!-- header -->
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create Notice</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <!-- form -->
            <form @submit.prevent="createNotice">
              <div class="xbody">
                <div class="row g-3">
                  <!-- Notice Text -->
                  <div class="col-12">
                    <label class="form-label">Notice Text <code class="req">*</code></label>
                    <textarea
                      class="form-control"
                      rows="4"
                      placeholder="Enter notice text..."
                      v-model="form.notice"
                    ></textarea>
                    <small v-if="errors.notice" class="text-danger d-block mt-1">{{ errors.notice }}</small>
                  </div>
                </div>
              </div>
              <!-- footer -->
              <div class="xfoot d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" @click="emitClose">Cancel</button>
                <button type="submit" class="btn btn-success" :disabled="saving">
                  <span v-if="saving"><i class="fa fa-spinner fa-spin me-1"></i> Saving...</span>
                  <span v-else><i class="ti ti-device-floppy me-1"></i> Submit</span>
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
  name: "NoticeCreateModal",

  props: {
    show: { type: Boolean, default: false },
  },

  emits: ["close", "created"],

  data() {
    return {
      form: { notice: "" },
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
        text: text || (type === "success" ? "Done" : "Something went wrong"),
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
      this.form = { notice: "" };
    },

    validate() {
      this.errors = {};
      if (!this.form.notice || this.form.notice.trim() === "") {
        this.errors.notice = "Notice text is required";
      }
      return Object.keys(this.errors).length === 0;
    },

    async createNotice() {
      if (!this.validate()) {
        this.toast("Please fill in the notice text", "warning");
        return;
      }

      this.saving = true;
      try {
        const res = await axios.post(
          this.url + "notice-store",
          { notice: this.form.notice }
        );

        this.toast(res.data?.message || "Notice Created ", "success");
        this.$emit("created");
        this.emitClose();
      } catch (e) {
        const data = e?.response?.data;
        if (e?.response?.status === 422 && data?.errors) {
          Object.keys(data.errors).forEach((k) => {
            this.errors[k] = Array.isArray(data.errors[k])
              ? data.errors[k][0]
              : data.errors[k];
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
  width: min(96vw, 630px);
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 18px 55px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.xhead {
  padding: 12px 16px;
  border-bottom: 1px solid #eef2f7;
  background: #fff;
}

.xbody {
  padding: 16px;
  max-height: calc(100vh - 190px);
  overflow: auto;
}

.xfoot {
  padding: 12px 16px;
  border-top: 1px solid #eef2f7;
  background: #fff;
}

.req { color: #dc3545; }

.slide-fade-enter-active,
.slide-fade-leave-active { transition: all 0.18s ease; }
.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(10px) scale(0.98);
}

@media (max-width: 576px) {
  .xmask { padding: 10px; }
  .xbox { width: 100%; border-radius: 12px; }
  .xbody { max-height: calc(100vh - 170px); }
}
</style>
