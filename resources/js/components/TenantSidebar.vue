<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    ClipboardList,
    FileText,
    LayoutGrid,
    User,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/tenant';
import { index as candidatesIndex } from '@/routes/tenant/candidates';
import { index as usersIndex } from '@/routes/tenant/users';
import type { NavItem } from '@/types';

const page = usePage();
const tenant = page.props.currentTenant;
const slug = tenant?.slug ?? '';

const platformNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url(slug),
        icon: LayoutGrid,
    },
    {
        title: 'Candidates',
        href: candidatesIndex.url(slug),
        icon: User,
    },
    {
        title: 'Questionnaires',
        href: `/t/${slug}/questionnaires`,
        icon: ClipboardList,
    },
    {
        title: 'Schedules',
        href: `/t/${slug}/schedules`,
        icon: CalendarDays,
    },
    {
        title: 'Submissions',
        href: `/t/${slug}/submissions`,
        icon: FileText,
    },
];

const internalNavItems: NavItem[] = [
    {
        title: 'Users',
        href: usersIndex.url(slug),
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard.url(slug)">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="platformNavItems" />
            <NavMain label="Internal" :items="internalNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
