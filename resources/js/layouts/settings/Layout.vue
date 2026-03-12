<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PagePanel from '@/components/page/PagePanel.vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Password',
        href: editPassword(),
    },
    {
        title: 'Two-factor auth',
        href: show(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <aside class="w-full max-w-xl xl:w-64 xl:shrink-0">
            <PagePanel body-class="p-2">
                <nav class="flex flex-col gap-1" aria-label="Settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start rounded-xl',
                            { 'bg-muted': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            <component
                                v-if="item.icon"
                                :is="item.icon"
                                class="size-4"
                            />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </PagePanel>
        </aside>

        <div class="min-w-0 flex-1">
            <section class="space-y-6">
                <slot />
            </section>
        </div>
    </div>
</template>
