<template>
  <div class="profile-list">
    <div class="profile-list__header flex-grid middle">
      <div class="box med-1of2">
        <h3>{{lang.reference_section.section_title}}</h3>
      </div>
      <div class="box med-1of2">
        <button
          class="profile-list__add-element-trigger"
          type="button"
          v-on:click="addItem"
        >{{lang.reference_section.add_button_label}}</button>
      </div>
      <div class="box full">
        <p>{{lang.reference_section.section_description}}</p>
      </div>
    </div>
    <div class="profile-element-list">
      <div v-if="references.length == 0" class="profile-null active">
        <p>{{lang.reference_section.null_copy}}</p>
      </div>
      <Reference
        v-for="reference in references"
        v-bind:key="reference.id"
        v-bind:reference="reference"
        v-bind:lang="langReference"
        v-bind:title="reference.name ? reference.name : langReference.new_reference_title"
      ></Reference>
    </div>
  </div>
</template>

<script>
import Reference from "./Reference";
export default {
  props: {
    lang: {
      type: Object,
      required: true
      //validator:
    },
    langReference: {
      type: Object,
      required: true
    },
    references: {
      type: Array,
      required: true
    }
  },
  //   data: {
  //     references: initialReferences //TODO: It should store a deep copy
  //   },
  components: {
    Reference
  },
  methods: {
    nextId: function(objs) {
      const ids = objs.map(x => x.id);
      const maxReducer = (a, b) => Math.max(a, b);
      return ids.reduce(maxReducer, 0) + 1;
    },
    createEmptyReference: function(id) {
      return {
        id: id,
        name: "",
        email: "",
        relationship_id: null,
        description: ""
      };
    },
    addItem: function(event) {
      console.log("Add Item");
      const nextId = this.nextId(this.references);
      //this.references.push(this.createEmptyReference(nextId));
    }
  },
  mounted() {
    console.log("ReferenceList mounted.");
  }
};
</script>
