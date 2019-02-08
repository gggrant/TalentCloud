<template>
  <div class="profile-list">
    <div class="profile-list__header flex-grid middle">
      <div class="box med-1of2">
        <h3>{this.props.lang.reference_section.section_title}</h3>
      </div>
      <div class="box med-1of2">
        <button
          class="profile-list__add-element-trigger"
          type="button"
          onClick="{this.handleAddItemClick}"
        >{this.props.lang.reference_section.add_button_label}</button>
      </div>
      <div class="box full">
        <p>
          {
          this.props.lang.reference_section
          .section_description
          }
        </p>
      </div>
    </div>
    <div class="profile-element-list">{content}</div>
  </div>
</template>

<script>
export default {

  mounted() {
    console.log("Component mounted.");

  }
};
</script>
