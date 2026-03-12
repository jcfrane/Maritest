<script setup lang="ts">
defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <img
        src="/maritest_logo.png"
        alt="Maritest"
        class="object-contain"
        v-bind="$attrs"
    />
</template>
