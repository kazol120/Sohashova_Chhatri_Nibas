<template>
<div>
	<transition name="slide-fade">
	    <div class="modal-mask" v-if="show">
			<div class="modal-wrapper">
				<div class="modal-container">
					<div class="modal-header">
					    <h4 style="text-align:right;cursor:pointer;" @click="closeModal"><i class="fa fa-times-circle"></i></h4>
					    <slot name="header">Add New Patient Type</slot>
					</div>
					<div class="modal-body">
					     Patient Type
					    <input type="text" v-model="name" class="form-control">
					</div>
					<div class="modal-footer">
					    <slot name="footer">
					        <button class="modal-default-button" >ADD</button>
					    </slot>
					</div>
				</div>
			</div>
		</div>
	</transition>
</div>
</template>
	
<script>


export default {

  props: {
    showModal: {
      type: Boolean,
      default: false
    }
  },
  data(){
  	return{
     show:false,
  	}
  },
  methods: {
    closeModal() {
      this.show = false;
      this.$emit('close');  
    },

  },
  watch:{
    showModal(val) {
      this.show = val; 
       
    },
    show(newVal) {
          this.$nextTick(() => {
              if (newVal) {
              document.querySelector('.modal-container').style.transform = 'translateY(0)';
               } else {
              document.querySelector('.modal-container').style.transform = 'translateY(-100%)';
              }
             });
        }
   }
};
</script>
<style>
	.modal-mask{
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, .5);
  display: table;
  transition: opacity .3s ease;
}
.modal-mask{
  z-index: 99999999999;
}
.modal-wrapper{
    display: table-cell;
  vertical-align: middle;
}

.modal-container{
  width: 50%;
  margin: 0px auto;
  padding: 20px 30px;
  background-color: #fff;
  border-radius: 2px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
  transition: all .3s ease;
  font-family: Helvetica, Arial, sans-serif;
  transform: translateY(-100%);
  transition: transform .3s ease-out;
}
</style>



