<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement } from 'chart.js'
import Td from '@/Components/Table/Td.vue';
import Th from '@/Components/Table/Th.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import H2 from '@/Components/Title/H2.vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement)

const chartOptions = { responsive: true }

const props = defineProps({
    simulation: Object,
    auth: Object,
})

const form = reactive({
    name: props.simulation?.name || '',
    general: {
        inflation: props.simulation?.general?.inflation || 1.00
    },
    rent: {
        monthly: props.simulation?.rent?.monthly || 850,
        charges: props.simulation?.rent?.charges || 0
    },
    buy: {
        price: props.simulation?.buy?.price || 150000,
        notary: props.simulation?.buy?.notary || 15000,
        charges: props.simulation?.buy?.charges || 200,
        taxes: props.simulation?.buy?.taxes || 1000,

        contribution: props.simulation?.buy?.contribution || 50000,
        refund: props.simulation?.buy?.refund || 840,

        rate: props.simulation?.buy?.rate || 4.2,
        duration: props.simulation?.buy?.duration || 0
    },
})

const buyRefundYear = form.buy.refund * 12;

const inflationRate = computed(() => {
    return 1 + form.general.inflation / 100;
})

let buyMaxPrice = form.buy.price;

const rents = () => {
    const rents = [];
    rents[0] = {
        year: 1,
        charges: form.rent.charges * 12,
        rent: form.rent.monthly * 12,
        total: form.rent.charges * 12 + form.rent.monthly * 12
    };

    for (let i = 1; i < 25; i++) {
        const year = i + 1;
        rents[i] = {
            year: year,
            charges: rents[i - 1].charges + form.rent.charges * 12 * inflationRate.value,
            rent: rents[i - 1].rent + form.rent.monthly * 12 * inflationRate.value,
        }
        rents[i].total = rents[i].charges + rents[i].rent;
    }

    return rents;
}

const mortages = () => {
    const mortages = [];
    mortages[0] = {
        year: 1,
        charges: form.buy.charges * 12,
        repayment: form.buy.refund * 12,
        taxe: Number(form.buy.taxes),
        interests: form.buy.price * form.buy.rate / 100,
        remains: form.buy.price,
        notary: Number(form.buy.notary),
    };
    mortages[0].refund = mortages[0].repayment - mortages[0].interests;
    mortages[0].remains = form.buy.price - mortages[0].refund;
    mortages[0].paid = mortages[0].notary + mortages[0].charges + mortages[0].taxe + mortages[0].repayment;
    mortages[0].loss = mortages[0].notary + mortages[0].interests + mortages[0].charges + mortages[0].taxe;
    mortages[0].totalLoss = mortages[0].loss;
    mortages[0].capital = mortages[0].refund;

    let i = 0;
    while (mortages[i].remains > 0 && i < 24) {
        i++;
        const year = i + 1;
        mortages[i] = {
            year: year,
            remains: Math.max(mortages[i - 1].remains - mortages[i - 1].refund, 0),
            charges: mortages[i - 1].charges * inflationRate.value,
            totalCharges: mortages[i - 1].charges + Number(form.buy.charges) * 12 * inflationRate.value,
            taxe: mortages[i - 1].taxe * inflationRate.value,
            totalTaxe: mortages[i - 1].taxe + Number(form.buy.taxes) * inflationRate.value,
            repayment: mortages[i - 1].repayment + Number(form.buy.refund) * 12,
        }
        mortages[i].interests = Math.max(mortages[i].remains * form.buy.rate / 100, 0);
        mortages[i].refund = Math.min(mortages[0].repayment - mortages[i].interests, mortages[i - 1].remains);
        mortages[i].paid = mortages[i].charges + mortages[i].taxe + mortages[i].repayment;
        mortages[i].loss = mortages[i].interests + mortages[i].charges + mortages[i].taxe;
        mortages[i].total = mortages[i].charges + mortages[i].repayment + mortages[i].taxe + mortages[i].notary;
        mortages[i].totalLoss = mortages[i - 1].totalLoss + mortages[i].loss;
        mortages[i].capital = mortages[i - 1].capital + mortages[i].refund;


        if (mortages[i].remains == 0) {
            form.buy.duration = year;
        }

        if (year == 25) {
            buyMaxPrice = form.buy.price;
        } else {
            buyMaxPrice = Infinity;
        }

    }

    return mortages;
}

