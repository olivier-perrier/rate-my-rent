<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import H2 from '@/Components/Title/H2.vue';

const props = defineProps({
    simulations: Object,
})

</script>

<template>
    <Head title="Mes simulations" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes simulations</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="bg-white overflow-hidden rounded-lg p-6">

                    <div class="my-4 text-center">
                        <H2>Selectionnez une simulation à charger.</H2>
                    </div>

                    <div class="flex flex-wrap gap-4">

                        <div v-for="simulation in props.simulations" :key="simulation.id"
                            class="border-2 rounded-lg hover:bg-green-100">
                            <Link :href="route('simulations.edit', simulation)">
                            <div class="p-4  cursor-pointer text-center">
                                {{ simulation.name ?? simulation.id }} <br>
                                <span class="text-xs text-gray-300">
                                    le {{ new Date(simulation.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            </Link>
                        </div>

                    </div>

                    <div v-if="!$page.props.can.createSimulation">
                        <div class="mt-4 rounded-lg border-2 p-4 text-center italic border-orange-300">
                            Vous avez atteint le nombre maximum de simulations que vous pouvez créer sur votre compte
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </AppLayout>
</template>
