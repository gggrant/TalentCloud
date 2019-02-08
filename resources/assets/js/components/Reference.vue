<template>
  <div class="profile-element accordion reference modal-target-object" v-bind:class="{active: isActive}">
    <button
      v-bind:aria-expanded="isActive"
      class="accordion-trigger"
      tabindex="0"
      type="button"
      v-on:click="toggleActive"
    >
      <span class="accordion-title">{{title}}</span>
    </button>
    <div v-bind:aria-hidden="!isActive" class="accordion-content">
      <form action="/" method="POST">
        <div class="form__wrapper">
          <div class="form-error box"/>
          <div class="flex-grid">
            <div class="box med-1of2">
              <div class="form__input- wrapper--float" v-bind:class="{active: name}">
                <label class="form__label" :for="nameId">{{lang.name_label}}</label>
                <input
                  class="form__input"
                  :id="nameId"
                  type="text"
                  name="name"
                  required
                  v-model="name"
                >
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    lang: {
      type: Object,
      required: true
      //validator:
    },
    reference: {
      type: Object,
      required: true
    },
    title: String
  },
  data() {
    return {
      isActive: false,
      name: "",
      nameId: null
    };
  },
  computed: {
    triggerClass: function() {
      return (
        "profile-element accordion reference modal-target-object" +
        (this.isActive ? " active" : "")
      );
    }
  },
  methods: {
    toggleActive: function() {
      this.isActive = !this.isActive;
      console.log('isActive toggled to ' + this.isActive);
    }
  },
  mounted() {
    this.nameId = "name" + this._uid;
  }
};
</script>