const chartData = computed(() => {
    return {
        labels: Array.from(Array(form.buy.duration).keys()).map(i => i + 1),
        datasets: [{
            label: 'Location',
            data: rents()?.map(rent => rent.total),
            backgroundColor: '#f87979',
            borderColor: '#f87979'
        },
        {
            label: 'Achat',
            data: mortages()?.map(rent => rent.totalLoss),
            backgroundColor: '#9BD0F5',
            borderColor: '#9BD0F5'
        }]
    };
})

const formatter = new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0
});


function submit() {
    if (props.simulation)
        router.put('/simulations/' + props.simulation.id, form)
    else
        router.post('/simulations', form)
}

</script>

<template>
    <Head title="Simulation" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ props.simulation ? 'Simulation ' + props.simulation?.name : 'Faire une simulation' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="bg-white overflow-hidden rounded-lg p-6">

                    <div class="px-4 py-3 rounded bg-green-200">
                        Simulation pour un emprunt de {{ formatter.format(form.buy.price) }} sur {{ form.buy.duration }} ans
                        avec un apport de {{ formatter.format(form.buy.contribution) }}
                        (capacité d'achat {{ formatter.format(Number(form.buy.price) + Number(form.buy.contribution)) }})
                    </div>

                    <form @submit.prevent="submit">

                        <!-- Save -->
                        <div class="flex flex-wrap justify-between mt-4 border-2 rounded-lg px-4 py-4 gap-4">

                            <TextInput id="name" type="text" class="w-full md:w-min" v-model="form.name"
                                placeholder="Nom de la simulation" />
                        
                            <div class="flex items-center justify-center">
                                <div v-if="auth.user" class="flex flex-wrap gap-4 items-center justify-center">
                                    <Link v-if="props.simulation" :href="route('simulations.destroy', props.simulation.id)"
                                        method="delete" as="button" class="text-sm text-red-500 hover:underline">
                                    Supprimer
                                    </Link>
                                    <PrimaryButton type="submit">
                                        Sauvegarder la simulation
                                    </PrimaryButton>
                                </div>
                                <div v-else class="flex flex-wrap justify-center items-center gap-2">
                                    <span class="pr-2 text-sm text-gray-500 text-center">Connectez vous pour sauvegarder
                                        votre
                                        simulation</span>
                                    <Link :href="route('login')">
                                    <PrimaryButton type="button">
                                        Se connecter
                                    </PrimaryButton>
                                    </Link>
                                </div>
                            </div>

                        </div>

                        <!-- General form -->
                        <div class="mt-4 px-4 py-4 rounded-lg border-2">
                            <H2 class="text-center">Général</H2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="inflation" value="Inflation" />
                                    <TextInput id="inflation" type="number" class="mt-1 block w-full"
                                        v-model="form.general.inflation" placeholder="1.00 %" min="0" max="10"
                                        step="0.01" />
                                </div>
                                <div>
                                    <InputLabel for="buyRate" value="Taux d'intérêt (avec assurance)" />
                                    <TextInput id="buyRate" type="number" class="mt-1 block w-full" v-model="form.buy.rate"
                                        placeholder="4.00 %" min="0" max="10" step="0.01" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">

                            <!-- Location form -->
                            <div class="px-4 py-4 rounded-lg border-2 h-full">
                                <H2 class="text-center">Location</H2>
                                <div>
                                    <InputLabel for="rent" value="Loyer (mois)" />
                                    <TextInput id="rent" type="number" class="mt-1 block w-full" v-model="form.rent.monthly"
                                        placeholder="800 €" min="0" />
                                </div>
                                <div>
                                    <InputLabel for="rentCharge" value="Charges (mois)" />
                                    <TextInput id="rentCharge" type="number" class="mt-1 block w-full"
                                        v-model="form.rent.charges" placeholder="200 €" min="0" />
                                </div>
                            </div>

                            <!-- Buy form -->
                            <div class="px-4 py-4 rounded-lg border-2 h-full">
                                <H2 class="text-center">Achat</H2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="buyPrice"
                                            :value="'Prix d\'achat' + (form.buy.price == buyMaxPrice ? ' (montant maximum)' : '')" />
                                        <TextInput id="buyPrice" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.price" placeholder="200 000 €" min="0" :max="buyMaxPrice"
                                            step="1000" />
                                    </div>
                                    <div>
                                        <InputLabel for="buyNotary" value="Frais notaire" />
                                        <TextInput id="buyNotary" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.notary" placeholder="15 000 €" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="buyContribution" value="Apport" />
                                        <TextInput id="buyContribution" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.contribution" placeholder="20 000 €" min="0" step="1000" />
                                    </div>
                                    <div>
                                        <InputLabel for="buyMortage" value="Remboursement" />
                                        <TextInput id="buyMortage" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.refund" placeholder="850 €" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="buyCharge" value="Charges (mois)" />
                                        <TextInput id="buyCharge" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.charges" placeholder="200 €" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="buyTaxe" value="Taxe foncière (an)" />
                                        <TextInput id="buyTaxe" type="number" class="mt-1 block w-full"
                                            v-model="form.buy.taxes" placeholder="1000 €" min="0" />
                                    </div>
                                </div>

                            </div>

                        </div>

                    </form>

                    <!-- Table rent -->
                    <div class="mt-4 px-4 py-4 rounded-lg border-2">

                        <div x-data="{show : false}">
                            <H2 x-on:click="show = !show" class="cursor-pointer">
                                Location (cliquez pour afficher les détails)
                            </H2>
                            <div class="mt-2 p-4 border rounded" x-show="show" x-transition>
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <Th>Année</Th>
                                            <Th>Loyer</Th>
                                            <Th>Charges</Th>
                                            <Th>Total</Th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="rent in rents()">
                                            <Td>{{ rent.year }}</Td>
                                            <Td>{{ formatter.format(rent.rent) }}</Td>
                                            <Td>{{ formatter.format(rent.charges) }}</Td>
                                            <Td>{{ formatter.format(rent.total) }}</Td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Table buy -->
                    <div class="mt-4 px-4 py-4 rounded-lg border-2">
                        <div x-data="{show : false}">
                            <H2 x-on:click="show = !show" class="cursor-pointer">
                                Détails du coût d'un achat
                            </H2>
                            <div class="mt-2 p-4 border rounded overflow-x-auto" x-show="show" x-transition>
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <Th>Année</Th>
                                            <Th>Mensualités</Th>
                                            <Th>Interets</Th>
                                            <Th>Remboursement</Th>
                                            <Th>Reste à payer</Th>
                                            <Th>Chages</Th>
                                            <Th>Taxe</Th>
                                            <Th>Paiement</Th>
                                            <Th>Perte</Th>
                                            <Th>Total perte</Th>
                                            <Th>Capital</Th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="(mortage) in mortages()">
                                            <Td>{{ mortage.year }}</Td>
                                            <Td>{{ formatter.format(buyRefundYear) }}</Td>
                                            <Td>{{ formatter.format(mortage.interests) }}</Td>
                                            <Td>{{ formatter.format(mortage.refund) }}</Td>
                                            <Td>{{ formatter.format(mortage.remains) }}</Td>
                                            <Td>{{ formatter.format(mortage.charges) }}</Td>
                                            <Td>{{ formatter.format(mortage.taxe) }}</Td>
                                            <Td>{{ formatter.format(mortage.paid) }}</Td>
                                            <Td>{{ formatter.format(mortage.loss) }}</Td>
                                            <Td>{{ formatter.format(mortage.totalLoss) }}</Td>
                                            <Td>{{ formatter.format(mortage.capital) }}</Td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>

                    </div>


                    <!-- Graphs -->
                    <div class="mt-8 px-2 w-full">
                        <h2 class="text-lg">Represenation du coût en euro d'un achat et d'une location sur les années</h2>

                        <div class="flex justify-center">
                            <Line id="my-chart-id" :options="chartOptions" :data="chartData" />
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </AppLayout>
</template>
