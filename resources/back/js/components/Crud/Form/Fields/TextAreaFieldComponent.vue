<template>
  <div class="form-control w-full mb-2">
    <label v-if="label" class="label">
      <span class="label-text">{{ label }}</span>
      <span v-if="tooltip" class="label-text-alt" :title="tooltip">(?)</span>
    </label>
    <textarea
      :name="name"
      :rows="rows"
      class="textarea textarea-bordered w-full"
      :class="fieldClass"
      v-model="current_value"
    ></textarea>
    <div class="text-red-500 text-xs font-semibold">
      <p v-for="(error, index) in errors[name]" :key="index">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "TextAreaField",
  props: {
    name: String,
    tooltip: String,
    extra: JSON,
    label: String,
    value: String,
    errors: JSON,
    submited: Boolean,
  },
  data() {
    return {
      current_value: this.value,
      rows: 4,
    };
  },
  computed: {
    fieldClass() {
      if (this.submited) {
        if (this.errors && this.errors[this.name]) {
          return "border-red-500";
        }
        return "border-green-500";
      }
      return "";
    }
  }
};
</script>
