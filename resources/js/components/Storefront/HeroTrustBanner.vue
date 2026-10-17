<script setup lang="ts">
import { ShieldCheck, Snowflake, UtensilsCrossed, Clock } from 'lucide-vue-next';

defineProps<{
    totalCutsCount?: number;
}>();
</script>

<template>
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-stone-900 via-rose-950 to-stone-900 p-8 text-white shadow-xl md:p-12">
        <div class="relative z-10 max-w-3xl space-y-6">
            <div class="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-950/60 px-3.5 py-1 text-xs font-semibold tracking-wide text-rose-300">
                <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                100% FARM FRESH • NEVER FROZEN • DAILY 4:00 AM CUTS
            </div>

            <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl sm:leading-tight">
                Freshly Cut Prime Meats,
                <span class="block text-rose-400 font-serif italic font-normal">Custom Butchered for Your Table.</span>
            </h1>

            <p class="text-base text-stone-300 sm:text-lg">
                Choose your cut, tell our butchers how thick or thin to slice it, and receive cold-chain temperature-controlled delivery directly to your door today.
            </p>

            <div class="flex flex-wrap gap-4 pt-2">
                <a
                    href="#catalog-section"
                    class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg transition hover:bg-rose-500 active:scale-95"
                >
                    Browse Today's Fresh Cuts
                </a>
                <div class="flex items-center gap-2 text-xs text-stone-300">
                    <ShieldCheck class="h-4 w-4 text-emerald-400" />
                    <span>NMIS Certified Quality</span>
                </div>
            </div>
        </div>

        <!-- 3 Trust Value Pillars -->
        <div class="relative z-10 mt-10 grid grid-cols-1 gap-4 border-t border-stone-800/80 pt-8 sm:grid-cols-3">
            <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 backdrop-blur-sm border border-white/10">
                <div class="rounded-lg bg-cyan-500/20 p-2 text-cyan-300">
                    <Snowflake class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Cold-Chain Guaranteed</h3>
                    <p class="text-xs text-stone-400">Packed with food-grade ice gels and insulated thermal liners.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 backdrop-blur-sm border border-white/10">
                <div class="rounded-lg bg-rose-500/20 p-2 text-rose-300">
                    <UtensilsCrossed class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Free Custom Butchery</h3>
                    <p class="text-xs text-stone-400">Sliced, cubed, or scored to your exact culinary recipe note.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl bg-white/5 p-4 backdrop-blur-sm border border-white/10">
                <div class="rounded-lg bg-amber-500/20 p-2 text-amber-300">
                    <Clock class="h-5 w-5" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Scheduled Dispatch</h3>
                    <p class="text-xs text-stone-400">Choose morning or dinner delivery slots or same-day pickup.</p>
                </div>
            </div>
        </div>
    </section>
</template>
