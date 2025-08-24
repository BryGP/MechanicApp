<template>
  <div class="card">
    <h2>{{ title }}</h2>
    <p>{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  description: String,
});
</script>

<style scoped>
.card {
  padding: 1rem;
  border-radius: 8px;
  color: #fff;
  background-color: #f57c00;
}
</style>