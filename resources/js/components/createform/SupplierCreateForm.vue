<template>
  <Teleport to="body">
    <transition name="slide-fade">
      <div v-if="show" class="xmask" @click.self="emitClose">
        <div class="xwrap">
          <div class="xbox" role="dialog">
            <div class="xhead d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Create supplier</h5>
              <button type="button" class="btn btn-sm btn-light" @click="emitClose">
                <i class="fa fa-times"></i>
              </button>
            </div>
            <form @submit.prevent="createsupplier">
              <div class="xbody">
                <div class="mb-3">
                  <label class="form-label fw-semibold">
                    Supplier Name <code class="req">*</code>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.name"
                    placeholder="Enter supplier name"/>
                    <small v-if="errors.name" class="text-danger">
                    {{ errors.name }}
                  </small>
                </div>
              </div>
              <!-- Footer -->
              <div class="xfoot d-flex justify-content-end gap-2">
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="emitClose" >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-success"
                  :disabled="saving">
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
import axios from "axios"
import Toastify from "toastify-js"
import "toastify-js/src/toastify.css"

export default {

  name: "supplierCreate",

  props: {
    show: Boolean
  },

  emits: ["close","created"],

  data() {
    return {

      form: {
        name: ""
      },

      errors: {},

      saving: false
    }
  },

  computed:{
    url(){
      return this.$store.state.url
    }
  },
  watch:{
    show(v){
      document.body.style.overflow = v ? "hidden" : ""
      if(v) this.resetForm()
    }
  },
  beforeUnmount(){
    document.body.style.overflow = ""
  },
  methods:{
    toast(text,type="success"){
      const bg =
        type === "success"
          ? "linear-gradient(to right,#00b09b,#96c93d)"
          : "linear-gradient(to right,#ff5f6d,#ffc371)"
        Toastify({
          text:text,
          duration:2500,
          close:true,
          gravity:"top",
          position:"right",
          backgroundColor:bg
        }).showToast()

    },

    emitClose(){
      this.$emit("close")
    },

    resetForm(){

      this.errors = {}

      this.form = {
        name:""
      }

    },

    validate(){

      this.errors = {}

      if(!this.form.name){
        this.errors.name = "supplier name is required"
      }

      return Object.keys(this.errors).length === 0

    },

      async createsupplier() {

        if (!this.validate()) {
          this.toast("Please fill required field", "error")
          return
        }

        this.saving = true

        try {

          const res = await axios.post(
            this.url + "supplier-create",
            {
              name: this.form.name
            }
          )

          this.toast(res.data.message || "Supplier created")
          this.$emit("created")
          this.emitClose()

        } catch (e) {

          const data = e?.response?.data

          if (e?.response?.status === 422 && data?.errors) {
            Object.keys(data.errors).forEach(k => {
              this.errors[k] = data.errors[k][0]
            })
          }

          this.toast(data?.message || "Create failed", "error")

        } finally {
          this.saving = false
        }
      }

  }

}
</script>

<style scoped>

.xmask{
position:fixed;
inset:0;
z-index:30000;
background:rgba(0,0,0,.55);
display:flex;
align-items:center;
justify-content:center;
padding:16px;
}

.xwrap{
width:100%;
display:flex;
align-items:center;
justify-content:center;
}

.xbox{
width:500px;
background:#fff;
border-radius:12px;
overflow:hidden;
box-shadow:0 10px 40px rgba(0,0,0,.2);
}

.xhead{
padding:15px 20px;
border-bottom:1px solid #eee;
}

.xbody{
padding:20px;
}

.xfoot{
padding:15px 20px;
border-top:1px solid #eee;
}

.req{
color:red;
}

</style>