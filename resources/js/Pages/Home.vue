<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';


</script>

<template>
    <Head title="Accueil" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rate my rent</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="bg-white overflow-hidden rounded-lg p-6">

                    <div class="flex justify-center mb-8">
                        <ApplicationLogo class="h-24" />
                    </div>

                    <div class="px-4 py-3 rounded bg-green-200 text-center">
                        Rate my rent est un outil qui vous permet de faire une simulation d'achat et location immobilier.
                        <br>
                        Rate my rent vous donne un détail très précis des différentes dépenses en crédit, charges et
                        économies réalisées. <br>
                        Vous pouvez aussi visualiser vos données sous forme graphique afin d'avoir encore une meilleure
                        compréhension de votre projet. <br>
                        <br>
                        Enfin, sauvegardez vos simulations pour venir les compléter plus tard.

                    </div>

                    <div class="text-center mt-8">
                        <PrimaryButton>
                            <Link :href="route('simulations.create')">
                            Faire une simulation
                            </Link>
                        </PrimaryButton>
                        <div v-if="!$page.props.auth.user" class="mt-2 text-gray-600">
                            <div class="text-sm">
                                ou
                            </div>
                            <div class="text-gray-600 hover:underline">
                                <Link :href="route('login')">
                                Se connecter
                                </Link>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
