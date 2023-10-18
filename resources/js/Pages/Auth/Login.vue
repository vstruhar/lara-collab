<script setup>
import {Head} from '@inertiajs/vue3';
import {useForm} from 'laravel-precognition-vue-inertia';

const form = useForm('post', '/login', {
    email: '',
    password: '',
});

const submit = () => form.submit({
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Login"/>

    <h1>Login</h1>

    <form @submit.prevent="submit">
        <input type="text" v-model="form.email">
        <div v-if="form.invalid('email')">{{ form.errors.email }}</div>

        <input type="password" v-model="form.password">
        <div v-if="form.invalid('password')">{{ form.errors.password }}</div>

        <div v-if="form.validating">Validating...</div>

        <button>Login</button>
    </form>
</template>

<style scoped>
h1 {
    font-size: 3rem;
}
</style>
